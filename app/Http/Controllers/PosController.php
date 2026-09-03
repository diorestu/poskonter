<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Provider;
use App\Models\PulsaSale;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Wallet;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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

    public function exportPdf(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::today()->toDateString());

        $sales = Sale::query()
            ->with(['items.product'])
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->orderBy('sale_date', 'desc')
            ->get();
        $totalPosSales = (float) $sales->sum('total');

        $pulsaSales = PulsaSale::query()
            ->with(['product'])
            ->where('status', 'success')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->latest()
            ->get();
        $totalPulsaSales = (float) $pulsaSales->sum('selling_price');
        $totalPulsaCost = (float) $pulsaSales->sum('cost_price');

        $expenses = Expense::query()
            ->whereDate('expense_date', '>=', $startDate)
            ->whereDate('expense_date', '<=', $endDate)
            ->orderBy('expense_date', 'desc')
            ->get();
        $totalExpenses = (float) $expenses->sum('amount');

        $totalRevenue = $totalPosSales + $totalPulsaSales;

        // Estimated Cost of Goods Sold for POS items
        $posCost = 0;
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $posCost += (float) ($item->cost_price * $item->quantity);
            }
        }

        $grossProfit = ($totalPosSales - $posCost) + ($totalPulsaSales - $totalPulsaCost);
        $netProfit = $grossProfit - $totalExpenses;

        $lowStockProducts = Product::query()
            ->where('product_type', 'regular')
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock', 'asc')
            ->get();

        // Process Category & Shift Sales Aggregation (2 Kolom: Shift Pagi vs Shift Sore)
        $categoriesMap = [];

        // 1. Process Physical Sales Items
        foreach ($sales as $sale) {
            $shift = strtolower($sale->shift ?: 'pagi');
            if (! in_array($shift, ['pagi', 'sore'])) {
                $shift = 'pagi';
            }

            foreach ($sale->items as $item) {
                $categoryName = trim($item->product?->category ?: 'Aksesoris & Lainnya');
                if (! isset($categoriesMap[$categoryName])) {
                    $categoriesMap[$categoryName] = [
                        'pagi' => ['items' => [], 'total_qty' => 0, 'total_amount' => 0],
                        'sore' => ['items' => [], 'total_qty' => 0, 'total_amount' => 0],
                    ];
                }

                $productKey = $item->product_id ? 'prod_'.$item->product_id : 'name_'.md5($item->product_name);
                if (! isset($categoriesMap[$categoryName][$shift]['items'][$productKey])) {
                    $categoriesMap[$categoryName][$shift]['items'][$productKey] = [
                        'name' => $item->product_name ?: ($item->product?->name ?? 'Item'),
                        'qty' => 0,
                        'price' => (float) $item->unit_price,
                        'total' => 0,
                    ];
                }

                $categoriesMap[$categoryName][$shift]['items'][$productKey]['qty'] += (int) $item->quantity;
                $categoriesMap[$categoryName][$shift]['items'][$productKey]['total'] += (float) $item->subtotal;
                $categoriesMap[$categoryName][$shift]['total_qty'] += (int) $item->quantity;
                $categoriesMap[$categoryName][$shift]['total_amount'] += (float) $item->subtotal;
            }
        }

        // 2. Process Pulsa & Voucher Sales
        foreach ($pulsaSales as $pSale) {
            $shift = strtolower($pSale->shift ?: 'pagi');
            if (! in_array($shift, ['pagi', 'sore'])) {
                $shift = 'pagi';
            }

            $categoryName = trim($pSale->product?->category ?: ($pSale->product?->product_type === 'pulsa' ? 'Pulsa Elektrik' : 'Voucher'));
            if (! isset($categoriesMap[$categoryName])) {
                $categoriesMap[$categoryName] = [
                    'pagi' => ['items' => [], 'total_qty' => 0, 'total_amount' => 0],
                    'sore' => ['items' => [], 'total_qty' => 0, 'total_amount' => 0],
                ];
            }

            $productKey = 'pulsa_'.$pSale->product_id.'_'.md5($pSale->product?->name ?? 'p');
            if (! isset($categoriesMap[$categoryName][$shift]['items'][$productKey])) {
                $categoriesMap[$categoryName][$shift]['items'][$productKey] = [
                    'name' => $pSale->product?->name ?: 'Pulsa/Voucher',
                    'qty' => 0,
                    'price' => (float) $pSale->selling_price,
                    'total' => 0,
                ];
            }

            $categoriesMap[$categoryName][$shift]['items'][$productKey]['qty'] += 1;
            $categoriesMap[$categoryName][$shift]['items'][$productKey]['total'] += (float) $pSale->selling_price;
            $categoriesMap[$categoryName][$shift]['total_qty'] += 1;
            $categoriesMap[$categoryName][$shift]['total_amount'] += (float) $pSale->selling_price;
        }

        // Shift Summary (Pagi vs Sore)
        $shiftSummary = [
            'pagi' => [
                'pos_sales' => (float) $sales->where('shift', 'pagi')->sum('total'),
                'pulsa_sales' => (float) $pulsaSales->where('shift', 'pagi')->sum('selling_price'),
                'cash' => (float) $sales->where('shift', 'pagi')->where('payment_method', 'cash')->sum('total') + (float) $pulsaSales->where('shift', 'pagi')->where('payment_method', 'cash')->sum('selling_price'),
                'non_cash' => (float) $sales->where('shift', 'pagi')->where('payment_method', '!=', 'cash')->sum('total') + (float) $pulsaSales->where('shift', 'pagi')->where('payment_method', '!=', 'cash')->sum('selling_price'),
                'transactions' => $sales->where('shift', 'pagi')->count() + $pulsaSales->where('shift', 'pagi')->count(),
            ],
            'sore' => [
                'pos_sales' => (float) $sales->where('shift', 'sore')->sum('total'),
                'pulsa_sales' => (float) $pulsaSales->where('shift', 'sore')->sum('selling_price'),
                'cash' => (float) $sales->where('shift', 'sore')->where('payment_method', 'cash')->sum('total') + (float) $pulsaSales->where('shift', 'sore')->where('payment_method', 'cash')->sum('selling_price'),
                'non_cash' => (float) $sales->where('shift', 'sore')->where('payment_method', '!=', 'cash')->sum('total') + (float) $pulsaSales->where('shift', 'sore')->where('payment_method', '!=', 'cash')->sum('selling_price'),
                'transactions' => $sales->where('shift', 'sore')->count() + $pulsaSales->where('shift', 'sore')->count(),
            ],
        ];
        $shiftSummary['pagi']['total'] = $shiftSummary['pagi']['pos_sales'] + $shiftSummary['pagi']['pulsa_sales'];
        $shiftSummary['sore']['total'] = $shiftSummary['sore']['pos_sales'] + $shiftSummary['sore']['pulsa_sales'];

        $pdf = Pdf::loadView('pos.reports.pdf', compact(
            'startDate',
            'endDate',
            'categoriesMap',
            'shiftSummary',
            'sales',
            'pulsaSales',
            'expenses',
            'lowStockProducts',
            'totalPosSales',
            'totalPulsaSales',
            'totalRevenue',
            'totalExpenses',
            'netProfit'
        ))->setPaper('a4', 'portrait');

        $fileName = 'Laporan-ZeniPOS-' . Carbon::parse($startDate)->format('Ymd') . '-' . Carbon::parse($endDate)->format('Ymd') . '.pdf';

        if ($request->query('view') === 'preview') {
            return $pdf->stream($fileName);
        }

        return $pdf->download($fileName);
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
            ->with(['items.product:id,name,code,variant'])
            ->latest('sale_date')
            ->latest('id')
            ->limit(10)
            ->get();

        $stockMovements = StockMovement::query()
            ->with('product:id,name,code,unit')
            ->latest('id')
            ->limit(5)
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
            'variant' => ['nullable', 'string', 'max:80'],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'unit' => ['required', 'string', 'max:30'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'wallet_ids' => ['nullable', 'array'],
            'wallet_ids.*' => ['integer', 'exists:wallets,id'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $provider = null;
        if (($validated['product_type'] ?? null) === 'pulsa') {
            $provider = $this->resolvePulsaProvider($validated);
            $validated['category'] = $provider->name;
            $validated['provider_id'] = $provider->id;
        } else {
            $validated['provider_id'] = null;
        }

        $product = DB::transaction(function () use ($validated, $imagePath, $provider): Product {
            $product = Product::create([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'product_type' => $validated['product_type'],
                'provider_id' => $validated['provider_id'] ?? null,
                'category' => $validated['category'] ?? null,
                'variant' => $validated['variant'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,
                'image_path' => $imagePath,
                'unit' => $validated['unit'],
                'price' => $validated['price'],
                'cost_price' => $validated['cost_price'] ?? 0,
                'stock' => $validated['product_type'] === 'pulsa' ? 0 : ($validated['stock'] ?? 0),
                'minimum_stock' => $validated['minimum_stock'] ?? 0,
                'is_active' => true,
            ]);

            if ($product->isPulsa()) {
                if (array_key_exists('wallet_ids', $validated)) {
                    $provider?->wallets()->sync($validated['wallet_ids'] ?? []);
                }
            } elseif ($product->stock > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => $product->stock,
                    'cost_price' => $product->cost_price,
                    'serial_number' => $product->serial_number,
                    'note' => 'Stok awal produk baru',
                ]);
            }

            return $product;
        });

        return response()->json([
            'message' => 'Produk berhasil disimpan.',
            'product' => $this->serializeProduct($product->load(['sourceWallets:id,name', 'provider:id,name', 'provider.wallets:id,name'])),
        ], 201);
    }

    public function updateProduct(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('products', 'code')->ignore($product->id)],
            'name' => ['required', 'string', 'max:120'],
            'product_type' => ['nullable', 'string', 'in:regular,pulsa'],
            'category' => ['nullable', 'string', 'max:80'],
            'provider_id' => ['nullable', 'integer', 'exists:providers,id'],
            'variant' => ['nullable', 'string', 'max:80'],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'unit' => ['required', 'string', 'max:30'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'wallet_ids' => ['nullable', 'array'],
            'wallet_ids.*' => ['integer', 'exists:wallets,id'],
        ]);

        $imagePath = $product->image_path;
        if ($request->hasFile('image')) {
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $provider = null;
        if (($validated['product_type'] ?? $product->product_type) === 'pulsa') {
            $provider = $this->resolvePulsaProvider($validated);
            $validated['category'] = $provider->name;
            $validated['provider_id'] = $provider->id;
        } else {
            $validated['provider_id'] = null;
        }

        DB::transaction(function () use ($product, $validated, $imagePath, $provider): void {
            $product->update([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'product_type' => $validated['product_type'] ?? $product->product_type,
                'provider_id' => $validated['provider_id'] ?? null,
                'category' => $validated['category'] ?? null,
                'variant' => $validated['variant'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,
                'image_path' => $imagePath,
                'unit' => $validated['unit'],
                'price' => $validated['price'],
                'cost_price' => $validated['cost_price'] ?? $product->cost_price,
                'minimum_stock' => $validated['minimum_stock'] ?? 0,
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
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
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
            $totalQuantity = $items->sum(fn ($item) => max(1, (int) ($item['quantity'] ?? 1)));
            $latestCost = $items
                ->pluck('cost_price')
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->map(fn ($value) => (float) $value)
                ->last();

            $product->increment('stock', $totalQuantity);

            if ($latestCost !== null) {
                $product->update([
                    'cost_price' => $latestCost,
                ]);
            }

            foreach ($items as $item) {
                $qty = max(1, (int) ($item['quantity'] ?? 1));
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => $qty,
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
            'shift' => ['nullable', 'string', 'in:pagi,sore'],
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
                    'serial_number' => ! empty($item['serial_number']) ? trim($item['serial_number']) : null,
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
                'shift' => $validated['shift'] ?? 'pagi',
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
                    'serial_number' => $item['serial_number'] ?? null,
                    'cost_price' => $item['product']->cost_price,
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

    private function serializeProduct(Product $product, bool $includeSerials = true): array
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

        $serials = $includeSerials ? $this->getAvailableSerials($product) : [];

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
            'available_serials' => $serials,
            'available_serials_count' => count($serials),
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

    private function getAvailableSerials(Product $product): array
    {
        $inMovements = StockMovement::query()
            ->where('product_id', $product->id)
            ->whereIn('type', ['in', 'adjustment'])
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->orderBy('id', 'asc')
            ->get(['id', 'serial_number', 'quantity', 'expiry_date', 'cost_price']);

        if ($inMovements->isEmpty() && empty($product->serial_number)) {
            return [];
        }

        $soldFromStock = StockMovement::query()
            ->where('product_id', $product->id)
            ->where('type', 'sale')
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->pluck('serial_number')
            ->all();

        $soldFromPulsa = PulsaSale::query()
            ->where('product_id', $product->id)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->pluck('serial_number')
            ->all();

        $soldFromSaleItems = SaleItem::query()
            ->where('product_id', $product->id)
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->pluck('serial_number')
            ->all();

        $allSold = array_merge($soldFromStock, $soldFromPulsa, $soldFromSaleItems);
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
