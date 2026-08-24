<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan Per Kategori & Shift - Zeni POS</title>
    <style>
        @page {
            margin: 18px 22px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .logo-text {
            font-size: 18px;
            font-weight: bold;
            color: #1d4ed8;
            letter-spacing: -0.5px;
        }
        .subtitle {
            font-size: 9.5px;
            color: #64748b;
        }
        .report-title {
            text-align: right;
        }
        .report-title h2 {
            margin: 0;
            font-size: 14px;
            color: #0f172a;
            text-transform: uppercase;
        }
        .report-period {
            font-size: 9.5px;
            color: #475569;
            margin-top: 2px;
        }
        .metrics-table {
            width: 100%;
            margin-bottom: 14px;
        }
        .metric-card {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 8px;
            text-align: center;
        }
        .metric-label {
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #64748b;
            margin-bottom: 3px;
        }
        .metric-value {
            font-size: 11.5px;
            font-weight: bold;
            color: #0f172a;
        }
        .metric-value.profit { color: #16a34a; }
        .metric-value.expense { color: #dc2626; }
        .metric-value.pagi { color: #d97706; }
        .metric-value.sore { color: #4338ca; }
        .metric-value.accent { color: #2563eb; }

        .category-wrapper {
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .category-header {
            background-color: #0f172a;
            color: #ffffff;
            padding: 4px 8px;
            font-size: 10.5px;
            font-weight: bold;
            border-radius: 4px 4px 0 0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .split-table {
            width: 100%;
            border-collapse: collapse;
        }
        .split-col {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }
        .split-col-left {
            padding-right: 4px;
        }
        .split-col-right {
            padding-left: 4px;
        }

        table.shift-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.shift-table th {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px 6px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        table.shift-table th.th-pagi {
            background-color: #fef3c7;
            color: #92400e;
            border-color: #fcd34d;
        }
        table.shift-table th.th-sore {
            background-color: #e0e7ff;
            color: #3730a3;
            border-color: #c7d2fe;
        }
        table.shift-table td {
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
            font-size: 9.5px;
        }
        table.shift-table tr:nth-child(even) td {
            background-color: #fafbfc;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .category-footer-bar {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-top: none;
            padding: 4px 8px;
            font-size: 9.5px;
            font-weight: bold;
            text-align: right;
            border-radius: 0 0 4px 4px;
        }

        table.general-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        table.general-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        table.general-table td {
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
            font-size: 9.5px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            border-left: 3px solid #2563eb;
            padding-left: 6px;
            margin: 14px 0 6px 0;
            text-transform: uppercase;
        }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 8px;
            border-radius: 3px;
            font-weight: bold;
        }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }

        .signature-table {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .signature-box {
            text-align: center;
            width: 30%;
        }
        .signature-line {
            margin-top: 45px;
            border-top: 1px solid #64748b;
            font-weight: bold;
        }
        .footer-note {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px dashed #cbd5e1;
            font-size: 8.5px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Kop Laporan -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="logo-text">ZENI POS</div>
                <div class="subtitle">Laporan Penjualan Shift Pagi & Shift Sore Per Kategori Produk</div>
            </td>
            <td class="report-title" style="vertical-align: middle;">
                <h2>Rekapitulasi Shift & Penjualan</h2>
                <div class="report-period">
                    Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}</strong>
                </div>
                <div class="report-period">Dicetak: {{ now()->translatedFormat('d M Y, H:i') }} WIB</div>
            </td>
        </tr>
    </table>

    <!-- Ringkasan Shift Settlement (Pagi vs Sore) -->
    <table class="metrics-table" cellpadding="0" cellspacing="3">
        <tr>
            <td class="metric-card" width="20%">
                <div class="metric-label">Shift Pagi (Omset)</div>
                <div class="metric-value pagi">Rp {{ number_format($shiftSummary['pagi']['total'], 0, ',', '.') }}</div>
                <div style="font-size: 8px; color: #64748b; margin-top: 2px;">{{ $shiftSummary['pagi']['transactions'] }} Trx (Tunai: Rp {{ number_format($shiftSummary['pagi']['cash'], 0, ',', '.') }})</div>
            </td>
            <td class="metric-card" width="20%">
                <div class="metric-label">Shift Sore (Omset)</div>
                <div class="metric-value sore">Rp {{ number_format($shiftSummary['sore']['total'], 0, ',', '.') }}</div>
                <div style="font-size: 8px; color: #64748b; margin-top: 2px;">{{ $shiftSummary['sore']['transactions'] }} Trx (Tunai: Rp {{ number_format($shiftSummary['sore']['cash'], 0, ',', '.') }})</div>
            </td>
            <td class="metric-card" width="20%">
                <div class="metric-label">Total Omset Toko</div>
                <div class="metric-value accent">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                <div style="font-size: 8px; color: #64748b; margin-top: 2px;">POS Fisik: Rp {{ number_format($totalPosSales, 0, ',', '.') }}</div>
            </td>
            <td class="metric-card" width="20%">
                <div class="metric-label">Total Pengeluaran</div>
                <div class="metric-value expense">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</div>
                <div style="font-size: 8px; color: #64748b; margin-top: 2px;">{{ $expenses->count() }} Pengeluaran</div>
            </td>
            <td class="metric-card" width="20%">
                <div class="metric-label">Estimasi Laba Bersih</div>
                <div class="metric-value profit">Rp {{ number_format($netProfit, 0, ',', '.') }}</div>
                <div style="font-size: 8px; color: #64748b; margin-top: 2px;">Setelah HPP & Beban</div>
            </td>
        </tr>
    </table>

    <!-- Tabel Penjualan Per Kategori (2 Kolom: Kiri Pagi, Kanan Sore) -->
    <div class="section-title">Penjualan Per Kategori (Shift Pagi & Shift Sore)</div>

    @forelse ($categoriesMap as $categoryName => $catData)
        @php
            $pagiItems = array_values($catData['pagi']['items']);
            $soreItems = array_values($catData['sore']['items']);
            $maxRows = max(count($pagiItems), count($soreItems));
            $catTotal = $catData['pagi']['total_amount'] + $catData['sore']['total_amount'];
            $catQty = $catData['pagi']['total_qty'] + $catData['sore']['total_qty'];
        @endphp

        <div class="category-wrapper">
            <div class="category-header">
                KATEGORI: {{ strtoupper($categoryName) }}
                <span style="float: right; font-size: 9.5px;">Total Kategori: {{ $catQty }} item (Rp {{ number_format($catTotal, 0, ',', '.') }})</span>
            </div>

            <table class="split-table">
                <tr>
                    <!-- Kolom Kiri: SHIFT PAGI -->
                    <td class="split-col split-col-left">
                        <table class="shift-table">
                            <thead>
                                <tr>
                                    <th colspan="3" class="th-pagi" style="text-align: center;">☀ SHIFT PAGI</th>
                                </tr>
                                <tr>
                                    <th width="60%">Nama Produk</th>
                                    <th width="15%" class="text-center">Qty</th>
                                    <th width="25%" class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (count($pagiItems) > 0)
                                    @for ($i = 0; $i < $maxRows; $i++)
                                        @if (isset($pagiItems[$i]))
                                            <tr>
                                                <td>{{ $pagiItems[$i]['name'] }}</td>
                                                <td class="text-center">{{ $pagiItems[$i]['qty'] }}</td>
                                                <td class="text-right">Rp {{ number_format($pagiItems[$i]['total'], 0, ',', '.') }}</td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td style="color: transparent;">-</td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        @endif
                                    @endfor
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center" style="color: #94a3b8; padding: 10px;">Tidak ada penjualan pagi</td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #fffbeb; font-weight: bold;">
                                    <td>Total Shift Pagi</td>
                                    <td class="text-center">{{ $catData['pagi']['total_qty'] }}</td>
                                    <td class="text-right" style="color: #b45309;">Rp {{ number_format($catData['pagi']['total_amount'], 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </td>

                    <!-- Kolom Kanan: SHIFT SORE -->
                    <td class="split-col split-col-right">
                        <table class="shift-table">
                            <thead>
                                <tr>
                                    <th colspan="3" class="th-sore" style="text-align: center;">🌙 SHIFT SORE</th>
                                </tr>
                                <tr>
                                    <th width="60%">Nama Produk</th>
                                    <th width="15%" class="text-center">Qty</th>
                                    <th width="25%" class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (count($soreItems) > 0)
                                    @for ($i = 0; $i < $maxRows; $i++)
                                        @if (isset($soreItems[$i]))
                                            <tr>
                                                <td>{{ $soreItems[$i]['name'] }}</td>
                                                <td class="text-center">{{ $soreItems[$i]['qty'] }}</td>
                                                <td class="text-right">Rp {{ number_format($soreItems[$i]['total'], 0, ',', '.') }}</td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td style="color: transparent;">-</td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        @endif
                                    @endfor
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center" style="color: #94a3b8; padding: 10px;">Tidak ada penjualan sore</td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #eef2ff; font-weight: bold;">
                                    <td>Total Shift Sore</td>
                                    <td class="text-center">{{ $catData['sore']['total_qty'] }}</td>
                                    <td class="text-right" style="color: #4338ca;">Rp {{ number_format($catData['sore']['total_amount'], 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    @empty
        <div style="border: 1px dashed #cbd5e1; padding: 15px; text-align: center; color: #64748b; margin-bottom: 15px;">
            Tidak ada data transaksi penjualan pada periode yang dipilih.
        </div>
    @endforelse

    <!-- Pengeluaran Operasional -->
    @if ($expenses->count())
    <div class="section-title">Pengeluaran Operasional</div>
    <table class="general-table">
        <thead>
            <tr>
                <th width="15%">Tanggal</th>
                <th width="20%">Kategori</th>
                <th width="45%">Deskripsi / Keperluan</th>
                <th width="20%" class="text-right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($expenses as $expense)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($expense->expense_date)->translatedFormat('d/m/Y') }}</td>
                    <td><span class="badge badge-warning">{{ $expense->category }}</span></td>
                    <td>{{ $expense->description }} {{ $expense->notes ? '('.$expense->notes.')' : '' }}</td>
                    <td class="text-right expense" style="color: #dc2626;"><strong>Rp {{ number_format($expense->amount, 0, ',', '.') }}</strong></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="3" class="text-right">Total Pengeluaran:</td>
                <td class="text-right" style="color: #dc2626;">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

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
                <div>Penanggung Jawab / Admin,</div>
                <div class="signature-line">{{ auth()->user()?->name ?? 'Admin Zeni POS' }}</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen Laporan Kasir & Rekap Shift Zeni POS &copy; {{ date('Y') }}. Tercatat sah dalam database sistem.
    </div>

</body>
</html>
