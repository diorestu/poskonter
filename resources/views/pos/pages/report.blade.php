@extends('layouts.app')

@section('content')
    <section class="space-y-4">
        <!-- Date Filter & PDF Download Card -->
        <div class="list-panel p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="font-semibold text-lg">Laporan & Rekapan Penjualan Harian</h1>
                    <p class="text-[color:var(--muted)] text-xs">Rekapitulasi penjualan per shift (pagi & sore), produk voucher, pulsa elektrik, serta barang aksesoris.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative flex items-center">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-[#64748b]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
                        </div>
                        <input type="text" id="reportDateRange" class="field py-1.5 pl-8 pr-3 text-xs w-60 bg-white font-medium cursor-pointer" placeholder="Pilih Rentang Tanggal..." readonly>
                    </div>
                    <button type="button" class="btn btn-secondary text-xs py-1.5" id="btnFilterToday">Hari Ini</button>
                    <button type="button" class="btn btn-secondary text-xs py-1.5" id="btnFilterThisMonth">Bulan Ini</button>
                    <button type="button" class="btn btn-secondary text-xs py-1.5 font-semibold text-blue-700 border-blue-300" id="btnFilterAll">Semua Data</button>
                    <a href="#" target="_blank" class="btn btn-secondary text-xs py-1.5" id="btnPreviewPdf">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></span>
                        <span>Lihat PDF</span>
                    </a>
                    <a href="#" class="btn btn-primary text-xs py-1.5 font-medium" id="btnDownloadPdf">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg></span>
                        <span>Unduh PDF</span>
                    </a>
                </div>
            </div>
            <div class="mt-2 pt-2 border-t border-[#f1f5f9] text-[11px] text-[color:var(--muted)] flex items-center justify-between">
                <span>Status Periode: <strong id="filterStatusText" class="text-blue-700">Hari Ini</strong>. Jika ingin melihat data penjualan terdahulu, gunakan pemilih tanggal atau tombol <strong>Semua Data</strong>.</span>
            </div>
        </div>

        <!-- Summary KPI Cards -->
        <div class="grid gap-3 grid-cols-2 sm:grid-cols-3 md:grid-cols-5 xl:grid-cols-10" id="summaryCards">
            <div class="stat-box p-3">
                <div class="text-[10px] uppercase tracking-[0.14em] text-[color:var(--muted)]">Total Penjualan</div>
                <div class="mt-1 text-base font-bold text-blue-600" id="statTotalSales">Rp 0</div>
                <div class="text-[10px] text-[color:var(--muted)]" id="statTotalCount">0 Trx</div>
            </div>
            <div class="stat-box p-3">
                <div class="text-[10px] uppercase tracking-[0.14em] text-[color:var(--muted)]">Voucher</div>
                <div class="mt-1 text-base font-bold text-purple-600" id="statVoucherSales">Rp 0</div>
                <div class="text-[10px] text-purple-700 font-semibold" id="statVoucherCount">0 Trx</div>
            </div>
            <div class="stat-box p-3">
                <div class="text-[10px] uppercase tracking-[0.14em] text-[color:var(--muted)]">Pulsa Elektrik</div>
                <div class="mt-1 text-base font-bold text-sky-600" id="statPulsaSales">Rp 0</div>
                <div class="text-[10px] text-sky-700 font-semibold" id="statPulsaCount">0 Trx</div>
            </div>
            <div class="stat-box p-3">
                <div class="text-[10px] uppercase tracking-[0.14em] text-[color:var(--muted)]">Acesories</div>
                <div class="mt-1 text-base font-bold text-teal-600" id="statAccessoriesSales">Rp 0</div>
                <div class="text-[10px] text-teal-700 font-semibold" id="statAccessoriesCount">0 Trx</div>
            </div>
            <div class="stat-box p-3">
                <div class="text-[10px] uppercase tracking-[0.14em] text-[color:var(--muted)]">Shift Pagi</div>
                <div class="mt-1 text-base font-bold text-amber-600" id="statShiftPagi">Rp 0</div>
            </div>
            <div class="stat-box p-3">
                <div class="text-[10px] uppercase tracking-[0.14em] text-[color:var(--muted)]">Shift Sore</div>
                <div class="mt-1 text-base font-bold text-indigo-600" id="statShiftSore">Rp 0</div>
            </div>
            <div class="stat-box p-3">
                <div class="text-[10px] uppercase tracking-[0.14em] text-[color:var(--muted)]">Utang Baru</div>
                <div class="mt-1 text-base font-bold text-amber-700" id="statDebtTotal">Rp 0</div>
                <div class="text-[10px] text-amber-700 font-semibold" id="statDebtCount">0 Trx</div>
            </div>
            <div class="stat-box p-3">
                <div class="text-[10px] uppercase tracking-[0.14em] text-[color:var(--muted)]">Pembayaran Utang</div>
                <div class="mt-1 text-base font-bold text-emerald-700" id="statDebtPaymentTotal">Rp 0</div>
                <div class="text-[10px] text-emerald-700 font-semibold" id="statDebtPaymentCount">0 Trx</div>
            </div>
            <div class="stat-box p-3">
                <div class="text-[10px] uppercase tracking-[0.14em] text-[color:var(--muted)]">Pengeluaran</div>
                <div class="mt-1 text-base font-bold text-rose-600" id="statExpenses">Rp 0</div>
            </div>
            <div class="stat-box p-3">
                <div class="text-[10px] uppercase tracking-[0.14em] text-[color:var(--muted)]">Sisa Kas Toko</div>
                <div class="mt-1 text-base font-bold text-emerald-600" id="statCashInDrawer">Rp 0</div>
            </div>
        </div>

        <!-- 2-Column Daily Sales Recap (Shift Pagi vs Shift Sore) -->
        <div class="list-panel p-4 space-y-4">
            <div class="flex items-center justify-between border-b pb-3 border-[#e2e8f0]">
                <div class="font-bold text-base text-[color:var(--text)] uppercase tracking-wide">
                    Rekapan Penjualan Harian (<span id="recapPeriodText" class="text-blue-600">Hari Ini</span>)
                </div>
                <div class="text-xs text-[color:var(--muted)] font-medium">Format 2 Kolom Shift Pagi & Sore (Sesuai Kertas Rekapan)</div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- ==================== KOLOM SHIFT PAGI ==================== -->
                <div class="border border-[#cbd5e1] rounded-lg p-4 bg-[#fafbfc] space-y-4">
                    <div class="bg-amber-100 text-amber-900 font-bold px-3 py-1.5 rounded text-sm text-center uppercase tracking-wide border border-amber-300">
                        Penjualan Pagi
                    </div>

                    <!-- 1. Voucher & Pulsa Elektrik Table -->
                    <div>
                        <div class="text-xs font-bold text-[color:var(--muted)] uppercase mb-1.5">1. Produk (Voucher & Pulsa Elektrik)</div>
                        <div class="table-wrap bg-white rounded border border-[#e2e8f0]">
                            <table class="table-grid min-w-full text-xs">
                                <thead>
                                    <tr class="bg-slate-50">
                                        <th width="65%">Produk</th>
                                        <th width="35%" class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="pagiVoucherPulsaBody">
                                    <tr><td colspan="2" class="text-center py-4 text-[color:var(--muted)]">Memuat data...</td></tr>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-slate-100 font-bold">
                                        <td>Total Penjualan Pulsa & Voucher</td>
                                        <td class="text-right text-emerald-700" id="pagiTotalVoucherPulsa">Rp 0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- 2. Penjualan Acesories (Barang Fisik) -->
                    <div>
                        <div class="text-xs font-bold text-[color:var(--muted)] uppercase mb-1.5">2. Penjualan Acesories (Barang Fisik)</div>
                        <div class="table-wrap bg-white rounded border border-[#e2e8f0]">
                            <table class="table-grid min-w-full text-xs">
                                <thead>
                                    <tr class="bg-slate-50">
                                        <th>Produk</th>
                                        <th class="text-right">Harga</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="pagiAccessoriesBody">
                                    <tr><td colspan="3" class="text-center py-4 text-[color:var(--muted)]">Memuat data...</td></tr>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-slate-100 font-bold">
                                        <td colspan="2">Total Acesories</td>
                                        <td class="text-right text-emerald-700" id="pagiTotalAccessories">Rp 0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- 3. Rincian Pembayaran Non-Tunai & Tunai -->
                    <div class="bg-white p-3 rounded border border-[#cbd5e1] space-y-1.5 text-xs">
                        <div class="font-bold text-[color:var(--muted)] uppercase text-[11px] pb-1 border-b">Breakdown Pembayaran Pagi</div>
                        <div class="flex justify-between"><span>TRF via WA:</span> <strong id="pagiTrfWa">Rp 0</strong></div>
                        <div class="flex justify-between"><span>TRF Bank:</span> <strong id="pagiTrfBank">Rp 0</strong></div>
                        <div class="flex justify-between"><span>TRF QRIS:</span> <strong id="pagiQris">Rp 0</strong></div>
                        <div class="flex justify-between"><span>E-MONEY / Debit:</span> <strong id="pagiDebit">Rp 0</strong></div>
                        <div class="flex justify-between pt-1 border-t border-dashed font-semibold text-emerald-700">
                            <span>Tunai (Cash):</span> <span id="pagiCash">Rp 0</span>
                        </div>
                    </div>

                    <!-- 4. Utang Pulsa / Bon Shift Pagi -->
                    <div class="bg-amber-50 p-3 rounded border border-amber-200 space-y-1 text-xs">
                        <div class="flex justify-between font-bold text-amber-900">
                            <span>Utang Pulsa / Bon:</span>
                            <span id="pagiDebtAmount">Rp 0</span>
                        </div>
                        <div class="text-[11px] text-amber-800 bg-amber-100/60 p-1.5 rounded border border-amber-200" id="pagiDebtNotes">Catatan / Rincian: -</div>
                    </div>

                    <!-- 5. Pengeluaran Shift Pagi -->
                    <div class="bg-rose-50 p-3 rounded border border-rose-200 space-y-1 text-xs">
                        <div class="flex justify-between font-bold text-rose-800">
                            <span>Pengeluaran:</span>
                            <span id="pagiExpenseAmount">Rp 0</span>
                        </div>
                        <div class="text-[11px] text-rose-600 bg-rose-100/60 p-1.5 rounded border border-rose-200" id="pagiExpenseNotes">Catatan: -</div>
                    </div>

                    <!-- 6. Rekapitulasi Akhir Shift Pagi -->
                    <div class="bg-amber-50 p-3.5 rounded border border-amber-300 space-y-2 text-xs">
                        <div class="flex justify-between"><span>Total Penjualan Pagi:</span> <strong id="pagiTotalSales">Rp 0</strong></div>
                        <div class="flex justify-between"><span>Total Uang + Non-Tunai:</span> <strong id="pagiTotalMoney">Rp 0</strong></div>
                        <div class="flex justify-between pt-1.5 border-t border-amber-200 font-medium">
                            <span>Selisih:</span>
                            <strong id="pagiSelisih" class="font-bold">Rp 0</strong>
                        </div>
                        <div class="pt-1.5 border-t border-amber-200 space-y-1">
                            <div class="flex justify-between font-semibold text-emerald-900">
                                <span>Pembayaran Utang (Pelunasan):</span>
                                <strong id="pagiDebtPaymentAmount">Rp 0</strong>
                            </div>
                            <div class="text-[11px] text-emerald-800 bg-emerald-50 p-1.5 rounded border border-emerald-200" id="pagiDebtPaymentNotes">Rincian: -</div>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-amber-400 font-bold text-sm text-amber-900">
                            <span>Sisa Kas Bersih Pagi:</span>
                            <span id="pagiNetCash">Rp 0</span>
                        </div>
                    </div>
                </div>

                <!-- ==================== KOLOM SHIFT SORE ==================== -->
                <div class="border border-[#cbd5e1] rounded-lg p-4 bg-[#fafbfc] space-y-4">
                    <div class="bg-indigo-100 text-indigo-900 font-bold px-3 py-1.5 rounded text-sm text-center uppercase tracking-wide border border-indigo-300">
                        Penjualan Sore
                    </div>

                    <!-- 1. Voucher & Pulsa Elektrik Table -->
                    <div>
                        <div class="text-xs font-bold text-[color:var(--muted)] uppercase mb-1.5">1. Produk (Voucher & Pulsa Elektrik)</div>
                        <div class="table-wrap bg-white rounded border border-[#e2e8f0]">
                            <table class="table-grid min-w-full text-xs">
                                <thead>
                                    <tr class="bg-slate-50">
                                        <th width="65%">Produk</th>
                                        <th width="35%" class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="soreVoucherPulsaBody">
                                    <tr><td colspan="2" class="text-center py-4 text-[color:var(--muted)]">Memuat data...</td></tr>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-slate-100 font-bold">
                                        <td>Total Penjualan Pulsa & Voucher</td>
                                        <td class="text-right text-emerald-700" id="soreTotalVoucherPulsa">Rp 0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- 2. Penjualan Acesories (Barang Fisik) -->
                    <div>
                        <div class="text-xs font-bold text-[color:var(--muted)] uppercase mb-1.5">2. Penjualan Acesories (Barang Fisik)</div>
                        <div class="table-wrap bg-white rounded border border-[#e2e8f0]">
                            <table class="table-grid min-w-full text-xs">
                                <thead>
                                    <tr class="bg-slate-50">
                                        <th>Produk</th>
                                        <th class="text-right">Harga</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="soreAccessoriesBody">
                                    <tr><td colspan="3" class="text-center py-4 text-[color:var(--muted)]">Memuat data...</td></tr>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-slate-100 font-bold">
                                        <td colspan="2">Total Acesories</td>
                                        <td class="text-right text-emerald-700" id="soreTotalAccessories">Rp 0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- 3. Rincian Pembayaran Non-Tunai & Tunai -->
                    <div class="bg-white p-3 rounded border border-[#cbd5e1] space-y-1.5 text-xs">
                        <div class="font-bold text-[color:var(--muted)] uppercase text-[11px] pb-1 border-b">Breakdown Pembayaran Sore</div>
                        <div class="flex justify-between"><span>TRF via WA:</span> <strong id="soreTrfWa">Rp 0</strong></div>
                        <div class="flex justify-between"><span>TRF Bank:</span> <strong id="soreTrfBank">Rp 0</strong></div>
                        <div class="flex justify-between"><span>TRF QRIS:</span> <strong id="soreQris">Rp 0</strong></div>
                        <div class="flex justify-between"><span>E-MONEY / Debit:</span> <strong id="soreDebit">Rp 0</strong></div>
                        <div class="flex justify-between pt-1 border-t border-dashed font-semibold text-emerald-700">
                            <span>Tunai (Cash):</span> <span id="soreCash">Rp 0</span>
                        </div>
                    </div>

                    <!-- 4. Utang Pulsa / Bon Shift Sore -->
                    <div class="bg-amber-50 p-3 rounded border border-amber-200 space-y-1 text-xs">
                        <div class="flex justify-between font-bold text-amber-900">
                            <span>Utang Pulsa / Bon:</span>
                            <span id="soreDebtAmount">Rp 0</span>
                        </div>
                        <div class="text-[11px] text-amber-800 bg-amber-100/60 p-1.5 rounded border border-amber-200" id="soreDebtNotes">Catatan / Rincian: -</div>
                    </div>

                    <!-- 5. Pengeluaran Shift Sore -->
                    <div class="bg-rose-50 p-3 rounded border border-rose-200 space-y-1 text-xs">
                        <div class="flex justify-between font-bold text-rose-800">
                            <span>Pengeluaran:</span>
                            <span id="soreExpenseAmount">Rp 0</span>
                        </div>
                        <div class="text-[11px] text-rose-600 bg-rose-100/60 p-1.5 rounded border border-rose-200" id="soreExpenseNotes">Catatan: -</div>
                    </div>

                    <!-- 6. Rekapitulasi Akhir Shift Sore -->
                    <div class="bg-indigo-50 p-3.5 rounded border border-indigo-300 space-y-2 text-xs">
                        <div class="flex justify-between"><span>Total Penjualan Sore:</span> <strong id="soreTotalSales">Rp 0</strong></div>
                        <div class="flex justify-between"><span>Total Uang + Non-Tunai:</span> <strong id="soreTotalMoney">Rp 0</strong></div>
                        <div class="flex justify-between pt-1.5 border-t border-indigo-200 font-medium">
                            <span>Selisih:</span>
                            <strong id="soreSelisih" class="font-bold">Rp 0</strong>
                        </div>
                        <div class="pt-1.5 border-t border-indigo-200 space-y-1">
                            <div class="flex justify-between font-semibold text-emerald-900">
                                <span>Pembayaran Utang (Pelunasan):</span>
                                <strong id="soreDebtPaymentAmount">Rp 0</strong>
                            </div>
                            <div class="text-[11px] text-emerald-800 bg-emerald-50 p-1.5 rounded border border-emerald-200" id="soreDebtPaymentNotes">Rincian: -</div>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-indigo-400 font-bold text-sm text-indigo-900">
                            <span>Sisa Kas Bersih Sore:</span>
                            <span id="soreNetCash">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== DETAIL TRANSAKSI PENJUALAN ==================== -->
        <div class="list-panel p-4 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b pb-3 border-[#e2e8f0]">
                <div>
                    <h2 class="font-bold text-base text-[color:var(--text)] uppercase tracking-wide">
                        Detail Hasil Transaksi Penjualan
                    </h2>
                    <p class="text-xs text-[color:var(--muted)]">Rincian data transaksi penjualan voucher fisik/elektronik, pulsa elektrik, dan barang fisik pada periode terpilih.</p>
                </div>
                <!-- Action Buttons & Tab Switching -->
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="btn btn-secondary text-xs py-1.5" id="btnOpenRecordDebt">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg></span>
                        <span>+ Catat Utang Manual</span>
                    </button>
                    <button type="button" class="btn btn-primary text-xs py-1.5 font-medium" id="btnOpenRecordDebtPayment">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg></span>
                        <span>+ Catat Pembayaran Utang</span>
                    </button>
                    <div class="flex flex-wrap items-center gap-1 rounded-lg bg-[#f1f5f9] p-1 border border-[#cbd5e1]">
                        <button type="button" class="report-tab-btn active px-2.5 py-1 text-xs font-semibold rounded-md transition-all bg-white shadow-sm text-blue-700 border border-blue-200" data-tab="all">
                            Semua (<span id="tabCountAll">0</span>)
                        </button>
                        <button type="button" class="report-tab-btn px-2.5 py-1 text-xs font-semibold rounded-md transition-all text-[#64748b] hover:text-[#0f172a]" data-tab="voucher">
                            Voucher (<span id="tabCountVoucher">0</span>)
                        </button>
                        <button type="button" class="report-tab-btn px-2.5 py-1 text-xs font-semibold rounded-md transition-all text-[#64748b] hover:text-[#0f172a]" data-tab="pulsa">
                            Pulsa Elektrik (<span id="tabCountPulsa">0</span>)
                        </button>
                        <button type="button" class="report-tab-btn px-2.5 py-1 text-xs font-semibold rounded-md transition-all text-[#64748b] hover:text-[#0f172a]" data-tab="accessories">
                            Acesories (<span id="tabCountAccessories">0</span>)
                        </button>
                        <button type="button" class="report-tab-btn px-2.5 py-1 text-xs font-semibold rounded-md transition-all text-[#64748b] hover:text-[#0f172a]" data-tab="debt">
                            Utang Pelanggan (<span id="tabCountDebt">0</span>)
                        </button>
                        <button type="button" class="report-tab-btn px-2.5 py-1 text-xs font-semibold rounded-md transition-all text-[#64748b] hover:text-[#0f172a]" data-tab="debt_payment">
                            Pembayaran Utang (<span id="tabCountDebtPayment">0</span>)
                        </button>
                    </div>
                </div>
            </div>

            <!-- TAB 1: DETAIL VOUCHER -->
            <div id="sectionVoucherTab" class="space-y-3 pt-2">
                <div class="text-xs font-bold uppercase tracking-wide text-purple-900 bg-purple-50 px-3 py-1.5 rounded border border-purple-200">
                    1. Rincian Penjualan Voucher (Fisik & Menu Voucher)
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="badge bg-purple-100 text-purple-800 font-semibold px-2.5 py-1 text-xs rounded border border-purple-200" id="badgeVoucherSummary">Total Voucher: Rp 0 (0 Transaksi)</span>
                    </div>
                    <input id="voucherSearch" class="field max-w-xs text-xs" type="text" placeholder="Cari ref / voucher / SN / pelanggan...">
                </div>
                <div class="table-wrap bg-white rounded border border-[#e2e8f0]">
                    <table id="voucherTable" class="table-grid min-w-full text-xs">
                        <thead class="text-left bg-slate-50">
                            <tr>
                                <th data-sort="reference_number">Ref / Invoice</th>
                                <th data-sort="date_raw">Tanggal & Jam</th>
                                <th data-sort="shift">Shift</th>
                                <th data-sort="product_name">Nama Produk Voucher</th>
                                <th data-sort="serial_number">Serial Number (SN)</th>
                                <th>No Tujuan</th>
                                <th data-sort="source">Sumber</th>
                                <th data-sort="payment_method">Pembayaran</th>
                                <th data-sort="price" class="text-right">Harga (Rp)</th>
                                <th data-sort="status" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="voucherTableBody">
                            <tr><td colspan="10" class="text-center py-6 text-[color:var(--muted)]">Memuat data voucher...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="voucherPagination"></div>
            </div>

            <!-- TAB 2: DETAIL PULSA ELEKTRIK -->
            <div id="sectionPulsaTab" class="space-y-3 pt-4 border-t border-slate-200">
                <div class="text-xs font-bold uppercase tracking-wide text-sky-900 bg-sky-50 px-3 py-1.5 rounded border border-sky-200">
                    2. Rincian Penjualan Pulsa Elektrik & PPOB
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="badge bg-sky-100 text-sky-800 font-semibold px-2.5 py-1 text-xs rounded border border-sky-200" id="badgePulsaSummary">Total Pulsa Elektrik: Rp 0 (0 Transaksi)</span>
                    </div>
                    <input id="pulsaSearch" class="field max-w-xs text-xs" type="text" placeholder="Cari ref / provider / no tujuan / dompet...">
                </div>
                <div class="table-wrap bg-white rounded border border-[#e2e8f0]">
                    <table id="pulsaTable" class="table-grid min-w-full text-xs">
                        <thead class="text-left bg-slate-50">
                            <tr>
                                <th data-sort="reference_number">Ref Transaksi</th>
                                <th data-sort="date_raw">Tanggal & Jam</th>
                                <th data-sort="shift">Shift</th>
                                <th data-sort="provider">Provider</th>
                                <th data-sort="product_name">Produk / Nominal</th>
                                <th data-sort="target_number">Nomor Tujuan</th>
                                <th data-sort="wallet_name">Dompet Saldo</th>
                                <th data-sort="customer_name">Pelanggan</th>
                                <th data-sort="payment_method">Pembayaran</th>
                                <th data-sort="price" class="text-right">Harga Jual (Rp)</th>
                                <th data-sort="status" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="pulsaTableBody">
                            <tr><td colspan="11" class="text-center py-6 text-[color:var(--muted)]">Memuat data pulsa elektrik...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="pulsaPagination"></div>
            </div>

            <!-- TAB 3: DETAIL ACESORIES -->
            <div id="sectionAccessoriesTab" class="space-y-3 pt-4 border-t border-slate-200">
                <div class="text-xs font-bold uppercase tracking-wide text-teal-900 bg-teal-50 px-3 py-1.5 rounded border border-teal-200">
                    3. Rincian Penjualan Barang / Acesories (Fisik)
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="badge bg-teal-100 text-teal-800 font-semibold px-2.5 py-1 text-xs rounded border border-teal-200" id="badgeAccSummary">Total Acesories: Rp 0 (0 Transaksi)</span>
                    </div>
                    <input id="accSearch" class="field max-w-xs text-xs" type="text" placeholder="Cari invoice / kode / produk / SN...">
                </div>
                <div class="table-wrap bg-white rounded border border-[#e2e8f0]">
                    <table id="accTable" class="table-grid min-w-full text-xs">
                        <thead class="text-left bg-slate-50">
                            <tr>
                                <th data-sort="invoice_number">Invoice</th>
                                <th data-sort="date_raw">Tanggal & Jam</th>
                                <th data-sort="shift">Shift</th>
                                <th data-sort="product_code">Kode</th>
                                <th data-sort="product_name">Nama Barang & Varian</th>
                                <th data-sort="serial_number">Serial Number (SN)</th>
                                <th data-sort="quantity" class="text-center">Qty</th>
                                <th data-sort="price" class="text-right">Harga Satuan (Rp)</th>
                                <th data-sort="subtotal" class="text-right">Total (Rp)</th>
                                <th data-sort="payment_method">Pembayaran</th>
                            </tr>
                        </thead>
                        <tbody id="accTableBody">
                            <tr><td colspan="10" class="text-center py-6 text-[color:var(--muted)]">Memuat data acesories...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="accPagination"></div>
            </div>

            <!-- TAB 4: DETAIL UTANG PELANGGAN -->
            <div id="sectionDebtTab" class="space-y-3 pt-4 border-t border-slate-200">
                <div class="text-xs font-bold uppercase tracking-wide text-amber-900 bg-amber-50 px-3 py-1.5 rounded border border-amber-200">
                    4. Rincian Utang Pelanggan (Bon Pulsa & Barang)
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="badge bg-amber-100 text-amber-800 font-semibold px-2.5 py-1 text-xs rounded border border-amber-200" id="badgeDebtSummary">Total Utang: Rp 0 (0 Transaksi)</span>
                    </div>
                    <input id="debtSearch" class="field max-w-xs text-xs" type="text" placeholder="Cari ref / pelanggan / telp / deskripsi...">
                </div>
                <div class="table-wrap bg-white rounded border border-[#e2e8f0]">
                    <table id="debtTable" class="table-grid min-w-full text-xs">
                        <thead class="text-left bg-slate-50">
                            <tr>
                                <th data-sort="reference_number">Ref / Kode</th>
                                <th data-sort="date_raw">Tanggal & Jam</th>
                                <th data-sort="shift">Shift</th>
                                <th data-sort="customer_name">Pelanggan</th>
                                <th>No Telepon</th>
                                <th data-sort="description">Keterangan / Barang</th>
                                <th data-sort="amount" class="text-right">Total Utang (Rp)</th>
                                <th data-sort="paid_amount" class="text-right">Terbayar (Rp)</th>
                                <th data-sort="remaining_amount" class="text-right">Sisa Utang (Rp)</th>
                                <th data-sort="status" class="text-center">Status</th>
                                <th>Catatan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="debtTableBody">
                            <tr><td colspan="12" class="text-center py-6 text-[color:var(--muted)]">Memuat data utang...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="debtPagination"></div>
            </div>

            <!-- TAB 5: DETAIL PEMBAYARAN UTANG -->
            <div id="sectionDebtPaymentTab" class="space-y-3 pt-4 border-t border-slate-200">
                <div class="text-xs font-bold uppercase tracking-wide text-emerald-900 bg-emerald-50 px-3 py-1.5 rounded border border-emerald-200">
                    5. Rincian Pembayaran & Pelunasan Utang
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="badge bg-emerald-100 text-emerald-800 font-semibold px-2.5 py-1 text-xs rounded border border-emerald-200" id="badgeDebtPaymentSummary">Total Pelunasan: Rp 0 (0 Transaksi)</span>
                    </div>
                    <input id="debtPaymentSearch" class="field max-w-xs text-xs" type="text" placeholder="Cari ref / pelanggan / ref utang / catatan...">
                </div>
                <div class="table-wrap bg-white rounded border border-[#e2e8f0]">
                    <table id="debtPaymentTable" class="table-grid min-w-full text-xs">
                        <thead class="text-left bg-slate-50">
                            <tr>
                                <th data-sort="reference_number">Ref Pembayaran</th>
                                <th data-sort="date_raw">Tanggal & Jam</th>
                                <th data-sort="shift">Shift</th>
                                <th data-sort="customer_name">Pelanggan</th>
                                <th data-sort="debt_reference">Ref Utang Terkait</th>
                                <th data-sort="amount" class="text-right">Nominal Bayar (Rp)</th>
                                <th data-sort="payment_method" class="text-center">Metode</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody id="debtPaymentTableBody">
                            <tr><td colspan="8" class="text-center py-6 text-[color:var(--muted)]">Memuat data pembayaran utang...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="debtPaymentPagination"></div>
            </div>
        </div>

        <!-- Details: Pengeluaran & Low Stock -->
        <div class="grid gap-4 xl:grid-cols-2">
            <!-- Rincian Pengeluaran -->
            <div class="list-panel p-3">
                <div class="mb-3 text-sm font-semibold">Rincian Pengeluaran Operasional</div>
                <div id="expenseListContainer" class="space-y-2">
                    <div class="text-xs text-[color:var(--muted)]">Memuat daftar pengeluaran...</div>
                </div>
            </div>

            <!-- Stok Menipis -->
            <div class="list-panel p-3">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div class="text-sm font-semibold">Peringatan Stok Menipis</div>
                    <input id="lowStockSearch" class="field max-w-xs text-xs" type="text" placeholder="Filter produk">
                </div>
                <div class="table-wrap">
                    <table id="lowStockMainTable" class="table-grid min-w-full text-xs">
                        <thead class="text-left">
                            <tr>
                                <th data-sort="code">Kode</th>
                                <th data-sort="name">Produk</th>
                                <th data-sort="stock">Stok Tersedia</th>
                            </tr>
                        </thead>
                        <tbody id="lowStockTable">
                            <tr><td colspan="3" class="text-center py-4 text-[color:var(--muted)]">Memuat data stok...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="lowStockPagination"></div>
            </div>
        </div>
    </section>

    <!-- Modal Catat Utang Manual -->
    <div id="modalRecordDebt" class="modal-backdrop hidden">
        <div class="modal-card max-w-lg">
            <div class="modal-head flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-base">Catat Utang Pelanggan</h2>
                    <p class="mt-0.5 text-xs text-[color:var(--muted)]">Catat bon pulsa atau utang manual pelanggan untuk shift berjalan.</p>
                </div>
                <button type="button" class="btn btn-secondary text-xs py-1" id="btnCloseModalDebt">
                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg></span>
                    <span>Tutup</span>
                </button>
            </div>
            <form id="formRecordDebt" class="modal-body p-4 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Nama Pelanggan <span class="text-rose-500">*</span></label>
                        <input type="text" name="customer_name" id="debtCustomerName" class="field text-xs w-full" placeholder="Contoh: Asep / Surya" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">No. Handphone / WA</label>
                        <input type="text" name="customer_phone" id="debtCustomerPhone" class="field text-xs w-full" placeholder="08xxxxxxxxxx">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Shift <span class="text-rose-500">*</span></label>
                        <select name="shift" id="debtShift" class="field text-xs w-full" required>
                            <option value="pagi">Shift Pagi</option>
                            <option value="sore">Shift Sore</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Tanggal</label>
                        <input type="date" name="debt_date" id="debtDate" class="field text-xs w-full">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Nominal Utang (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="amount" id="debtAmount" class="field text-xs w-full font-bold text-amber-700" min="1" step="any" placeholder="0" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Deskripsi / Produk</label>
                    <input type="text" name="description" id="debtDescription" class="field text-xs w-full" placeholder="Contoh: Pulsa Telkomsel 25k, Bon kabel, dsb">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Catatan Tambahan</label>
                    <input type="text" name="notes" id="debtNotes" class="field text-xs w-full" placeholder="Catatan khusus jika ada">
                </div>
                <div class="pt-2 border-t border-[#edf2f7] flex justify-end gap-2">
                    <button type="button" class="btn btn-secondary text-xs" id="btnCancelDebt">Batal</button>
                    <button type="submit" class="btn btn-primary text-xs font-semibold" id="btnSubmitDebt">Simpan Utang</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Catat Pembayaran Utang -->
    <div id="modalRecordDebtPayment" class="modal-backdrop hidden">
        <div class="modal-card max-w-lg">
            <div class="modal-head flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-base">Catat Pembayaran / Pelunasan Utang</h2>
                    <p class="mt-0.5 text-xs text-[color:var(--muted)]">Catat penerimaan uang pelunasan utang dari pelanggan.</p>
                </div>
                <button type="button" class="btn btn-secondary text-xs py-1" id="btnCloseModalDebtPayment">
                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg></span>
                    <span>Tutup</span>
                </button>
            </div>
            <form id="formRecordDebtPayment" class="modal-body p-4 space-y-3">
                <div>
                    <label class="block text-xs font-semibold mb-1">Pilih Utang Belum Lunas</label>
                    <select id="paymentDebtSelect" class="field text-xs w-full">
                        <option value="">-- Pembayaran Manual / Bebas --</option>
                    </select>
                    <p class="text-[10px] text-[color:var(--muted)] mt-0.5">Memilih utang akan otomatis mengisi nama pelanggan dan mengurangi sisa utang terkait.</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Nama Pelanggan <span class="text-rose-500">*</span></label>
                        <input type="text" name="customer_name" id="paymentCustomerName" class="field text-xs w-full" placeholder="Nama pelanggan pembayar" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Shift Penerimaan <span class="text-rose-500">*</span></label>
                        <select name="shift" id="paymentShift" class="field text-xs w-full" required>
                            <option value="pagi">Shift Pagi</option>
                            <option value="sore">Shift Sore</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Tanggal Bayar</label>
                        <input type="date" name="payment_date" id="paymentDate" class="field text-xs w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Metode Pembayaran <span class="text-rose-500">*</span></label>
                        <select name="payment_method" id="paymentMethod" class="field text-xs w-full" required>
                            <option value="cash">Tunai (Cash)</option>
                            <option value="transfer">Transfer WA / Bank</option>
                            <option value="qris">QRIS</option>
                            <option value="debit">E-Money / Debit</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Nominal Pembayaran (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="amount" id="paymentAmount" class="field text-xs w-full font-bold text-emerald-700" min="1" step="any" placeholder="0" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Catatan</label>
                    <input type="text" name="notes" id="paymentNotes" class="field text-xs w-full" placeholder="Contoh: Pelunasan transfer Surya, dsb">
                </div>
                <div class="pt-2 border-t border-[#edf2f7] flex justify-end gap-2">
                    <button type="button" class="btn btn-secondary text-xs" id="btnCancelDebtPayment">Batal</button>
                    <button type="submit" class="btn btn-primary text-xs font-semibold" id="btnSubmitDebtPayment">Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        window.runAppPage(function ($, helpers) {
            const { money, showAlert } = helpers;
            const urls = {
                reportData: '/ajax/report/data',
                pdfBase: '/report/pdf',
                debtsStore: '/ajax/debts',
                debtPaymentsStore: '/ajax/debts/payments',
                unpaidDebts: '/ajax/debts/unpaid',
            };

            // Table Controller: Low Stock
            const lowStockTable = helpers.createTableController({
                perPage: 6,
                defaultSortKey: 'stock',
                defaultSortDir: 'asc',
                search: (product, query) => (product.name || '').toLowerCase().includes(query) || (product.code || '').toLowerCase().includes(query),
                sorters: {
                    code: (product) => product.code,
                    name: (product) => product.name,
                    stock: (product) => Number(product.stock),
                },
            });

            // Table Controller: Voucher Details
            const voucherTable = helpers.createTableController({
                perPage: 10,
                defaultSortKey: 'date_raw',
                defaultSortDir: 'desc',
                search: (item, query) => (
                    (item.reference_number || '').toLowerCase().includes(query) ||
                    (item.product_name || '').toLowerCase().includes(query) ||
                    (item.serial_number || '').toLowerCase().includes(query) ||
                    (item.target_number || '').toLowerCase().includes(query) ||
                    (item.customer_name || '').toLowerCase().includes(query)
                ),
                sorters: {
                    reference_number: (i) => i.reference_number,
                    date_raw: (i) => i.date_raw,
                    shift: (i) => i.shift,
                    product_name: (i) => i.product_name,
                    serial_number: (i) => i.serial_number,
                    source: (i) => i.source,
                    payment_method: (i) => i.payment_method,
                    price: (i) => Number(i.price),
                    status: (i) => i.status,
                },
            });

            // Table Controller: Pulsa Elektrik Details
            const pulsaTable = helpers.createTableController({
                perPage: 10,
                defaultSortKey: 'date_raw',
                defaultSortDir: 'desc',
                search: (item, query) => (
                    (item.reference_number || '').toLowerCase().includes(query) ||
                    (item.provider || '').toLowerCase().includes(query) ||
                    (item.product_name || '').toLowerCase().includes(query) ||
                    (item.target_number || '').toLowerCase().includes(query) ||
                    (item.wallet_name || '').toLowerCase().includes(query) ||
                    (item.customer_name || '').toLowerCase().includes(query)
                ),
                sorters: {
                    reference_number: (i) => i.reference_number,
                    date_raw: (i) => i.date_raw,
                    shift: (i) => i.shift,
                    provider: (i) => i.provider,
                    product_name: (i) => i.product_name,
                    target_number: (i) => i.target_number,
                    wallet_name: (i) => i.wallet_name,
                    customer_name: (i) => i.customer_name,
                    payment_method: (i) => i.payment_method,
                    price: (i) => Number(i.price),
                    status: (i) => i.status,
                },
            });

            // Table Controller: Accessories Details
            const accTable = helpers.createTableController({
                perPage: 10,
                defaultSortKey: 'date_raw',
                defaultSortDir: 'desc',
                search: (item, query) => (
                    (item.invoice_number || '').toLowerCase().includes(query) ||
                    (item.product_name || '').toLowerCase().includes(query) ||
                    (item.product_code || '').toLowerCase().includes(query) ||
                    (item.serial_number || '').toLowerCase().includes(query) ||
                    (item.customer_name || '').toLowerCase().includes(query)
                ),
                sorters: {
                    invoice_number: (i) => i.invoice_number,
                    date_raw: (i) => i.date_raw,
                    shift: (i) => i.shift,
                    product_code: (i) => i.product_code,
                    product_name: (i) => i.product_name,
                    serial_number: (i) => i.serial_number,
                    quantity: (i) => Number(i.quantity),
                    price: (i) => Number(i.price),
                    subtotal: (i) => Number(i.subtotal),
                    payment_method: (i) => i.payment_method,
                },
            });

            // Table Controller: Debt Details
            const debtTable = helpers.createTableController({
                perPage: 10,
                defaultSortKey: 'date_raw',
                defaultSortDir: 'desc',
                search: (item, query) => (
                    (item.reference_number || '').toLowerCase().includes(query) ||
                    (item.customer_name || '').toLowerCase().includes(query) ||
                    (item.customer_phone || '').toLowerCase().includes(query) ||
                    (item.description || '').toLowerCase().includes(query) ||
                    (item.notes || '').toLowerCase().includes(query)
                ),
                sorters: {
                    reference_number: (i) => i.reference_number,
                    date_raw: (i) => i.date_raw,
                    shift: (i) => i.shift,
                    customer_name: (i) => i.customer_name,
                    description: (i) => i.description,
                    amount: (i) => Number(i.amount),
                    paid_amount: (i) => Number(i.paid_amount),
                    remaining_amount: (i) => Number(i.remaining_amount),
                    status: (i) => i.status,
                },
            });

            // Table Controller: Debt Payment Details
            const debtPaymentTable = helpers.createTableController({
                perPage: 10,
                defaultSortKey: 'date_raw',
                defaultSortDir: 'desc',
                search: (item, query) => (
                    (item.reference_number || '').toLowerCase().includes(query) ||
                    (item.customer_name || '').toLowerCase().includes(query) ||
                    (item.debt_reference || '').toLowerCase().includes(query) ||
                    (item.notes || '').toLowerCase().includes(query)
                ),
                sorters: {
                    reference_number: (i) => i.reference_number,
                    date_raw: (i) => i.date_raw,
                    shift: (i) => i.shift,
                    customer_name: (i) => i.customer_name,
                    debt_reference: (i) => i.debt_reference,
                    amount: (i) => Number(i.amount),
                    payment_method: (i) => i.payment_method,
                },
            });

            let startDateStr = new Date().toISOString().split('T')[0];
            let endDateStr = new Date().toISOString().split('T')[0];

            function updatePdfLinks(start, end) {
                $('#btnDownloadPdf').attr('href', `${urls.pdfBase}?start_date=${start}&end_date=${end}&download=1`);
                $('#btnPreviewPdf').attr('href', `${urls.pdfBase}?start_date=${start}&end_date=${end}&view=preview`);
            }

            function renderRecap(data) {
                if (!data) return;

                // KPI Cards
                $('#statTotalSales').text(money(data.summary.grand_total_sales));
                $('#statTotalCount').text((data.summary.total_transactions || 0) + ' Trx');
                $('#statVoucherSales').text(money(data.summary.total_voucher_sales || 0));
                $('#statVoucherCount').text((data.summary.total_voucher_count || 0) + ' Trx');
                $('#statPulsaSales').text(money(data.summary.total_pulsa_sales || 0));
                $('#statPulsaCount').text((data.summary.total_pulsa_count || 0) + ' Trx');
                $('#statAccessoriesSales').text(money(data.summary.total_accessories_sales || 0));
                $('#statAccessoriesCount').text((data.summary.total_accessories_count || 0) + ' Trx');

                $('#statShiftPagi').text(money(data.shifts?.pagi?.total_sales || 0));
                $('#statShiftSore').text(money(data.shifts?.sore?.total_sales || 0));
                $('#statDebtTotal').text(money(data.summary.grand_total_debt || 0));
                $('#statDebtCount').text((data.summary.total_debt_count || 0) + ' Trx');
                $('#statDebtPaymentTotal').text(money(data.summary.grand_total_debt_payment || 0));
                $('#statDebtPaymentCount').text((data.summary.total_debt_payment_count || 0) + ' Trx');
                $('#statExpenses').text(money(data.summary.grand_total_expenses));
                $('#statCashInDrawer').text(money(data.summary.cash_in_drawer));

                $('#recapPeriodText').text(data.period.formatted);

                // --- SHIFT PAGI ---
                const pagi = data.shifts?.pagi || {};
                const pagiVp = pagi.voucher_pulsa || [];
                $('#pagiVoucherPulsaBody').html(pagiVp.length ? pagiVp.map((item) => `
                    <tr>
                        <td class="font-medium ${item.total > 0 ? 'text-[#0f172a] font-semibold' : 'text-[#64748b]'}">${item.name}</td>
                        <td class="text-right font-semibold ${item.total > 0 ? 'text-emerald-700' : 'text-[#94a3b8]'}">${item.total > 0 ? money(item.total) : '-'}</td>
                    </tr>
                `).join('') : '<tr><td colspan="2" class="text-center py-3 text-[color:var(--muted)]">- Tidak ada transaksi pulsa / voucher -</td></tr>');
                $('#pagiTotalVoucherPulsa').text(money(pagi.total_voucher_pulsa || 0));

                const pagiAcc = pagi.accessories || [];
                $('#pagiAccessoriesBody').html(pagiAcc.length ? pagiAcc.map((item) => `
                    <tr>
                        <td class="font-medium">${item.name} <span class="badge font-bold">x${item.qty}</span></td>
                        <td class="text-right">${money(item.price)}</td>
                        <td class="text-right font-semibold text-emerald-700">${money(item.total)}</td>
                    </tr>
                `).join('') : '<tr><td colspan="3" class="text-center py-3 text-[color:var(--muted)]">- Tidak ada transaksi aksesoris -</td></tr>');
                $('#pagiTotalAccessories').text(money(pagi.total_accessories || 0));

                // Pagi Payments
                $('#pagiTrfWa').text(money(pagi.payments?.transfer || 0));
                $('#pagiTrfBank').text(money(pagi.payments?.bank || 0));
                $('#pagiQris').text(money(pagi.payments?.qris || 0));
                $('#pagiDebit').text(money(pagi.payments?.debit || 0));
                $('#pagiCash').text(money(pagi.total_cash || 0));

                // Pagi Debt (Utang Pulsa / Bon)
                $('#pagiDebtAmount').text(money(pagi.total_debt || 0));
                $('#pagiDebtNotes').text(pagi.debt_notes ? `Catatan / Rincian: ${pagi.debt_notes}` : 'Catatan / Rincian: -');

                // Pagi Expenses
                const pagiExpense = pagi.total_expenses !== undefined ? Number(pagi.total_expenses) : 0;
                $('#pagiExpenseAmount').text(money(pagiExpense));
                const pagiNotes = pagi.expense_notes && pagi.expense_notes !== '-' ? pagi.expense_notes : '';
                $('#pagiExpenseNotes').text(pagiNotes ? `Catatan: ${pagiNotes}` : 'Catatan: -');

                // Pagi Summary & Selisih
                $('#pagiTotalSales').text(money(pagi.total_sales || 0));
                $('#pagiTotalMoney').text(money(pagi.total_money !== undefined ? pagi.total_money : ((pagi.total_cash || 0) + (pagi.total_non_cash || 0) + (pagi.total_debt || 0))));
                
                const pagiSelisih = pagi.selisih !== undefined ? Number(pagi.selisih) : ((Number(pagi.total_cash || 0) + Number(pagi.total_non_cash || 0) + Number(pagi.total_debt || 0)) - Number(pagi.total_sales || 0));
                const $pagiSel = $('#pagiSelisih');
                $pagiSel.text((pagiSelisih < 0 ? '- ' : '') + money(Math.abs(pagiSelisih)));
                $pagiSel.removeClass('text-rose-600 text-emerald-600 text-slate-800');
                if (pagiSelisih < 0) {
                    $pagiSel.addClass('text-rose-600 font-bold');
                } else if (pagiSelisih > 0) {
                    $pagiSel.addClass('text-emerald-600 font-bold');
                } else {
                    $pagiSel.addClass('text-slate-800 font-bold');
                }

                $('#pagiDebtPaymentAmount').text(money(pagi.total_debt_payment || 0));
                $('#pagiDebtPaymentNotes').text(pagi.debt_payment_notes ? `Rincian: ${pagi.debt_payment_notes}` : 'Rincian: -');
                $('#pagiNetCash').text(money(pagi.net_cash !== undefined ? pagi.net_cash : Math.max(0, ((pagi.total_cash || 0) + (pagi.debt_payment_cash || 0)) - pagiExpense)));

                // --- SHIFT SORE ---
                const sore = data.shifts?.sore || {};
                const soreVp = sore.voucher_pulsa || [];
                $('#soreVoucherPulsaBody').html(soreVp.length ? soreVp.map((item) => `
                    <tr>
                        <td class="font-medium ${item.total > 0 ? 'text-[#0f172a] font-semibold' : 'text-[#64748b]'}">${item.name}</td>
                        <td class="text-right font-semibold ${item.total > 0 ? 'text-emerald-700' : 'text-[#94a3b8]'}">${item.total > 0 ? money(item.total) : '-'}</td>
                    </tr>
                `).join('') : '<tr><td colspan="2" class="text-center py-3 text-[color:var(--muted)]">- Tidak ada transaksi pulsa / voucher -</td></tr>');
                $('#soreTotalVoucherPulsa').text(money(sore.total_voucher_pulsa || 0));

                const soreAcc = sore.accessories || [];
                $('#soreAccessoriesBody').html(soreAcc.length ? soreAcc.map((item) => `
                    <tr>
                        <td class="font-medium">${item.name} <span class="badge font-bold">x${item.qty}</span></td>
                        <td class="text-right">${money(item.price)}</td>
                        <td class="text-right font-semibold text-emerald-700">${money(item.total)}</td>
                    </tr>
                `).join('') : '<tr><td colspan="3" class="text-center py-3 text-[color:var(--muted)]">- Tidak ada transaksi aksesoris -</td></tr>');
                $('#soreTotalAccessories').text(money(sore.total_accessories || 0));

                // Sore Payments
                $('#soreTrfWa').text(money(sore.payments?.transfer || 0));
                $('#soreTrfBank').text(money(sore.payments?.bank || 0));
                $('#soreQris').text(money(sore.payments?.qris || 0));
                $('#soreDebit').text(money(sore.payments?.debit || 0));
                $('#soreCash').text(money(sore.total_cash || 0));

                // Sore Debt (Utang Pulsa / Bon)
                $('#soreDebtAmount').text(money(sore.total_debt || 0));
                $('#soreDebtNotes').text(sore.debt_notes ? `Catatan / Rincian: ${sore.debt_notes}` : 'Catatan / Rincian: -');

                // Sore Expenses
                const soreExpense = sore.total_expenses !== undefined ? Number(sore.total_expenses) : 0;
                $('#soreExpenseAmount').text(money(soreExpense));
                const soreNotes = sore.expense_notes && sore.expense_notes !== '-' ? sore.expense_notes : '';
                $('#soreExpenseNotes').text(soreNotes ? `Catatan: ${soreNotes}` : 'Catatan: -');

                // Sore Summary & Selisih
                $('#soreTotalSales').text(money(sore.total_sales || 0));
                $('#soreTotalMoney').text(money(sore.total_money !== undefined ? sore.total_money : ((sore.total_cash || 0) + (sore.total_non_cash || 0) + (sore.total_debt || 0))));

                const soreSelisih = sore.selisih !== undefined ? Number(sore.selisih) : ((Number(sore.total_cash || 0) + Number(sore.total_non_cash || 0) + Number(sore.total_debt || 0)) - Number(sore.total_sales || 0));
                const $soreSel = $('#soreSelisih');
                $soreSel.text((soreSelisih < 0 ? '- ' : '') + money(Math.abs(soreSelisih)));
                $soreSel.removeClass('text-rose-600 text-emerald-600 text-slate-800');
                if (soreSelisih < 0) {
                    $soreSel.addClass('text-rose-600 font-bold');
                } else if (soreSelisih > 0) {
                    $soreSel.addClass('text-emerald-600 font-bold');
                } else {
                    $soreSel.addClass('text-slate-800 font-bold');
                }

                $('#soreDebtPaymentAmount').text(money(sore.total_debt_payment || 0));
                $('#soreDebtPaymentNotes').text(sore.debt_payment_notes ? `Rincian: ${sore.debt_payment_notes}` : 'Rincian: -');
                $('#soreNetCash').text(money(sore.net_cash !== undefined ? sore.net_cash : Math.max(0, ((sore.total_cash || 0) + (sore.debt_payment_cash || 0)) - soreExpense)));

                // --- DETAIL TABLES & TABS ---
                $('#tabCountAll').text(data.summary.total_transactions || 0);
                $('#tabCountVoucher').text(data.summary.total_voucher_count || 0);
                $('#tabCountPulsa').text(data.summary.total_pulsa_count || 0);
                $('#tabCountAccessories').text(data.summary.total_accessories_count || 0);
                $('#tabCountDebt').text(data.details?.debts?.length || 0);
                $('#tabCountDebtPayment').text(data.details?.debt_payments?.length || 0);

                $('#badgeVoucherSummary').text(`Total Voucher: ${money(data.summary.total_voucher_sales || 0)} (${data.summary.total_voucher_count || 0} Transaksi)`);
                $('#badgePulsaSummary').text(`Total Pulsa Elektrik: ${money(data.summary.total_pulsa_sales || 0)} (${data.summary.total_pulsa_count || 0} Transaksi)`);
                $('#badgeAccSummary').text(`Total Acesories: ${money(data.summary.total_accessories_sales || 0)} (${data.summary.total_accessories_count || 0} Transaksi)`);
                $('#badgeDebtSummary').text(`Total Utang: ${money(data.summary.grand_total_debt || 0)} (${data.details?.debts?.length || 0} Transaksi)`);
                $('#badgeDebtPaymentSummary').text(`Total Pelunasan: ${money(data.summary.grand_total_debt_payment || 0)} (${data.details?.debt_payments?.length || 0} Transaksi)`);

                voucherTable.setData(data.details?.vouchers || []);
                renderVoucherTable();

                pulsaTable.setData(data.details?.pulsa || []);
                renderPulsaTable();

                accTable.setData(data.details?.accessories || []);
                renderAccTable();

                debtTable.setData(data.details?.debts || []);
                renderDebtTable();

                debtPaymentTable.setData(data.details?.debt_payments || []);
                renderDebtPaymentTable();

                // Expense List
                const expenses = data.expenses?.items || [];
                $('#expenseListContainer').html(expenses.length ? expenses.map((e) => `
                    <div class="border border-[#e2e8f0] bg-white p-2.5 rounded flex items-center justify-between text-xs">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-rose-700">${e.description}</span>
                                <span class="badge ${e.shift === 'sore' ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800'} uppercase font-bold text-[9px] px-1.5 py-0.5 rounded">${e.shift === 'sore' ? 'Shift Sore' : 'Shift Pagi'}</span>
                            </div>
                            <div class="text-[11px] text-[color:var(--muted)]">${e.date}</div>
                        </div>
                        <div class="font-bold text-rose-700">${money(e.amount)}</div>
                    </div>
                `).join('') : '<div class="text-xs text-[color:var(--muted)]">Tidak ada pengeluaran pada periode ini.</div>');

                // Low Stock
                renderLowStock(data.low_stock || []);
            }

            // Render Table: Vouchers
            function renderVoucherTable() {
                voucherTable.setQuery($('#voucherSearch').val());
                const meta = voucherTable.pagedRows();
                $('#voucherTableBody').html(meta.rows.length ? meta.rows.map((v) => `
                    <tr>
                        <td class="font-semibold text-purple-700">${v.reference_number}</td>
                        <td>${v.date}</td>
                        <td><span class="badge ${v.shift === 'pagi' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800'} uppercase font-bold text-[10px]">${v.shift}</span></td>
                        <td class="font-medium">${v.product_name} ${v.variant && v.variant !== '-' ? `(${v.variant})` : ''}</td>
                        <td><span class="font-mono text-[11px] bg-slate-100 px-1.5 py-0.5 rounded">${v.serial_number || '-'}</span></td>
                        <td class="font-mono text-[11px]">${v.target_number || '-'}</td>
                        <td><span class="badge text-[10px] ${v.source === 'Menu Voucher' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-700'}">${v.source}</span></td>
                        <td><span class="uppercase text-[11px] font-semibold">${v.payment_method}</span></td>
                        <td class="text-right font-bold text-emerald-700">${money(v.price)}</td>
                        <td class="text-center"><span class="badge ${v.status === 'success' ? 'badge-success' : 'badge-warning'} uppercase text-[10px]">${v.status}</span></td>
                    </tr>
                `).join('') : '<tr><td colspan="10" class="text-center py-6 text-[color:var(--muted)]">Tidak ada transaksi voucher pada periode ini.</td></tr>');
                voucherTable.syncSortUi('#voucherTable thead');
                voucherTable.renderFooter('#voucherPagination', meta);
            }

            // Render Table: Pulsa Elektrik
            function renderPulsaTable() {
                pulsaTable.setQuery($('#pulsaSearch').val());
                const meta = pulsaTable.pagedRows();
                $('#pulsaTableBody').html(meta.rows.length ? meta.rows.map((p) => `
                    <tr>
                        <td class="font-semibold text-sky-700">${p.reference_number}</td>
                        <td>${p.date}</td>
                        <td><span class="badge ${p.shift === 'pagi' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800'} uppercase font-bold text-[10px]">${p.shift}</span></td>
                        <td class="font-bold text-slate-800">${p.provider}</td>
                        <td class="font-medium">${p.product_name}</td>
                        <td class="font-mono font-semibold">${p.target_number}</td>
                        <td><span class="badge bg-slate-100 text-slate-700">${p.wallet_name || '-'}</span></td>
                        <td>${p.customer_name || '-'}</td>
                        <td><span class="uppercase text-[11px] font-semibold">${p.payment_method}</span></td>
                        <td class="text-right font-bold text-emerald-700">${money(p.price)}</td>
                        <td class="text-center"><span class="badge ${p.status === 'success' ? 'badge-success' : (p.status === 'cancelled' ? 'badge-danger' : 'badge-warning')} uppercase text-[10px]">${p.status}</span></td>
                    </tr>
                `).join('') : '<tr><td colspan="11" class="text-center py-6 text-[color:var(--muted)]">Tidak ada transaksi pulsa elektrik pada periode ini.</td></tr>');
                pulsaTable.syncSortUi('#pulsaTable thead');
                pulsaTable.renderFooter('#pulsaPagination', meta);
            }

            // Render Table: Accessories
            function renderAccTable() {
                accTable.setQuery($('#accSearch').val());
                const meta = accTable.pagedRows();
                $('#accTableBody').html(meta.rows.length ? meta.rows.map((a) => `
                    <tr>
                        <td class="font-semibold text-teal-700">${a.invoice_number}</td>
                        <td>${a.date}</td>
                        <td><span class="badge ${a.shift === 'pagi' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800'} uppercase font-bold text-[10px]">${a.shift}</span></td>
                        <td class="font-mono text-[11px]">${a.product_code || '-'}</td>
                        <td class="font-medium">${a.product_name} ${a.variant && a.variant !== '-' ? `(${a.variant})` : ''}</td>
                        <td><span class="font-mono text-[11px] bg-slate-100 px-1.5 py-0.5 rounded">${a.serial_number || '-'}</span></td>
                        <td class="text-center"><span class="badge font-bold">x${a.quantity}</span></td>
                        <td class="text-right">${money(a.price)}</td>
                        <td class="text-right font-bold text-emerald-700">${money(a.subtotal)}</td>
                        <td><span class="uppercase text-[11px] font-semibold">${a.payment_method}</span></td>
                    </tr>
                `).join('') : '<tr><td colspan="10" class="text-center py-6 text-[color:var(--muted)]">Tidak ada transaksi acesories pada periode ini.</td></tr>');
                accTable.syncSortUi('#accTable thead');
                accTable.renderFooter('#accPagination', meta);
            }

            // Render Table: Debts
            function renderDebtTable() {
                debtTable.setQuery($('#debtSearch').val());
                const meta = debtTable.pagedRows();
                $('#debtTableBody').html(meta.rows.length ? meta.rows.map((d) => {
                    const statusBadge = d.status === 'paid' 
                        ? '<span class="badge badge-success uppercase text-[10px]">Lunas</span>'
                        : (d.status === 'partial' 
                            ? '<span class="badge badge-warning uppercase text-[10px]">Dicicil</span>'
                            : '<span class="badge badge-danger uppercase text-[10px]">Belum Lunas</span>');
                    const actionBtn = d.status !== 'paid'
                        ? `<button type="button" class="btn btn-secondary text-[11px] py-0.5 px-2 btn-pay-debt font-semibold text-emerald-700 border-emerald-300 hover:bg-emerald-50" data-id="${d.id}" data-name="${d.customer_name}" data-remaining="${d.remaining_amount}">Bayar</button>`
                        : '<span class="text-[11px] text-emerald-600 font-semibold">-</span>';

                    return `
                        <tr>
                            <td class="font-semibold text-amber-800">${d.reference_number}</td>
                            <td>${d.date}</td>
                            <td><span class="badge ${d.shift === 'pagi' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800'} uppercase font-bold text-[10px]">${d.shift}</span></td>
                            <td class="font-bold text-slate-800">${d.customer_name}</td>
                            <td class="font-mono text-[11px]">${d.customer_phone || '-'}</td>
                            <td class="font-medium">${d.description}</td>
                            <td class="text-right font-bold text-slate-800">${money(d.amount)}</td>
                            <td class="text-right font-semibold text-emerald-700">${money(d.paid_amount)}</td>
                            <td class="text-right font-bold text-rose-600">${money(d.remaining_amount)}</td>
                            <td class="text-center">${statusBadge}</td>
                            <td class="text-[11px] text-[color:var(--muted)]">${d.notes || '-'}</td>
                            <td class="text-center">${actionBtn}</td>
                        </tr>
                    `;
                }).join('') : '<tr><td colspan="12" class="text-center py-6 text-[color:var(--muted)]">Tidak ada catatan utang pada periode ini.</td></tr>');
                debtTable.syncSortUi('#debtTable thead');
                debtTable.renderFooter('#debtPagination', meta);
            }

            // Render Table: Debt Payments
            function renderDebtPaymentTable() {
                debtPaymentTable.setQuery($('#debtPaymentSearch').val());
                const meta = debtPaymentTable.pagedRows();
                $('#debtPaymentTableBody').html(meta.rows.length ? meta.rows.map((p) => `
                    <tr>
                        <td class="font-semibold text-emerald-700">${p.reference_number}</td>
                        <td>${p.date}</td>
                        <td><span class="badge ${p.shift === 'pagi' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800'} uppercase font-bold text-[10px]">${p.shift}</span></td>
                        <td class="font-bold text-slate-800">${p.customer_name}</td>
                        <td class="font-mono text-[11px] text-amber-700">${p.debt_reference}</td>
                        <td class="text-right font-bold text-emerald-700">${money(p.amount)}</td>
                        <td class="text-center"><span class="badge bg-slate-100 text-slate-700 uppercase font-semibold text-[10px]">${p.payment_method}</span></td>
                        <td class="text-[11px] text-[color:var(--muted)]">${p.notes || '-'}</td>
                    </tr>
                `).join('') : '<tr><td colspan="8" class="text-center py-6 text-[color:var(--muted)]">Tidak ada penerimaan pembayaran utang pada periode ini.</td></tr>');
                debtPaymentTable.syncSortUi('#debtPaymentTable thead');
                debtPaymentTable.renderFooter('#debtPaymentPagination', meta);
            }

            // Render Table: Low Stock
            function renderLowStock(products) {
                lowStockTable.setData(products);
                lowStockTable.setQuery($('#lowStockSearch').val());
                const meta = lowStockTable.pagedRows();

                $('#lowStockTable').html(meta.rows.length ? meta.rows.map((product) => `
                    <tr>
                        <td class="font-semibold">${product.code}</td>
                        <td>${product.name}</td>
                        <td><span class="badge badge-warning">${product.stock} ${product.unit || 'pcs'}</span></td>
                    </tr>
                `).join('') : '<tr><td colspan="3" class="text-center py-4 text-[color:var(--muted)]">Semua stok aman.</td></tr>');
                lowStockTable.syncSortUi('#lowStockMainTable thead');
                lowStockTable.renderFooter('#lowStockPagination', meta);
            }

            function loadReportData() {
                $.get(urls.reportData, { start_date: startDateStr, end_date: endDateStr })
                    .done((response) => renderRecap(response))
                    .fail(() => showAlert('Gagal memuat data laporan shift.', 'error'));
            }

            // Tab Switching Event
            $('.report-tab-btn').on('click', function () {
                const tab = $(this).data('tab');
                $('.report-tab-btn').removeClass('active bg-white shadow-sm text-blue-700 text-purple-700 text-sky-700 text-teal-700 text-amber-700 text-emerald-700 border border-blue-200 border-purple-200 border-sky-200 border-teal-200 border-amber-200 border-emerald-200').addClass('text-[#64748b]');
                
                if (tab === 'all') {
                    $(this).addClass('active bg-white shadow-sm text-blue-700 border border-blue-200').removeClass('text-[#64748b]');
                    $('#sectionVoucherTab, #sectionPulsaTab, #sectionAccessoriesTab, #sectionDebtTab, #sectionDebtPaymentTab').removeClass('hidden');
                } else if (tab === 'voucher') {
                    $(this).addClass('active bg-white shadow-sm text-purple-700 border border-purple-200').removeClass('text-[#64748b]');
                    $('#sectionVoucherTab').removeClass('hidden');
                    $('#sectionPulsaTab, #sectionAccessoriesTab, #sectionDebtTab, #sectionDebtPaymentTab').addClass('hidden');
                } else if (tab === 'pulsa') {
                    $(this).addClass('active bg-white shadow-sm text-sky-700 border border-sky-200').removeClass('text-[#64748b]');
                    $('#sectionPulsaTab').removeClass('hidden');
                    $('#sectionVoucherTab, #sectionAccessoriesTab, #sectionDebtTab, #sectionDebtPaymentTab').addClass('hidden');
                } else if (tab === 'accessories') {
                    $(this).addClass('active bg-white shadow-sm text-teal-700 border border-teal-200').removeClass('text-[#64748b]');
                    $('#sectionAccessoriesTab').removeClass('hidden');
                    $('#sectionVoucherTab, #sectionPulsaTab, #sectionDebtTab, #sectionDebtPaymentTab').addClass('hidden');
                } else if (tab === 'debt') {
                    $(this).addClass('active bg-white shadow-sm text-amber-700 border border-amber-200').removeClass('text-[#64748b]');
                    $('#sectionDebtTab').removeClass('hidden');
                    $('#sectionVoucherTab, #sectionPulsaTab, #sectionAccessoriesTab, #sectionDebtPaymentTab').addClass('hidden');
                } else if (tab === 'debt_payment') {
                    $(this).addClass('active bg-white shadow-sm text-emerald-700 border border-emerald-200').removeClass('text-[#64748b]');
                    $('#sectionDebtPaymentTab').removeClass('hidden');
                    $('#sectionVoucherTab, #sectionPulsaTab, #sectionAccessoriesTab, #sectionDebtTab').addClass('hidden');
                }
            });

            // Search events
            $('#voucherSearch').on('input', renderVoucherTable);
            $('#pulsaSearch').on('input', renderPulsaTable);
            $('#accSearch').on('input', renderAccTable);

            // Sorting Voucher
            $(document).on('click', '#voucherTable thead [data-sort]', function () {
                voucherTable.toggleSort($(this).data('sort'));
                renderVoucherTable();
            });
            $(document).on('click', '#voucherPagination .page-btn', function () {
                voucherTable.setPage(Number($(this).data('page')));
                renderVoucherTable();
            });

            // Sorting Pulsa
            $(document).on('click', '#pulsaTable thead [data-sort]', function () {
                pulsaTable.toggleSort($(this).data('sort'));
                renderPulsaTable();
            });
            $(document).on('click', '#pulsaPagination .page-btn', function () {
                pulsaTable.setPage(Number($(this).data('page')));
                renderPulsaTable();
            });

            // Sorting Accessories
            $(document).on('click', '#accTable thead [data-sort]', function () {
                accTable.toggleSort($(this).data('sort'));
                renderAccTable();
            });
            $(document).on('click', '#accPagination .page-btn', function () {
                accTable.setPage(Number($(this).data('page')));
                renderAccTable();
            });

            // Low Stock Events
            $('#lowStockSearch').on('input', function () {
                lowStockTable.setQuery($(this).val());
                const meta = lowStockTable.pagedRows();
                $('#lowStockTable').html(meta.rows.length ? meta.rows.map((product) => `
                    <tr>
                        <td class="font-semibold">${product.code}</td>
                        <td>${product.name}</td>
                        <td><span class="badge badge-warning">${product.stock} ${product.unit || 'pcs'}</span></td>
                    </tr>
                `).join('') : '<tr><td colspan="3" class="text-center py-4 text-[color:var(--muted)]">Semua stok aman.</td></tr>');
                lowStockTable.syncSortUi('#lowStockMainTable thead');
                lowStockTable.renderFooter('#lowStockPagination', meta);
            });

            $(document).on('click', '#lowStockMainTable thead [data-sort]', function () {
                lowStockTable.toggleSort($(this).data('sort'));
                const meta = lowStockTable.pagedRows();
                $('#lowStockTable').html(meta.rows.length ? meta.rows.map((product) => `
                    <tr>
                        <td class="font-semibold">${product.code}</td>
                        <td>${product.name}</td>
                        <td><span class="badge badge-warning">${product.stock} ${product.unit || 'pcs'}</span></td>
                    </tr>
                `).join('') : '<tr><td colspan="3" class="text-center py-4 text-[color:var(--muted)]">Semua stok aman.</td></tr>');
                lowStockTable.syncSortUi('#lowStockMainTable thead');
                lowStockTable.renderFooter('#lowStockPagination', meta);
            });

            $(document).on('click', '#lowStockPagination .page-btn', function () {
                lowStockTable.setPage(Number($(this).data('page')));
                const meta = lowStockTable.pagedRows();
                $('#lowStockTable').html(meta.rows.length ? meta.rows.map((product) => `
                    <tr>
                        <td class="font-semibold">${product.code}</td>
                        <td>${product.name}</td>
                        <td><span class="badge badge-warning">${product.stock} ${product.unit || 'pcs'}</span></td>
                    </tr>
                `).join('') : '<tr><td colspan="3" class="text-center py-4 text-[color:var(--muted)]">Semua stok aman.</td></tr>');
                lowStockTable.syncSortUi('#lowStockMainTable thead');
                lowStockTable.renderFooter('#lowStockPagination', meta);
            });

            // Debt Search & Sorting
            $('#debtSearch').on('input', renderDebtTable);
            $(document).on('click', '#debtTable thead [data-sort]', function () {
                debtTable.toggleSort($(this).data('sort'));
                renderDebtTable();
            });
            $(document).on('click', '#debtPagination .page-btn', function () {
                debtTable.setPage(Number($(this).data('page')));
                renderDebtTable();
            });

            // Debt Payment Search & Sorting
            $('#debtPaymentSearch').on('input', renderDebtPaymentTable);
            $(document).on('click', '#debtPaymentTable thead [data-sort]', function () {
                debtPaymentTable.toggleSort($(this).data('sort'));
                renderDebtPaymentTable();
            });
            $(document).on('click', '#debtPaymentPagination .page-btn', function () {
                debtPaymentTable.setPage(Number($(this).data('page')));
                renderDebtPaymentTable();
            });

            // Modal: Record Debt
            $('#btnOpenRecordDebt').on('click', function () {
                $('#formRecordDebt')[0].reset();
                const today = new Date().toISOString().split('T')[0];
                const currentHour = new Date().getHours();
                $('#debtDate').val(today);
                $('#debtShift').val(currentHour >= 14 ? 'sore' : 'pagi');
                $('#modalRecordDebt').removeClass('hidden');
            });

            $('#btnCloseModalDebt, #btnCancelDebt').on('click', function () {
                $('#modalRecordDebt').addClass('hidden');
            });

            $('#formRecordDebt').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#btnSubmitDebt').prop('disabled', true);
                $.post(urls.debtsStore, $(this).serialize())
                    .done(function (res) {
                        $('#modalRecordDebt').addClass('hidden');
                        showAlert(res.message || 'Utang berhasil dicatat.', 'success');
                        loadReportData();
                    })
                    .fail(function (xhr) {
                        showAlert(xhr.responseJSON?.message || 'Gagal menyimpan catatan utang.', 'error');
                    })
                    .always(function () {
                        $btn.prop('disabled', false);
                    });
            });

            // Modal: Record Debt Payment
            function loadUnpaidDebts(selectedId) {
                const $select = $('#paymentDebtSelect');
                $select.html('<option value="">Memuat utang belum lunas...</option>');
                $.get(urls.unpaidDebts)
                    .done(function (debts) {
                        let html = '<option value="">-- Pembayaran Manual / Bebas --</option>';
                        (debts || []).forEach(function (d) {
                            const remaining = Math.max(0, Number(d.amount) - Number(d.paid_amount));
                            const isSelected = selectedId && Number(selectedId) === Number(d.id) ? 'selected' : '';
                            html += `<option value="${d.id}" data-name="${d.customer_name}" data-remaining="${remaining}" ${isSelected}>[${d.reference_number}] ${d.customer_name} - Sisa: ${money(remaining)} (${d.description || 'Utang'})</option>`;
                        });
                        $select.html(html);

                        if (selectedId) {
                            $select.trigger('change');
                        }
                    })
                    .fail(function () {
                        $select.html('<option value="">-- Gagal memuat utang terdaftar --</option>');
                    });
            }

            $('#btnOpenRecordDebtPayment').on('click', function () {
                $('#formRecordDebtPayment')[0].reset();
                const today = new Date().toISOString().split('T')[0];
                const currentHour = new Date().getHours();
                $('#paymentDate').val(today);
                $('#paymentShift').val(currentHour >= 14 ? 'sore' : 'pagi');
                $('#paymentMethod').val('cash');
                loadUnpaidDebts();
                $('#modalRecordDebtPayment').removeClass('hidden');
            });

            $('#paymentDebtSelect').on('change', function () {
                const $opt = $(this).find(':selected');
                const name = $opt.data('name');
                const remaining = $opt.data('remaining');
                if (name) {
                    $('#paymentCustomerName').val(name);
                }
                if (remaining !== undefined && remaining > 0) {
                    $('#paymentAmount').val(remaining);
                }
            });

            $(document).on('click', '.btn-pay-debt', function () {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const remaining = $(this).data('remaining');

                $('#formRecordDebtPayment')[0].reset();
                const today = new Date().toISOString().split('T')[0];
                const currentHour = new Date().getHours();
                $('#paymentDate').val(today);
                $('#paymentShift').val(currentHour >= 14 ? 'sore' : 'pagi');
                $('#paymentMethod').val('cash');
                $('#paymentCustomerName').val(name);
                $('#paymentAmount').val(remaining);
                loadUnpaidDebts(id);
                $('#modalRecordDebtPayment').removeClass('hidden');
            });

            $('#btnCloseModalDebtPayment, #btnCancelDebtPayment').on('click', function () {
                $('#modalRecordDebtPayment').addClass('hidden');
            });

            $('#formRecordDebtPayment').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#btnSubmitDebtPayment').prop('disabled', true);
                $.post(urls.debtPaymentsStore, $(this).serialize())
                    .done(function (res) {
                        $('#modalRecordDebtPayment').addClass('hidden');
                        showAlert(res.message || 'Pembayaran utang berhasil dicatat.', 'success');
                        loadReportData();
                    })
                    .fail(function (xhr) {
                        showAlert(xhr.responseJSON?.message || 'Gagal menyimpan pembayaran utang.', 'error');
                    })
                    .always(function () {
                        $btn.prop('disabled', false);
                    });
            });

            // Date Range Picker
            const fp = window.flatpickr ? window.flatpickr('#reportDateRange', {
                mode: 'range',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd M Y',
                defaultDate: [startDateStr, endDateStr],
                locale: window.flatpickrIndonesian || 'default',
                onChange: function (selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        const s = instance.formatDate(selectedDates[0], 'Y-m-d');
                        const e = instance.formatDate(selectedDates[1], 'Y-m-d');
                        startDateStr = s;
                        endDateStr = e;
                        $('#filterStatusText').text(s === e ? s : `${s} s/d ${e}`);
                        updatePdfLinks(s, e);
                        loadReportData();
                    } else if (selectedDates.length === 1) {
                        const s = instance.formatDate(selectedDates[0], 'Y-m-d');
                        startDateStr = s;
                        endDateStr = s;
                        $('#filterStatusText').text(s);
                        updatePdfLinks(s, s);
                        loadReportData();
                    }
                }
            }) : null;

            $('#btnFilterToday').on('click', function () {
                const today = new Date().toISOString().split('T')[0];
                startDateStr = today;
                endDateStr = today;
                if (fp) fp.setDate([today, today], true);
                $('#filterStatusText').text('Hari Ini (' + today + ')');
                updatePdfLinks(today, today);
                loadReportData();
            });

            $('#btnFilterThisMonth').on('click', function () {
                const now = new Date();
                const firstDay = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
                const today = now.toISOString().split('T')[0];
                startDateStr = firstDay;
                endDateStr = today;
                if (fp) fp.setDate([firstDay, today], true);
                $('#filterStatusText').text('Bulan Ini (' + firstDay + ' s/d ' + today + ')');
                updatePdfLinks(firstDay, today);
                loadReportData();
            });

            $('#btnFilterAll').on('click', function () {
                const start = '2020-01-01';
                const today = new Date().toISOString().split('T')[0];
                startDateStr = start;
                endDateStr = today;
                if (fp) fp.setDate([start, today], true);
                $('#filterStatusText').text('Semua Data (Keseluruhan Riwayat)');
                updatePdfLinks(start, today);
                loadReportData();
            });

            updatePdfLinks(startDateStr, endDateStr);
            loadReportData();
        });
    </script>
@endsection
