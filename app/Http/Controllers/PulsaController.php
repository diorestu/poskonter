<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\Product;
use App\Models\Provider;
use App\Models\PulsaSale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PulsaController extends Controller
{
    public function index(): View
    {
        return view('pulsa.index');
    }

    public function history(): View
    {
        return view('pulsa.history');
    }

    public function walletSettings(): View
    {
        return view('pulsa.wallet-settings');
    }

    public function dashboard(): JsonResponse
    {
        $products = Product::query()
            ->with(['provider:id,name', 'provider.wallets:id,name', 'sourceWallets:id,name'])
            ->where('product_type', 'pulsa')
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => $this->serializeProduct($product));

        $providers = Provider::query()
            ->withCount(['electricProducts as products_count' => function ($query): void {
                $query->where('is_active', true);
            }])
            ->with('wallets:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn (Provider $provider) => [
                'id' => $provider->id,
                'name' => $provider->name,
                'products_count' => $provider->products_count,
                'wallet_ids' => $provider->wallets->pluck('id')->all(),
            ]);

        $wallets = Wallet::query()
            ->with(['electricProviders' => function ($query): void {
                $query
                    ->withCount(['electricProducts as products_count' => function ($productsQuery): void {
                        $productsQuery->where('is_active', true);
                    }])
                    ->orderBy('name')
                    ->select('providers.id', 'providers.name');
            }])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'balance', 'is_active'])
            ->map(fn (Wallet $wallet) => $this->serializeWallet($wallet));

        $recentSales = PulsaSale::query()
            ->with(['product:id,name,variant,category', 'wallet:id,name'])
            ->latest('sale_date')
            ->latest('id')
            ->limit(15)
            ->get();

        return response()->json([
            'products' => $products,
            'providers' => $providers,
            'wallets' => $wallets,
            'sales' => $recentSales,
            'summary' => [
                'wallet_balance' => (float) Wallet::where('is_active', true)->sum('balance'),
                'pending_sales' => PulsaSale::where('status', 'pending')->count(),
                'today_total' => (float) PulsaSale::whereDate('sale_date', today())->sum('price'),
            ],
        ]);
    }

    public function storeWallet(Request $request): JsonResponse
    {
        $providerIds = $this->normalizeProviderIds($request);
        if ($providerIds === []) {
            $providerIds = Provider::query()
                ->whereHas('electricProducts', fn ($query) => $query->where('is_active', true))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }
        $request->merge(['provider_ids' => $providerIds]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:wallets,name'],
            'balance' => ['nullable', 'numeric', 'min:0'],
            'provider_ids' => ['nullable', 'array'],
            'provider_ids.*' => [
                'integer',
                'distinct',
                'exists:providers,id',
            ],
        ]);

        $wallet = DB::transaction(function () use ($validated, $providerIds): Wallet {
            $wallet = Wallet::create([
                'name' => $validated['name'],
                'balance' => $validated['balance'] ?? 0,
                'is_active' => true,
            ]);

            $wallet->electricProviders()->sync($providerIds);

            return $wallet->load(['electricProviders' => function ($query): void {
                $query
                    ->withCount(['electricProducts as products_count' => function ($productsQuery): void {
                        $productsQuery->where('is_active', true);
                    }])
                    ->orderBy('name')
                    ->select('providers.id', 'providers.name');
            }]);
        });

        return response()->json([
            'message' => 'Dompet saldo berhasil ditambahkan.',
            'wallet' => $this->serializeWallet($wallet),
        ], 201);
    }

    public function topupWallet(Request $request, Wallet $wallet): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'type' => ['nullable', 'string', 'in:add,reduce'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $type = $validated['type'] ?? 'add';
        $amount = (float) $validated['amount'];

        if ($type === 'reduce') {
            if ((float) $wallet->balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo dompet tidak mencukupi untuk dikurangi sebesar Rp ' . number_format($amount, 0, ',', '.') . '. Saldo saat ini: Rp ' . number_format((float) $wallet->balance, 0, ',', '.'),
                ]);
            }
            $wallet->decrement('balance', $amount);
            $message = 'Saldo dompet berhasil dikurangi sebesar Rp ' . number_format($amount, 0, ',', '.') . '.';
        } else {
            $wallet->increment('balance', $amount);
            $message = 'Saldo dompet berhasil ditambahkan sebesar Rp ' . number_format($amount, 0, ',', '.') . '.';
        }

        return response()->json([
            'message' => $message,
            'wallet' => $this->serializeWallet($wallet->fresh(['electricProviders' => function ($query): void {
                $query
                    ->withCount(['electricProducts as products_count' => function ($productsQuery): void {
                        $productsQuery->where('is_active', true);
                    }])
                    ->orderBy('name')
                    ->select('providers.id', 'providers.name');
            }])),
        ]);
    }

    public function updateWallet(Request $request, Wallet $wallet): JsonResponse
    {
        $providerIds = $this->normalizeProviderIds($request);
        $request->merge(['provider_ids' => $providerIds]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('wallets', 'name')->ignore($wallet->id)],
            'provider_ids' => ['nullable', 'array'],
            'provider_ids.*' => [
                'integer',
                'distinct',
                'exists:providers,id',
            ],
        ]);

        DB::transaction(function () use ($wallet, $validated, $providerIds): void {
            $wallet->update([
                'name' => $validated['name'],
            ]);

            $wallet->electricProviders()->sync($providerIds);
        });

        return response()->json([
            'message' => 'Pengaturan dompet berhasil diperbarui.',
            'wallet' => $this->serializeWallet($wallet->fresh(['electricProviders' => function ($query): void {
                $query
                    ->withCount(['electricProducts as products_count' => function ($productsQuery): void {
                        $productsQuery->where('is_active', true);
                    }])
                    ->orderBy('name')
                    ->select('providers.id', 'providers.name');
            }])),
        ]);
    }

    public function storeProvider(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:providers,name'],
        ]);

        $provider = Provider::create([
            'name' => trim($validated['name']),
        ]);

        return response()->json([
            'message' => 'Provider berhasil ditambahkan.',
            'provider' => [
                'id' => $provider->id,
                'name' => $provider->name,
                'products_count' => 0,
                'wallet_ids' => [],
            ],
        ], 201);
    }

    public function storeSale(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'wallet_id' => ['required', 'integer', 'exists:wallets,id'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'target_number' => ['required', 'string', 'max:30'],
            'shift' => ['nullable', 'string', 'in:pagi,sore'],
            'payment_method' => ['required', 'string', 'in:cash,qris,transfer,debit,utang'],
            'cash_paid' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $sale = DB::transaction(function () use ($validated, $request): PulsaSale {
            $product = Product::query()->lockForUpdate()->findOrFail($validated['product_id']);
            $wallet = Wallet::query()->lockForUpdate()->findOrFail($validated['wallet_id']);

            if (! $product->is_active || ! $product->isPulsa()) {
                throw ValidationException::withMessages([
                    'product_id' => 'Produk pulsa tidak tersedia.',
                ]);
            }

            $sourceWalletIds = $product->provider_id
                ? ($product->provider()->first()?->wallets()->pluck('wallets.id') ?? collect())
                : $product->sourceWallets()->pluck('wallets.id');

            if ($sourceWalletIds->isEmpty()) {
                throw ValidationException::withMessages([
                    'product_id' => 'Produk pulsa belum memiliki sumber dompet saldo.',
                ]);
            }

            if (! $sourceWalletIds->contains($wallet->id)) {
                throw ValidationException::withMessages([
                    'wallet_id' => 'Dompet yang dipilih tidak terdaftar sebagai sumber saldo produk ini.',
                ]);
            }

            if (! $wallet->is_active) {
                throw ValidationException::withMessages([
                    'wallet_id' => 'Dompet yang dipilih tidak aktif.',
                ]);
            }

            $price = (float) $product->price;
            $costAmount = (float) $product->cost_price;
            $isDebt = ($validated['payment_method'] ?? 'cash') === 'utang';
            $cashPaid = (float) ($validated['cash_paid'] ?? 0);

            if ($wallet->balance < $costAmount) {
                throw ValidationException::withMessages([
                    'wallet_id' => 'Saldo dompet tidak mencukupi.',
                ]);
            }

            if (! $isDebt && $cashPaid < $price) {
                throw ValidationException::withMessages([
                    'cash_paid' => 'Pembayaran customer kurang dari harga jual.',
                ]);
            }

            $wallet->decrement('balance', $costAmount);

            $sale = PulsaSale::create([
                'reference_number' => 'PLS-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'sale_date' => now(),
                'product_id' => $product->id,
                'wallet_id' => $wallet->id,
                'customer_name' => $validated['customer_name'] ?? null,
                'target_number' => $validated['target_number'],
                'shift' => $validated['shift'] ?? 'pagi',
                'payment_method' => $validated['payment_method'],
                'price' => $price,
                'cost_amount' => $costAmount,
                'cash_paid' => $cashPaid,
                'change_amount' => $isDebt ? 0 : ($cashPaid - $price),
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($isDebt) {
                Debt::create([
                    'reference_number' => 'DEBT-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                    'debt_date' => $sale->sale_date,
                    'customer_name' => $sale->customer_name ?: ($sale->target_number ?: 'Pelanggan Pulsa'),
                    'customer_phone' => $sale->target_number,
                    'amount' => $price,
                    'paid_amount' => $cashPaid,
                    'status' => $cashPaid >= $price ? 'paid' : ($cashPaid > 0 ? 'partial' : 'unpaid'),
                    'shift' => $sale->shift,
                    'pulsa_sale_id' => $sale->id,
                    'description' => 'Pulsa: ' . ($product->name ?? 'Pulsa') . ' (' . $sale->target_number . ')',
                    'notes' => $sale->notes,
                    'created_by' => $request->user()?->id,
                ]);
            }

            return $sale;
        });

        return response()->json([
            'message' => 'Penjualan pulsa berhasil disimpan.',
            'sale' => $sale->load(['product:id,name,variant,category', 'wallet:id,name']),
        ], 201);
    }

    public function markSuccess(PulsaSale $pulsaSale): JsonResponse
    {
        if ($pulsaSale->status === 'cancelled') {
            throw ValidationException::withMessages([
                'status' => 'Transaksi yang sudah dibatalkan tidak bisa diubah menjadi sukses.',
            ]);
        }

        if ($pulsaSale->status !== 'success') {
            $pulsaSale->update(['status' => 'success']);
        }

        return response()->json([
            'message' => 'Transaksi pulsa ditandai sukses.',
            'sale' => $pulsaSale->fresh(['product:id,name,variant,category', 'wallet:id,name']),
        ]);
    }

    public function cancelSale(Request $request, PulsaSale $pulsaSale): JsonResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        if ($pulsaSale->status === 'cancelled') {
            throw ValidationException::withMessages([
                'status' => 'Transaksi ini sudah dibatalkan sebelumnya.',
            ]);
        }

        $message = DB::transaction(function () use ($pulsaSale, $validated): string {
            $refundedWallet = null;

            // Jika produk elektrik atau ada wallet_id terkait, kembalikan saldo ke dompet
            if ($pulsaSale->wallet_id && (float) $pulsaSale->cost_amount > 0) {
                $wallet = Wallet::query()->lockForUpdate()->find($pulsaSale->wallet_id);
                if ($wallet) {
                    $wallet->increment('balance', $pulsaSale->cost_amount);
                    $refundedWallet = $wallet;
                }
            }

            // Jika produk fisik/voucher yang memotong stok, kembalikan stok
            $product = Product::query()->lockForUpdate()->find($pulsaSale->product_id);
            $refUpper = strtoupper(trim($pulsaSale->reference_number ?? ''));
            $isVoucher = str_starts_with($refUpper, 'VCR')
                || ! empty($pulsaSale->serial_number)
                || ($product && ($product->product_type === 'voucher' || $product->product_type === 'regular' || $product->isVoucher() || ! $product->isPulsa()));

            $restoredStock = false;
            if ($product && $isVoucher) {
                $product->increment('stock', 1);
                $restoredStock = true;

                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'adjustment',
                    'quantity' => 1,
                    'serial_number' => $pulsaSale->serial_number,
                    'cost_price' => $pulsaSale->cost_amount,
                    'note' => 'Pembatalan Transaksi ' . $pulsaSale->reference_number,
                ]);
            }

            // Jika transaksi terikat dengan catatan utang, batalkan utang terkait
            Debt::where('pulsa_sale_id', $pulsaSale->id)->update([
                'status' => 'cancelled',
            ]);

            $note = trim(implode(' | ', array_filter([
                $pulsaSale->notes,
                $validated['notes'] ?? 'Dibatalkan',
            ])));

            $pulsaSale->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'notes' => $note,
            ]);

            if ($restoredStock && $refundedWallet) {
                return 'Transaksi ' . $pulsaSale->reference_number . ' berhasil dibatalkan. Stok voucher telah dikembalikan (+1) dan saldo modal Rp ' . number_format($pulsaSale->cost_amount, 0, ',', '.') . ' telah dikembalikan ke ' . $refundedWallet->name . '.';
            }

            if ($restoredStock) {
                return 'Transaksi ' . $pulsaSale->reference_number . ' berhasil dibatalkan dan 1 pcs stok voucher telah dikembalikan.';
            }

            if ($refundedWallet) {
                return 'Transaksi ' . $pulsaSale->reference_number . ' dibatalkan. Saldo modal Rp ' . number_format($pulsaSale->cost_amount, 0, ',', '.') . ' telah dikembalikan ke ' . $refundedWallet->name . '.';
            }

            return 'Transaksi ' . $pulsaSale->reference_number . ' berhasil dibatalkan.';
        });

        return response()->json([
            'message' => $message,
            'sale' => $pulsaSale->fresh(['product:id,name,variant,category', 'wallet:id,name']),
        ]);
    }

    public function voucherIndex(): View
    {
        return view('pulsa.voucher');
    }

    public function voucherDashboard(): JsonResponse
    {
        $products = Product::query()
            ->with(['provider:id,name', 'provider.wallets:id,name', 'sourceWallets:id,name'])
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->where('product_type', 'voucher')
                    ->orWhere('category', 'like', '%voucher%')
                    ->orWhere('name', 'like', '%voucher%');
            })
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => $this->serializeProduct($product, true));

        $categories = $products
            ->map(fn ($product) => $product['category'] ?: ($product['provider'] ?: 'Voucher'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $wallets = Wallet::query()
            ->with(['electricProviders' => function ($query): void {
                $query
                    ->withCount(['electricProducts as products_count' => function ($productsQuery): void {
                        $productsQuery->where('is_active', true);
                    }])
                    ->orderBy('name')
                    ->select('providers.id', 'providers.name');
            }])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'balance', 'is_active'])
            ->map(fn (Wallet $wallet) => $this->serializeWallet($wallet));

        $recentSales = PulsaSale::query()
            ->with(['product:id,name,variant,category', 'wallet:id,name'])
            ->where('reference_number', 'like', 'VCR-%')
            ->latest('sale_date')
            ->latest('id')
            ->limit(10)
            ->get();

        return response()->json([
            'products' => $products,
            'categories' => $categories,
            'wallets' => $wallets,
            'sales' => $recentSales,
            'summary' => [
                'wallet_balance' => (float) Wallet::where('is_active', true)->sum('balance'),
                'today_voucher_total' => (float) PulsaSale::where('reference_number', 'like', 'VCR-%')
                    ->whereDate('sale_date', today())
                    ->where('status', '!=', 'cancelled')
                    ->sum('price'),
            ],
        ]);
    }

    public function storeVoucherSale(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'wallet_id' => ['nullable', 'integer', 'exists:wallets,id'],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'target_number' => ['nullable', 'string', 'max:30'],
            'shift' => ['nullable', 'string', 'in:pagi,sore'],
            'payment_method' => ['required', 'string', 'in:cash,qris,transfer,debit,utang'],
            'cash_paid' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $sale = DB::transaction(function () use ($validated, $request): PulsaSale {
            $product = Product::query()->lockForUpdate()->findOrFail($validated['product_id']);

            if (! $product->is_active) {
                throw ValidationException::withMessages([
                    'product_id' => 'Produk voucher tidak tersedia.',
                ]);
            }

            $price = (float) $product->price;
            $isDebt = ($validated['payment_method'] ?? 'cash') === 'utang';
            $cashPaid = (float) ($validated['cash_paid'] ?? 0);
            $serialNumber = ! empty($validated['serial_number']) ? trim($validated['serial_number']) : null;

            $costAmount = (float) $product->cost_price;
            if ($serialNumber !== null) {
                $matchedMovement = StockMovement::query()
                    ->where('product_id', $product->id)
                    ->whereIn('type', ['in', 'adjustment'])
                    ->where('serial_number', $serialNumber)
                    ->latest('id')
                    ->first();
                if ($matchedMovement && (float) $matchedMovement->cost_price > 0) {
                    $costAmount = (float) $matchedMovement->cost_price;
                }
            }

            if (! $isDebt && $cashPaid < $price) {
                throw ValidationException::withMessages([
                    'cash_paid' => 'Pembayaran customer kurang dari harga jual.',
                ]);
            }

            $referenceNumber = 'VCR-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));

            if ($product->product_type !== 'pulsa' || $product->stock > 0) {
                if ($product->stock > 0) {
                    $product->decrement('stock', 1);
                }

                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'sale',
                    'quantity' => 1,
                    'serial_number' => $serialNumber,
                    'cost_price' => $costAmount,
                    'note' => 'Penjualan Voucher ' . $referenceNumber,
                ]);
            }

            $walletId = null;
            if (! empty($validated['wallet_id'])) {
                $wallet = Wallet::query()->lockForUpdate()->findOrFail($validated['wallet_id']);
                if (! $wallet->is_active) {
                    throw ValidationException::withMessages([
                        'wallet_id' => 'Dompet yang dipilih tidak aktif.',
                    ]);
                }
                if ($wallet->balance < $costAmount) {
                    throw ValidationException::withMessages([
                        'wallet_id' => 'Saldo dompet tidak mencukupi.',
                    ]);
                }
                $wallet->decrement('balance', $costAmount);
                $walletId = $wallet->id;
            }

            $sale = PulsaSale::create([
                'reference_number' => $referenceNumber,
                'sale_date' => now(),
                'product_id' => $product->id,
                'wallet_id' => $walletId,
                'serial_number' => $serialNumber,
                'customer_name' => $validated['customer_name'] ?? null,
                'target_number' => ! empty($validated['target_number']) ? $validated['target_number'] : '-',
                'shift' => $validated['shift'] ?? 'pagi',
                'payment_method' => $validated['payment_method'],
                'price' => $price,
                'cost_amount' => $costAmount,
                'cash_paid' => $cashPaid,
                'change_amount' => $isDebt ? 0 : ($cashPaid - $price),
                'status' => 'success',
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($isDebt) {
                Debt::create([
                    'reference_number' => 'DEBT-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                    'debt_date' => $sale->sale_date,
                    'customer_name' => $sale->customer_name ?: ($sale->target_number ?: 'Pelanggan Voucher'),
                    'customer_phone' => $sale->target_number,
                    'amount' => $price,
                    'paid_amount' => $cashPaid,
                    'status' => $cashPaid >= $price ? 'paid' : ($cashPaid > 0 ? 'partial' : 'unpaid'),
                    'shift' => $sale->shift,
                    'pulsa_sale_id' => $sale->id,
                    'description' => 'Voucher: ' . ($product->name ?? 'Voucher') . ($serialNumber ? ' [SN: ' . $serialNumber . ']' : ''),
                    'notes' => $sale->notes,
                    'created_by' => $request->user()?->id,
                ]);
            }

            return $sale;
        });

        return response()->json([
            'message' => 'Penjualan voucher berhasil disimpan.',
            'sale' => $sale->load(['product:id,name,variant,category', 'wallet:id,name']),
        ], 201);
    }

    private function serializeProduct(Product $product, bool $includeSerials = false): array
    {
        $serials = $includeSerials ? $this->getAvailableSerials($product) : [];

        return [
            'id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'product_type' => $product->product_type,
            'category' => $product->category,
            'provider_id' => $product->provider_id,
            'provider' => $product->provider?->name ?? $product->category,
            'variant' => $product->variant,
            'serial_number' => $product->serial_number,
            'image_path' => $product->image_path,
            'image_url' => $product->image_path ? Storage::disk('public')->url($product->image_path) : null,
            'unit' => $product->unit,
            'price' => $product->price,
            'cost_price' => $product->cost_price,
            'stock' => $product->stock,
            'minimum_stock' => $product->minimum_stock,
            'is_active' => $product->is_active,
            'available_serials' => $serials,
            'available_serials_count' => count($serials),
            'source_wallet_ids' => $this->resolveProductWalletCollection($product)->pluck('id')->all(),
            'source_wallets' => $this->resolveProductWalletCollection($product)
                ->map(fn (Wallet $wallet) => ['id' => $wallet->id, 'name' => $wallet->name])
                ->values()
                ->all(),
        ];
    }

    private function getAvailableSerials(Product $product): array
    {
        $inMovements = StockMovement::query()
            ->where('product_id', $product->id)
            ->where(function ($q): void {
                $q->where('type', 'in')
                    ->orWhere(function ($sub): void {
                        $sub->where('type', 'adjustment')
                            ->where('note', 'not like', 'Pembatalan Transaksi%');
                    });
            })
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->orderBy('id', 'asc')
            ->get(['id', 'serial_number', 'quantity', 'expiry_date', 'cost_price']);

        if ($inMovements->isEmpty() && empty($product->serial_number)) {
            return [];
        }

        $soldFromPulsa = PulsaSale::query()
            ->where('product_id', $product->id)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->pluck('serial_number')
            ->all();

        $soldFromSaleItems = SaleItem::query()
            ->where('product_id', $product->id)
            ->whereHas('sale', function ($query): void {
                $query->where('status', '!=', 'cancelled');
            })
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->pluck('serial_number')
            ->all();

        $allSold = array_merge($soldFromPulsa, $soldFromSaleItems);
        $soldCounts = array_count_values(array_map('strval', $allSold));

        $available = [];
        $consumedCounts = [];

        foreach ($inMovements as $movement) {
            $sn = (string) $movement->serial_number;
            $movementQty = max(1, (int) ($movement->quantity ?? 1));
            $totalSold = $soldCounts[$sn] ?? 0;
            $alreadyConsumed = $consumedCounts[$sn] ?? 0;

            $remaining = 0;
            for ($q = 0; $q < $movementQty; $q++) {
                if ($alreadyConsumed < $totalSold) {
                    $alreadyConsumed++;
                    $consumedCounts[$sn] = $alreadyConsumed;
                } else {
                    $remaining++;
                }
            }

            if ($remaining > 0) {
                $available[] = [
                    'id' => $movement->id,
                    'serial_number' => $sn,
                    'quantity' => $remaining,
                    'expiry_date' => $movement->expiry_date ? $movement->expiry_date->format('Y-m-d') : null,
                    'cost_price' => (float) $movement->cost_price,
                ];
            }
        }

        if (! empty($product->serial_number)) {
            $productSn = (string) $product->serial_number;
            $totalSold = $soldCounts[$productSn] ?? 0;
            $alreadyConsumed = $consumedCounts[$productSn] ?? 0;
            if ($alreadyConsumed < (int) max(1, $product->stock)) {
                $exists = collect($available)->contains('serial_number', $productSn);
                if (! $exists) {
                    $available[] = [
                        'id' => null,
                        'serial_number' => $productSn,
                        'quantity' => max(1, (int) $product->stock - $alreadyConsumed),
                        'expiry_date' => null,
                        'cost_price' => (float) $product->cost_price,
                    ];
                }
            }
        }

        return $available;
    }

    private function serializeWallet(Wallet $wallet): array
    {
        $providers = $wallet->relationLoaded('electricProviders')
            ? $wallet->electricProviders->map(fn (Provider $provider) => [
                'id' => $provider->id,
                'name' => $provider->name,
                'products_count' => (int) ($provider->products_count ?? 0),
            ])->values()->all()
            : [];

        return [
            'id' => $wallet->id,
            'name' => $wallet->name,
            'balance' => $wallet->balance,
            'is_active' => $wallet->is_active,
            'child_provider_ids' => array_column($providers, 'id'),
            'child_providers' => $providers,
            'source_provider_ids' => array_column($providers, 'id'),
            'source_providers' => $providers,
        ];
    }

    private function normalizeProviderIds(Request $request): array
    {
        $providerIds = $request->input('provider_ids', []);
        if (is_array($providerIds) && $providerIds !== []) {
            return array_values(array_unique(array_map('intval', $providerIds)));
        }

        $productIds = $request->input('electric_product_ids', $request->input('product_ids', []));
        if (! is_array($productIds) || $productIds === []) {
            return [];
        }

        return Product::query()
            ->whereIn('id', array_map('intval', $productIds))
            ->whereNotNull('provider_id')
            ->pluck('provider_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function resolveProductWalletCollection(Product $product)
    {
        if ($product->relationLoaded('provider') && $product->provider && $product->provider->relationLoaded('wallets')) {
            return $product->provider->wallets;
        }

        return $product->relationLoaded('sourceWallets')
            ? $product->sourceWallets
            : collect();
    }
}
