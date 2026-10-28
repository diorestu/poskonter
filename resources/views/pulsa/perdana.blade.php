@extends('layouts.app')

@section('content')
    <section class="space-y-4">
        <!-- Header & Ringkasan Hari Ini -->
        <div class="list-panel p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white font-bold text-xs shadow-sm">
                            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect width="16" height="20" x="4" y="2" rx="2" />
                                <line x1="8" x2="16" y1="6" y2="6" />
                                <line x1="16" x2="16" y1="14" y2="14" />
                                <path d="M16 10h.01" />
                                <path d="M12 10h.01" />
                                <path d="M8 10h.01" />
                                <path d="M12 14h.01" />
                                <path d="M8 14h.01" />
                                <path d="M12 18h.01" />
                                <path d="M8 18h.01" />
                            </svg>
                        </span>
                        <div>
                            <h1 class="text-base font-bold text-[color:var(--text)]">Penjualan Kartu Perdana</h1>
                            <p class="text-xs text-[color:var(--muted)]">Input manual nama operator, harga jual, dan harga beli (modal) kartu perdana.</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="btn btn-primary py-1.5 px-3 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 border-emerald-600 text-white shadow-xs" id="btnOpenAddProduct">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg></span>
                        <span>+ Input Produk Perdana</span>
                    </button>
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50/80 px-3 py-1.5 text-xs">
                        <span class="text-emerald-700 font-medium">Penjualan Hari Ini:</span>
                        <strong id="todayPerdanaTotal" class="ml-1 text-emerald-800 font-bold">Rp 0</strong>
                        <span id="todayPerdanaCount" class="ml-1 text-[11px] text-emerald-600">(0 kartu)</span>
                    </div>
                    <div class="rounded-lg border border-blue-200 bg-blue-50/80 px-3 py-1.5 text-xs">
                        <span class="text-blue-700 font-medium">Laba Hari Ini:</span>
                        <strong id="todayPerdanaProfit" class="ml-1 text-blue-800 font-bold">Rp 0</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2 Column Layout: Quick Operators + History on Left, Sale Form on Right -->
        <div class="grid gap-4 lg:grid-cols-[1fr_400px] xl:grid-cols-[1fr_440px] items-start">
            <!-- Left Side: Quick Operator Badges & Recent Transactions Table -->
            <div class="space-y-4 min-w-0">
                <!-- Pilihan Cepat Operator -->
                <div class="list-panel p-4 space-y-3">
                    <div>
                        <div class="text-xs font-bold text-[color:var(--text)] uppercase tracking-wider">Pilih Operator Cepat</div>
                        <div class="text-[11px] text-[color:var(--muted)]">Klik salah satu operator untuk mengisi nama secara otomatis atau ketik manual di formulir kanan.</div>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2" id="operatorChipsContainer">
                        <button type="button" class="operator-chip flex items-center gap-2 p-2 rounded-lg border border-rose-200 bg-rose-50/70 hover:bg-rose-100/80 text-rose-900 transition-all text-left text-xs font-semibold" data-operator="Telkomsel">
                            <span class="inline-block h-3 w-3 rounded-full bg-rose-600 shrink-0"></span>
                            <span>Telkomsel</span>
                        </button>
                        <button type="button" class="operator-chip flex items-center gap-2 p-2 rounded-lg border border-amber-200 bg-amber-50/70 hover:bg-amber-100/80 text-amber-900 transition-all text-left text-xs font-semibold" data-operator="Indosat IM3">
                            <span class="inline-block h-3 w-3 rounded-full bg-amber-500 shrink-0"></span>
                            <span>Indosat IM3</span>
                        </button>
                        <button type="button" class="operator-chip flex items-center gap-2 p-2 rounded-lg border border-blue-200 bg-blue-50/70 hover:bg-blue-100/80 text-blue-900 transition-all text-left text-xs font-semibold" data-operator="XL">
                            <span class="inline-block h-3 w-3 rounded-full bg-blue-600 shrink-0"></span>
                            <span>XL Axiata</span>
                        </button>
                        <button type="button" class="operator-chip flex items-center gap-2 p-2 rounded-lg border border-purple-200 bg-purple-50/70 hover:bg-purple-100/80 text-purple-900 transition-all text-left text-xs font-semibold" data-operator="AXIS">
                            <span class="inline-block h-3 w-3 rounded-full bg-purple-600 shrink-0"></span>
                            <span>AXIS</span>
                        </button>
                        <button type="button" class="operator-chip flex items-center gap-2 p-2 rounded-lg border border-orange-200 bg-orange-50/70 hover:bg-orange-100/80 text-orange-900 transition-all text-left text-xs font-semibold" data-operator="Tri (3)">
                            <span class="inline-block h-3 w-3 rounded-full bg-orange-500 shrink-0"></span>
                            <span>Tri (3)</span>
                        </button>
                        <button type="button" class="operator-chip flex items-center gap-2 p-2 rounded-lg border border-pink-200 bg-pink-50/70 hover:bg-pink-100/80 text-pink-900 transition-all text-left text-xs font-semibold" data-operator="Smartfren">
                            <span class="inline-block h-3 w-3 rounded-full bg-pink-600 shrink-0"></span>
                            <span>Smartfren</span>
                        </button>
                        <button type="button" class="operator-chip flex items-center gap-2 p-2 rounded-lg border border-sky-200 bg-sky-50/70 hover:bg-sky-100/80 text-sky-900 transition-all text-left text-xs font-semibold" data-operator="by.U">
                            <span class="inline-block h-3 w-3 rounded-full bg-sky-500 shrink-0"></span>
                            <span>by.U</span>
                        </button>
                        <button type="button" class="operator-chip flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50/70 hover:bg-slate-100/80 text-slate-800 transition-all text-left text-xs font-semibold" data-operator="Perdana Lainnya">
                            <span class="inline-block h-3 w-3 rounded-full bg-slate-400 shrink-0"></span>
                            <span>Lainnya</span>
                        </button>
                    </div>
                </div>

                <!-- Tab Switcher: Katalog Produk Perdana vs Riwayat Penjualan -->
                <div class="list-panel p-4 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-2.5">
                        <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-lg border border-slate-200 text-xs font-semibold">
                            <button type="button" class="left-tab-btn active px-3 py-1 rounded-md transition-all bg-white text-emerald-800 shadow-xs" data-tab="catalog">
                                📦 Katalog Kartu Perdana (<span id="perdanaCatalogCount">0</span>)
                            </button>
                            <button type="button" class="left-tab-btn px-3 py-1 rounded-md transition-all text-slate-500 hover:text-slate-800" data-tab="history">
                                📜 Riwayat Penjualan (<span id="perdanaHistoryCount">0</span>)
                            </button>
                        </div>
                        <input id="perdanaCatalogSearch" class="field max-w-xs text-xs py-1.5" type="text" placeholder="Cari nama, kode, operator...">
                    </div>

                    <!-- Panel 1: Katalog Kartu Perdana -->
                    <div id="tabContentCatalog" class="space-y-3">
                        <div id="perdanaCatalogContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 max-h-[480px] overflow-y-auto pr-1">
                            <div class="col-span-full text-center py-6 text-slate-400 text-xs">Memuat katalog kartu perdana...</div>
                        </div>
                    </div>

                    <!-- Panel 2: Riwayat Penjualan Perdana -->
                    <div id="tabContentHistory" class="hidden space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="text-xs text-[color:var(--muted)]">Daftar transaksi penjualan kartu perdana terbaru.</div>
                            <button type="button" class="btn btn-secondary py-1 px-2.5 text-xs" id="btnRefreshHistory">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg></span>
                                <span>Segarkan</span>
                            </button>
                        </div>

                        <div class="table-wrap rounded-lg border border-[#e2e8f0] overflow-x-auto max-h-[460px] overflow-y-auto">
                            <table class="table-grid min-w-full text-xs">
                                <thead class="bg-slate-50 sticky top-0 z-10 text-left">
                                    <tr>
                                        <th>Ref / Waktu</th>
                                        <th>Shift</th>
                                        <th>Operator / Nama</th>
                                        <th class="text-right">Harga Beli</th>
                                        <th class="text-right">Harga Jual</th>
                                        <th class="text-right">Laba</th>
                                        <th>Bayar</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" width="70">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="perdanaHistoryBody">
                                    <tr>
                                        <td colspan="9" class="text-center py-6 text-[color:var(--muted)]">Memuat data transaksi...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Mode Switcher & Form Panels -->
            <div class="space-y-3 max-w-full self-start lg:sticky lg:top-[calc(var(--header-height,0px)+1rem)]">
                <!-- Panel Switcher Tabs -->
                <div class="flex items-center rounded-lg border border-[#d8e1ec] bg-[#f1f5f9] p-1 gap-1">
                    <button type="button" class="perdana-mode-tab flex-1 py-1.5 px-3 text-xs font-semibold rounded-md transition-all bg-white text-[color:var(--text)] shadow-xs" id="tabModePerdanaSale" data-mode="sale">
                        <span class="flex items-center justify-center gap-1.5">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                            <span>Form Penjualan</span>
                        </span>
                    </button>
                    <button type="button" class="perdana-mode-tab flex-1 py-1.5 px-3 text-xs font-semibold rounded-md transition-all text-[color:var(--muted)] hover:text-[color:var(--text)]" id="tabModePerdanaAddProduct" data-mode="add">
                        <span class="flex items-center justify-center gap-1.5">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                            <span>+ Form Input Produk Perdana</span>
                        </span>
                    </button>
                </div>

                <!-- PANEL 1: Form Penjualan Kartu Perdana -->
                <div id="panelPerdanaSale">
                    <form id="perdanaForm" class="list-panel p-4 space-y-3.5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-[#e2e8f0] pb-2.5">
                            <div>
                                <div class="text-sm font-bold text-[color:var(--text)]">Form Input Penjualan Perdana</div>
                                <div class="text-[11px] text-[color:var(--muted)]">Pilih dari katalog kiri atau ketik manual.</div>
                            </div>
                            <button type="button" class="btn btn-secondary py-0.5 px-2 text-[10px]" id="btnClearSaleForm">Reset</button>
                        </div>

                        <!-- 1. Nama Operator / Produk -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-[color:var(--text)]">Nama Operator / Kartu Perdana <span class="text-red-500">*</span></label>
                                <span id="selectedProductBadge" class="hidden text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">Katalog</span>
                            </div>
                            <input class="field text-xs py-1.5" type="text" id="perdana_operator_name" placeholder="Contoh: Telkomsel 14GB / Axis Kuota 5GB" required autocomplete="off">
                            <div class="mt-0.5 text-[10px] text-[color:var(--muted)]">Bisa nama operator saja atau beserta detail kuota/paket.</div>
                        </div>

                        <!-- 2. Harga Jual & Harga Beli (Modal) -->
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-emerald-800">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                                <input class="field text-xs py-1.5 font-bold text-emerald-800 bg-emerald-50/30 border-emerald-300" type="text" inputmode="numeric" data-number-mask id="perdana_price" value="0" required autocomplete="off">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-blue-800">Harga Beli / Modal (Rp) <span class="text-red-500">*</span></label>
                                <input class="field text-xs py-1.5 font-bold text-blue-800 bg-blue-50/30 border-blue-300" type="text" inputmode="numeric" data-number-mask id="perdana_cost_price" value="0" required autocomplete="off">
                            </div>
                        </div>

                        <!-- Live Profit / Margin Preview Box -->
                        <div id="profitPreviewBox" class="rounded-lg border border-emerald-200 bg-emerald-50/60 p-2.5 text-xs flex items-center justify-between">
                            <span class="text-emerald-800 font-medium">Estimasi Laba / Untung:</span>
                            <div class="text-right">
                                <strong id="profitAmountText" class="text-emerald-700 font-bold text-sm">Rp 0</strong>
                                <span id="profitPercentText" class="text-[11px] text-emerald-600 block">(0%)</span>
                            </div>
                        </div>

                        <!-- 3. Nomor Perdana / SN (Opsional) -->
                        <div>
                            <label class="mb-1 block text-xs font-medium text-[color:var(--text)]">Nomor Perdana / Barcode SN <span class="text-[10px] text-[color:var(--muted)] font-normal">(Opsional)</span></label>
                            <input class="field text-xs py-1.5 font-mono" type="text" id="perdana_serial_number" placeholder="Contoh: 08123456789 atau scan barcode kartu" autocomplete="off">
                        </div>

                        <!-- 4. Shift & Metode Pembayaran -->
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-[color:var(--text)]">Shift</label>
                                <select class="select text-xs py-1.5" id="perdana_shift">
                                    <option value="pagi" {{ now()->hour < 15 ? 'selected' : '' }}>Pagi</option>
                                    <option value="sore" {{ now()->hour >= 15 ? 'selected' : '' }}>Sore</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-[color:var(--text)]">Metode Pembayaran</label>
                                <select class="select text-xs py-1.5" id="perdana_payment_method">
                                    <option value="cash">Tunai (Cash)</option>
                                    <option value="qris">QRIS</option>
                                    <option value="transfer">Transfer Bank</option>
                                    <option value="debit">Kartu Debit</option>
                                    <option value="utang">Utang (Belum Lunas)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Banner Utang -->
                        <div id="perdanaDebtBanner" class="hidden p-2 rounded-lg bg-amber-50 border border-amber-300 text-amber-900 text-xs">
                            <div class="font-bold flex items-center gap-1 text-[11px]">
                                <span>⚠️ Pembayaran Utang</span>
                            </div>
                            <div class="mt-0.5 text-[11px] text-amber-800">
                                Nama pelanggan wajib diisi dan akan disinkronkan langsung ke data utang & laporan harian.
                            </div>
                        </div>

                        <!-- 5. Nama Pelanggan -->
                        <div>
                            <label class="mb-1 block text-xs font-medium text-[color:var(--text)]" id="perdanaCustomerLabel">Nama Pelanggan / Pembeli</label>
                            <input class="field text-xs py-1.5" type="text" id="perdana_customer_name" placeholder="Umum / Nama Pembeli" autocomplete="off">
                        </div>

                        <!-- 6. Uang Diterima & Kembalian -->
                        <div id="perdanaCashSection" class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-medium text-[color:var(--text)]">Uang Diterima (Rp)</label>
                                <div class="flex items-center gap-1">
                                    <button type="button" class="btn btn-secondary py-0.5 px-2 text-[10px] quick-cash-btn" data-val="exact">Uang Pas</button>
                                    <button type="button" class="btn btn-secondary py-0.5 px-2 text-[10px] quick-cash-btn" data-val="20000">20rb</button>
                                    <button type="button" class="btn btn-secondary py-0.5 px-2 text-[10px] quick-cash-btn" data-val="50000">50rb</button>
                                    <button type="button" class="btn btn-secondary py-0.5 px-2 text-[10px] quick-cash-btn" data-val="100000">100rb</button>
                                </div>
                            </div>
                            <input class="field text-xs py-1.5 font-bold text-slate-800" type="text" inputmode="numeric" data-number-mask id="perdana_cash_paid" value="0" autocomplete="off">
                            
                            <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                                <span class="text-[color:var(--muted)]">Kembalian:</span>
                                <strong id="perdanaChangeText" class="text-emerald-700 font-bold">Rp 0</strong>
                            </div>
                        </div>

                        <!-- 7. Catatan Opsional -->
                        <div>
                            <label class="mb-1 block text-xs font-medium text-[color:var(--muted)]">Catatan <span class="text-[10px] font-normal">(Opsional)</span></label>
                            <input class="field text-xs py-1" type="text" id="perdana_notes" placeholder="Catatan transaksi..." autocomplete="off">
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button type="submit" class="btn btn-primary w-full py-2.5 text-xs font-bold shadow-sm justify-center bg-emerald-600 hover:bg-emerald-700 border-emerald-600 text-white" id="btnSubmitPerdana">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                                <span>Simpan & Proses Penjualan</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- PANEL 2: Form Input / Tambah Produk Kartu Perdana (BARU) -->
                <div id="panelPerdanaAddProduct" class="hidden">
                    <form id="perdanaInlineAddForm" class="list-panel p-4 space-y-3 shadow-sm">
                        <div class="flex items-center justify-between border-b border-[#e2e8f0] pb-2.5">
                            <div>
                                <div class="text-sm font-bold text-slate-800">Form Input Produk Kartu Perdana</div>
                                <div class="text-[11px] text-[color:var(--muted)]">Input master produk kartu perdana baru ke katalog.</div>
                            </div>
                            <button type="button" class="btn btn-secondary py-1 px-2.5 text-xs" id="btnCancelInlinePerdana">Batal</button>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-800">Kode Produk <span class="text-red-500">*</span></label>
                                <div class="flex gap-1">
                                    <input class="field text-xs font-mono py-1.5" type="text" id="inline_perdana_code" placeholder="PRD-TSEL-14GB" required autocomplete="off">
                                    <button type="button" class="btn btn-secondary py-1 px-2 text-[10px] font-bold" id="btnGenInlinePerdanaCode" title="Buat kode acak otomatis">Auto</button>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-800">Operator / Provider <span class="text-red-500">*</span></label>
                                <input class="field text-xs py-1.5" list="inlinePerdanaProviderList" id="inline_perdana_category" placeholder="Pilih operator..." required autocomplete="off">
                                <datalist id="inlinePerdanaProviderList">
                                    <option value="Telkomsel">
                                    <option value="Indosat IM3">
                                    <option value="XL Axiata">
                                    <option value="AXIS">
                                    <option value="Tri (3)">
                                    <option value="Smartfren">
                                    <option value="by.U">
                                    <option value="Perdana Lainnya">
                                </datalist>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-800">Nama Produk Kartu Perdana <span class="text-red-500">*</span></label>
                            <input class="field text-xs py-1.5" type="text" id="inline_perdana_name" placeholder="Contoh: Perdana Telkomsel Kuota 14GB" required autocomplete="off">
                        </div>

                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-800">Varian / Kuota / Masa Aktif</label>
                                <input class="field text-xs py-1.5" type="text" id="inline_perdana_variant" placeholder="Contoh: 14 GB / 30 Hari" autocomplete="off">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-800">Satuan <span class="text-red-500">*</span></label>
                                <input class="field text-xs py-1.5" type="text" id="inline_perdana_unit" value="pcs" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-blue-800">Harga Modal (Harga Beli) <span class="text-red-500">*</span></label>
                                <input class="field text-xs py-1.5 font-bold text-blue-800 bg-blue-50/30 border-blue-300" type="text" inputmode="numeric" data-number-mask id="inline_perdana_cost_price" placeholder="Contoh: 28.000" required autocomplete="off">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-emerald-800">Harga Jual <span class="text-red-500">*</span></label>
                                <input class="field text-xs py-1.5 font-bold text-emerald-800 bg-emerald-50/30 border-emerald-300" type="text" inputmode="numeric" data-number-mask id="inline_perdana_price" placeholder="Contoh: 35.000" required autocomplete="off">
                            </div>
                        </div>

                        <!-- Margin Preview -->
                        <div class="rounded-lg border border-slate-200 bg-slate-50/70 p-2 text-xs flex items-center justify-between">
                            <span class="text-slate-600 font-medium">Margin / Keuntungan:</span>
                            <strong class="text-emerald-700 font-bold" id="inlinePerdanaMarginPreview">Rp 0 (0%)</strong>
                        </div>

                        <!-- Stok Awal Section -->
                        <div id="inlinePerdanaStockSection" class="rounded-lg border border-slate-200 bg-slate-50/60 p-2.5 space-y-2">
                            <div class="text-[11px] font-bold text-[color:var(--muted)] uppercase tracking-wider">Stok Awal Fisik (Opsional)</div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="mb-1 block text-xs font-medium">Stok Awal (pcs)</label>
                                    <input class="field text-xs text-center font-bold" type="text" inputmode="numeric" data-number-mask id="inline_perdana_stock" value="0">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium">Stok Minimum (pcs)</label>
                                    <input class="field text-xs text-center font-bold" type="text" inputmode="numeric" data-number-mask id="inline_perdana_minimum_stock" value="2">
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium">Nomor Seri / ICCID / Barcode Kartu (Opsional)</label>
                                <input class="field text-xs font-mono py-1.5" type="text" id="inline_perdana_serial_number" placeholder="Bisa scan barcode kartu fisik..." autocomplete="off">
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="btn btn-primary w-full py-2.5 text-xs font-bold shadow-sm justify-center bg-emerald-600 hover:bg-emerald-700 border-emerald-600 text-white" id="btnSubmitInlinePerdana">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                                <span>Simpan Produk Kartu Perdana</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        window.runAppPage(function ($, helpers) {
            const { money, showAlert, parseNumber, formatNumber, maskedNumberValue, setMaskedNumberValue, bindNumberMasks } = helpers;

            let perdanaProducts = [];

            const urls = {
                dashboard: @json(route('ajax.perdana.dashboard')),
                storeSale: @json(route('ajax.perdana.sales.store')),
                storeProduct: window.posConfig.storeProductUrl,
                cancelSaleTemplate: @json(url('/ajax/pulsa/sales/__ID__/cancel')),
            };

            // Mode Tab Switcher: Sale vs Add Product
            function switchPerdanaMode(mode) {
                $('.perdana-mode-tab')
                    .removeClass('bg-white text-[color:var(--text)] shadow-xs')
                    .addClass('text-[color:var(--muted)]');

                if (mode === 'add') {
                    $('#tabModePerdanaAddProduct').addClass('bg-white text-[color:var(--text)] shadow-xs').removeClass('text-[color:var(--muted)]');
                    $('#panelPerdanaSale').addClass('hidden');
                    $('#panelPerdanaAddProduct').removeClass('hidden');
                    if (!$('#inline_perdana_code').val()) {
                        generateInlineCode();
                    }
                    $('#inline_perdana_name').focus();
                } else {
                    $('#tabModePerdanaSale').addClass('bg-white text-[color:var(--text)] shadow-xs').removeClass('text-[color:var(--muted)]');
                    $('#panelPerdanaAddProduct').addClass('hidden');
                    $('#panelPerdanaSale').removeClass('hidden');
                }
            }

            $('.perdana-mode-tab').on('click', function () {
                switchPerdanaMode($(this).data('mode'));
            });

            $('#btnOpenAddProduct').on('click', function () {
                switchPerdanaMode('add');
            });

            $('#btnCancelInlinePerdana').on('click', function () {
                switchPerdanaMode('sale');
            });

            // Left Tab Switcher: Catalog vs History
            $('.left-tab-btn').on('click', function () {
                const tab = $(this).data('tab');
                $('.left-tab-btn')
                    .removeClass('active bg-white text-emerald-800 shadow-xs')
                    .addClass('text-slate-500 hover:text-slate-800');
                $(this)
                    .addClass('active bg-white text-emerald-800 shadow-xs')
                    .removeClass('text-slate-500 hover:text-slate-800');

                if (tab === 'history') {
                    $('#tabContentCatalog').addClass('hidden');
                    $('#tabContentHistory').removeClass('hidden');
                } else {
                    $('#tabContentHistory').addClass('hidden');
                    $('#tabContentCatalog').removeClass('hidden');
                }
            });

            // Inline Product Code Generator
            function generateInlineCode() {
                const prefix = 'PRD-';
                const cat = ($('#inline_perdana_category').val() || 'GEN').replace(/[^a-zA-Z0-9]/g, '').substring(0, 4).toUpperCase();
                const rand = Math.floor(1000 + Math.random() * 9000);
                $('#inline_perdana_code').val(`${prefix}${cat}-${rand}`);
            }

            $('#btnGenInlinePerdanaCode').on('click', generateInlineCode);

            $('#inline_perdana_category').on('change', function () {
                if (!$('#inline_perdana_code').val() || $('#inline_perdana_code').val().startsWith('PRD-GEN-')) {
                    generateInlineCode();
                }
            });

            function updateInlineMargin() {
                const cost = maskedNumberValue('#inline_perdana_cost_price') || 0;
                const price = maskedNumberValue('#inline_perdana_price') || 0;
                const margin = price - cost;
                const percent = cost > 0 ? Math.round((margin / cost) * 100) : 0;

                const $preview = $('#inlinePerdanaMarginPreview');
                if (margin > 0) {
                    $preview.html(`<span class="text-emerald-700 font-bold">+${money(margin)} (+${percent}%)</span>`);
                } else if (margin < 0) {
                    $preview.html(`<span class="text-rose-600 font-bold">Rugi ${money(Math.abs(margin))} (${percent}%)</span>`);
                } else {
                    $preview.html(`<span class="text-slate-500 font-bold">Rp 0 (0%)</span>`);
                }
            }

            $('#inline_perdana_cost_price, #inline_perdana_price').on('input', updateInlineMargin);

            // Submit Inline Add Product Form
            $('#perdanaInlineAddForm').on('submit', function (e) {
                e.preventDefault();

                const code = $('#inline_perdana_code').val().trim();
                const name = $('#inline_perdana_name').val().trim();
                const category = $('#inline_perdana_category').val().trim();
                const price = maskedNumberValue('#inline_perdana_price');
                const costPrice = maskedNumberValue('#inline_perdana_cost_price');

                if (!code || !name) {
                    showAlert('Kode produk dan nama kartu perdana wajib diisi.', 'error');
                    return;
                }
                if (!category) {
                    showAlert('Operator / Provider kartu perdana wajib diisi.', 'error');
                    $('#inline_perdana_category').focus();
                    return;
                }
                if (price <= 0) {
                    showAlert('Harga jual harus lebih besar dari Rp 0.', 'error');
                    $('#inline_perdana_price').focus();
                    return;
                }
                if (costPrice <= 0) {
                    showAlert('Harga modal (harga beli) wajib diisi.', 'error');
                    $('#inline_perdana_cost_price').focus();
                    return;
                }

                const formData = new FormData();
                formData.append('code', code);
                formData.append('name', name);
                formData.append('product_type', 'perdana');
                formData.append('category', category);
                formData.append('variant', $('#inline_perdana_variant').val().trim());
                formData.append('unit', $('#inline_perdana_unit').val().trim() || 'pcs');
                formData.append('price', price);
                formData.append('cost_price', costPrice);
                formData.append('stock', maskedNumberValue('#inline_perdana_stock'));
                formData.append('minimum_stock', maskedNumberValue('#inline_perdana_minimum_stock'));

                const sn = $('#inline_perdana_serial_number').val().trim();
                if (sn) {
                    formData.append('serial_number', sn);
                }

                const $btn = $('#btnSubmitInlinePerdana');
                $btn.prop('disabled', true).addClass('opacity-75');

                $.ajax({
                    url: urls.storeProduct,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                }).done((response) => {
                    showAlert(response.message || `Produk kartu perdana "${name}" berhasil didaftarkan!`);
                    $('#perdanaInlineAddForm')[0].reset();
                    setMaskedNumberValue('#inline_perdana_stock', 0);
                    setMaskedNumberValue('#inline_perdana_minimum_stock', 2);
                    setMaskedNumberValue('#inline_perdana_cost_price', 0);
                    setMaskedNumberValue('#inline_perdana_price', 0);
                    updateInlineMargin();
                    switchPerdanaMode('sale');
                    loadDashboard();
                }).fail((xhr) => {
                    const msg = xhr.responseJSON?.message || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('<br>') : 'Gagal menyimpan produk kartu perdana.');
                    showAlert(msg, 'error');
                }).always(() => {
                    $btn.prop('disabled', false).removeClass('opacity-75');
                });
            });

            // Render Catalog Cards
            function renderCatalog(products) {
                perdanaProducts = products || [];
                $('#perdanaCatalogCount').text(perdanaProducts.length);

                const query = ($('#perdanaCatalogSearch').val() || '').toLowerCase();
                const filtered = perdanaProducts.filter((p) => {
                    if (!query) return true;
                    return (p.name || '').toLowerCase().includes(query)
                        || (p.code || '').toLowerCase().includes(query)
                        || (p.category || '').toLowerCase().includes(query)
                        || (p.variant || '').toLowerCase().includes(query);
                });

                const $container = $('#perdanaCatalogContainer');
                if (!filtered.length) {
                    if (!perdanaProducts.length) {
                        $container.html(`
                            <div class="col-span-full text-center py-6 border border-dashed border-slate-200 rounded-xl bg-slate-50/50 p-4 space-y-2">
                                <div class="text-xs text-slate-500 font-medium">Belum ada master produk kartu perdana di katalog.</div>
                                <button type="button" class="btn btn-primary py-1.5 px-3 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs inline-flex items-center gap-1.5" onclick="$('#btnOpenAddProduct').trigger('click')">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                                    <span>+ Input Produk Perdana Sekarang</span>
                                </button>
                            </div>
                        `);
                    } else {
                        $container.html('<div class="col-span-full text-center py-6 text-slate-400 text-xs">Kartu perdana tidak ditemukan sesuai pencarian.</div>');
                    }
                    return;
                }

                $container.html(filtered.map((item) => `
                    <div class="catalog-card p-3 rounded-xl border border-slate-200 bg-white hover:border-emerald-500 hover:shadow-xs transition-all cursor-pointer flex flex-col justify-between" data-id="${item.id}">
                        <div>
                            <div class="flex items-center justify-between gap-1 mb-1">
                                <span class="badge bg-emerald-50 text-emerald-700 font-bold text-[9px] border border-emerald-200">${item.category || item.provider || 'Perdana'}</span>
                                <span class="badge ${item.stock > 0 ? 'bg-slate-100 text-slate-800 font-semibold' : 'badge-danger-soft'} text-[10px]">${item.stock} ${item.unit || 'pcs'}</span>
                            </div>
                            <div class="font-bold text-xs text-slate-900 leading-tight">${item.name}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">${item.code} ${item.variant && item.variant !== '-' ? '· ' + item.variant : ''}</div>
                        </div>
                        <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between">
                            <div class="text-[10px] text-slate-500">Modal: <span class="font-medium text-slate-700">${money(item.cost_price || 0)}</span></div>
                            <strong class="text-xs font-black text-emerald-800">${money(item.price || 0)}</strong>
                        </div>
                    </div>
                `).join(''));
            }

            $('#perdanaCatalogSearch').on('input', function () {
                renderCatalog(perdanaProducts);
            });

            // Selecting a product from Catalog
            $(document).on('click', '.catalog-card', function () {
                const id = Number($(this).data('id'));
                const product = perdanaProducts.find((p) => Number(p.id) === id);
                if (!product) return;

                $('.catalog-card').removeClass('border-emerald-600 ring-2 ring-emerald-500/30 bg-emerald-50/20');
                $(this).addClass('border-emerald-600 ring-2 ring-emerald-500/30 bg-emerald-50/20');

                switchPerdanaMode('sale');
                $('#perdana_operator_name').val(product.name);
                setMaskedNumberValue('#perdana_price', product.price || 0);
                setMaskedNumberValue('#perdana_cost_price', product.cost_price || 0);
                if (product.serial_number) {
                    $('#perdana_serial_number').val(product.serial_number);
                }
                $('#selectedProductBadge').removeClass('hidden').text(`${product.code} (Stok: ${product.stock})`);

                updateProfitCalculation();
                window.setTimeout(() => {
                    if (maskedNumberValue('#perdana_price') > 0) {
                        setMaskedNumberValue('#perdana_cash_paid', maskedNumberValue('#perdana_price'));
                    }
                    $('#perdana_serial_number').focus();
                }, 50);
            });

            $('#btnClearSaleForm').on('click', function () {
                $('#perdanaForm')[0].reset();
                $('#perdana_operator_name').val('');
                setMaskedNumberValue('#perdana_price', 0);
                setMaskedNumberValue('#perdana_cost_price', 0);
                setMaskedNumberValue('#perdana_cash_paid', 0);
                $('#selectedProductBadge').addClass('hidden');
                $('.operator-chip').removeClass('ring-2 ring-emerald-500 font-bold');
                $('.catalog-card').removeClass('border-emerald-600 ring-2 ring-emerald-500/30 bg-emerald-50/20');
                updateProfitCalculation();
            });

            function updateProfitCalculation() {
                const price = maskedNumberValue('#perdana_price') || 0;
                const cost = maskedNumberValue('#perdana_cost_price') || 0;
                const profit = price - cost;
                const percent = price > 0 ? ((profit / price) * 100).toFixed(1) : '0';

                const $box = $('#profitPreviewBox');
                const $amount = $('#profitAmountText');
                const $pct = $('#profitPercentText');

                if (profit >= 0) {
                    $box.removeClass('border-rose-200 bg-rose-50/60').addClass('border-emerald-200 bg-emerald-50/60');
                    $amount.removeClass('text-rose-700').addClass('text-emerald-700').text(money(profit));
                    $pct.removeClass('text-rose-600').addClass('text-emerald-600').text(`(${percent}% margin)`);
                } else {
                    $box.removeClass('border-emerald-200 bg-emerald-50/60').addClass('border-rose-200 bg-rose-50/60');
                    $amount.removeClass('text-emerald-700').addClass('text-rose-700').text(`- ${money(Math.abs(profit))}`);
                    $pct.removeClass('text-emerald-600').addClass('text-rose-600').text(`(Rugi ${percent}%)`);
                }

                updateChangeCalculation();
            }

            function updateChangeCalculation() {
                const method = $('#perdana_payment_method').val();
                if (method === 'utang') {
                    $('#perdanaChangeText').text('Rp 0 (Utang)');
                    return;
                }

                const price = maskedNumberValue('#perdana_price') || 0;
                const cash = maskedNumberValue('#perdana_cash_paid') || 0;
                const change = cash - price;

                const $changeEl = $('#perdanaChangeText');
                if (change < 0) {
                    $changeEl.removeClass('text-emerald-700').addClass('text-rose-600').text(`Kurang ${money(Math.abs(change))}`);
                } else {
                    $changeEl.removeClass('text-rose-600').addClass('text-emerald-700').text(money(change));
                }
            }

            function loadDashboard() {
                $.getJSON(urls.dashboard)
                    .done((res) => {
                        const sum = res.summary || {};
                        $('#todayPerdanaTotal').text(money(sum.today_total || 0));
                        $('#todayPerdanaCount').text(`(${sum.today_count || 0} kartu)`);
                        $('#todayPerdanaProfit').text(money(sum.today_profit || 0));

                        renderCatalog(res.products || []);
                        renderHistory(res.sales || []);
                    })
                    .fail((xhr) => {
                        console.error('Failed to load perdana dashboard', xhr);
                    });
            }

            function renderHistory(sales) {
                const list = sales || [];
                $('#perdanaHistoryCount').text(list.length);
                const $tbody = $('#perdanaHistoryBody');
                if (!list.length) {
                    $tbody.html('<tr><td colspan="9" class="text-center py-6 text-[color:var(--muted)]">Belum ada transaksi kartu perdana.</td></tr>');
                    return;
                }

                $tbody.html(sales.map((item) => {
                    const isCancelled = item.status === 'cancelled';
                    const profit = Number(item.price || 0) - Number(item.cost_amount || 0);
                    const formattedDate = item.sale_date ? new Date(item.sale_date).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '-';

                    return `
                        <tr class="${isCancelled ? 'opacity-60 bg-slate-50' : ''}">
                            <td>
                                <strong class="text-purple-700">${item.reference_number}</strong>
                                <div class="text-[10px] text-[color:var(--muted)]">${formattedDate}</div>
                            </td>
                            <td>
                                <span class="badge ${item.shift === 'pagi' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800'} uppercase font-bold text-[10px]">
                                    ${item.shift || 'pagi'}
                                </span>
                            </td>
                            <td>
                                <div class="font-medium text-slate-900">${item.manual_product_name || 'Kartu Perdana'}</div>
                                ${item.serial_number ? `<div class="font-mono text-[10px] text-[color:var(--muted)]">SN: ${item.serial_number}</div>` : ''}
                            </td>
                            <td class="text-right text-slate-600">${money(item.cost_amount || 0)}</td>
                            <td class="text-right font-bold text-slate-900">${money(item.price || 0)}</td>
                            <td class="text-right font-semibold ${profit >= 0 ? 'text-emerald-700' : 'text-rose-600'}">${money(profit)}</td>
                            <td><span class="uppercase font-semibold text-[10px]">${item.payment_method || 'cash'}</span></td>
                            <td class="text-center">
                                <span class="badge ${isCancelled ? 'badge-danger' : 'badge-success'} uppercase text-[10px]">
                                    ${item.status || 'success'}
                                </span>
                            </td>
                            <td class="text-center">
                                ${!isCancelled ? `
                                    <button type="button" class="btn btn-danger py-0.5 px-2 text-[10px] btn-cancel-sale" data-id="${item.id}" data-ref="${item.reference_number}">
                                        Batalkan
                                    </button>
                                ` : '<span class="text-[10px] text-[color:var(--muted)]">-</span>'}
                            </td>
                        </tr>
                    `;
                }).join(''));
            }

            // Quick Operator chip click
            $(document).on('click', '.operator-chip', function () {
                const op = $(this).data('operator');
                $('.operator-chip').removeClass('ring-2 ring-emerald-500 font-bold');
                $(this).addClass('ring-2 ring-emerald-500 font-bold');

                if (op === 'Perdana Lainnya') {
                    $('#perdana_operator_name').val('').focus();
                } else {
                    $('#perdana_operator_name').val(`Perdana ${op}`).focus();
                }
            });

            // Payment method change: show/hide utang alert and cash section
            $('#perdana_payment_method').on('change', function () {
                const method = $(this).val();
                if (method === 'utang') {
                    $('#perdanaDebtBanner').removeClass('hidden');
                    $('#perdanaCustomerLabel').html('Nama Pelanggan / Penghutang <span class="text-red-500">*</span>');
                    $('#perdanaCashSection').addClass('hidden');
                } else {
                    $('#perdanaDebtBanner').addClass('hidden');
                    $('#perdanaCustomerLabel').text('Nama Pelanggan / Pembeli');
                    $('#perdanaCashSection').removeClass('hidden');
                    if (maskedNumberValue('#perdana_cash_paid') === 0) {
                        setMaskedNumberValue('#perdana_cash_paid', maskedNumberValue('#perdana_price'));
                    }
                }
                updateChangeCalculation();
            });

            // Quick cash buttons
            $(document).on('click', '.quick-cash-btn', function () {
                const val = $(this).data('val');
                if (val === 'exact') {
                    setMaskedNumberValue('#perdana_cash_paid', maskedNumberValue('#perdana_price'));
                } else {
                    setMaskedNumberValue('#perdana_cash_paid', Number(val));
                }
                updateChangeCalculation();
            });

            // Mask and inputs listener
            $('#perdana_price, #perdana_cost_price').on('input', function () {
                updateProfitCalculation();
            });

            $('#perdana_price').on('change', function () {
                const price = maskedNumberValue('#perdana_price');
                if ($('#perdana_payment_method').val() !== 'utang') {
                    const currentCash = maskedNumberValue('#perdana_cash_paid');
                    if (currentCash === 0 || currentCash < price) {
                        setMaskedNumberValue('#perdana_cash_paid', price);
                    }
                }
                updateProfitCalculation();
            });

            $('#perdana_cash_paid').on('input', updateChangeCalculation);

            // Submit Sale Form
            $('#perdanaForm').on('submit', function (e) {
                e.preventDefault();

                const operatorName = $('#perdana_operator_name').val().trim();
                if (!operatorName) {
                    showAlert('Nama operator / kartu perdana wajib diisi.', 'error');
                    $('#perdana_operator_name').focus();
                    return;
                }

                const price = maskedNumberValue('#perdana_price');
                if (price <= 0) {
                    showAlert('Harga jual kartu perdana harus lebih besar dari Rp 0.', 'error');
                    $('#perdana_price').focus();
                    return;
                }

                const costPrice = maskedNumberValue('#perdana_cost_price');
                const paymentMethod = $('#perdana_payment_method').val();
                const customerName = $('#perdana_customer_name').val().trim();

                if (paymentMethod === 'utang' && !customerName) {
                    showAlert('Nama pelanggan wajib diisi untuk transaksi utang.', 'error');
                    $('#perdana_customer_name').focus();
                    return;
                }

                const cashPaid = paymentMethod === 'utang' ? 0 : maskedNumberValue('#perdana_cash_paid');
                if (paymentMethod !== 'utang' && cashPaid < price) {
                    showAlert('Uang yang diterima kurang dari harga jual.', 'error');
                    $('#perdana_cash_paid').focus();
                    return;
                }

                const payload = {
                    operator_name: operatorName,
                    price: price,
                    cost_price: costPrice,
                    serial_number: $('#perdana_serial_number').val().trim() || null,
                    phone_number: $('#perdana_serial_number').val().trim() || null,
                    customer_name: customerName || null,
                    shift: $('#perdana_shift').val() || 'pagi',
                    payment_method: paymentMethod,
                    cash_paid: cashPaid,
                    notes: $('#perdana_notes').val().trim() || null,
                };

                const $btn = $('#btnSubmitPerdana');
                $btn.prop('disabled', true).addClass('opacity-75');

                $.post(urls.storeSale, payload)
                    .done((res) => {
                        showAlert(`${res.message} (Ref: ${res.sale.reference_number})`);
                        $('#perdanaForm')[0].reset();
                        $('#perdana_operator_name').val('');
                        setMaskedNumberValue('#perdana_price', 0);
                        setMaskedNumberValue('#perdana_cost_price', 0);
                        setMaskedNumberValue('#perdana_cash_paid', 0);
                        $('#perdana_payment_method').val('cash').trigger('change');
                        $('.operator-chip').removeClass('ring-2 ring-emerald-500 font-bold');
                        updateProfitCalculation();
                        loadDashboard();
                    })
                    .fail((xhr) => {
                        const msg = xhr.responseJSON?.message || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('<br>') : 'Gagal menyimpan transaksi kartu perdana.');
                        showAlert(msg, 'error');
                    })
                    .always(() => {
                        $btn.prop('disabled', false).removeClass('opacity-75');
                    });
            });

            // Cancel sale
            $(document).on('click', '.btn-cancel-sale', function () {
                const id = $(this).data('id');
                const ref = $(this).data('ref');

                if (!confirm(`Apakah Anda yakin ingin membatalkan transaksi kartu perdana ${ref}?`)) {
                    return;
                }

                const url = urls.cancelSaleTemplate.replace('__ID__', id);
                $.post(url, { notes: 'Dibatalkan oleh kasir di menu perdana' })
                    .done((res) => {
                        showAlert(res.message);
                        loadDashboard();
                    })
                    .fail((xhr) => {
                        const msg = xhr.responseJSON?.message || 'Gagal membatalkan transaksi.';
                        showAlert(msg, 'error');
                    });
            });

            $('#btnRefreshHistory').on('click', function () {
                loadDashboard();
            });

            // Initial load
            loadDashboard();
            updateProfitCalculation();
        });
    </script>
@endsection
