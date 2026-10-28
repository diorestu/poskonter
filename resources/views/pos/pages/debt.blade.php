@extends('layouts.app')

@section('content')
    <section class="space-y-4">
        <!-- 1. Header & Quick Actions -->
        <div class="list-panel p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500 text-white font-bold text-sm shadow-xs">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/>
                            <path d="M6 6h10"/>
                            <path d="M6 10h10"/>
                            <path d="M6 14h6"/>
                        </svg>
                    </span>
                    <div>
                        <h1 class="text-base font-bold text-[color:var(--text)]">Buku Utang Pelanggan</h1>
                        <p class="text-xs text-[color:var(--muted)]">Kelola bon utang, pantau grafik & progress pelunasan, serta catat pembayaran dengan cepat.</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="btn btn-secondary text-xs py-1.5 px-3 font-semibold" id="btnOpenManualDebtModal">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg></span>
                        <span>+ Catat Utang Manual</span>
                    </button>
                    <button type="button" class="btn btn-primary text-xs py-1.5 px-3 font-bold shadow-xs bg-emerald-600 hover:bg-emerald-700 border-emerald-600" id="btnOpenQuickPaymentModal">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 2v20"/><path d="m17 5-5-3-5 3"/><path d="m17 19-5 3-5-3"/></svg></span>
                        <span>⚡ Bayar / Pelunasan Utang</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. Dashboard Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="list-panel p-3.5 border-l-4 border-l-slate-600">
                <div class="text-[11px] font-semibold text-[color:var(--muted)] uppercase tracking-wider">Total Piutang Toko</div>
                <div class="text-lg font-black text-slate-800 mt-1" id="sumTotalDebt">Rp 0</div>
                <div class="text-[10px] text-[color:var(--muted)] mt-0.5" id="sumTotalCount">0 total bon tercatat</div>
            </div>

            <div class="list-panel p-3.5 border-l-4 border-l-emerald-500 bg-emerald-50/20">
                <div class="text-[11px] font-semibold text-emerald-800 uppercase tracking-wider">Sudah Dilunasi</div>
                <div class="text-lg font-black text-emerald-700 mt-1" id="sumTotalPaid">Rp 0</div>
                <div class="text-[10px] text-emerald-600 font-medium mt-0.5" id="sumPaidCount">0 transaksi lunas</div>
            </div>

            <div class="list-panel p-3.5 border-l-4 border-l-rose-500 bg-rose-50/20">
                <div class="text-[11px] font-semibold text-rose-800 uppercase tracking-wider">Sisa Belum Lunas</div>
                <div class="text-lg font-black text-rose-700 mt-1" id="sumTotalRemaining">Rp 0</div>
                <div class="text-[10px] text-rose-600 font-medium mt-0.5" id="sumUnpaidCount">0 transaksi belum lunas</div>
            </div>

            <div class="list-panel p-3.5 border-l-4 border-l-blue-500 bg-blue-50/20">
                <div class="text-[11px] font-semibold text-blue-800 uppercase tracking-wider">Penerimaan Hari Ini</div>
                <div class="text-lg font-black text-blue-700 mt-1" id="sumTodayPayments">Rp 0</div>
                <div class="text-[10px] text-blue-600 font-medium mt-0.5" id="sumTodayPaymentsCount">0 pelunasan hari ini</div>
            </div>
        </div>

        <!-- 3. Global Progress Bar & Visual Graphic Charts -->
        <div class="list-panel p-4 space-y-4">
            <!-- Global Animated Progress Bar -->
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center justify-between text-xs">
                    <span class="font-bold text-slate-800 flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                        <span>Tingkat Pengembalian Piutang Toko</span>
                    </span>
                    <strong class="text-emerald-700 text-sm font-black" id="overallProgressBarLabel">0% Terbayar</strong>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-3.5 p-0.5 border border-slate-200 overflow-hidden shadow-inner">
                    <div id="overallProgressBar" class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-400 transition-all duration-700 relative" style="width: 0%">
                        <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between text-[11px] text-[color:var(--muted)]">
                    <span><strong id="barPaidText" class="text-emerald-700">Rp 0</strong> sudah terbayar</span>
                    <span>Sisa di luar: <strong id="barRemainingText" class="text-rose-600">Rp 0</strong></span>
                </div>
            </div>

            <div class="border-t border-[#f1f5f9] pt-3">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-center">
                    <!-- Grafik 1: Donut Chart SVG Rasio Pelunasan -->
                    <div class="p-3.5 rounded-xl border border-slate-200/80 bg-[#f8fafc] flex flex-col sm:flex-row items-center gap-4">
                        <div class="relative flex items-center justify-center shrink-0">
                            <svg class="w-28 h-28 transform -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="40" stroke="#e2e8f0" stroke-width="12" fill="none" />
                                <circle id="donutPaidCircle" cx="50" cy="50" r="40" stroke="#10b981" stroke-width="12" fill="none" stroke-dasharray="0 251.32" stroke-linecap="round" class="transition-all duration-700" />
                            </svg>
                            <div class="absolute text-center">
                                <div class="text-base font-black text-slate-800" id="donutPercentText">0%</div>
                                <div class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Lunas</div>
                            </div>
                        </div>

                        <div class="space-y-1.5 text-xs flex-1">
                            <div class="text-xs font-bold text-slate-800">Rasio Pelunasan Piutang</div>
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Terbayar</span>
                                <strong id="donutPaidText" class="text-emerald-700 font-bold">Rp 0</strong>
                            </div>
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span>Sisa Belum Bayar</span>
                                <strong id="donutRemainingText" class="text-rose-600 font-bold">Rp 0</strong>
                            </div>
                            <div class="pt-1 border-t border-slate-200 text-[10px] text-slate-500 flex justify-between">
                                <span>Total Piutang:</span>
                                <strong id="donutTotalText" class="text-slate-700">Rp 0</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Grafik 2: Status Distribusi Batang -->
                    <div class="p-3.5 rounded-xl border border-slate-200/80 bg-[#f8fafc] space-y-2.5">
                        <div class="text-xs font-bold text-slate-800 flex items-center justify-between">
                            <span>Status Distribusi Utang</span>
                            <span class="text-[11px] font-medium text-[color:var(--muted)]" id="distTotalCount">0 Transaksi</span>
                        </div>

                        <!-- Bar Lunas -->
                        <div class="space-y-0.5">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-emerald-800 font-medium">Lunas Sepenuhnya (100%)</span>
                                <strong id="distPaidCount" class="text-emerald-700">0 bon</strong>
                            </div>
                            <div class="w-full bg-slate-200/70 rounded-full h-2 overflow-hidden">
                                <div id="distPaidBar" class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                            </div>
                        </div>

                        <!-- Bar Sebagian -->
                        <div class="space-y-0.5">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-amber-800 font-medium">Sebagian / Dicicil</span>
                                <strong id="distPartialCount" class="text-amber-700">0 bon</strong>
                            </div>
                            <div class="w-full bg-slate-200/70 rounded-full h-2 overflow-hidden">
                                <div id="distPartialBar" class="bg-amber-500 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                            </div>
                        </div>

                        <!-- Bar Belum Bayar -->
                        <div class="space-y-0.5">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-rose-800 font-medium">Belum Dibayar Sama Sekali (0%)</span>
                                <strong id="distUnpaidCount" class="text-rose-700">0 bon</strong>
                            </div>
                            <div class="w-full bg-slate-200/70 rounded-full h-2 overflow-hidden">
                                <div id="distUnpaidBar" class="bg-rose-500 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Daftar Utang Pelanggan & Riwayat Cicilan -->
        <div class="list-panel p-4 space-y-3.5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-1.5 rounded-lg bg-[#f1f5f9] p-1 border border-[#cbd5e1] text-xs font-semibold">
                    <button type="button" class="debt-tab-btn active px-3 py-1 rounded-md transition-all bg-white shadow-xs text-blue-700" data-tab="unpaid">
                        Belum Lunas (<span id="tabCountUnpaid">0</span>)
                    </button>
                    <button type="button" class="debt-tab-btn px-3 py-1 rounded-md transition-all text-[#64748b] hover:text-[#0f172a]" data-tab="all">
                        Semua Utang (<span id="tabCountAll">0</span>)
                    </button>
                    <button type="button" class="debt-tab-btn px-3 py-1 rounded-md transition-all text-[#64748b] hover:text-[#0f172a]" data-tab="paid">
                        Sudah Lunas (<span id="tabCountPaid">0</span>)
                    </button>
                    <button type="button" class="debt-tab-btn px-3 py-1 rounded-md transition-all text-[#64748b] hover:text-[#0f172a]" data-tab="payments">
                        Riwayat Pelunasan (<span id="tabCountPayments">0</span>)
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <div class="relative w-64 sm:w-80">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-[#64748b]">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </div>
                        <input type="text" id="debtTableSearch" class="field py-1.5 pl-8 pr-2.5 text-xs w-full" placeholder="Cari nama pelanggan / no telp / ref utang...">
                    </div>
                    <button type="button" class="btn btn-secondary py-1.5 px-2.5 text-xs shrink-0" id="btnRefreshDebtData" title="Segarkan Data">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg></span>
                        <span>Refresh</span>
                    </button>
                </div>
            </div>

            <!-- View 1: Tabel Utang Pelanggan (dengan Progress Bar) -->
            <div id="sectionDebtsTable" class="table-wrap rounded-lg border border-[#e2e8f0] overflow-x-auto max-h-[560px] overflow-y-auto">
                <table class="table-grid min-w-full text-xs">
                    <thead class="bg-slate-50 sticky top-0 z-10 text-left">
                        <tr>
                            <th width="125">Ref / Tanggal</th>
                            <th>Pelanggan & Kontak</th>
                            <th>Keterangan Bon</th>
                            <th class="text-center" width="60">Shift</th>
                            <th class="text-right" width="95">Total Utang</th>
                            <th class="text-right" width="95">Terbayar</th>
                            <th class="text-right" width="95">Sisa Utang</th>
                            <th width="150">Progress Pembayaran</th>
                            <th class="text-center" width="90">Status</th>
                            <th class="text-center" width="75">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="debtsTableBody">
                        <tr>
                            <td colspan="10" class="text-center py-8 text-[color:var(--muted)]">Memuat data utang pelanggan...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- View 2: Tabel Riwayat Pelunasan -->
            <div id="sectionPaymentsTable" class="hidden table-wrap rounded-lg border border-[#e2e8f0] overflow-x-auto max-h-[560px] overflow-y-auto">
                <table class="table-grid min-w-full text-xs">
                    <thead class="bg-slate-50 sticky top-0 z-10 text-left">
                        <tr>
                            <th width="130">Ref Pembayaran</th>
                            <th>Tanggal & Waktu</th>
                            <th class="text-center" width="60">Shift</th>
                            <th>Nama Pelanggan</th>
                            <th>Ref Utang Terkait</th>
                            <th class="text-right">Nominal Bayar</th>
                            <th class="text-center">Metode</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody id="paymentsTableBody">
                        <tr>
                            <td colspan="8" class="text-center py-8 text-[color:var(--muted)]">Memuat riwayat pembayaran...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- 5. Modal Pembayaran Utang Cepat & Searchable Combobox -->
    <div id="modalQuickDebtPayment" class="modal-backdrop hidden">
        <div class="modal-card max-w-lg">
            <div class="modal-head flex items-start justify-between gap-3 border-b border-[#f1f5f9] pb-3">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-800 font-bold text-sm">
                        ⚡
                    </span>
                    <div>
                        <h2 class="font-bold text-base text-[color:var(--text)]">Pembayaran / Pelunasan Utang</h2>
                        <p class="text-xs text-[color:var(--muted)]">Pilih orang yang berutang dengan fitur search atau bayar instan.</p>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary text-xs py-1" id="btnCloseQuickPaymentModal">
                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg></span>
                    <span>Tutup</span>
                </button>
            </div>

            <form id="formQuickDebtPayment" class="modal-body p-4 space-y-3.5">
                <!-- Searchable Debtor Combobox -->
                <div class="space-y-1 relative" id="debtorSearchComboboxWrapper">
                    <label class="block text-xs font-bold text-[color:var(--text)]">
                        Pilih Orang yang Berutang <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-[#64748b]">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </div>
                        <input type="text" id="debtorSearchInput" class="field text-xs w-full pl-8 pr-7 py-1.5" placeholder="Ketik nama pelanggan / no telp / ref utang..." autocomplete="off">
                        <button type="button" id="btnClearDebtorSelection" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 font-bold text-xs" title="Reset Pilihan">✕</button>
                    </div>
                    <input type="hidden" name="debt_id" id="quickDebtId">
                    <input type="hidden" name="customer_name" id="quickCustomerName" required>

                    <!-- Dropdown List Hasil Pencarian -->
                    <div id="debtorSearchResults" class="hidden absolute left-0 right-0 top-full mt-1 max-h-56 overflow-y-auto bg-white rounded-lg border border-slate-200 shadow-xl z-50 divide-y divide-slate-100 text-xs"></div>
                </div>

                <!-- Info Box Pelanggan Terpilih -->
                <div id="selectedDebtorCard" class="hidden p-3 rounded-xl border border-amber-300 bg-amber-50/70 text-xs space-y-1.5">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-900 text-sm" id="cardCustomerName">-</span>
                            <span class="text-[11px] text-slate-500 block font-mono" id="cardCustomerPhone">-</span>
                        </div>
                        <span class="badge bg-amber-100 text-amber-800 border border-amber-300 font-mono text-[10px] font-bold px-2 py-0.5" id="cardDebtRef">-</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-1 border-t border-amber-200/60 text-[11px]">
                        <div>Total Utang: <strong id="cardTotalDebt" class="text-slate-800">Rp 0</strong></div>
                        <div class="text-right">Sisa Piutang: <strong id="cardRemainingDebt" class="text-rose-700 font-bold">Rp 0</strong></div>
                    </div>

                    <!-- Mini Progress Bar Pelanggan Terpilih -->
                    <div class="space-y-0.5 pt-1">
                        <div class="flex items-center justify-between text-[10px] text-slate-600">
                            <span>Status Terbayar</span>
                            <strong id="cardProgressLabel" class="text-emerald-700">0%</strong>
                        </div>
                        <div class="w-full bg-amber-200/60 rounded-full h-1.5 overflow-hidden">
                            <div id="cardProgressBar" class="bg-emerald-600 h-1.5 rounded-full transition-all" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <!-- Nominal Pembayaran & Shortcut Cepat -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-emerald-800">Nominal Pembayaran (Rp) <span class="text-rose-500">*</span></label>
                        <!-- Quick Fill Buttons -->
                        <div class="flex items-center gap-1" id="quickFillButtons">
                            <button type="button" class="btn btn-secondary py-0.5 px-2 text-[10px] font-bold bg-emerald-50 text-emerald-800 border-emerald-300 hover:bg-emerald-100" id="btnFillFullAmount">
                                ⚡ Bayar Lunas
                            </button>
                            <button type="button" class="btn btn-secondary py-0.5 px-2 text-[10px]" id="btnFillHalfAmount">
                                50%
                            </button>
                        </div>
                    </div>
                    <input type="number" name="amount" id="quickPaymentAmount" class="field text-sm font-bold text-emerald-800 py-1.5 w-full bg-emerald-50/20 border-emerald-300" min="1" step="any" placeholder="0" required>
                    
                    <!-- Live Sisa Utang Setelah Pembayaran -->
                    <div id="afterPaymentStatusBox" class="rounded-lg p-2 bg-slate-50 border border-slate-200 text-xs flex items-center justify-between">
                        <span class="text-slate-600">Sisa Utang Setelah Bayar:</span>
                        <strong id="afterPaymentAmountText" class="font-bold text-slate-800">Rp 0</strong>
                    </div>
                </div>

                <!-- Shift, Metode, Tanggal -->
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Metode Pembayaran <span class="text-rose-500">*</span></label>
                        <select name="payment_method" id="quickPaymentMethod" class="select text-xs py-1.5 w-full" required>
                            <option value="cash">Tunai (Cash)</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="qris">QRIS</option>
                            <option value="debit">E-Money / Debit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Shift Penerimaan <span class="text-rose-500">*</span></label>
                        <select name="shift" id="quickPaymentShift" class="select text-xs py-1.5 w-full" required>
                            <option value="pagi">Shift Pagi</option>
                            <option value="sore">Shift Sore</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Pembayaran</label>
                        <input type="date" name="payment_date" id="quickPaymentDate" class="field text-xs py-1 w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Catatan <span class="text-[10px] text-[color:var(--muted)] font-normal">(Opsional)</span></label>
                        <input type="text" name="notes" id="quickPaymentNotes" class="field text-xs py-1 w-full" placeholder="Contoh: Titip lewat Budi">
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2 border-t border-[#f1f5f9]">
                    <button type="submit" class="btn btn-primary w-full py-2.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 border-emerald-600 justify-center shadow-xs" id="btnSubmitQuickPayment">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                        <span>Konfirmasi Pembayaran Utang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 6. Modal Catat Utang Manual -->
    <div id="modalManualDebt" class="modal-backdrop hidden">
        <div class="modal-card max-w-md">
            <div class="modal-head flex items-start justify-between gap-3 border-b border-[#f1f5f9] pb-3">
                <div>
                    <h2 class="font-bold text-base text-[color:var(--text)]">Catat Utang / Bon Baru</h2>
                    <p class="text-xs text-[color:var(--muted)]">Catat utang manual tanpa melalui transaksi POS.</p>
                </div>
                <button type="button" class="btn btn-secondary text-xs py-1" id="btnCloseManualDebtModal">
                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg></span>
                    <span>Tutup</span>
                </button>
            </div>

            <form id="formManualDebt" class="modal-body p-4 space-y-3">
                <div>
                    <label class="block text-xs font-semibold mb-1">Nama Pelanggan <span class="text-rose-500">*</span></label>
                    <input type="text" name="customer_name" id="manualDebtCustomerName" class="field text-xs py-1.5 w-full" placeholder="Nama penghutang..." required autocomplete="off">
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Nominal Utang (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="amount" id="manualDebtAmount" class="field text-xs py-1.5 w-full font-bold text-rose-700" min="1" step="any" placeholder="0" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Shift <span class="text-rose-500">*</span></label>
                        <select name="shift" id="manualDebtShift" class="select text-xs py-1.5 w-full" required>
                            <option value="pagi">Shift Pagi</option>
                            <option value="sore">Shift Sore</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-xs font-medium mb-1">No. Telepon / WA</label>
                        <input type="text" name="customer_phone" id="manualDebtPhone" class="field text-xs py-1.5 w-full" placeholder="08xxxxxx">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Bon</label>
                        <input type="date" name="debt_date" id="manualDebtDate" class="field text-xs py-1.5 w-full">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium mb-1">Keterangan Barang / Transaksi</label>
                    <input type="text" name="description" id="manualDebtDesc" class="field text-xs py-1.5 w-full" placeholder="Contoh: Utang Pulsa 50rb + Case HP">
                </div>

                <div>
                    <label class="block text-xs font-medium mb-1">Catatan Tambahan</label>
                    <input type="text" name="notes" id="manualDebtNotes" class="field text-xs py-1.5 w-full" placeholder="Contoh: Janji bayar akhir bulan">
                </div>

                <div class="pt-2 border-t border-[#f1f5f9]">
                    <button type="submit" class="btn btn-primary w-full py-2 text-xs font-bold justify-center" id="btnSubmitManualDebt">
                        <span>Simpan Catatan Utang</span>
                    </button>
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
                dashboard: @json(route('ajax.debts.dashboard')),
                unpaidDebts: @json(route('ajax.debts.unpaid')),
                storeDebt: @json(route('ajax.debts.store')),
                storePayment: @json(route('ajax.debts.payments.store')),
            };

            let state = {
                debts: [],
                payments: [],
                unpaidDebtsList: [],
                activeTab: 'unpaid', // 'unpaid', 'all', 'paid', 'payments'
                selectedDebt: null,
            };

            function loadData() {
                $.getJSON(urls.dashboard)
                    .done(function (res) {
                        state.debts = res.debts || [];
                        state.payments = res.payments || [];
                        updateDashboardStats(res.summary || {});
                        renderTables();
                    })
                    .fail(function (xhr) {
                        console.error('Gagal memuat data utang', xhr);
                    });

                // Load unpaid debts for combobox
                $.getJSON(urls.unpaidDebts)
                    .done(function (res) {
                        state.unpaidDebtsList = res || [];
                    });
            }

            function updateDashboardStats(summary) {
                const totalDebt = Number(summary.total_debt || 0);
                const totalPaid = Number(summary.total_paid || 0);
                const totalRemaining = Number(summary.total_remaining || 0);
                const progress = Number(summary.overall_progress || 0);

                $('#sumTotalDebt').text(money(totalDebt));
                $('#sumTotalCount').text(`${summary.total_count || 0} total bon tercatat`);

                $('#sumTotalPaid').text(money(totalPaid));
                $('#sumPaidCount').text(`${summary.paid_count || 0} transaksi lunas`);

                $('#sumTotalRemaining').text(money(totalRemaining));
                $('#sumUnpaidCount').text(`${(summary.unpaid_count || 0) + (summary.partial_count || 0)} transaksi belum lunas`);

                $('#sumTodayPayments').text(money(summary.today_payments_amount || 0));
                $('#sumTodayPaymentsCount').text(`${summary.today_payments_count || 0} pelunasan hari ini`);

                // Global Progress Bar
                $('#overallProgressBar').css('width', `${progress}%`);
                $('#overallProgressBarLabel').text(`${progress}% Terbayar`);
                $('#barPaidText').text(money(totalPaid));
                $('#barRemainingText').text(money(totalRemaining));

                // Donut Chart SVG
                const circumference = 251.32; // 2 * PI * 40
                const paidStroke = (progress / 100) * circumference;
                $('#donutPaidCircle').attr('stroke-dasharray', `${paidStroke} ${circumference}`);
                $('#donutPercentText').text(`${progress}%`);
                $('#donutPaidText').text(money(totalPaid));
                $('#donutRemainingText').text(money(totalRemaining));
                $('#donutTotalText').text(money(totalDebt));

                // Status Distribution Bars
                const totalCount = summary.total_count || 1;
                const paidPct = Math.round(((summary.paid_count || 0) / totalCount) * 100);
                const partialPct = Math.round(((summary.partial_count || 0) / totalCount) * 100);
                const unpaidPct = Math.round(((summary.unpaid_count || 0) / totalCount) * 100);

                $('#distTotalCount').text(`${summary.total_count || 0} Transaksi`);
                $('#distPaidCount').text(`${summary.paid_count || 0} bon (${paidPct}%)`);
                $('#distPaidBar').css('width', `${paidPct}%`);

                $('#distPartialCount').text(`${summary.partial_count || 0} bon (${partialPct}%)`);
                $('#distPartialBar').css('width', `${partialPct}%`);

                $('#distUnpaidCount').text(`${summary.unpaid_count || 0} bon (${unpaidPct}%)`);
                $('#distUnpaidBar').css('width', `${unpaidPct}%`);

                // Tab counts
                const unpaidCount = state.debts.filter(d => d.status !== 'paid' && d.status !== 'cancelled').length;
                const allCount = state.debts.length;
                const paidCount = state.debts.filter(d => d.status === 'paid').length;

                $('#tabCountUnpaid').text(unpaidCount);
                $('#tabCountAll').text(allCount);
                $('#tabCountPaid').text(paidCount);
                $('#tabCountPayments').text(state.payments.length);
            }

            function renderTables() {
                if (state.activeTab === 'payments') {
                    $('#sectionDebtsTable').addClass('hidden');
                    $('#sectionPaymentsTable').removeClass('hidden');
                    renderPaymentsTable();
                } else {
                    $('#sectionPaymentsTable').addClass('hidden');
                    $('#sectionDebtsTable').removeClass('hidden');
                    renderDebtsTable();
                }
            }

            function renderDebtsTable() {
                const query = ($('#debtTableSearch').val() || '').toLowerCase().trim();
                let filtered = state.debts;

                if (state.activeTab === 'unpaid') {
                    filtered = filtered.filter(d => d.status !== 'paid' && d.status !== 'cancelled');
                } else if (state.activeTab === 'paid') {
                    filtered = filtered.filter(d => d.status === 'paid');
                }

                if (query) {
                    filtered = filtered.filter(d =>
                        (d.customer_name || '').toLowerCase().includes(query) ||
                        (d.customer_phone || '').toLowerCase().includes(query) ||
                        (d.reference_number || '').toLowerCase().includes(query) ||
                        (d.description || '').toLowerCase().includes(query)
                    );
                }

                const $tbody = $('#debtsTableBody');
                if (!filtered.length) {
                    $tbody.html('<tr><td colspan="10" class="text-center py-8 text-[color:var(--muted)]">Tidak ada data utang yang cocok.</td></tr>');
                    return;
                }

                $tbody.html(filtered.map(d => {
                    const progress = Number(d.progress_percent || 0);
                    const remaining = Number(d.remaining_amount || 0);
                    const isPaid = d.status === 'paid';
                    const isCancelled = d.status === 'cancelled';

                    let statusBadge = '<span class="badge bg-rose-100 text-rose-800 border border-rose-300 font-bold text-[10px]">BELUM BAYAR</span>';
                    if (isPaid) {
                        statusBadge = '<span class="badge bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold text-[10px]">LUNAS</span>';
                    } else if (d.status === 'partial') {
                        statusBadge = '<span class="badge bg-amber-100 text-amber-800 border border-amber-300 font-bold text-[10px]">SEBAGIAN</span>';
                    } else if (isCancelled) {
                        statusBadge = '<span class="badge bg-slate-100 text-slate-600 font-normal text-[10px]">DIBATALKAN</span>';
                    }

                    return `
                        <tr class="${isCancelled ? 'opacity-50 bg-slate-50' : (isPaid ? 'bg-emerald-50/20' : '')}">
                            <td>
                                <strong class="text-purple-700 font-mono text-[11px]">${d.reference_number}</strong>
                                <div class="text-[10px] text-slate-500">${d.debt_date || '-'}</div>
                            </td>
                            <td>
                                <div class="font-bold text-slate-900">${d.customer_name}</div>
                                <div class="text-[10px] text-slate-500 font-mono">${d.customer_phone && d.customer_phone !== '-' ? d.customer_phone : ''}</div>
                            </td>
                            <td>
                                <div class="text-slate-800">${d.description || '-'}</div>
                                ${d.notes && d.notes !== '-' ? `<div class="text-[10px] text-slate-400 italic">${d.notes}</div>` : ''}
                            </td>
                            <td class="text-center">
                                <span class="badge ${d.shift === 'pagi' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800'} uppercase font-bold text-[10px]">
                                    ${d.shift || 'pagi'}
                                </span>
                            </td>
                            <td class="text-right font-medium text-slate-800">${money(d.amount)}</td>
                            <td class="text-right font-semibold text-emerald-700">${money(d.paid_amount)}</td>
                            <td class="text-right font-bold ${remaining > 0 ? 'text-rose-700' : 'text-slate-400'}">${money(remaining)}</td>
                            <td>
                                <!-- Progress Bar Per Transaksi -->
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between text-[10px]">
                                        <span class="font-bold ${progress >= 100 ? 'text-emerald-700' : (progress > 0 ? 'text-amber-700' : 'text-slate-400')}">${progress}%</span>
                                        <span class="text-slate-500">${money(d.paid_amount)} / ${money(d.amount)}</span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full ${progress >= 100 ? 'bg-emerald-500' : (progress > 0 ? 'bg-amber-500' : 'bg-slate-300')} transition-all" style="width: ${progress}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">${statusBadge}</td>
                            <td class="text-center">
                                ${!isPaid && !isCancelled ? `
                                    <button type="button" class="btn btn-primary py-1 px-2.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 border-emerald-600 btn-table-pay" data-id="${d.id}" data-name="${d.customer_name}" data-phone="${d.customer_phone}" data-ref="${d.reference_number}" data-amount="${d.amount}" data-paid="${d.paid_amount}" data-remaining="${remaining}">
                                        ⚡ Bayar
                                    </button>
                                ` : '<span class="text-slate-400 text-[10px]">-</span>'}
                            </td>
                        </tr>
                    `;
                }).join(''));
            }

            function renderPaymentsTable() {
                const query = ($('#debtTableSearch').val() || '').toLowerCase().trim();
                let filtered = state.payments;

                if (query) {
                    filtered = filtered.filter(p =>
                        (p.customer_name || '').toLowerCase().includes(query) ||
                        (p.reference_number || '').toLowerCase().includes(query) ||
                        (p.debt_reference || '').toLowerCase().includes(query) ||
                        (p.notes || '').toLowerCase().includes(query)
                    );
                }

                const $tbody = $('#paymentsTableBody');
                if (!filtered.length) {
                    $tbody.html('<tr><td colspan="8" class="text-center py-8 text-[color:var(--muted)]">Tidak ada catatan pembayaran.</td></tr>');
                    return;
                }

                $tbody.html(filtered.map(p => `
                    <tr>
                        <td>
                            <strong class="text-purple-700 font-mono text-[11px]">${p.reference_number}</strong>
                        </td>
                        <td>${p.payment_date || '-'}</td>
                        <td class="text-center">
                            <span class="badge ${p.shift === 'pagi' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800'} uppercase font-bold text-[10px]">
                                ${p.shift || 'pagi'}
                            </span>
                        </td>
                        <td class="font-bold text-slate-900">${p.customer_name}</td>
                        <td class="font-mono text-[11px] text-slate-600">${p.debt_reference || '-'}</td>
                        <td class="text-right font-bold text-emerald-700">${money(p.amount)}</td>
                        <td class="text-center"><span class="badge bg-slate-100 text-slate-700 uppercase text-[10px] font-semibold">${p.payment_method || 'cash'}</span></td>
                        <td class="text-slate-500">${p.notes && p.notes !== '-' ? p.notes : '-'}</td>
                    </tr>
                `).join(''));
            }

            // Tabs Switcher
            $('.debt-tab-btn').on('click', function () {
                $('.debt-tab-btn').removeClass('active bg-white shadow-xs text-blue-700').addClass('text-[#64748b]');
                $(this).addClass('active bg-white shadow-xs text-blue-700').removeClass('text-[#64748b]');
                state.activeTab = $(this).data('tab');
                renderTables();
            });

            $('#debtTableSearch').on('input', renderTables);
            $('#btnRefreshDebtData').on('click', loadData);

            // -------------------------------------------------------------
            // SEARCHABLE DEBTOR COMBOBOX & QUICK PAYMENT MODAL LOGIC
            // -------------------------------------------------------------
            function selectDebtor(d) {
                state.selectedDebt = d;
                const remaining = Number(d.remaining_amount || (Number(d.amount) - Number(d.paid_amount)));

                $('#quickDebtId').val(d.id);
                $('#quickCustomerName').val(d.customer_name);
                $('#debtorSearchInput').val(d.customer_name);
                $('#btnClearDebtorSelection').removeClass('hidden');
                $('#debtorSearchResults').addClass('hidden');

                // Fill card
                $('#cardCustomerName').text(d.customer_name);
                $('#cardCustomerPhone').text(d.customer_phone && d.customer_phone !== '-' ? d.customer_phone : '');
                $('#cardDebtRef').text(d.reference_number);
                $('#cardTotalDebt').text(money(d.amount));
                $('#cardRemainingDebt').text(money(remaining));

                const progress = d.amount > 0 ? Math.min(100, Math.round((Number(d.paid_amount) / Number(d.amount)) * 100)) : 0;
                $('#cardProgressLabel').text(`${progress}% terbayar`);
                $('#cardProgressBar').css('width', `${progress}%`);

                $('#selectedDebtorCard').removeClass('hidden');

                // Quick fill default amount
                $('#quickPaymentAmount').val(remaining);
                $('#btnFillFullAmount').text(`⚡ Bayar Lunas (${money(remaining)})`).data('val', remaining);
                $('#btnFillHalfAmount').text(`50% (${money(Math.round(remaining / 2))})`).data('val', Math.round(remaining / 2));

                updateAfterPaymentStatus();
            }

            function clearDebtorSelection() {
                state.selectedDebt = null;
                $('#quickDebtId').val('');
                $('#quickCustomerName').val('');
                $('#debtorSearchInput').val('').focus();
                $('#btnClearDebtorSelection').addClass('hidden');
                $('#selectedDebtorCard').addClass('hidden');
                $('#quickPaymentAmount').val('');
                $('#btnFillFullAmount').text('⚡ Bayar Lunas').data('val', 0);
                $('#btnFillHalfAmount').text('50%').data('val', 0);
                updateAfterPaymentStatus();
            }

            function renderDebtorSearchResults(query) {
                const q = (query || '').toLowerCase().trim();
                const list = state.unpaidDebtsList || [];
                const filtered = list.filter(d =>
                    !q ||
                    (d.customer_name || '').toLowerCase().includes(q) ||
                    (d.customer_phone || '').toLowerCase().includes(q) ||
                    (d.reference_number || '').toLowerCase().includes(q)
                );

                const $box = $('#debtorSearchResults');
                if (!filtered.length) {
                    $box.html('<div class="p-3 text-center text-slate-400">Tidak ada utang belum lunas yang cocok.</div>').removeClass('hidden');
                    return;
                }

                $box.html(filtered.map(d => {
                    const remaining = Number(d.remaining_amount || (Number(d.amount) - Number(d.paid_amount)));
                    const progress = d.amount > 0 ? Math.min(100, Math.round((Number(d.paid_amount) / Number(d.amount)) * 100)) : 0;

                    return `
                        <div class="debtor-search-item p-2.5 hover:bg-emerald-50/80 cursor-pointer transition-colors flex items-center justify-between gap-2" data-id="${d.id}">
                            <div>
                                <div class="font-bold text-slate-900">${d.customer_name}</div>
                                <div class="text-[10px] text-slate-500 font-mono">${d.reference_number} ${d.customer_phone && d.customer_phone !== '-' ? `• ${d.customer_phone}` : ''}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-rose-700">${money(remaining)}</div>
                                <div class="text-[10px] text-emerald-600 font-medium">${progress}% lunas</div>
                            </div>
                        </div>
                    `;
                }).join('')).removeClass('hidden');
            }

            function updateAfterPaymentStatus() {
                const remaining = state.selectedDebt ? Number(state.selectedDebt.remaining_amount || (Number(state.selectedDebt.amount) - Number(state.selectedDebt.paid_amount))) : 0;
                const paymentAmount = Number($('#quickPaymentAmount').val() || 0);

                if (!state.selectedDebt) {
                    $('#afterPaymentStatusBox').addClass('hidden');
                    return;
                }

                $('#afterPaymentStatusBox').removeClass('hidden');
                const diff = remaining - paymentAmount;
                const $amountEl = $('#afterPaymentAmountText');

                if (diff <= 0) {
                    $amountEl.html('<span class="text-emerald-700 font-bold">Rp 0 (LUNAS SEPENUHNYA ✅)</span>');
                } else {
                    $amountEl.html(`<span class="text-rose-700 font-bold">${money(diff)}</span> <span class="text-slate-500 font-normal">tersisa</span>`);
                }
            }

            // Input listener on Search Box
            $('#debtorSearchInput').on('input focus', function () {
                if (!state.selectedDebt) {
                    renderDebtorSearchResults($(this).val());
                }
            });

            $(document).on('click', '.debtor-search-item', function () {
                const id = $(this).data('id');
                const found = state.unpaidDebtsList.find(d => Number(d.id) === Number(id));
                if (found) {
                    selectDebtor(found);
                }
            });

            $('#btnClearDebtorSelection').on('click', clearDebtorSelection);

            $(document).on('click', function (e) {
                if (!$(e.target).closest('#debtorSearchComboboxWrapper').length) {
                    $('#debtorSearchResults').addClass('hidden');
                }
            });

            // Quick Fill Amount Buttons
            $('#btnFillFullAmount, #btnFillHalfAmount').on('click', function () {
                const val = $(this).data('val');
                if (val > 0) {
                    $('#quickPaymentAmount').val(val);
                    updateAfterPaymentStatus();
                }
            });

            $('#quickPaymentAmount').on('input', updateAfterPaymentStatus);

            // Table "⚡ Bayar" button (Instant 1-Click to open modal with debtor preloaded)
            $(document).on('click', '.btn-table-pay', function () {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const phone = $(this).data('phone');
                const ref = $(this).data('ref');
                const amount = $(this).data('amount');
                const paid = $(this).data('paid');
                const remaining = $(this).data('remaining');

                const debtObj = {
                    id: id,
                    customer_name: name,
                    customer_phone: phone,
                    reference_number: ref,
                    amount: amount,
                    paid_amount: paid,
                    remaining_amount: remaining,
                };

                $('#formQuickDebtPayment')[0].reset();
                const today = new Date().toISOString().split('T')[0];
                const hour = new Date().getHours();
                $('#quickPaymentDate').val(today);
                $('#quickPaymentShift').val(hour >= 15 ? 'sore' : 'pagi');
                $('#quickPaymentMethod').val('cash');

                selectDebtor(debtObj);
                $('#modalQuickDebtPayment').removeClass('hidden');
            });

            // Main "+ Bayar / Pelunasan Utang" Button
            $('#btnOpenQuickPaymentModal').on('click', function () {
                $('#formQuickDebtPayment')[0].reset();
                clearDebtorSelection();
                const today = new Date().toISOString().split('T')[0];
                const hour = new Date().getHours();
                $('#quickPaymentDate').val(today);
                $('#quickPaymentShift').val(hour >= 15 ? 'sore' : 'pagi');
                $('#quickPaymentMethod').val('cash');

                $('#modalQuickDebtPayment').removeClass('hidden');
                setTimeout(() => $('#debtorSearchInput').focus(), 100);
            });

            $('#btnCloseQuickPaymentModal').on('click', function () {
                $('#modalQuickDebtPayment').addClass('hidden');
            });

            // Submit Payment Form
            $('#formQuickDebtPayment').on('submit', function (e) {
                e.preventDefault();

                if (!state.selectedDebt && !$('#quickCustomerName').val().trim()) {
                    showAlert('Pilih orang yang berutang dari daftar pencarian.', 'error');
                    $('#debtorSearchInput').focus();
                    return;
                }

                const amount = Number($('#quickPaymentAmount').val() || 0);
                if (amount <= 0) {
                    showAlert('Nominal pembayaran harus lebih besar dari Rp 0.', 'error');
                    $('#quickPaymentAmount').focus();
                    return;
                }

                const $btn = $('#btnSubmitQuickPayment');
                $btn.prop('disabled', true).addClass('opacity-75');

                const payload = {
                    debt_id: $('#quickDebtId').val() || null,
                    customer_name: $('#quickCustomerName').val().trim(),
                    amount: amount,
                    payment_method: $('#quickPaymentMethod').val(),
                    shift: $('#quickPaymentShift').val(),
                    payment_date: $('#quickPaymentDate').val() || null,
                    notes: $('#quickPaymentNotes').val().trim() || null,
                };

                $.post(urls.storePayment, payload)
                    .done(function (res) {
                        $('#modalQuickDebtPayment').addClass('hidden');
                        showAlert(res.message || 'Pembayaran utang berhasil dicatat.');
                        loadData();
                    })
                    .fail(function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Gagal menyimpan pembayaran utang.';
                        showAlert(msg, 'error');
                    })
                    .always(function () {
                        $btn.prop('disabled', false).removeClass('opacity-75');
                    });
            });

            // -------------------------------------------------------------
            // MANUAL DEBT MODAL LOGIC
            // -------------------------------------------------------------
            $('#btnOpenManualDebtModal').on('click', function () {
                $('#formManualDebt')[0].reset();
                const today = new Date().toISOString().split('T')[0];
                const hour = new Date().getHours();
                $('#manualDebtDate').val(today);
                $('#manualDebtShift').val(hour >= 15 ? 'sore' : 'pagi');
                $('#modalManualDebt').removeClass('hidden');
                setTimeout(() => $('#manualDebtCustomerName').focus(), 100);
            });

            $('#btnCloseManualDebtModal').on('click', function () {
                $('#modalManualDebt').addClass('hidden');
            });

            $('#formManualDebt').on('submit', function (e) {
                e.preventDefault();

                const name = $('#manualDebtCustomerName').val().trim();
                const amount = Number($('#manualDebtAmount').val() || 0);

                if (!name) {
                    showAlert('Nama pelanggan wajib diisi.', 'error');
                    return;
                }
                if (amount <= 0) {
                    showAlert('Nominal utang harus lebih besar dari Rp 0.', 'error');
                    return;
                }

                const $btn = $('#btnSubmitManualDebt').prop('disabled', true);
                const payload = $(this).serialize();

                $.post(urls.storeDebt, payload)
                    .done(function (res) {
                        $('#modalManualDebt').addClass('hidden');
                        showAlert(res.message || 'Catatan utang berhasil disimpan.');
                        loadData();
                    })
                    .fail(function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Gagal menyimpan catatan utang.';
                        showAlert(msg, 'error');
                    })
                    .always(function () {
                        $btn.prop('disabled', false);
                    });
            });

            // Initial load
            loadData();
        });
    </script>
@endsection
