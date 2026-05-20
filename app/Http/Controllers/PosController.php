<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PosController extends Controller
{
    public function home()
    {
        return redirect()->route('pos.index');
    }

    public function pos(): View
    {
        return view('pos.pages.pos');
    }

    public function stock(): View
    {
        return view('pos.pages.stock');
    }

    public function report(): View
    {
        return view('pos.pages.report');
    }

    public function masterData(): RedirectResponse
    {
        return redirect()->route('master.physical.index');
    }

    public function masterPhysical(): View
    {
        return view('pos.pages.master-physical');
    }

    public function masterElectric(): View
    {
        return view('pos.pages.master-electric');
    }

    public function dashboard(): JsonResponse
    {
        $summary = [
            'total_products' => Product::count(),
            'low_stock_count' => Product::where('product_type', 'regular')->whereColumn('stock', '<=', 'minimum_stock')->count(),
            'today_sales_total' => (float) Sale::whereDate('sale_date', today())->sum('total'),
            'today_expenses_total' => (float) Expense::whereDate('expense_date', today())->sum('amount'),
            'today_transactions' => Sale::whereDate('sale_date', today())->count(),
        ];

        $products = Product::query()
            ->with(['provider:id,name', 'provider.wallets:id,name'])
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'product_type', 'provider_id', 'category', 'variant', 'serial_number', 'image_path', 'unit', 'price', 'cost_price', 'stock', 'minimum_stock', 'is_active'])
            ->map(fn (Product $product) => $this->serializeProduct($product));

        $recentSales = Sale::query()
            ->latest('sale_date')
            ->latest('id')
            ->limit(8)
            ->get(['id', 'invoice_number', 'sale_date', 'customer_name', 'payment_method', 'total', 'cash_paid', 'change_amount']);

        $stockMovements = StockMovement::query()
            ->with('product:id,name,code,unit')
            ->latest('id')
            ->limit(10)
            ->get(['id', 'product_id', 'type', 'quantity', 'expiry_date', 'serial_number', 'cost_price', 'note', 'created_at']);

        return response()->json([
            'summary' => $summary,
            'products' => $products,
            'recent_sales' => $recentSales,
            'stock_movements' => $stockMovements,
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $search = (string) $request->string('search');

        $products = Product::query()
            ->with(['provider:id,name', 'provider.wallets:id,name'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('variant', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'code', 'name', 'product_type', 'provider_id', 'category', 'variant', 'serial_number', 'image_path', 'unit', 'price', 'cost_price', 'stock'])
            ->map(fn (Product $product) => $this->serializeProduct($product));

        return response()->json($products);
    }

    public function storeProduct(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:products,code'],
            'name' => ['required', 'string', 'max:120'],
            'product_type' => ['required', 'string', 'in:regular,pulsa'],
            'category' => ['nullable', 'string', 'max:80'],
            'provider_id' => ['nullable', 'integer', 'exists:providers,id'],
            'variant' => ['nullable', 'string', 'max:120'],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'image' => ['nullable', 'image', 'max:2048'],
            'unit' => ['required', 'string', 'max:30'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'wallet_ids' => ['sometimes', 'array'],
            'wallet_ids.*' => ['integer', 'distinct', 'exists:wallets,id'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        $provider = $validated['product_type'] === 'pulsa'
            ? $this->resolvePulsaProvider($validated)
            : null;

        if ($validated['product_type'] === 'regular' && blank($validated['category'] ?? null)) {
            throw ValidationException::withMessages([
                'category' => 'Kategori produk fisik wajib diisi.',
            ]);
        }

        $product = DB::transaction(function () use ($validated, $provider): Product {
            $product = Product::create([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'product_type' => $validated['product_type'],
                'provider_id' => $provider?->id,
                'category' => $provider?->name ?? $validated['category'],
                'variant' => $validated['variant'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,
                'image_path' => $validated['image_path'] ?? null,
                'unit' => $validated['unit'],
                'price' => $validated['price'],
                'cost_price' => $validated['cost_price'] ?? 0,
                'minimum_stock' => $validated['product_type'] === 'pulsa' ? 0 : ($validated['minimum_stock'] ?? 0),
                'stock' => 0,
                'is_active' => true,
            ]);

            if (($validated['product_type'] ?? null) !== 'pulsa') {
                $product->sourceWallets()->sync([]);
            } elseif (array_key_exists('wallet_ids', $validated)) {
                $provider?->wallets()->syncWithoutDetaching($validated['wallet_ids'] ?? []);
            }

            return $product->load(['sourceWallets:id,name', 'provider:id,name', 'provider.wallets:id,name']);
        });

        return response()->json([
            'message' => 'Produk berhasil ditambahkan.',
            'product' => $this->serializeProduct($product),
        ], 201);
    }

    public function updateProduct(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:products,code,' . $product->id],
            'name' => ['required', 'string', 'max:120'],
            'product_type' => ['required', 'string', 'in:regular,pulsa'],
            'category' => ['nullable', 'string', 'max:80'],
            'provider_id' => ['nullable', 'integer', 'exists:providers,id'],
            'variant' => ['nullable', 'string', 'max:120'],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'image' => ['nullable', 'image', 'max:2048'],
            'unit' => ['required', 'string', 'max:30'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'wallet_ids' => ['sometimes', 'array'],
            'wallet_ids.*' => ['integer', 'distinct', 'exists:wallets,id'],
        ]);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        $provider = $validated['product_type'] === 'pulsa'
            ? $this->resolvePulsaProvider($validated)
            : null;

        if ($validated['product_type'] === 'regular' && blank($validated['category'] ?? null)) {
            throw ValidationException::withMessages([
                'category' => 'Kategori produk fisik wajib diisi.',
            ]);
        }

        DB::transaction(function () use ($product, $validated, $provider): void {
            $product->update([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'product_type' => $validated['product_type'],
                'provider_id' => $provider?->id,
                'category' => $provider?->name ?? $validated['category'],
                'variant' => $validated['variant'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,
                'image_path' => $validated['image_path'] ?? $product->image_path,
                'unit' => $validated['unit'],
                'price' => $validated['price'],
                'cost_price' => $validated['cost_price'] ?? 0,
                'minimum_stock' => $validated['product_type'] === 'pulsa' ? 0 : ($validated['minimum_stock'] ?? 0),
                'stock' => $validated['product_type'] === 'pulsa' ? 0 : $product->stock,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            if (($validated['product_type'] ?? null) !== 'pulsa') {
                $product->sourceWallets()->sync([]);
            } elseif (array_key_exists('wallet_ids', $validated)) {
                $provider?->wallets()->sync($validated['wallet_ids'] ?? []);
            }
        });

        return response()->json([
            'message' => 'Produk berhasil diperbarui.',
            'product' => $this->serializeProduct($product->fresh()->load(['sourceWallets:id,name', 'provider:id,name', 'provider.wallets:id,name'])),
        ]);
    }

    public function addStock(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.serial_number' => ['nullable', 'string', 'max:120'],
            'items.*.expiry_date' => ['nullable', 'date'],
            'items.*.cost_price' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        if ($product->isPulsa()) {
            throw ValidationException::withMessages([
                'product' => 'Produk pulsa elektrik tidak memakai stok barang.',
            ]);
        }

        DB::transaction(function () use ($product, $validated): void {
            $items = collect($validated['items']);
            $quantity = $items->count();
            $latestCost = $items
                ->pluck('cost_price')
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->map(fn ($value) => (float) $value)
                ->last();

            $product->increment('stock', $quantity);

            if ($latestCost !== null) {
                $product->update([
                    'cost_price' => $latestCost,
                ]);
            }

            foreach ($items as $item) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => 1,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'serial_number' => $item['serial_number'] ?? null,
                    'cost_price' => $item['cost_price'] ?? 0,
                    'note' => $validated['note'] ?? 'Tambah stok manual',
                ]);
            }
        });

        return response()->json([
            'message' => 'Stok berhasil ditambahkan.',
            'product' => $product->fresh(),
        ]);
    }

    public function storeSale(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:120'],
            'shift' => ['required', 'string', 'in:pagi,sore'],
            'payment_method' => ['required', 'string', 'in:cash,qris,transfer,debit'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'cash_paid' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.serial_number' => ['nullable', 'string', 'max:120'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $sale = DB::transaction(function () use ($validated): Sale {
            $productIds = collect($validated['items'])->pluck('product_id')->all();
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $items = [];

            foreach ($validated['items'] as $item) {
                $product = $products[$item['product_id']] ?? null;

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => 'Produk tidak tersedia.',
                    ]);
                }

                if ($product->isPulsa()) {
                    throw ValidationException::withMessages([
                        'items' => 'Produk pulsa hanya bisa dijual dari menu Pulsa Elektrik.',
                    ]);
                }

                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => 'Stok untuk ' . trim($product->name . ' ' . ($product->variant ?? '')) . ' tidak mencukupi.',
                    ]);
                }

                $lineSubtotal = $product->price * $item['quantity'];
                $subtotal += $lineSubtotal;

                $items[] = [
                    'product' => $product,
                    'serial_number' => $item['serial_number'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $discount = (float) ($validated['discount'] ?? 0);
            $total = max($subtotal - $discount, 0);
            $cashPaid = (float) $validated['cash_paid'];

            if ($cashPaid < $total) {
                throw ValidationException::withMessages([
                    'cash_paid' => 'Uang bayar kurang dari total belanja.',
                ]);
            }

            $sale = Sale::create([
                'invoice_number' => 'INV-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'sale_date' => now(),
                'customer_name' => $validated['customer_name'] ?? null,
                'shift' => $validated['shift'],
                'payment_method' => $validated['payment_method'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'cash_paid' => $cashPaid,
                'change_amount' => $cashPaid - $total,
            ]);

            foreach ($items as $item) {
                $sale->items()->create([
                    'product_id' => $item['product']->id,
                    'serial_number' => $item['serial_number'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);

                $item['product']->decrement('stock', $item['quantity']);

                StockMovement::create([
                    'product_id' => $item['product']->id,
                    'type' => 'sale',
                    'quantity' => $item['quantity'],
                    'note' => 'Penjualan ' . $sale->invoice_number,
                ]);
            }

            return $sale;
        });

        return response()->json([
            'message' => 'Transaksi berhasil disimpan.',
            'sale' => $sale->load('items.product:id,name,code'),
        ], 201);
    }

    private function serializeProduct(Product $product): array
    {
        $providerWallets = $product->relationLoaded('provider') && $product->provider?->relationLoaded('wallets')
            ? $product->provider->wallets
            : collect();
        $legacyWallets = $product->relationLoaded('sourceWallets')
            ? $product->sourceWallets
            : collect();
        $wallets = ($providerWallets->isNotEmpty() ? $providerWallets : $legacyWallets)
            ->map(fn (Wallet $wallet) => [
                'id' => $wallet->id,
                'name' => $wallet->name,
            ])->values()->all();

        return [
            'id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'category' => $product->category,
            'provider_id' => $product->provider_id,
            'provider' => $product->isPulsa() ? $product->category : null,
            'variant' => $product->variant,
            'product_type' => $product->product_type,
            'serial_number' => $product->serial_number,
            'image_path' => $product->image_path,
            'image_url' => $product->image_path ? Storage::disk('public')->url($product->image_path) : null,
            'unit' => $product->unit,
            'price' => $product->price,
            'cost_price' => $product->cost_price,
            'stock' => $product->stock,
            'minimum_stock' => $product->minimum_stock,
            'is_active' => $product->is_active,
            'source_wallet_ids' => array_column($wallets, 'id'),
            'source_wallets' => $wallets,
        ];
    }

    private function resolvePulsaProvider(array $validated): Provider
    {
        $providerId = $validated['provider_id'] ?? null;
        if ($providerId) {
            return Provider::query()->findOrFail($providerId);
        }

        $providerName = trim((string) ($validated['category'] ?? ''));
        if ($providerName === '') {
            throw ValidationException::withMessages([
                'provider_id' => 'Provider produk elektrik wajib dipilih.',
            ]);
        }

        return Provider::query()->firstOrCreate([
            'name' => $providerName,
        ]);
    }
}
