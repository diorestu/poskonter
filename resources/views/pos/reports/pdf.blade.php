<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekapan Penjualan Harian - Zeni POS</title>
    <style>
        @page {
            margin: 14px 16px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8px;
            color: #0f172a;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .header-box {
            width: 100%;
            margin-bottom: 6px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 4px;
            position: relative;
        }
        .header-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
        }
        .date-box {
            border: 1px solid #0f172a;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: bold;
            display: inline-block;
            float: right;
            margin-top: -16px;
        }
        
        table.main-layout {
            width: 100%;
            border-collapse: collapse;
        }
        td.shift-column {
            width: 50%;
            vertical-align: top;
        }
        td.shift-column-left {
            padding-right: 4px;
        }
        td.shift-column-right {
            padding-left: 4px;
        }

        .section-header {
            background-color: #f1f5f9;
            border: 1px solid #0f172a;
            font-weight: bold;
            font-size: 8.5px;
            text-transform: uppercase;
            padding: 2px 4px;
            text-align: center;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        table.data-table th {
            background-color: #f8fafc;
            border: 1px solid #0f172a;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 2px 3px;
            text-align: left;
        }
        table.data-table td {
            border: 1px solid #0f172a;
            padding: 2px 3px;
            font-size: 7.5px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        .total-row td {
            font-weight: bold;
            background-color: #f8fafc;
        }

        .payment-box {
            border: 1px solid #0f172a;
            padding: 3px 5px;
            margin-bottom: 5px;
            background-color: #ffffff;
        }
        .payment-row {
            margin-bottom: 1.5px;
            font-size: 7.5px;
        }

        .notes-box {
            border: 1px solid #0f172a;
            min-height: 28px;
            padding: 3px 4px;
            margin-bottom: 5px;
            font-size: 7.5px;
            color: #334155;
        }

        .summary-box {
            border: 1px solid #0f172a;
            padding: 3px 5px;
            background-color: #f8fafc;
            margin-bottom: 6px;
        }
        .summary-row {
            margin-bottom: 2px;
            font-size: 8px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .signature-box {
            width: 33.33%;
            border: 1px solid #0f172a;
            text-align: center;
            padding: 3px;
            font-size: 7.5px;
        }
        .signature-line {
            margin-top: 25px;
            font-weight: bold;
        }

        .page-break {
            page-break-before: always;
        }

        .kpi-strip {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .kpi-strip td {
            border: 1px solid #0f172a;
            padding: 4px;
            text-align: center;
            background-color: #f8fafc;
        }
        .kpi-title {
            font-size: 7px;
            text-transform: uppercase;
            color: #64748b;
        }
        .kpi-val {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
        }
    </style>
</head>
<body>

    <!-- ==================== HALAMAN 1: REKAPAN 2 KOLOM (PAGI & SORE) ==================== -->
    <div class="header-box">
        <div class="header-title">Rekapan Penjualan Harian</div>
        <div class="date-box">tgl: {{ $reportData['period']['formatted'] }}</div>
    </div>

    @php
        $pagi = $reportData['shifts']['pagi'] ?? [];
        $sore = $reportData['shifts']['sore'] ?? [];
        $expenses = $reportData['expenses'] ?? [];
        $summary = $reportData['summary'] ?? [];
        $details = $reportData['details'] ?? [];
    @endphp

    <table class="main-layout">
        <tr>
            <!-- ==================== KOLOM SHIFT PAGI ==================== -->
            <td class="shift-column shift-column-left">
                <div class="section-header">Penjualan Pagi</div>

                <!-- 1. Voucher & Pulsa Elektrik Table -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="65%">Produk</th>
                            <th width="35%" class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pagi['voucher_pulsa'] ?? [] as $vp)
                            <tr>
                                <td style="{{ $vp['total'] > 0 ? 'font-weight: bold;' : 'color: #64748b;' }}">{{ $vp['name'] }}</td>
                                <td class="text-right" style="{{ $vp['total'] > 0 ? 'font-weight: bold;' : 'color: #94a3b8;' }}">
                                    {{ $vp['total'] > 0 ? number_format($vp['total'], 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center" style="color: #64748b;">-</td>
                            </tr>
                        @endforelse
                        <tr class="total-row">
                            <td>Total Penjualan</td>
                            <td class="text-right">Rp {{ number_format($pagi['total_voucher_pulsa'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- 2. Penjualan Acesories (Barang Fisik) -->
                <div class="section-header" style="border-top: none;">Penjualan Acesories</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="50%">Produk</th>
                            <th width="22%" class="text-right">Harga</th>
                            <th width="28%" class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pagi['accessories'] ?? [] as $acc)
                            <tr>
                                <td>{{ $acc['name'] }} (x{{ $acc['qty'] }})</td>
                                <td class="text-right">{{ number_format($acc['price'], 0, ',', '.') }}</td>
                                <td class="text-right font-bold">{{ number_format($acc['total'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center" style="color: #64748b; padding: 4px;">- Tidak ada penjualan acesories -</td>
                            </tr>
                        @endforelse
                        <tr class="total-row">
                            <td colspan="2">Total Acesories</td>
                            <td class="text-right">Rp {{ number_format($pagi['total_accessories'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- 3. Breakdown Pembayaran Non-Tunai & Utang -->
                <div class="payment-box">
                    <div class="payment-row">trf via wa: <strong>Rp {{ number_format($pagi['payments']['transfer'] ?? 0, 0, ',', '.') }}</strong></div>
                    <div class="payment-row">trf bank: <strong>Rp {{ number_format($pagi['payments']['bank'] ?? 0, 0, ',', '.') }}</strong></div>
                    <div class="payment-row">TRF QRIS: <strong>Rp {{ number_format($pagi['payments']['qris'] ?? 0, 0, ',', '.') }}</strong></div>
                    <div class="payment-row">E-MONEY: <strong>Rp {{ number_format($pagi['payments']['debit'] ?? 0, 0, ',', '.') }}</strong></div>
                    <div class="payment-row" style="color: #b45309;">utang pulsa rp.: <strong>Rp {{ number_format($pagi['total_debt'] ?? 0, 0, ',', '.') }}</strong></div>
                    <div class="payment-row" style="margin-top: 2px; border-top: 1px dashed #cbd5e1; padding-top: 2px;">
                        Tunai (Cash): <strong>Rp {{ number_format($pagi['total_cash'] ?? 0, 0, ',', '.') }}</strong>
                    </div>
                </div>

                <div class="notes-box" style="min-height: 18px; margin-top: -3px; margin-bottom: 4px;">
                    <strong>Catatan Utang / Bon:</strong>
                    <div>{{ $pagi['debt_notes'] ?? '-' }}</div>
                </div>

                <!-- 4. Pengeluaran Shift -->
                <div class="payment-box" style="margin-top: -2px;">
                    <div class="payment-row font-bold">Pengeluaran: Rp {{ number_format(($expenses['total'] ?? 0) / 2, 0, ',', '.') }}</div>
                </div>
                <div class="notes-box">
                    <strong>Catatan / Rincian:</strong>
                    @if (!empty($expenses['items']))
                        @foreach ($expenses['items'] as $exp)
                            <div>- {{ $exp['description'] }}: Rp {{ number_format($exp['amount'], 0, ',', '.') }}</div>
                        @endforeach
                    @else
                        <div>-</div>
                    @endif
                </div>

                <!-- 5. Rekapitulasi Akhir Shift Pagi -->
                @php
                    $pagiTotalMoney = $pagi['total_money'] ?? (($pagi['total_cash'] ?? 0) + ($pagi['total_non_cash'] ?? 0) + ($pagi['total_debt'] ?? 0));
                    $pagiSel = $pagi['selisih'] ?? ($pagiTotalMoney - ($pagi['total_sales'] ?? 0));
                @endphp
                <div class="summary-box">
                    <div class="summary-row">total penjualan pagi: <strong style="float: right;">Rp {{ number_format($pagi['total_sales'] ?? 0, 0, ',', '.') }}</strong></div>
                    <div class="summary-row">total uang + nontunai: <strong style="float: right;">Rp {{ number_format($pagiTotalMoney, 0, ',', '.') }}</strong></div>
                    <div class="summary-row font-bold" style="color: {{ $pagiSel < 0 ? '#b91c1c' : ($pagiSel > 0 ? '#047857' : '#0f172a') }};">
                        selisih: <strong style="float: right;">{{ $pagiSel < 0 ? '- ' : '' }}Rp {{ number_format(abs($pagiSel), 0, ',', '.') }}</strong>
                    </div>
                    <div class="summary-row" style="margin-top: 2px; border-top: 1px dashed #cbd5e1; padding-top: 2px;">
                        pembayaran utang: <strong style="float: right;">Rp {{ number_format($pagi['total_debt_payment'] ?? 0, 0, ',', '.') }}</strong>
                    </div>
                    @if (!empty($pagi['debt_payment_notes']) && $pagi['debt_payment_notes'] !== '-')
                        <div style="font-size: 7px; color: #475569; margin-bottom: 2px;">({{ $pagi['debt_payment_notes'] }})</div>
                    @endif
                    <div class="summary-row font-bold" style="border-top: 1px solid #0f172a; padding-top: 2px; margin-top: 2px;">
                        sisa kas bersih: <strong style="float: right;">Rp {{ number_format($pagi['net_cash'] ?? max(0, ($pagi['total_cash'] ?? 0) - (($expenses['total'] ?? 0) / 2)), 0, ',', '.') }}</strong>
                    </div>
                </div>
            </td>

            <!-- ==================== KOLOM SHIFT SORE ==================== -->
            <td class="shift-column shift-column-right">
                <div class="section-header">Penjualan Sore</div>

                <!-- 1. Voucher & Pulsa Elektrik Table -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="65%">Produk</th>
                            <th width="35%" class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sore['voucher_pulsa'] ?? [] as $vp)
                            <tr>
                                <td style="{{ $vp['total'] > 0 ? 'font-weight: bold;' : 'color: #64748b;' }}">{{ $vp['name'] }}</td>
                                <td class="text-right" style="{{ $vp['total'] > 0 ? 'font-weight: bold;' : 'color: #94a3b8;' }}">
                                    {{ $vp['total'] > 0 ? number_format($vp['total'], 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center" style="color: #64748b;">-</td>
                            </tr>
                        @endforelse
                        <tr class="total-row">
                            <td>Total Penjualan</td>
                            <td class="text-right">Rp {{ number_format($sore['total_voucher_pulsa'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- 2. Penjualan Acesories (Barang Fisik) -->
                <div class="section-header" style="border-top: none;">Penjualan Acesories</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="50%">Produk</th>
                            <th width="22%" class="text-right">Harga</th>
                            <th width="28%" class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sore['accessories'] ?? [] as $acc)
                            <tr>
                                <td>{{ $acc['name'] }} (x{{ $acc['qty'] }})</td>
                                <td class="text-right">{{ number_format($acc['price'], 0, ',', '.') }}</td>
                                <td class="text-right font-bold">{{ number_format($acc['total'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center" style="color: #64748b; padding: 4px;">- Tidak ada penjualan acesories -</td>
                            </tr>
                        @endforelse
                        <tr class="total-row">
                            <td colspan="2">Total Acesories</td>
                            <td class="text-right">Rp {{ number_format($sore['total_accessories'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- 3. Breakdown Pembayaran Non-Tunai & Utang -->
                <div class="payment-box">
                    <div class="payment-row">trf via wa: <strong>Rp {{ number_format($sore['payments']['transfer'] ?? 0, 0, ',', '.') }}</strong></div>
                    <div class="payment-row">trf bank: <strong>Rp {{ number_format($sore['payments']['bank'] ?? 0, 0, ',', '.') }}</strong></div>
                    <div class="payment-row">TRF QRIS: <strong>Rp {{ number_format($sore['payments']['qris'] ?? 0, 0, ',', '.') }}</strong></div>
                    <div class="payment-row">E-MONEY: <strong>Rp {{ number_format($sore['payments']['debit'] ?? 0, 0, ',', '.') }}</strong></div>
                    <div class="payment-row" style="color: #b45309;">utang pulsa rp.: <strong>Rp {{ number_format($sore['total_debt'] ?? 0, 0, ',', '.') }}</strong></div>
                    <div class="payment-row" style="margin-top: 2px; border-top: 1px dashed #cbd5e1; padding-top: 2px;">
                        Tunai (Cash): <strong>Rp {{ number_format($sore['total_cash'] ?? 0, 0, ',', '.') }}</strong>
                    </div>
                </div>

                <div class="notes-box" style="min-height: 18px; margin-top: -3px; margin-bottom: 4px;">
                    <strong>Catatan Utang / Bon:</strong>
                    <div>{{ $sore['debt_notes'] ?? '-' }}</div>
                </div>

                <!-- 4. Pengeluaran Shift -->
                <div class="payment-box" style="margin-top: -2px;">
                    <div class="payment-row font-bold">Pengeluaran: Rp {{ number_format(($expenses['total'] ?? 0) / 2, 0, ',', '.') }}</div>
                </div>
                <div class="notes-box">
                    <strong>Catatan / Rincian:</strong>
                    <div>-</div>
                </div>

                <!-- 5. Rekapitulasi Akhir Shift Sore -->
                @php
                    $soreTotalMoney = $sore['total_money'] ?? (($sore['total_cash'] ?? 0) + ($sore['total_non_cash'] ?? 0) + ($sore['total_debt'] ?? 0));
                    $soreSel = $sore['selisih'] ?? ($soreTotalMoney - ($sore['total_sales'] ?? 0));
                @endphp
                <div class="summary-box">
                    <div class="summary-row">total penjualan sore: <strong style="float: right;">Rp {{ number_format($sore['total_sales'] ?? 0, 0, ',', '.') }}</strong></div>
                    <div class="summary-row">total uang + nontunai: <strong style="float: right;">Rp {{ number_format($soreTotalMoney, 0, ',', '.') }}</strong></div>
                    <div class="summary-row font-bold" style="color: {{ $soreSel < 0 ? '#b91c1c' : ($soreSel > 0 ? '#047857' : '#0f172a') }};">
                        selisih: <strong style="float: right;">{{ $soreSel < 0 ? '- ' : '' }}Rp {{ number_format(abs($soreSel), 0, ',', '.') }}</strong>
                    </div>
                    <div class="summary-row" style="margin-top: 2px; border-top: 1px dashed #cbd5e1; padding-top: 2px;">
                        pembayaran utang: <strong style="float: right;">Rp {{ number_format($sore['total_debt_payment'] ?? 0, 0, ',', '.') }}</strong>
                    </div>
                    @if (!empty($sore['debt_payment_notes']) && $sore['debt_payment_notes'] !== '-')
                        <div style="font-size: 7px; color: #475569; margin-bottom: 2px;">({{ $sore['debt_payment_notes'] }})</div>
                    @endif
                    <div class="summary-row font-bold" style="border-top: 1px solid #0f172a; padding-top: 2px; margin-top: 2px;">
                        sisa kas bersih: <strong style="float: right;">Rp {{ number_format($sore['net_cash'] ?? max(0, ($sore['total_cash'] ?? 0) - (($expenses['total'] ?? 0) / 2)), 0, ',', '.') }}</strong>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Tanda Tangan Serah Terima Kasir Shift -->
    <table class="signature-table">
        <tr>
            <td class="signature-box">
                <div>Kasir Shift Pagi,</div>
                <div class="signature-line">( ........................................ )</div>
            </td>
            <td class="signature-box">
                <div>Kasir Shift Sore,</div>
                <div class="signature-line">( ........................................ )</div>
            </td>
            <td class="signature-box">
                <div>Penanggung Jawab / Owner,</div>
                <div class="signature-line">( ........................................ )</div>
            </td>
        </tr>
    </table>

    <!-- ==================== HALAMAN 2: DETAIL TRANSAKSI PENJUALAN ==================== -->
    <div class="page-break"></div>

    <div class="header-box">
        <div class="header-title">Detail Hasil Transaksi Penjualan</div>
        <div class="date-box">Periode: {{ $reportData['period']['formatted'] }}</div>
    </div>

    <!-- Ringkasan KPI -->
    <table class="kpi-strip">
        <tr>
            <td width="17%">
                <div class="kpi-title">Total Penjualan</div>
                <div class="kpi-val" style="color: #2563eb;">Rp {{ number_format($summary['grand_total_sales'] ?? 0, 0, ',', '.') }}</div>
            </td>
            <td width="17%">
                <div class="kpi-title">Voucher ({{ $summary['total_voucher_count'] ?? 0 }} Trx)</div>
                <div class="kpi-val" style="color: #7e22ce;">Rp {{ number_format($summary['total_voucher_sales'] ?? 0, 0, ',', '.') }}</div>
            </td>
            <td width="17%">
                <div class="kpi-title">Pulsa ({{ $summary['total_pulsa_count'] ?? 0 }} Trx)</div>
                <div class="kpi-val" style="color: #0284c7;">Rp {{ number_format($summary['total_pulsa_sales'] ?? 0, 0, ',', '.') }}</div>
            </td>
            <td width="17%">
                <div class="kpi-title">Acesories ({{ $summary['total_accessories_count'] ?? 0 }} Trx)</div>
                <div class="kpi-val" style="color: #0d9488;">Rp {{ number_format($summary['total_accessories_sales'] ?? 0, 0, ',', '.') }}</div>
            </td>
            <td width="16%">
                <div class="kpi-title">Utang ({{ $summary['total_debt_count'] ?? 0 }} Trx)</div>
                <div class="kpi-val" style="color: #b45309;">Rp {{ number_format($summary['grand_total_debt'] ?? 0, 0, ',', '.') }}</div>
            </td>
            <td width="16%">
                <div class="kpi-title">Bayar Utang ({{ $summary['total_debt_payment_count'] ?? 0 }} Trx)</div>
                <div class="kpi-val" style="color: #047857;">Rp {{ number_format($summary['grand_total_debt_payment'] ?? 0, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- 1. Detail Transaksi Voucher -->
    <div class="section-header" style="text-align: left; background-color: #f3e8ff; border-color: #a855f7;">
        1. Rincian Penjualan Voucher (Total: Rp {{ number_format($summary['total_voucher_sales'] ?? 0, 0, ',', '.') }})
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="18%">Ref / Invoice</th>
                <th width="15%">Waktu</th>
                <th width="8%">Shift</th>
                <th width="24%">Produk Voucher</th>
                <th width="15%">Serial Number (SN)</th>
                <th width="10%">Metode</th>
                <th width="10%" class="text-right">Harga</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($details['vouchers'] ?? [] as $v)
                <tr>
                    <td class="font-bold">{{ $v['reference_number'] }}</td>
                    <td>{{ $v['date'] }}</td>
                    <td class="text-center">{{ strtoupper($v['shift']) }}</td>
                    <td>{{ $v['product_name'] }} {{ $v['variant'] && $v['variant'] !== '-' ? '(' . $v['variant'] . ')' : '' }}</td>
                    <td>{{ $v['serial_number'] }}</td>
                    <td class="text-center">{{ strtoupper($v['payment_method']) }}</td>
                    <td class="text-right font-bold">{{ number_format($v['price'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="color: #64748b; padding: 4px;">- Tidak ada transaksi voucher -</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 2. Detail Transaksi Pulsa Elektrik -->
    <div class="section-header" style="text-align: left; background-color: #e0f2fe; border-color: #0284c7; margin-top: 6px;">
        2. Rincian Penjualan Pulsa Elektrik (Total: Rp {{ number_format($summary['total_pulsa_sales'] ?? 0, 0, ',', '.') }})
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="18%">Ref Transaksi</th>
                <th width="15%">Waktu</th>
                <th width="8%">Shift</th>
                <th width="12%">Provider</th>
                <th width="16%">Produk</th>
                <th width="14%">No Tujuan</th>
                <th width="8%">Metode</th>
                <th width="9%" class="text-right">Harga</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($details['pulsa'] ?? [] as $p)
                <tr>
                    <td class="font-bold">{{ $p['reference_number'] }}</td>
                    <td>{{ $p['date'] }}</td>
                    <td class="text-center">{{ strtoupper($p['shift']) }}</td>
                    <td>{{ $p['provider'] }}</td>
                    <td>{{ $p['product_name'] }}</td>
                    <td>{{ $p['target_number'] }}</td>
                    <td class="text-center">{{ strtoupper($p['payment_method']) }}</td>
                    <td class="text-right font-bold">{{ number_format($p['price'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="color: #64748b; padding: 4px;">- Tidak ada transaksi pulsa elektrik -</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 3. Detail Transaksi Acesories -->
    <div class="section-header" style="text-align: left; background-color: #ccfbf1; border-color: #0d9488; margin-top: 6px;">
        3. Rincian Penjualan Acesories / Barang Fisik (Total: Rp {{ number_format($summary['total_accessories_sales'] ?? 0, 0, ',', '.') }})
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="18%">Invoice</th>
                <th width="15%">Waktu</th>
                <th width="8%">Shift</th>
                <th width="24%">Produk & Varian</th>
                <th width="15%">Serial Number (SN)</th>
                <th width="6%" class="text-center">Qty</th>
                <th width="14%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($details['accessories'] ?? [] as $a)
                <tr>
                    <td class="font-bold">{{ $a['invoice_number'] }}</td>
                    <td>{{ $a['date'] }}</td>
                    <td class="text-center">{{ strtoupper($a['shift']) }}</td>
                    <td>{{ $a['product_name'] }} {{ $a['variant'] && $a['variant'] !== '-' ? '(' . $a['variant'] . ')' : '' }}</td>
                    <td>{{ $a['serial_number'] }}</td>
                    <td class="text-center">{{ $a['quantity'] }}</td>
                    <td class="text-right font-bold">{{ number_format($a['subtotal'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="color: #64748b; padding: 4px;">- Tidak ada transaksi acesories -</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 4. Detail Utang Pelanggan -->
    <div class="section-header" style="text-align: left; background-color: #fef3c7; border-color: #d97706; margin-top: 6px;">
        4. Rincian Utang Pelanggan / Bon (Total: Rp {{ number_format($summary['grand_total_debt'] ?? 0, 0, ',', '.') }})
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="18%">Ref / Kode</th>
                <th width="15%">Waktu</th>
                <th width="8%">Shift</th>
                <th width="18%">Pelanggan</th>
                <th width="17%">Keterangan</th>
                <th width="12%" class="text-right">Total Utang</th>
                <th width="12%" class="text-right">Sisa Utang</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($details['debts'] ?? [] as $d)
                <tr>
                    <td class="font-bold">{{ $d['reference_number'] }}</td>
                    <td>{{ $d['date'] }}</td>
                    <td class="text-center">{{ strtoupper($d['shift']) }}</td>
                    <td>{{ $d['customer_name'] }}</td>
                    <td>{{ $d['description'] }}</td>
                    <td class="text-right font-bold">{{ number_format($d['amount'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #b91c1c;">{{ number_format($d['remaining_amount'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="color: #64748b; padding: 4px;">- Tidak ada catatan utang pada periode ini -</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 5. Detail Pembayaran Utang -->
    <div class="section-header" style="text-align: left; background-color: #d1fae5; border-color: #059669; margin-top: 6px;">
        5. Rincian Penerimaan Pembayaran Utang (Total: Rp {{ number_format($summary['grand_total_debt_payment'] ?? 0, 0, ',', '.') }})
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="18%">Ref Pembayaran</th>
                <th width="15%">Waktu</th>
                <th width="8%">Shift</th>
                <th width="18%">Pelanggan</th>
                <th width="15%">Ref Utang</th>
                <th width="12%">Metode</th>
                <th width="14%" class="text-right">Nominal Bayar</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($details['debt_payments'] ?? [] as $p)
                <tr>
                    <td class="font-bold">{{ $p['reference_number'] }}</td>
                    <td>{{ $p['date'] }}</td>
                    <td class="text-center">{{ strtoupper($p['shift']) }}</td>
                    <td>{{ $p['customer_name'] }}</td>
                    <td>{{ $p['debt_reference'] }}</td>
                    <td class="text-center">{{ strtoupper($p['payment_method']) }}</td>
                    <td class="text-right font-bold" style="color: #047857;">{{ number_format($p['amount'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="color: #64748b; padding: 4px;">- Tidak ada pembayaran utang pada periode ini -</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
