<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\DebtPayment;
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

    public function reportData(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', Carbon::today()->toDateString());
        $endDate = $request->query('end_date', Carbon::today()->toDateString());

        return response()->json($this->getReportData($startDate, $endDate));
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::today()->toDateString());
        $endDate = $request->query('end_date', Carbon::today()->toDateString());

        $reportData = $this->getReportData($startDate, $endDate);

        $pdf = Pdf::loadView('pos.reports.pdf', compact('reportData', 'startDate', 'endDate'))
            ->setPaper('a4', 'portrait');

        $fileName = 'Rekapan-Penjualan-Harian-' . Carbon::parse($startDate)->format('Ymd') . '-' . Carbon::parse($endDate)->format('Ymd') . '.pdf';

        if ($request->query('view') === 'preview') {
            return $pdf->stream($fileName);
        }

        return $pdf->download($fileName);
    }

    public function getReportData(string $startDate, string $endDate): array
    {
        $sales = Sale::query()
            ->with(['items.product'])
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->orderBy('sale_date', 'asc')
            ->get();

        $pulsaSales = PulsaSale::query()
            ->with(['product.provider', 'wallet'])
            ->where('status', '!=', 'cancelled')
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->orderBy('sale_date', 'asc')
            ->get();

        $expenses = Expense::query()
            ->whereDate('expense_date', '>=', $startDate)
            ->whereDate('expense_date', '<=', $endDate)
            ->orderBy('expense_date', 'asc')
            ->get();

        $debts = Debt::query()
            ->whereDate('debt_date', '>=', $startDate)
            ->whereDate('debt_date', '<=', $endDate)
            ->orderBy('debt_date', 'asc')
            ->get();

        $debtPayments = DebtPayment::query()
            ->with('debt')
            ->whereDate('payment_date', '>=', $startDate)
            ->whereDate('payment_date', '<=', $endDate)
            ->orderBy('payment_date', 'asc')
            ->get();

        // 14 Standard Predefined Voucher & Pulsa Categories (from physical paper report)
        $standardCategories = [
            'digipost (Telkomsel)',
            'rita/voucher tree (Three)',
            'sidompul xl (XL)',
            'voucher/sisimple indosat (Indosat)',
            'voucher telkomsel',
            'voucher xl',
            'voucher smartfren',
            'voucher/perdana axis',
            'VOUCHER BYU',
            'perdana ALL OPERATOR',
            'mitra shopee',
            'propana',
            'E-money',
            'payfazz',
        ];

        $shifts = ['pagi' => 'Shift Pagi', 'sore' => 'Shift Sore'];
        $shiftData = [];

        foreach ($shifts as $shiftKey => $shiftLabel) {
            $shiftSales = $sales->filter(fn ($s) => strtolower($s->shift ?: 'pagi') === $shiftKey);
            $shiftPulsa = $pulsaSales->filter(fn ($p) => strtolower($p->shift ?: 'pagi') === $shiftKey);

            // 1. Voucher & Pulsa Elektrik Items (Initialized with standard 14 paper rows)
            $voucherPulsaGroups = [];
            foreach ($standardCategories as $catName) {
                $voucherPulsaGroups[$catName] = [
                    'name' => $catName,
                    'qty' => 0,
                    'total' => 0.0,
                ];
            }

            // From PulsaSale
            foreach ($shiftPulsa as $ps) {
                $groupName = $this->resolveVoucherPulsaGroupName(
                    $ps->wallet?->name,
                    $ps->product?->provider?->name,
                    $ps->product?->category,
                    $ps->product?->name
                );

                if (! isset($voucherPulsaGroups[$groupName])) {
                    $voucherPulsaGroups[$groupName] = [
                        'name' => $groupName,
                        'qty' => 0,
                        'total' => 0.0,
                    ];
                }
                $voucherPulsaGroups[$groupName]['qty'] += 1;
                $voucherPulsaGroups[$groupName]['total'] += (float) ($ps->price ?? 0);
            }

            // From POS Sales (Vouchers / Perdana)
            foreach ($shiftSales as $s) {
                foreach ($s->items as $item) {
                    if ($this->isVoucher($item->product)) {
                        $groupName = $this->resolveVoucherPulsaGroupName(
                            null,
                            $item->product?->provider?->name,
                            $item->product?->category,
                            $item->product?->name
                        );

                        if (! isset($voucherPulsaGroups[$groupName])) {
                            $voucherPulsaGroups[$groupName] = [
                                'name' => $groupName,
                                'qty' => 0,
                                'total' => 0.0,
                            ];
                        }
                        $voucherPulsaGroups[$groupName]['qty'] += (int) $item->quantity;
                        $voucherPulsaGroups[$groupName]['total'] += (float) ($item->subtotal ?? ($item->price * $item->quantity));
                    }
                }
            }

            $totalVoucherPulsa = (float) array_sum(array_column($voucherPulsaGroups, 'total'));

            // 2. Acesories (Physical Goods)
            $accessoriesItems = [];
            foreach ($shiftSales as $s) {
                foreach ($s->items as $item) {
                    if (! $this->isVoucherOrPulsa($item->product)) {
                        $productName = $item->product?->name ?: 'Aksesoris';
                        $variant = $item->product?->variant ?: '';
                        $fullName = $variant ? "{$productName} ({$variant})" : $productName;
                        if ($item->serial_number) {
                            $fullName .= " [SN: {$item->serial_number}]";
                        }

                        $itemPrice = (float) ($item->price ?? ($item->product?->price ?? 0));
                        $itemSubtotal = (float) ($item->subtotal ?? ($itemPrice * $item->quantity));

                        $accessoriesItems[] = [
                            'name' => $fullName,
                            'price' => $itemPrice,
                            'qty' => (int) $item->quantity,
                            'total' => $itemSubtotal,
                        ];
                    }
                }
            }
            $totalAccessories = (float) array_sum(array_column($accessoriesItems, 'total'));

            // 3. Payment Methods Breakdown
            $payments = [
                'cash' => 0.0,
                'qris' => 0.0,
                'transfer' => 0.0,
                'debit' => 0.0,
                'utang' => 0.0,
            ];
            foreach ($shiftSales as $s) {
                $method = strtolower($s->payment_method ?: 'cash');
                $payments[$method] = ($payments[$method] ?? 0.0) + (float) $s->total;
            }
            foreach ($shiftPulsa as $ps) {
                $method = strtolower($ps->payment_method ?: 'cash');
                $payments[$method] = ($payments[$method] ?? 0.0) + (float) ($ps->price ?? 0);
            }

            // 4. Utang Shift & Pembayaran Utang Shift (Sesuai Kertas Rekapan)
            $shiftDebts = $debts->filter(fn ($d) => strtolower($d->shift ?: 'pagi') === $shiftKey);
            $totalShiftDebt = (float) $shiftDebts->sum('amount');
            $debtNotes = $shiftDebts->map(fn ($d) => "{$d->customer_name}: " . number_format($d->amount, 0, ',', '.'))->implode(', ');

            $shiftDebtPayments = $debtPayments->filter(fn ($p) => strtolower($p->shift ?: 'pagi') === $shiftKey);
            $totalShiftDebtPayment = (float) $shiftDebtPayments->sum('amount');
            $shiftDebtPaymentCash = (float) $shiftDebtPayments->filter(fn ($p) => strtolower($p->payment_method ?: 'cash') === 'cash')->sum('amount');
            $shiftDebtPaymentNonCash = (float) $shiftDebtPayments->filter(fn ($p) => strtolower($p->payment_method ?: 'cash') !== 'cash')->sum('amount');
            $debtPaymentNotes = $shiftDebtPayments->map(fn ($p) => "{$p->customer_name}: " . number_format($p->amount, 0, ',', '.'))->implode(', ');

            if ($totalShiftDebt > 0 && ($payments['utang'] ?? 0.0) < $totalShiftDebt) {
                $payments['utang'] = $totalShiftDebt;
            }

            $totalShiftSales = $totalVoucherPulsa + $totalAccessories;
            $totalNonCash = (float) (($payments['qris'] ?? 0.0) + ($payments['transfer'] ?? 0.0) + ($payments['debit'] ?? 0.0));
            $totalCash = (float) ($payments['cash'] ?? 0.0);

            // Pada struk fisik: total uang + nontunai mencakup uang tunai + non-tunai + utang pulsa
            $totalUangDanNontunai = (float) ($totalCash + $totalNonCash + $totalShiftDebt);
            // Selisih = total uang + nontunai - total penjualan shift
            $selisih = (float) ($totalUangDanNontunai - $totalShiftSales);

            $halfExpense = (float) (($expenses->sum('amount')) / 2);
            // Sisa kas bersih = uang penjualan tunai + penerimaan pembayaran utang tunai - pengeluaran shift
            $netCash = (float) max(0, ($totalCash + $shiftDebtPaymentCash) - $halfExpense);

            $shiftData[$shiftKey] = [
                'label' => $shiftLabel,
                'voucher_pulsa' => array_values($voucherPulsaGroups),
                'total_voucher_pulsa' => $totalVoucherPulsa,
                'accessories' => $accessoriesItems,
                'total_accessories' => $totalAccessories,
                'payments' => $payments,
                'total_cash' => $totalCash,
                'total_non_cash' => $totalNonCash,
                'total_debt' => $totalShiftDebt,
                'debt_notes' => $debtNotes ?: '-',
                'debt_items' => $shiftDebts->values()->all(),
                'total_sales' => $totalShiftSales,
                'total_money' => $totalUangDanNontunai,
                'selisih' => $selisih,
                'total_debt_payment' => $totalShiftDebtPayment,
                'debt_payment_cash' => $shiftDebtPaymentCash,
                'debt_payment_non_cash' => $shiftDebtPaymentNonCash,
                'debt_payment_notes' => $debtPaymentNotes ?: '-',
                'debt_payment_items' => $shiftDebtPayments->values()->all(),
                'net_cash' => $netCash,
            ];
        }

        // Detailed Breakdown of Transactions
        $detailedVouchers = [];
        $detailedPulsa = [];
        $detailedAccessories = [];

        // 1. Detailed Vouchers from PulsaSale
        foreach ($pulsaSales as $ps) {
            $isVcr = str_starts_with($ps->reference_number ?? '', 'VCR-') || $this->isVoucher($ps->product);
            if ($isVcr) {
                $detailedVouchers[] = [
                    'id' => 'VCR-' . $ps->id,
                    'reference_number' => $ps->reference_number,
                    'date' => $ps->sale_date ? $ps->sale_date->format('d/m/Y H:i') : '-',
                    'date_raw' => $ps->sale_date?->toISOString(),
                    'shift' => strtolower($ps->shift ?: 'pagi'),
                    'product_name' => $ps->product?->name ?: 'Voucher',
                    'category' => $ps->product?->category ?: 'Voucher',
                    'variant' => $ps->product?->variant ?: '-',
                    'serial_number' => $ps->serial_number ?: '-',
                    'target_number' => $ps->target_number ?: '-',
                    'customer_name' => $ps->customer_name ?: '-',
                    'quantity' => 1,
                    'price' => (float) $ps->price,
                    'payment_method' => strtolower($ps->payment_method ?: 'cash'),
                    'status' => $ps->status ?: 'success',
                    'source' => 'Menu Voucher',
                ];
            } else {
                $detailedPulsa[] = [
                    'id' => 'PLS-' . $ps->id,
                    'reference_number' => $ps->reference_number,
                    'date' => $ps->sale_date ? $ps->sale_date->format('d/m/Y H:i') : '-',
                    'date_raw' => $ps->sale_date?->toISOString(),
                    'shift' => strtolower($ps->shift ?: 'pagi'),
                    'provider' => $ps->product?->category ?: ($ps->product?->provider?->name ?: 'Elektrik'),
                    'product_name' => $ps->product?->name ?: 'Pulsa',
                    'target_number' => $ps->target_number ?: '-',
                    'serial_number' => $ps->serial_number ?: '-',
                    'wallet_name' => $ps->wallet?->name ?: '-',
                    'customer_name' => $ps->customer_name ?: '-',
                    'price' => (float) $ps->price,
                    'cost_price' => (float) $ps->cost_amount,
                    'profit' => (float) ($ps->price - $ps->cost_amount),
                    'payment_method' => strtolower($ps->payment_method ?: 'cash'),
                    'status' => $ps->status ?: 'pending',
                ];
            }
        }

        // 2. Detailed Vouchers & Accessories from POS Sales
        foreach ($sales as $s) {
            foreach ($s->items as $item) {
                if ($this->isVoucher($item->product)) {
                    $detailedVouchers[] = [
                        'id' => 'POS-VCR-' . $s->id . '-' . $item->id,
                        'reference_number' => $s->invoice_number,
                        'date' => $s->sale_date ? $s->sale_date->format('d/m/Y H:i') : '-',
                        'date_raw' => $s->sale_date?->toISOString(),
                        'shift' => strtolower($s->shift ?: 'pagi'),
                        'product_name' => $item->product?->name ?: 'Voucher',
                        'category' => $item->product?->category ?: 'Voucher',
                        'variant' => $item->product?->variant ?: '-',
                        'serial_number' => $item->serial_number ?: ($item->product?->serial_number ?: '-'),
                        'target_number' => '-',
                        'customer_name' => $s->customer_name ?: '-',
                        'quantity' => (int) $item->quantity,
                        'price' => (float) ($item->subtotal ?? ($item->price * $item->quantity)),
                        'payment_method' => strtolower($s->payment_method ?: 'cash'),
                        'status' => 'success',
                        'source' => 'Kasir POS',
                    ];
                } elseif (! $this->isPulsaElektrik($item->product)) {
                    $detailedAccessories[] = [
                        'id' => 'ACC-' . $s->id . '-' . $item->id,
                        'invoice_number' => $s->invoice_number,
                        'date' => $s->sale_date ? $s->sale_date->format('d/m/Y H:i') : '-',
                        'date_raw' => $s->sale_date?->toISOString(),
                        'shift' => strtolower($s->shift ?: 'pagi'),
                        'product_code' => $item->product?->code ?: '-',
                        'product_name' => $item->product?->name ?: 'Barang Fisik',
                        'variant' => $item->product?->variant ?: '-',
                        'serial_number' => $item->serial_number ?: '-',
                        'customer_name' => $s->customer_name ?: '-',
                        'quantity' => (int) $item->quantity,
                        'price' => (float) $item->price,
                        'subtotal' => (float) ($item->subtotal ?? ($item->price * $item->quantity)),
                        'payment_method' => strtolower($s->payment_method ?: 'cash'),
                    ];
                }
            }
        }

        // Global Expenses
        $totalExpenses = (float) $expenses->sum('amount');
        $expenseList = $expenses->map(fn ($e) => [
            'description' => $e->description ?: ($e->notes ?: ($e->category ?: 'Pengeluaran Operasional')),
            'amount' => (float) $e->amount,
            'date' => $e->expense_date ? $e->expense_date->format('d/m/Y H:i') : '-',
        ])->all();

        // Overall Totals
        $totalVoucherSales = (float) array_sum(array_column($detailedVouchers, 'price'));
        $totalPulsaSales = (float) array_sum(array_column($detailedPulsa, 'price'));
        $totalAccessoriesSales = (float) array_sum(array_column($detailedAccessories, 'subtotal'));
        $grandTotalSales = $shiftData['pagi']['total_sales'] + $shiftData['sore']['total_sales'];
        $grandTotalCash = $shiftData['pagi']['total_cash'] + $shiftData['sore']['total_cash'];
        $grandTotalNonCash = $shiftData['pagi']['total_non_cash'] + $shiftData['sore']['total_non_cash'];
        $totalDebt = (float) $debts->sum('amount');
        $totalDebtPayment = (float) $debtPayments->sum('amount');
        $totalCashDebtPayment = (float) $debtPayments->filter(fn ($p) => strtolower($p->payment_method ?: 'cash') === 'cash')->sum('amount');

        // Low stock products
        $lowStockProducts = Product::query()
            ->where('product_type', 'regular')
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock', 'asc')
            ->get(['id', 'code', 'name', 'stock', 'minimum_stock', 'unit']);

        $detailedDebts = $debts->map(fn ($d) => [
            'id' => $d->id,
            'reference_number' => $d->reference_number,
            'date' => $d->debt_date ? $d->debt_date->format('d/m/Y H:i') : '-',
            'date_raw' => $d->debt_date?->toISOString(),
            'shift' => strtolower($d->shift ?: 'pagi'),
            'customer_name' => $d->customer_name,
            'customer_phone' => $d->customer_phone ?: '-',
            'description' => $d->description ?: 'Utang Pulsa/Barang',
            'amount' => (float) $d->amount,
            'paid_amount' => (float) $d->paid_amount,
            'remaining_amount' => (float) max(0, $d->amount - $d->paid_amount),
            'status' => $d->status,
            'notes' => $d->notes ?: '-',
        ])->all();

        $detailedDebtPayments = $debtPayments->map(fn ($p) => [
            'id' => $p->id,
            'reference_number' => $p->reference_number,
            'date' => $p->payment_date ? $p->payment_date->format('d/m/Y H:i') : '-',
            'date_raw' => $p->payment_date?->toISOString(),
            'shift' => strtolower($p->shift ?: 'pagi'),
            'customer_name' => $p->customer_name,
            'amount' => (float) $p->amount,
            'payment_method' => strtolower($p->payment_method ?: 'cash'),
            'debt_reference' => $p->debt?->reference_number ?: '-',
            'notes' => $p->notes ?: '-',
        ])->all();

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'formatted' => Carbon::parse($startDate)->isoFormat('D MMMM Y') . ($startDate !== $endDate ? ' - ' . Carbon::parse($endDate)->isoFormat('D MMMM Y') : ''),
            ],
            'shifts' => $shiftData,
            'details' => [
                'vouchers' => array_reverse($detailedVouchers),
                'pulsa' => array_reverse($detailedPulsa),
                'accessories' => array_reverse($detailedAccessories),
                'debts' => array_reverse($detailedDebts),
                'debt_payments' => array_reverse($detailedDebtPayments),
            ],
            'expenses' => [
                'items' => $expenseList,
                'total' => $totalExpenses,
            ],
            'summary' => [
                'grand_total_sales' => $grandTotalSales,
                'total_voucher_sales' => $totalVoucherSales,
                'total_pulsa_sales' => $totalPulsaSales,
                'total_accessories_sales' => $totalAccessoriesSales,
                'grand_total_cash' => $grandTotalCash,
                'grand_total_non_cash' => $grandTotalNonCash,
                'grand_total_debt' => $totalDebt,
                'grand_total_debt_payment' => $totalDebtPayment,
                'grand_total_expenses' => $totalExpenses,
                'net_income' => $grandTotalSales - $totalExpenses,
                'cash_in_drawer' => max(0, ($grandTotalCash + $totalCashDebtPayment) - $totalExpenses),
                'total_transactions' => count($detailedVouchers) + count($detailedPulsa) + count($detailedAccessories),
                'total_voucher_count' => count($detailedVouchers),
                'total_pulsa_count' => count($detailedPulsa),
                'total_accessories_count' => count($detailedAccessories),
                'total_debt_count' => $debts->count(),
                'total_debt_payment_count' => $debtPayments->count(),
            ],
            'low_stock' => $lowStockProducts,
        ];
    }

    public function isVoucher(?Product $product): bool
    {
        if (! $product) {
            return false;
        }
        $type = strtolower($product->product_type ?? '');
        $cat = strtolower($product->category ?? '');
        $name = strtolower($product->name ?? '');

        return $type === 'voucher'
            || str_contains($cat, 'voucher')
            || str_contains($cat, 'perdana')
            || str_contains($cat, 'kuota')
            || str_contains($cat, 'data')
            || str_contains($cat, 'paket')
            || str_contains($cat, 'internet')
            || str_starts_with($name, 'voucher')
            || str_starts_with($name, 'perdana')
            || str_contains($name, 'voucher')
            || str_contains($name, 'perdana')
            || str_contains($name, 'kuota')
            || str_contains($name, 'paket data');
    }

    public function isPulsaElektrik(?Product $product): bool
    {
        if (! $product) {
            return false;
        }

        return $product->product_type === 'pulsa' || $product->provider_id !== null;
    }

    public function isVoucherOrPulsa(?Product $product): bool
    {
        return $this->isVoucher($product) || $this->isPulsaElektrik($product);
    }

    private function resolveVoucherPulsaGroupName(?string $walletName, ?string $providerName, ?string $category, ?string $productName): string
    {
        $combined = strtolower(trim(($walletName ?? '') . ' ' . ($providerName ?? '') . ' ' . ($category ?? '') . ' ' . ($productName ?? '')));

        if (str_contains($combined, 'byu') || str_contains($combined, 'by.u')) {
            return 'VOUCHER BYU';
        }
        if (str_contains($combined, 'payfazz')) {
            return 'payfazz';
        }
        if (str_contains($combined, 'propana')) {
            return 'propana';
        }
        if (str_contains($combined, 'shopee')) {
            return 'mitra shopee';
        }
        if (str_contains($combined, 'e-money') || str_contains($combined, 'emoney') || str_contains($combined, 'dana') || str_contains($combined, 'ovo') || str_contains($combined, 'gopay')) {
            return 'E-money';
        }
        if (str_contains($combined, 'smartfren') || str_contains($combined, 'smart')) {
            return 'voucher smartfren';
        }
        if (str_contains($combined, 'axis')) {
            return 'voucher/perdana axis';
        }
        if (str_contains($combined, 'rita') || str_contains($combined, 'three') || str_contains($combined, 'tri') || str_contains($combined, 'tree')) {
            return 'rita/voucher tree (Three)';
        }
        if (str_contains($combined, 'sisimple') || str_contains($combined, 'indosat') || str_contains($combined, 'im3') || str_contains($combined, 'isat')) {
            return 'voucher/sisimple indosat (Indosat)';
        }
        if (str_contains($combined, 'sidompul')) {
            return 'sidompul xl (XL)';
        }
        if (str_contains($combined, 'xl')) {
            if (str_contains($combined, 'voucher') || str_contains($combined, 'vcr') || str_starts_with($combined, 'v')) {
                return 'voucher xl';
            }
            return 'sidompul xl (XL)';
        }
        if (str_contains($combined, 'telkomsel') || str_contains($combined, 'tsel') || str_contains($combined, 'digipos')) {
            if (str_contains($combined, 'voucher') || str_contains($combined, 'vcr')) {
                return 'voucher telkomsel';
            }
            return 'digipost (Telkomsel)';
        }
        if (str_contains($combined, 'perdana')) {
            return 'perdana ALL OPERATOR';
        }

        $w = trim($walletName ?? '');
        $c = trim($category ?? '');
        $p = trim($productName ?? '');
        if ($w && $c && strtolower($w) !== strtolower($c)) {
            return "{$w} ({$c})";
        }

        return $w ?: ($c ?: ($p ?: 'Lain-lain'));
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

    public function masterVoucher(): View
    {
        return view('pos.pages.master-voucher');
    }

    public function dashboard(): JsonResponse
    {
        $expiringProducts = $this->getExpiringStock(30);

        $summary = [
            'total_products' => Product::count(),
            'low_stock_count' => Product::where('product_type', '!=', 'pulsa')->whereColumn('stock', '<=', 'minimum_stock')->count(),
            'expiring_count' => count($expiringProducts),
            'today_sales_total' => (float) Sale::whereDate('sale_date', today())->sum('total'),
            'today_expenses_total' => (float) Expense::whereDate('expense_date', today())->sum('amount'),
            'today_transactions' => Sale::whereDate('sale_date', today())->count(),
        ];

        $products = Product::query()
            ->with(['provider:id,name', 'provider.wallets:id,name', 'sourceWallets:id,name'])
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
            'expiring_products' => $expiringProducts,
            'expiring_count' => count($expiringProducts),
        ]);
    }

    public function getExpiringStock(int $daysThreshold = 30): array
    {
        $today = Carbon::today();
        $cutoff = $today->copy()->addDays($daysThreshold);

        $movements = StockMovement::query()
            ->with('product:id,name,code,variant,unit,stock,is_active,product_type')
            ->whereIn('type', ['in', 'adjustment'])
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $cutoff)
            ->orderBy('expiry_date', 'asc')
            ->get();

        if ($movements->isEmpty()) {
            return [];
        }

        $productIds = $movements->pluck('product_id')->unique()->all();

        $soldFromStock = StockMovement::query()
            ->whereIn('product_id', $productIds)
            ->where('type', 'sale')
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->pluck('serial_number')
            ->all();

        $soldFromPulsa = PulsaSale::query()
            ->whereIn('product_id', $productIds)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->pluck('serial_number')
            ->all();

        $soldFromSaleItems = SaleItem::query()
            ->whereIn('product_id', $productIds)
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->pluck('serial_number')
            ->all();

        $soldCounts = array_count_values(array_map('strval', array_merge($soldFromStock, $soldFromPulsa, $soldFromSaleItems)));

        $expiring = [];
        $consumedCounts = [];
        $productRemainingStock = [];

        foreach ($movements as $m) {
            $prod = $m->product;
            if (!$prod || !$prod->is_active || (int) $prod->stock <= 0) {
                continue;
            }

            if (!isset($productRemainingStock[$prod->id])) {
                $productRemainingStock[$prod->id] = (int) $prod->stock;
            }

            $sn = (string) ($m->serial_number ?? '');
            $movementQty = max(1, (int) ($m->quantity ?? 1));
            $remaining = 0;

            if ($sn !== '') {
                $totalSold = $soldCounts[$sn] ?? 0;
                $alreadyConsumed = $consumedCounts[$sn] ?? 0;

                for ($q = 0; $q < $movementQty; $q++) {
                    if ($alreadyConsumed < $totalSold) {
                        $alreadyConsumed++;
                        $consumedCounts[$sn] = $alreadyConsumed;
                    } else {
                        $remaining++;
                    }
                }
            } else {
                if ($productRemainingStock[$prod->id] > 0) {
                    $remaining = min($movementQty, $productRemainingStock[$prod->id]);
                    $productRemainingStock[$prod->id] -= $remaining;
                }
            }

            if ($remaining > 0) {
                $expDate = Carbon::parse($m->expiry_date)->startOfDay();
                $days = (int) $today->diffInDays($expDate, false);
                $isExpired = $days < 0;

                $statusText = $isExpired
                    ? 'Sudah expired ' . abs($days) . ' hari lalu'
                    : ($days === 0 ? 'Expired hari ini' : 'Expired dalam ' . $days . ' hari');

                $expiring[] = [
                    'id' => $m->id,
                    'product_id' => $prod->id,
                    'product_name' => $prod->name,
                    'product_code' => $prod->code,
                    'variant' => $prod->variant,
                    'product_type' => $prod->product_type,
                    'serial_number' => $sn ?: null,
                    'quantity' => $remaining,
                    'expiry_date' => $m->expiry_date ? $m->expiry_date->format('Y-m-d') : null,
                    'days_remaining' => $days,
                    'is_expired' => $isExpired,
                    'status_text' => $statusText,
                    'cost_price' => (float) $m->cost_price,
                ];
            }
        }

        return $expiring;
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
            'product_type' => ['required', 'string', 'in:regular,pulsa,voucher'],
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
        } elseif (($validated['product_type'] ?? null) === 'voucher') {
            if (!empty($validated['provider_id'])) {
                $provider = Provider::find($validated['provider_id']);
                if ($provider && empty($validated['category'])) {
                    $validated['category'] = $provider->name;
                }
            } elseif (!empty($validated['category'])) {
                $provider = Provider::where('name', $validated['category'])->first();
                if ($provider) {
                    $validated['provider_id'] = $provider->id;
                }
            }
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
            } else {
                if ($product->product_type === 'voucher' && array_key_exists('wallet_ids', $validated)) {
                    $product->sourceWallets()->sync($validated['wallet_ids'] ?? []);
                }
                if ($product->stock > 0) {
                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'in',
                        'quantity' => $product->stock,
                        'cost_price' => $product->cost_price,
                        'serial_number' => $product->serial_number,
                        'note' => 'Stok awal produk baru',
                    ]);
                }
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
            'product_type' => ['nullable', 'string', 'in:regular,pulsa,voucher'],
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

        $targetType = $validated['product_type'] ?? $product->product_type;
        $provider = null;
        if ($targetType === 'pulsa') {
            $provider = $this->resolvePulsaProvider($validated);
            $validated['category'] = $provider->name;
            $validated['provider_id'] = $provider->id;
        } elseif ($targetType === 'voucher') {
            if (!empty($validated['provider_id'])) {
                $provider = Provider::find($validated['provider_id']);
                if ($provider && empty($validated['category'])) {
                    $validated['category'] = $provider->name;
                }
            } elseif (!empty($validated['category'])) {
                $provider = Provider::where('name', $validated['category'])->first();
                if ($provider) {
                    $validated['provider_id'] = $provider->id;
                }
            }
        } else {
            $validated['provider_id'] = null;
        }

        DB::transaction(function () use ($product, $validated, $imagePath, $provider, $targetType): void {
            $product->update([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'product_type' => $targetType,
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

            if ($targetType === 'pulsa') {
                if (array_key_exists('wallet_ids', $validated)) {
                    $provider?->wallets()->sync($validated['wallet_ids'] ?? []);
                }
            } elseif ($targetType === 'voucher') {
                if (array_key_exists('wallet_ids', $validated)) {
                    $product->sourceWallets()->sync($validated['wallet_ids'] ?? []);
                }
            } else {
                $product->sourceWallets()->sync([]);
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

    public function adjustStock(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:add,reduce,set'],
            'quantity' => ['required', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($product->isPulsa()) {
            throw ValidationException::withMessages([
                'product' => 'Produk pulsa elektrik tidak memakai stok fisik.',
            ]);
        }

        $type = $validated['type'];
        $qty = (int) $validated['quantity'];
        $currentStock = (int) $product->stock;

        if ($type === 'add') {
            if ($qty < 1) {
                throw ValidationException::withMessages([
                    'quantity' => 'Jumlah penambahan stok minimal 1.',
                ]);
            }
            $delta = $qty;
            $newStock = $currentStock + $delta;
        } elseif ($type === 'reduce') {
            if ($qty < 1) {
                throw ValidationException::withMessages([
                    'quantity' => 'Jumlah pengurangan stok minimal 1.',
                ]);
            }
            if ($qty > $currentStock) {
                throw ValidationException::withMessages([
                    'quantity' => "Stok tidak mencukupi untuk dikurangi {$qty}. Stok saat ini: {$currentStock}.",
                ]);
            }
            $delta = -$qty;
            $newStock = $currentStock + $delta;
        } else { // 'set'
            $delta = $qty - $currentStock;
            $newStock = $qty;
        }

        if ($delta === 0) {
            return response()->json([
                'message' => 'Tidak ada perubahan pada jumlah stok (stok tetap ' . $currentStock . ').',
                'product' => $this->serializeProduct($product),
            ]);
        }

        DB::transaction(function () use ($product, $delta, $newStock, $validated): void {
            $product->update([
                'stock' => $newStock,
            ]);

            if (isset($validated['cost_price']) && $validated['cost_price'] !== '' && $validated['cost_price'] !== null) {
                $product->update([
                    'cost_price' => (float) $validated['cost_price'],
                ]);
            }

            $note = !empty($validated['note'])
                ? $validated['note']
                : ($delta > 0 ? "Penyesuaian stok tambah (+{$delta})" : "Penyesuaian stok kurang ({$delta})");

            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'adjustment',
                'quantity' => abs($delta),
                'cost_price' => $product->cost_price ?? 0,
                'note' => $note,
            ]);
        });

        return response()->json([
            'message' => "Stok berhasil disesuaikan menjadi {$newStock}.",
            'product' => $this->serializeProduct($product->fresh()),
        ]);
    }

    public function getProductSerials(Product $product): JsonResponse
    {
        $movements = StockMovement::query()
            ->where('product_id', $product->id)
            ->whereIn('type', ['in', 'adjustment'])
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->orderBy('id', 'desc')
            ->get(['id', 'serial_number', 'quantity', 'expiry_date', 'cost_price', 'note', 'created_at']);

        $soldSerials = SaleItem::query()
            ->where('product_id', $product->id)
            ->whereNotNull('serial_number')
            ->pluck('serial_number')
            ->merge(
                StockMovement::query()
                    ->where('product_id', $product->id)
                    ->where('type', 'sale')
                    ->whereNotNull('serial_number')
                    ->pluck('serial_number')
            )
            ->merge(
                PulsaSale::query()
                    ->where('product_id', $product->id)
                    ->where('status', '!=', 'cancelled')
                    ->whereNotNull('serial_number')
                    ->pluck('serial_number')
            )
            ->map(fn ($s) => (string) $s)
            ->all();

        $soldCounts = array_count_values($soldSerials);

        $items = [];
        $movementSerials = [];
        foreach ($movements as $m) {
            $sn = (string) $m->serial_number;
            $movementSerials[] = $sn;
            $isSold = ($soldCounts[$sn] ?? 0) > 0;
            $items[] = [
                'id' => $m->id,
                'serial_number' => $sn,
                'quantity' => (int) $m->quantity,
                'expiry_date' => $m->expiry_date ? $m->expiry_date->format('Y-m-d') : null,
                'cost_price' => (float) $m->cost_price,
                'is_sold' => $isSold,
                'note' => $m->note,
                'created_at' => $m->created_at ? $m->created_at->format('Y-m-d H:i') : null,
            ];
        }

        if (!empty($product->serial_number) && !in_array((string) $product->serial_number, $movementSerials, true)) {
            $pSn = (string) $product->serial_number;
            $items[] = [
                'id' => null,
                'serial_number' => $pSn,
                'quantity' => max(1, (int) $product->stock),
                'expiry_date' => null,
                'cost_price' => (float) $product->cost_price,
                'is_sold' => ($soldCounts[$pSn] ?? 0) > 0,
                'note' => 'Serial bawaan produk',
                'created_at' => $product->created_at ? $product->created_at->format('Y-m-d H:i') : null,
            ];
        }

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'stock' => $product->stock,
            ],
            'serials' => $items,
        ]);
    }

    public function updateProductSerial(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'movement_id' => ['nullable', 'integer'],
            'serial_number' => ['required', 'string', 'max:120'],
            'expiry_date' => ['nullable', 'date'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $newSn = trim($validated['serial_number']);
        $expiryDate = !empty($validated['expiry_date']) ? $validated['expiry_date'] : null;
        $costPrice = isset($validated['cost_price']) && $validated['cost_price'] !== '' && $validated['cost_price'] !== null
            ? (float) $validated['cost_price']
            : null;

        DB::transaction(function () use ($product, $validated, $newSn, $expiryDate, $costPrice): void {
            if (!empty($validated['movement_id'])) {
                $movement = StockMovement::query()
                    ->where('product_id', $product->id)
                    ->findOrFail($validated['movement_id']);

                $updates = ['serial_number' => $newSn];
                if (array_key_exists('expiry_date', $validated)) {
                    $updates['expiry_date'] = $expiryDate;
                }
                if ($costPrice !== null) {
                    $updates['cost_price'] = $costPrice;
                }
                $movement->update($updates);
            } else {
                $product->update([
                    'serial_number' => $newSn,
                ]);
            }
        });

        return response()->json([
            'message' => 'Serial number berhasil diperbarui.',
            'product' => $this->serializeProduct($product->fresh()),
        ]);
    }

    public function storeSale(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:120'],
            'shift' => ['nullable', 'string', 'in:pagi,sore'],
            'payment_method' => ['required', 'string', 'in:cash,qris,transfer,debit,utang'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'cash_paid' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.serial_number' => ['nullable', 'string', 'max:120'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $sale = DB::transaction(function () use ($validated, $request): Sale {
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
            $isDebt = ($validated['payment_method'] ?? 'cash') === 'utang';
            $cashPaid = (float) ($validated['cash_paid'] ?? 0);

            if (! $isDebt && $cashPaid < $total) {
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
                'change_amount' => $isDebt ? 0 : ($cashPaid - $total),
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

            if ($isDebt) {
                $itemNames = collect($items)->map(fn ($i) => $i['product']->name . ' (x' . $i['quantity'] . ')')->implode(', ');
                Debt::create([
                    'reference_number' => 'DEBT-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                    'debt_date' => $sale->sale_date,
                    'customer_name' => $sale->customer_name ?: 'Pelanggan Umum',
                    'amount' => $total,
                    'paid_amount' => $cashPaid,
                    'status' => $cashPaid >= $total ? 'paid' : ($cashPaid > 0 ? 'partial' : 'unpaid'),
                    'shift' => $sale->shift,
                    'sale_id' => $sale->id,
                    'description' => 'Aksesoris: ' . $itemNames,
                    'notes' => $sale->notes,
                    'created_by' => $request->user()?->id,
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
            'provider' => $product->isPulsa() ? $product->category : ($product->provider?->name ?? $product->category),
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

    public function salesHistory(Request $request): JsonResponse
    {
        $physicalSales = Sale::query()
            ->with(['items.product:id,name,code,variant,category'])
            ->latest('sale_date')
            ->latest('id')
            ->limit(200)
            ->get();

        $pulsaSales = PulsaSale::query()
            ->with(['product:id,name,variant,category', 'wallet:id,name'])
            ->latest('sale_date')
            ->latest('id')
            ->limit(200)
            ->get();

        return response()->json([
            'pos_sales' => $physicalSales,
            'pulsa_sales' => $pulsaSales,
        ]);
    }

    public function storeDebt(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:1'],
            'shift' => ['nullable', 'string', 'in:pagi,sore'],
            'debt_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $debt = Debt::create([
            'reference_number' => 'DEBT-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
            'debt_date' => $validated['debt_date'] ?? now(),
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'amount' => $validated['amount'],
            'paid_amount' => 0,
            'status' => 'unpaid',
            'shift' => $validated['shift'] ?? 'pagi',
            'description' => $validated['description'] ?? 'Utang Pulsa/Barang',
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

        return response()->json([
            'message' => 'Catatan utang berhasil disimpan.',
            'debt' => $debt,
        ], 201);
    }

    public function storeDebtPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'debt_id' => ['nullable', 'integer', 'exists:debts,id'],
            'customer_name' => ['required', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string', 'in:cash,qris,transfer,debit'],
            'shift' => ['nullable', 'string', 'in:pagi,sore'],
            'payment_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $payment = DB::transaction(function () use ($validated, $request): DebtPayment {
            $debt = ! empty($validated['debt_id']) ? Debt::lockForUpdate()->find($validated['debt_id']) : null;

            $payment = DebtPayment::create([
                'reference_number' => 'PAY-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'payment_date' => $validated['payment_date'] ?? now(),
                'debt_id' => $debt?->id,
                'customer_name' => $debt?->customer_name ?: $validated['customer_name'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'shift' => $validated['shift'] ?? 'pagi',
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()?->id,
            ]);

            if ($debt) {
                $newPaid = $debt->paid_amount + $validated['amount'];
                $debt->paid_amount = $newPaid;
                $debt->status = $newPaid >= $debt->amount ? 'paid' : 'partial';
                $debt->save();
            }

            return $payment;
        });

        return response()->json([
            'message' => 'Pembayaran utang berhasil dicatat.',
            'payment' => $payment->load('debt'),
        ], 201);
    }

    public function unpaidDebts(): JsonResponse
    {
        $debts = Debt::query()
            ->where('status', '!=', 'paid')
            ->latest('debt_date')
            ->get(['id', 'reference_number', 'customer_name', 'amount', 'paid_amount', 'shift', 'description', 'debt_date']);

        return response()->json($debts);
    }
}
