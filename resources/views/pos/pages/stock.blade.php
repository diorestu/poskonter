@extends('layouts.app')

@section('content')
    <section class="pane">
        <!-- Banner Peringatan Kadaluarsa Voucher & Kartu Perdana -->
        <div id="stockExpiringBanner" class="hidden m-4 mb-0 rounded-xl border border-amber-300 bg-amber-50 p-3.5 sm:p-4 text-xs">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-200 text-amber-800">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div>
                        <strong class="font-semibold text-amber-900 block" id="stockExpiringTitle">Peringatan Kadaluarsa Voucher & Kartu Perdana</strong>
                        <span class="text-amber-800" id="stockExpiringDesc">Terdapat unit voucher fisik & kartu perdana yang mendekati masa kadaluarsa (&le; 30 hari) atau sudah kadaluarsa.</span>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary border-amber-300 bg-white text-amber-900 hover:bg-amber-100 py-1.5 px-3 text-xs font-semibold" id="openStockExpiringModal">
                    <span>Lihat Daftar Produk Expired</span>
                </button>
            </div>
        </div>

        <div class="grid gap-4 p-4 xl:grid-cols-[430px_1fr]">
            <div class="space-y-4">
                <form id="stockForm" class="list-panel p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="text-xs font-semibold uppercase tracking-wider text-[color:var(--muted)]">Input Stok Masuk</div>
                    </div>
                    <div class="space-y-3">
                        <div class="search-select">
                            <label class="mb-1 block font-medium">Produk</label>
                            <input class="field" type="text" id="stock_product_search" placeholder="Cari nama atau kode produk" autocomplete="off">
                            <input type="hidden" id="stock_product_id">
                            <div id="stockProductList" class="search-select-list hidden"></div>
                        </div>

                        <div>
                            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                <label class="block font-medium">Detail Item Masuk</label>
                                <div class="flex items-center gap-1.5">
                                    <button class="btn btn-primary py-1 px-2.5 min-h-[30px] text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white border-emerald-600 shadow-xs" type="button" id="openScanStockModal" title="Scan barcode fisik beruntun dengan scanner">
                                        <span class="btn-icon">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/>
                                                <line x1="7" y1="12" x2="17" y2="12"/>
                                            </svg>
                                        </span>
                                        <span>Scan Barcode</span>
                                    </button>
                                    <button class="btn btn-secondary py-1 px-2 min-h-[30px] text-xs" type="button" id="openRangeModal" title="Generate banyak serial otomatis">
                                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/><circle cx="8" cy="6" r="1.5"/><circle cx="16" cy="12" r="1.5"/><circle cx="8" cy="18" r="1.5"/></svg></span>
                                        <span>+ Range SN</span>
                                    </button>
                                    <button class="btn btn-secondary py-1 px-2 min-h-[30px] text-xs" type="button" id="addStockRow">
                                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg></span>
                                        <span>+ Baris</span>
                                    </button>
                                </div>
                            </div>
                            <div id="stockItemRows" class="space-y-2"></div>
                        </div>

                        <div>
                            <label class="mb-1 block font-medium">Catatan</label>
                            <input class="field" type="text" id="stock_note" placeholder="Contoh: Barang masuk dari supplier">
                        </div>

                        <button class="btn btn-primary w-full" type="submit">
                            <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/><path d="M4 4h16v16H4z"/></svg></span>
                            <span>Simpan Stok Masuk</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="list-panel p-4">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <div class="text-xs font-semibold uppercase tracking-wider text-[color:var(--muted)]">Daftar Stok Produk Fisik</div>
                    <input id="stockSearch" class="field max-w-xs text-xs" type="text" placeholder="Cari nama, kode, serial...">
                </div>
                <div class="table-wrap">
                    <table id="stockMainTable" class="table-grid min-w-full text-xs">
                        <thead class="text-left">
                            <tr>
                                <th data-sort="code">Kode</th>
                                <th data-sort="name">Produk</th>
                                <th data-sort="cost_price">Modal Terakhir</th>
                                <th data-sort="price">Harga Jual</th>
                                <th data-sort="stock">Stok</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="stockTable"></tbody>
                    </table>
                </div>
                <div id="stockPagination"></div>
            </div>
        </div>
    </section>

    <!-- Modal Scan Barcode Tambah Stok -->
    <div id="scanStockModal" class="modal-backdrop hidden">
        <div class="modal-card max-w-2xl">
            <div class="modal-head flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 text-white font-bold text-sm shadow-xs">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/>
                            <line x1="7" y1="12" x2="17" y2="12"/>
                        </svg>
                    </span>
                    <h2 class="font-bold text-base text-[color:var(--text)]">Scan Barcode Tambah Stok</h2>
                </div>
                <button type="button" class="btn btn-secondary close-scan-modal">
                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg></span>
                    <span>Tutup</span>
                </button>
            </div>

            <div class="modal-body space-y-4 p-4">
                <!-- 1. Pemilihan Produk & Pengaturan Batch -->
                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5 space-y-3">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="search-select relative">
                            <label class="mb-1 block font-semibold text-xs text-slate-800">Pilih Produk <span class="text-red-500">*</span></label>
                            <input class="field text-xs font-medium" type="text" id="scan_product_search" placeholder="Cari nama atau kode produk..." autocomplete="off">
                            <input type="hidden" id="scan_product_id">
                            <div id="scanProductDropdown" class="search-select-list hidden max-h-48 overflow-y-auto"></div>
                        </div>

                        <div>
                            <label class="mb-1 block font-semibold text-xs text-slate-800">Mode Scanner</label>
                            <div class="grid grid-cols-2 gap-1.5 p-1 bg-white rounded-lg border border-slate-200 text-xs">
                                <button type="button" class="scan-mode-btn active py-1 px-2 rounded-md font-semibold text-center transition-all bg-emerald-50 text-emerald-700 border border-emerald-300" data-mode="sn">
                                    Voucher / Serial Unik
                                </button>
                                <button type="button" class="scan-mode-btn py-1 px-2 rounded-md font-semibold text-center transition-all text-slate-600 hover:text-slate-900 border border-transparent" data-mode="qty">
                                    SKU / Tambah Qty
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Info Produk Terpilih & Detail Batch -->
                    <div id="scanProductSelectedInfo" class="hidden flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-200/80 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="badge badge-info font-bold" id="scanProductBadge">KODE - NAMA</span>
                            <span class="text-slate-600">Stok Saat Ini: <strong id="scanCurrentStockText" class="text-slate-900">0 pcs</strong></span>
                        </div>
                        <div class="text-slate-500 text-[11px]">Harga Jual: <strong id="scanProductPriceText" class="text-emerald-700">Rp 0</strong></div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 pt-1">
                        <div>
                            <label class="mb-1 block font-semibold text-xs text-slate-800">Harga Modal / pcs <span class="text-red-500">*</span></label>
                            <input class="field text-xs font-semibold" type="text" inputmode="numeric" data-number-mask id="scan_batch_cost_price" placeholder="Contoh: 15.000">
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold text-xs text-slate-800">Tanggal Kadaluarsa (Opsional)</label>
                            <input class="field text-xs" type="date" id="scan_batch_expiry_date">
                        </div>
                    </div>
                </div>

                <!-- 2. Interactive Scanner Input Area -->
                <div class="rounded-xl border-2 border-dashed border-emerald-400 bg-emerald-50/40 p-4 space-y-3 transition-all" id="scanInputZone">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                            </span>
                            <span class="text-xs font-bold text-emerald-950">Scanner Siap</span>
                        </div>
                        <button type="button" class="inline-flex items-center justify-center h-7 w-7 rounded-lg border border-emerald-300 bg-white text-emerald-700 hover:bg-emerald-50 transition-colors shadow-2xs" id="toggleScanSound" title="Suara Beep: ON" aria-label="Toggle Suara Beep">
                            <span id="soundIconWrapper">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
                            </span>
                            <span id="soundLabel" class="sr-only">Suara Beep: ON</span>
                        </button>
                    </div>

                    <div class="relative">
                        <input class="field text-sm font-mono tracking-wider py-2.5 px-3.5 border-emerald-500 focus:border-emerald-600 focus:ring-emerald-200" type="text" id="scan_barcode_input" placeholder="Scan barcode / ketik serial number..." autocomplete="off">
                    </div>

                    <div id="scanStatusFeedback" class="text-xs hidden rounded-lg p-2 font-medium"></div>
                </div>

                <!-- 3. Real-Time Scanned Items Table -->
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="text-xs font-bold text-slate-800 flex items-center gap-2">
                            <span>Item Discan</span>
                            <span class="badge bg-emerald-100 text-emerald-800 font-black text-xs px-2 py-0.5" id="scanCountBadge">0 Unit</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="text-slate-600">Total Modal: <strong id="scanTotalCostText" class="text-slate-900 font-bold">Rp 0</strong></span>
                            <button type="button" class="inline-flex items-center gap-1 text-[11px] font-medium text-rose-600 hover:text-rose-800 p-1 rounded hover:bg-rose-50" id="btnClearScanList" title="Kosongkan daftar scan">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                <span>Reset</span>
                            </button>
                        </div>
                    </div>

                    <div class="max-h-52 overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-inner">
                        <table class="table-grid min-w-full text-xs">
                            <thead class="bg-slate-50 sticky top-0 text-left">
                                <tr>
                                    <th class="w-10 text-center">#</th>
                                    <th>Barcode / Serial Number</th>
                                    <th class="w-20 text-center">Qty</th>
                                    <th class="w-24 text-right">Modal / pcs</th>
                                    <th class="w-14 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="scanTableBody">
                                <tr id="scanEmptyRow">
                                    <td colspan="5" class="text-center py-6 text-slate-400">
                                        Belum ada item discan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 4. Catatan & Tombol Simpan -->
                <div class="pt-2 border-t border-slate-200 space-y-3">
                    <div>
                        <label class="mb-1 block font-medium text-xs">Catatan Stok Masuk (Opsional)</label>
                        <input class="field text-xs" type="text" id="scan_stock_note" placeholder="Contoh: Stok masuk dari supplier / faktur #1234">
                    </div>

                    <div class="flex gap-2.5">
                        <button type="button" class="btn btn-secondary flex-1 py-2 close-scan-modal">Batal</button>
                        <button type="button" class="btn btn-primary flex-[2] py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-xs border-emerald-600" id="btnSubmitScannedStock">
                            <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg></span>
                            <span id="btnSubmitScannedText">Simpan Stok (0 Unit)</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Generate Serial Number Range -->
    <div id="serialRangeModal" class="modal-backdrop hidden">
        <div class="modal-card max-w-lg">
            <div class="modal-head flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-base">Generate Serial Number Range</h2>
                    <p class="mt-1 text-xs text-[color:var(--muted)]">Input stok banyak sekaligus dengan nomor seri berurutan otomatis (misal: 20 pcs).</p>
                </div>
                <button type="button" class="btn btn-secondary" id="closeRangeModal">
                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg></span>
                    <span>Tutup</span>
                </button>
            </div>
            <div class="modal-body space-y-3.5 p-4">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block font-medium text-xs">Nomor Seri Awal (Start SN) <span class="text-red-500">*</span></label>
                        <input class="field text-xs font-mono" type="text" id="range_start_sn" placeholder="Contoh: KBL-001 / SN10001" autocomplete="off" required>
                        <div class="mt-1 text-[10px] text-[color:var(--muted)]">Format akhiran angka (misal: 001, 101).</div>
                    </div>
                    <div>
                        <label class="mb-1 block font-medium text-xs">Jumlah Unit (Qty Pcs) <span class="text-red-500">*</span></label>
                        <input class="field text-xs font-bold text-center" type="number" min="1" max="500" id="range_count" value="20" required>
                        <div class="mt-1 text-[10px] text-[color:var(--muted)]">Jumlah baris serial yang dibuat.</div>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block font-medium text-xs">Harga Modal (per pcs) <span class="text-red-500">*</span></label>
                        <input class="field text-xs" type="text" inputmode="numeric" data-number-mask id="range_cost_price" placeholder="Wajib diisi">
                    </div>
                    <div>
                        <label class="mb-1 block font-medium text-xs">Expiry Date (Opsional)</label>
                        <input class="field text-xs" type="date" id="range_expiry_date">
                    </div>
                </div>

                <div class="rounded-lg border border-[#e2e8f0] bg-[#f8fafc] p-3 text-xs">
                    <div class="flex items-center justify-between text-[#475569] font-medium">
                        <span>Pratinjau Rentang SN:</span>
                        <span id="rangeCountText" class="text-xs font-bold text-[#2563eb]">20 Unit</span>
                    </div>
                    <div id="rangePreviewSample" class="mt-1.5 font-mono text-[11px] text-[#1e293b] break-all bg-white p-2 rounded border border-[#cbd5e1]">
                        Ketik Nomor Seri Awal di atas...
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" class="btn btn-primary flex-1 py-2 font-medium" id="btnApplyRange">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                        <span>Generate & Masukkan ke Form Stok</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adjust Stok -->
    <div id="adjustStockModal" class="modal-backdrop hidden">
        <div class="modal-card max-w-md">
            <div class="modal-head flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-sm">Edit / Adjust Stok Produk</h3>
                    <p class="text-[11px] text-[color:var(--muted)]" id="adjust_product_title">Pilih produk</p>
                </div>
                <button type="button" class="btn-icon text-[color:var(--muted)] hover:text-[color:var(--text)] close-adjust-modal">
                    <svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg>
                </button>
            </div>
            <form id="adjustStockForm" class="modal-body space-y-3.5 p-4">
                <input type="hidden" id="adjust_product_id">

                <div class="rounded-lg border border-[#e2e8f0] bg-[#f8fafc] p-2.5 space-y-1 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-[color:var(--muted)]">Stok Saat Ini:</span>
                        <strong id="adjust_current_stock_text" class="font-bold font-mono text-blue-600">0 pcs</strong>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-[#e2e8f0]">
                        <span class="text-[color:var(--muted)]">Stok Setelah Penyesuaian:</span>
                        <strong id="adjust_preview_stock_text" class="font-bold font-mono text-emerald-700">0 pcs</strong>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block font-medium text-xs">Jenis Penyesuaian</label>
                    <div class="grid grid-cols-3 gap-1.5">
                        <button type="button" class="btn btn-secondary py-1.5 px-2 text-xs font-semibold adjust-type-btn border-2 border-emerald-600 bg-emerald-50 text-emerald-700" data-type="add">
                            + Tambah
                        </button>
                        <button type="button" class="btn btn-secondary py-1.5 px-2 text-xs font-semibold adjust-type-btn border border-[#cbd5e1] text-[#64748b]" data-type="reduce">
                            - Kurang
                        </button>
                        <button type="button" class="btn btn-secondary py-1.5 px-2 text-xs font-semibold adjust-type-btn border border-[#cbd5e1] text-[#64748b]" data-type="set">
                            = Set Baru
                        </button>
                    </div>
                    <input type="hidden" id="adjust_type" value="add">
                </div>

                <div>
                    <label class="mb-1 block font-medium text-xs" id="adjust_quantity_label">Jumlah Penambahan (Pcs)</label>
                    <input class="field text-xs font-bold text-center" type="number" min="1" id="adjust_quantity" value="1" required>
                </div>

                <div>
                    <label class="mb-1 block font-medium text-xs">Harga Modal Baru (Opsional)</label>
                    <input class="field text-xs" type="text" inputmode="numeric" data-number-mask id="adjust_cost_price" placeholder="Biarkan kosong jika tidak berubah">
                </div>

                <div>
                    <label class="mb-1 block font-medium text-xs">Catatan / Alasan</label>
                    <input class="field text-xs" type="text" id="adjust_note" placeholder="Contoh: Stok opname / Barang rusak / Retur" required>
                </div>

                <div class="mt-4 flex gap-2 pt-2 border-t border-[#f1f5f9]">
                    <button class="btn btn-primary flex-1 py-2" type="submit" id="btnSubmitAdjust">
                        <span id="btnSubmitAdjustText">Simpan Penyesuaian Stok</span>
                    </button>
                    <button class="btn btn-secondary close-adjust-modal" type="button">
                        <span>Batal</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Kelola / Edit Serial Number -->
    <div id="manageSerialsModal" class="modal-backdrop hidden">
        <div class="modal-card max-w-2xl">
            <div class="modal-head flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-sm">Kelola & Edit Serial Number</h3>
                    <p class="text-[11px] text-[color:var(--muted)]" id="manage_serials_product_title">Pilih produk</p>
                </div>
                <button type="button" class="btn-icon text-[color:var(--muted)] hover:text-[color:var(--text)] close-serials-modal">
                    <svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg>
                </button>
            </div>
            <div class="modal-body space-y-3.5 p-4">
                <!-- Inline Edit Form (Hidden by default, shown when editing an SN) -->
                <form id="editSerialForm" class="hidden rounded-xl border border-blue-200 bg-blue-50/60 p-3 space-y-2.5">
                    <input type="hidden" id="edit_serial_product_id">
                    <input type="hidden" id="edit_serial_movement_id">
                    <div class="flex items-center justify-between">
                        <strong class="text-xs font-semibold text-blue-900">Form Edit Serial Number</strong>
                        <button type="button" class="btn btn-secondary py-0.5 px-2 text-[10px]" id="btnCancelEditSerial">Batal</button>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block font-medium text-[11px]">Nomor Seri / IMEI</label>
                            <input class="field text-xs font-mono font-semibold" type="text" id="edit_serial_number" required>
                        </div>
                        <div>
                            <label class="mb-1 block font-medium text-[11px]">Tanggal Expiry</label>
                            <input class="field text-xs" type="date" id="edit_serial_expiry">
                        </div>
                        <div>
                            <label class="mb-1 block font-medium text-[11px]">Harga Modal (Rp)</label>
                            <input class="field text-xs" type="text" inputmode="numeric" data-number-mask id="edit_serial_cost">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="submit" class="btn btn-primary text-xs py-1 px-3">
                            <span>Simpan Perubahan SN</span>
                        </button>
                    </div>
                </form>

                <div class="table-wrap max-h-[340px] overflow-y-auto">
                    <table class="table-grid min-w-full text-xs">
                        <thead class="text-left sticky top-0 bg-[#f8fafc] z-10">
                            <tr>
                                <th>Serial Number</th>
                                <th>Qty</th>
                                <th>Expiry Date</th>
                                <th>Harga Modal</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="productSerialsTable">
                            <tr>
                                <td colspan="6" class="text-center text-[color:var(--muted)] py-6">Memuat data serial...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Daftar Produk Expired / Akan Expired -->
    <div id="stockExpiringModal" class="modal-backdrop hidden">
        <div class="modal-card max-w-3xl">
            <div class="modal-head flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-sm text-amber-900 flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Daftar Voucher & Kartu Perdana Kadaluarsa (&le; 30 Hari)
                    </h3>
                    <p class="text-[11px] text-[color:var(--muted)]">Daftar item voucher fisik & kartu perdana yang memerlukan penanganan khusus sebelum atau sesudah tanggal kadaluarsa.</p>
                </div>
                <button type="button" class="btn-icon text-[color:var(--muted)] hover:text-[color:var(--text)] close-expiring-modal">
                    <svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg>
                </button>
            </div>
            <div class="modal-body p-4 space-y-3">
                <div class="table-wrap max-h-[380px] overflow-y-auto">
                    <table class="table-grid min-w-full text-xs">
                        <thead class="text-left sticky top-0 bg-[#f8fafc] z-10">
                            <tr>
                                <th>Produk</th>
                                <th>Tipe</th>
                                <th>Serial Number</th>
                                <th>Sisa Qty</th>
                                <th>Tgl Kadaluarsa</th>
                                <th>Status / Sisa Hari</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="expiringTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        window.runAppPage(function ($, helpers) {
            const state = { products: [] };
            const { money, showAlert, stockUrl, bindNumberMasks, parseNumber, formatNumber, maskedNumberValue } = helpers;

            const stockTable = helpers.createTableController({
                perPage: 10,
                maxPageButtons: 5,
                defaultSortKey: 'name',
                search: (product, query) => [product.name, product.code, product.serial_number || ''].some((value) => String(value || '').toLowerCase().includes(query)),
                sorters: {
                    code: (product) => product.code,
                    name: (product) => product.name,
                    cost_price: (product) => Number(product.cost_price),
                    price: (product) => Number(product.price),
                    stock: (product) => Number(product.stock),
                },
            });

            function regularProducts() {
                return state.products.filter((product) => product.product_type !== 'pulsa');
            }

            function stockRowTemplate(index, values = {}) {
                return `
                    <div class="rounded-xl border border-[#d8e1ec] bg-white p-3 stock-item-row" data-index="${index}">
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <div class="text-xs font-semibold uppercase tracking-[0.12em] text-[color:var(--muted)]">Item / Baris ${index + 1}</div>
                            <button class="btn btn-secondary remove-stock-row py-1 px-2 text-xs" type="button">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg></span>
                                <span>Hapus</span>
                            </button>
                        </div>
                        <div class="grid gap-2.5">
                            <div class="grid gap-2.5 grid-cols-1 sm:grid-cols-[1fr_110px]">
                                <div>
                                    <label class="mb-1 block font-medium">Serial Number / IMEI</label>
                                    <input class="field stock-serial-number font-mono text-xs" type="text" value="${values.serial_number || ''}" placeholder="Opsional (contoh: SN12345)">
                                </div>
                                <div>
                                    <label class="mb-1 block font-medium">Jumlah (Pcs)</label>
                                    <input class="field stock-quantity text-center font-bold text-xs" type="number" min="1" step="1" value="${values.quantity || 1}" required>
                                </div>
                            </div>
                            <div class="grid gap-2.5 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block font-medium">Expiry Date</label>
                                    <input class="field stock-expiry-date text-xs" type="date" value="${values.expiry_date || ''}">
                                </div>
                                <div>
                                    <label class="mb-1 block font-medium">Harga Modal (per pcs)</label>
                                    <input class="field stock-cost-price text-xs" type="text" inputmode="numeric" data-number-mask value="${values.cost_price || ''}" placeholder="Wajib diisi">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            function reindexRows() {
                $('#stockItemRows .stock-item-row').each(function (index) {
                    $(this).attr('data-index', index);
                    $(this).find('.text-xs').first().text(`Item / Baris ${index + 1}`);
                });
            }

            function ensureAtLeastOneRow() {
                if (!$('#stockItemRows .stock-item-row').length) {
                    $('#stockItemRows').append(stockRowTemplate(0));
                    bindNumberMasks('#stockItemRows .stock-item-row:last');
                }
            }

            function addStockRow(values = {}) {
                const index = $('#stockItemRows .stock-item-row').length;
                $('#stockItemRows').append(stockRowTemplate(index, values));
                bindNumberMasks('#stockItemRows .stock-item-row:last');
                reindexRows();
            }

            function selectedStockItems() {
                return $('#stockItemRows .stock-item-row').map(function () {
                    const $row = $(this);
                    const qty = parseInt($row.find('.stock-quantity').val(), 10);
                    return {
                        serial_number: $row.find('.stock-serial-number').val().trim(),
                        quantity: isNaN(qty) || qty < 1 ? 1 : qty,
                        expiry_date: $row.find('.stock-expiry-date').val(),
                        cost_price: maskedNumberValue($row.find('.stock-cost-price')),
                    };
                }).get();
            }

            function resetForm() {
                $('#stockForm')[0].reset();
                $('#stock_product_id').val('');
                $('#stockProductList').addClass('hidden');
                $('#stockItemRows').empty();
                addStockRow();
            }

            function drawTable(meta) {
                $('#stockTable').html(meta.rows.length ? meta.rows.map((product) => `
                    <tr>
                        <td class="font-semibold">${product.code}</td>
                        <td>
                            <div class="font-medium">${product.name}</div>
                            <div class="text-[11px] text-[color:var(--muted)]">${product.variant || '-'} · ${product.unit}</div>
                        </td>
                        <td>${Number(product.cost_price || 0) > 0 ? money(product.cost_price) : '<span class="text-[11px] text-[color:var(--muted)]">Belum ada</span>'}</td>
                        <td>${money(product.price)}</td>
                        <td><span class="badge ${Number(product.stock) <= Number(product.minimum_stock) ? 'badge-warning' : 'badge-ok'}">${product.stock} ${product.unit}</span></td>
                        <td class="text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-1">
                                <button class="btn btn-secondary py-1 px-2 text-xs btn-open-scan-stock text-emerald-700 hover:text-emerald-800" type="button" data-id="${product.id}" title="Scan Barcode Masuk">
                                    <span class="btn-icon">
                                        <svg viewBox="0 0 24 24" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/>
                                            <line x1="7" y1="12" x2="17" y2="12"/>
                                        </svg>
                                    </span>
                                    <span>Scan</span>
                                </button>
                                <button class="btn btn-secondary py-1 px-2 text-xs btn-open-adjust" type="button" data-id="${product.id}" title="Edit / Adjust Stok">
                                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg></span>
                                    <span>Adjust</span>
                                </button>
                                <button class="btn btn-secondary py-1 px-2 text-xs btn-open-serials" type="button" data-id="${product.id}" title="Kelola & Edit Serial Number">
                                    <span class="btn-icon"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="7" y1="8" x2="7" y2="8.01"/><line x1="7" y1="12" x2="17" y2="12"/><line x1="7" y1="16" x2="13" y2="16"/></svg></span>
                                    <span>SN</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('') : '<tr><td colspan="6" class="text-center text-[color:var(--muted)]">Tidak ada data.</td></tr>');
                stockTable.syncSortUi('#stockMainTable thead');
                stockTable.renderFooter('#stockPagination', meta);
            }

            function renderExpiringAlert(expiringProducts) {
                state.expiringProducts = expiringProducts || [];
                if (state.expiringProducts.length > 0) {
                    const expiredCount = state.expiringProducts.filter((p) => p.is_expired).length;
                    const soonCount = state.expiringProducts.length - expiredCount;

                    let desc = `Terdapat ${state.expiringProducts.length} voucher & kartu perdana yang kadaluarsa atau mendekati kadaluarsa (&le; 30 hari).`;
                    if (expiredCount > 0 && soonCount > 0) {
                        desc = `${expiredCount} voucher/perdana SUDAH KADALUARSA, dan ${soonCount} item akan kadaluarsa dalam 30 hari ke depan.`;
                    } else if (expiredCount > 0) {
                        desc = `${expiredCount} voucher/perdana SUDAH KADALUARSA! Harap segera periksa atau sesuaikan stok.`;
                    }

                    $('#stockExpiringTitle').html(`⚠️ Peringatan Kadaluarsa Voucher & Kartu Perdana (${state.expiringProducts.length} Item)`);
                    $('#stockExpiringDesc').html(desc);
                    $('#stockExpiringBanner').removeClass('hidden');
                } else {
                    $('#stockExpiringBanner').addClass('hidden');
                }
            }

            function renderExpiringModalTable() {
                const list = state.expiringProducts || [];
                $('#expiringTableBody').html(list.length ? list.map((item) => `
                    <tr>
                        <td>
                            <div class="font-medium">${item.product_name}</div>
                            <div class="text-[11px] text-[color:var(--muted)]">${item.product_code} · ${item.variant || '-'}</div>
                        </td>
                        <td>
                            <span class="badge ${item.item_type === 'Kartu Perdana' ? 'badge-info' : 'badge-primary'} font-semibold text-[11px]">
                                ${item.item_type || 'Voucher'}
                            </span>
                        </td>
                        <td class="font-mono text-xs font-semibold">${item.serial_number || '<span class="text-[color:var(--muted)] font-normal">Non-Serial</span>'}</td>
                        <td class="font-bold text-center">${item.quantity}</td>
                        <td class="font-mono">${item.expiry_date || '-'}</td>
                        <td>
                            <span class="badge ${item.is_expired ? 'border border-red-300 bg-red-100 text-red-700' : 'border border-amber-300 bg-amber-100 text-amber-800'}">
                                ${item.status_text}
                            </span>
                        </td>
                        <td class="text-right whitespace-nowrap">
                            <button type="button" class="btn btn-secondary py-1 px-2 text-xs btn-open-adjust" data-id="${item.product_id}">
                                Adjust
                            </button>
                        </td>
                    </tr>
                `).join('') : '<tr><td colspan="7" class="text-center py-6 text-[color:var(--muted)]">Tidak ada voucher & kartu perdana yang kadaluarsa.</td></tr>');
            }

            function renderSearchList() {
                const query = ($('#stock_product_search').val() || '').toLowerCase();
                const filtered = regularProducts()
                    .filter((product) => !query || product.name.toLowerCase().includes(query) || product.code.toLowerCase().includes(query))
                    .slice(0, 12);

                $('#stockProductList').html(filtered.length ? filtered.map((product) => `
                    <button type="button" class="search-select-item stock-product-option" data-id="${product.id}">
                        <div class="font-semibold">${product.name}</div>
                        <div class="text-[11px] text-[color:var(--muted)]">${product.code} · stok ${product.stock} ${product.unit}</div>
                    </button>
                `).join('') : '<div class="px-3 py-3 text-sm text-[color:var(--muted)]">Produk tidak ditemukan.</div>').removeClass('hidden');
            }

            function renderProducts(products) {
                state.products = products;
                const rows = regularProducts();
                stockTable.setData(rows);
                stockTable.setQuery($('#stockSearch').val());
                drawTable(stockTable.pagedRows());
                renderSearchList();
            }

            function loadData() {
                $.getJSON(window.posConfig.dashboardUrl, { _: Date.now() })
                    .done((response) => {
                        try {
                            renderProducts(response.products || []);
                            renderExpiringAlert(response.expiring_products || []);
                        } catch (err) {
                            console.error('Error rendering stock data:', err);
                        }
                    })
                    .fail((xhr, status, error) => {
                        console.error('AJAX Error Stock Dashboard:', status, error, xhr);
                        showAlert('Gagal memuat data stok.', 'error');
                    });
            }

            function parseSerialRange(startSn, count) {
                if (!startSn || count <= 0) return [];

                const match = startSn.match(/^(.*?)(\d+)$/);
                if (!match) {
                    const res = [];
                    for (let i = 1; i <= count; i++) {
                        res.push(`${startSn}-${i}`);
                    }
                    return res;
                }

                const prefix = match[1];
                const numStr = match[2];
                const startNum = parseInt(numStr, 10);
                const padLen = numStr.length;

                const list = [];
                for (let i = 0; i < count; i++) {
                    const curNum = startNum + i;
                    const curNumPadded = String(curNum).padStart(padLen, '0');
                    list.push(`${prefix}${curNumPadded}`);
                }
                return list;
            }

            function updateRangePreview() {
                const startSn = ($('#range_start_sn').val() || '').trim();
                const count = Math.max(1, Math.min(500, parseInt($('#range_count').val(), 10) || 1));
                $('#rangeCountText').text(`${count} Unit`);

                if (!startSn) {
                    $('#rangePreviewSample').text('Ketik Nomor Seri Awal di atas...');
                    return;
                }

                const serials = parseSerialRange(startSn, count);
                if (serials.length <= 4) {
                    $('#rangePreviewSample').text(serials.join(', '));
                } else {
                    $('#rangePreviewSample').text(`${serials[0]}, ${serials[1]}, ${serials[2]}, ... , ${serials[serials.length - 1]}`);
                }
            }

            $('#openRangeModal').on('click', function () {
                $('#serialRangeModal').removeClass('hidden');
                $('#range_start_sn').focus();
                updateRangePreview();
            });

            $('#closeRangeModal').on('click', function () {
                $('#serialRangeModal').addClass('hidden');
            });

            $('#range_start_sn, #range_count').on('input', function () {
                updateRangePreview();
            });

            $('#btnApplyRange').on('click', function () {
                const startSn = ($('#range_start_sn').val() || '').trim();
                const count = Math.max(1, Math.min(500, parseInt($('#range_count').val(), 10) || 1));
                const costPrice = maskedNumberValue('#range_cost_price');
                const expiryDate = $('#range_expiry_date').val();

                if (!startSn) {
                    showAlert('Nomor seri awal wajib diisi.', 'error');
                    $('#range_start_sn').focus();
                    return;
                }

                if (costPrice <= 0) {
                    showAlert('Harga modal per pcs wajib diisi.', 'error');
                    $('#range_cost_price').focus();
                    return;
                }

                const serials = parseSerialRange(startSn, count);

                const currentRows = $('#stockItemRows .stock-item-row');
                if (currentRows.length === 1) {
                    const firstSn = currentRows.first().find('.stock-serial-number').val().trim();
                    const firstCost = maskedNumberValue(currentRows.first().find('.stock-cost-price'));
                    if (!firstSn && firstCost <= 0) {
                        $('#stockItemRows').empty();
                    }
                }

                serials.forEach((sn) => {
                    addStockRow({
                        serial_number: sn,
                        quantity: 1,
                        cost_price: costPrice,
                        expiry_date: expiryDate,
                    });
                });

                $('#serialRangeModal').addClass('hidden');
                showAlert(`${serials.length} baris serial number (${serials[0]} s/d ${serials[serials.length - 1]}) berhasil ditambahkan ke form!`);
            });

            $('#stockSearch').on('input', function () {
                renderProducts(state.products);
            });

            $('#stock_product_search').on('focus input', function () {
                renderSearchList();
            });

            $('#addStockRow').on('click', function () {
                addStockRow();
            });

            $(document).on('click', '.remove-stock-row', function () {
                $(this).closest('.stock-item-row').remove();
                ensureAtLeastOneRow();
                reindexRows();
            });

            $(document).on('click', '.stock-product-option', function () {
                const product = state.products.find((item) => item.id === Number($(this).data('id')));
                if (!product) return;
                $('#stock_product_id').val(product.id);
                $('#stock_product_search').val(`${product.code} - ${product.name}`);
                $('#stockProductList').addClass('hidden');
            });

            $(document).on('click', function (event) {
                if (!$(event.target).closest('.search-select').length) {
                    $('#stockProductList').addClass('hidden');
                }
            });

            $(document).on('click', '#stockMainTable thead [data-sort]', function () {
                stockTable.toggleSort($(this).data('sort'));
                renderProducts(state.products);
            });

            $(document).on('click', '#stockPagination .page-btn', function () {
                if ($(this).prop('disabled') || $(this).attr('disabled')) return;
                const page = Number($(this).data('page'));
                if (!page) return;
                stockTable.setPage(page);
                drawTable(stockTable.pagedRows());
            });

            $('#stockForm').on('submit', function (event) {
                event.preventDefault();

                const productId = $('#stock_product_id').val();
                if (!productId) {
                    showAlert('Pilih produk terlebih dahulu.', 'error');
                    return;
                }

                const items = selectedStockItems();
                if (!items.length) {
                    showAlert('Tambahkan minimal satu item stok.', 'error');
                    return;
                }

                if (items.some((item) => Number(item.cost_price || 0) <= 0)) {
                    showAlert('Harga modal setiap item wajib diisi.', 'error');
                    return;
                }

                $.post(stockUrl(productId), {
                    items,
                    note: $('#stock_note').val(),
                }).done((response) => {
                    showAlert(response.message);
                    resetForm();
                    loadData();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal menambah stok.', 'error');
                });
            });

            // --- Adjust Stock Modal Handlers ---
            $(document).on('click', '.btn-open-adjust', function () {
                const productId = Number($(this).data('id'));
                const product = state.products.find((p) => Number(p.id) === productId);
                if (!product) return;

                $('#adjust_product_id').val(product.id);
                $('#adjust_product_title').text(`${product.code} - ${product.name} (${product.variant || '-'})`);
                $('#adjust_current_stock_text').text(`${product.stock} ${product.unit}`);
                $('#adjust_quantity').val(1);
                $('#adjust_cost_price').val('');
                $('#adjust_note').val('');
                $('.adjust-type-btn[data-type="add"]').trigger('click');
                updateAdjustStockPreview();

                $('#adjustStockModal').removeClass('hidden');
                window.setTimeout(() => $('#adjust_quantity').focus().select(), 50);
            });

            $(document).on('click', '.close-adjust-modal', function () {
                $('#adjustStockModal').addClass('hidden');
            });

            $(document).on('click', '.adjust-type-btn', function () {
                const type = $(this).data('type');
                $('#adjust_type').val(type);
                $('.adjust-type-btn')
                    .removeClass('border-2 border-emerald-600 bg-emerald-50 text-emerald-700 border-rose-600 bg-rose-50 text-rose-700 border-blue-600 bg-blue-50 text-blue-700')
                    .addClass('border border-[#cbd5e1] text-[#64748b]');

                if (type === 'add') {
                    $(this).removeClass('border border-[#cbd5e1] text-[#64748b]').addClass('border-2 border-emerald-600 bg-emerald-50 text-emerald-700');
                    $('#adjust_quantity_label').text('Jumlah Penambahan (Pcs)');
                    $('#btnSubmitAdjustText').text('Simpan Penambahan Stok');
                    $('#btnSubmitAdjust').removeClass('btn-danger').addClass('btn-primary');
                } else if (type === 'reduce') {
                    $(this).removeClass('border border-[#cbd5e1] text-[#64748b]').addClass('border-2 border-rose-600 bg-rose-50 text-rose-700');
                    $('#adjust_quantity_label').text('Jumlah Pengurangan (Pcs)');
                    $('#btnSubmitAdjustText').text('Simpan Pengurangan Stok');
                    $('#btnSubmitAdjust').removeClass('btn-primary').addClass('btn-danger');
                } else { // 'set'
                    $(this).removeClass('border border-[#cbd5e1] text-[#64748b]').addClass('border-2 border-blue-600 bg-blue-50 text-blue-700');
                    $('#adjust_quantity_label').text('Set Jumlah Stok Akhir (Pcs)');
                    $('#btnSubmitAdjustText').text('Simpan Set Stok Baru');
                    $('#btnSubmitAdjust').removeClass('btn-danger').addClass('btn-primary');
                }
                updateAdjustStockPreview();
            });

            function updateAdjustStockPreview() {
                const productId = Number($('#adjust_product_id').val());
                const product = state.products.find((p) => Number(p.id) === productId);
                if (!product) return;

                const curStock = Number(product.stock || 0);
                const type = $('#adjust_type').val() || 'add';
                const qty = parseInt($('#adjust_quantity').val(), 10) || 0;

                let preview = curStock;
                if (type === 'add') {
                    preview = curStock + qty;
                } else if (type === 'reduce') {
                    preview = curStock - qty;
                } else if (type === 'set') {
                    preview = qty;
                }

                $('#adjust_preview_stock_text').text(`${preview} ${product.unit}`);
                if (preview < 0) {
                    $('#adjust_preview_stock_text').removeClass('text-emerald-700 text-blue-600').addClass('text-rose-600');
                } else {
                    $('#adjust_preview_stock_text').removeClass('text-rose-600').addClass('text-emerald-700');
                }
            }

            $('#adjust_quantity').on('input change', updateAdjustStockPreview);

            $('#adjustStockForm').on('submit', function (event) {
                event.preventDefault();
                const productId = $('#adjust_product_id').val();
                if (!productId) return;

                const type = $('#adjust_type').val();
                const quantity = parseInt($('#adjust_quantity').val(), 10);
                const note = $('#adjust_note').val().trim();
                const costPrice = maskedNumberValue('#adjust_cost_price');

                if (isNaN(quantity) || quantity < 0) {
                    showAlert('Masukkan jumlah stok yang valid.', 'error');
                    return;
                }

                $.post(helpers.adjustStockUrl(productId), {
                    type: type,
                    quantity: quantity,
                    note: note,
                    cost_price: costPrice > 0 ? costPrice : null,
                }).done((response) => {
                    showAlert(response.message);
                    $('#adjustStockModal').addClass('hidden');
                    loadData();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal menyesuaikan stok.', 'error');
                });
            });

            // --- Manage / Edit Serials Modal Handlers ---
            function loadProductSerials(productId) {
                $.getJSON(helpers.serialsUrl(productId))
                    .done((response) => {
                        state.currentSerials = response.serials || [];
                        state.currentSerialProduct = response.product;
                        renderSerialsTable(state.currentSerials);
                    })
                    .fail(() => {
                        showAlert('Gagal memuat serial number produk.', 'error');
                        $('#productSerialsTable').html('<tr><td colspan="6" class="text-center py-6 text-red-500">Gagal memuat data serial.</td></tr>');
                    });
            }

            function renderSerialsTable(serials) {
                $('#productSerialsTable').html(serials.length ? serials.map((s) => `
                    <tr>
                        <td class="font-mono font-semibold">${s.serial_number}</td>
                        <td>${s.quantity}</td>
                        <td>${s.expiry_date || '<span class="text-[color:var(--muted)]">-</span>'}</td>
                        <td>${s.cost_price > 0 ? money(s.cost_price) : '<span class="text-[color:var(--muted)]">-</span>'}</td>
                        <td>
                            <span class="badge ${s.is_sold ? 'badge-muted' : 'badge-ok'}">${s.is_sold ? 'Terjual' : 'Tersedia'}</span>
                        </td>
                        <td class="text-right whitespace-nowrap">
                            <button type="button" class="btn btn-secondary py-0.5 px-2 text-xs btn-edit-single-serial" data-id="${s.id || ''}" data-sn="${s.serial_number}" data-expiry="${s.expiry_date || ''}" data-cost="${s.cost_price || 0}">
                                Edit SN
                            </button>
                        </td>
                    </tr>
                `).join('') : '<tr><td colspan="6" class="text-center py-6 text-[color:var(--muted)]">Belum ada serial number tercatat untuk produk ini.</td></tr>');
            }

            $(document).on('click', '.btn-open-serials', function () {
                const productId = Number($(this).data('id'));
                const product = state.products.find((p) => Number(p.id) === productId);
                if (!product) return;

                $('#manage_serials_product_title').text(`${product.code} - ${product.name} (Stok: ${product.stock} ${product.unit})`);
                $('#editSerialForm').addClass('hidden');
                $('#productSerialsTable').html('<tr><td colspan="6" class="text-center py-6 text-[color:var(--muted)]">Memuat serial number...</td></tr>');
                $('#manageSerialsModal').removeClass('hidden');

                loadProductSerials(productId);
            });

            $(document).on('click', '.close-serials-modal', function () {
                $('#manageSerialsModal').addClass('hidden');
            });

            $(document).on('click', '.btn-edit-single-serial', function () {
                const movementId = $(this).data('id');
                const sn = $(this).data('sn');
                const expiry = $(this).data('expiry');
                const cost = Number($(this).data('cost') || 0);

                $('#edit_serial_product_id').val(state.currentSerialProduct.id);
                $('#edit_serial_movement_id').val(movementId);
                $('#edit_serial_number').val(sn);
                $('#edit_serial_expiry').val(expiry);
                setMaskedNumberValue('#edit_serial_cost', cost);

                $('#editSerialForm').removeClass('hidden');
                window.setTimeout(() => $('#edit_serial_number').focus().select(), 50);
            });

            $('#btnCancelEditSerial').on('click', function () {
                $('#editSerialForm').addClass('hidden');
            });

            $('#editSerialForm').on('submit', function (event) {
                event.preventDefault();
                const productId = $('#edit_serial_product_id').val();
                const movementId = $('#edit_serial_movement_id').val();
                const sn = $('#edit_serial_number').val().trim();
                const expiry = $('#edit_serial_expiry').val();
                const cost = maskedNumberValue('#edit_serial_cost');

                if (!sn) {
                    showAlert('Nomor seri tidak boleh kosong.', 'error');
                    return;
                }

                $.ajax({
                    url: helpers.updateSerialUrl(productId),
                    method: 'PUT',
                    data: {
                        movement_id: movementId || null,
                        serial_number: sn,
                        expiry_date: expiry || null,
                        cost_price: cost > 0 ? cost : null,
                    },
                }).done((response) => {
                    showAlert(response.message);
                    $('#editSerialForm').addClass('hidden');
                    loadProductSerials(productId);
                    loadData();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal memperbarui serial number.', 'error');
                });
            });

            // ==========================================
            // --- Scan Barcode Stock Modal Handlers ---
            // ==========================================
            let scanSoundEnabled = true;
            const scanState = {
                productId: null,
                product: null,
                mode: 'sn', // 'sn' or 'qty'
                items: [], // [{ barcode, quantity, cost_price, expiry_date, scanned_at }]
            };

            function playScanAudio(type = 'success') {
                if (!scanSoundEnabled) return;
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    if (type === 'success') {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(1760, ctx.currentTime);
                        gain.gain.setValueAtTime(0.15, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.08);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.08);
                    } else if (type === 'duplicate') {
                        const now = ctx.currentTime;
                        [0, 0.09].forEach((delay) => {
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            osc.type = 'sawtooth';
                            osc.frequency.setValueAtTime(360, now + delay);
                            gain.gain.setValueAtTime(0.15, now + delay);
                            gain.gain.exponentialRampToValueAtTime(0.01, now + delay + 0.07);
                            osc.connect(gain);
                            gain.connect(ctx.destination);
                            osc.start(now + delay);
                            osc.stop(now + delay + 0.07);
                        });
                    }
                } catch (e) {
                    // Audio context ignored if not supported
                }
            }

            function resetScanState() {
                scanState.productId = null;
                scanState.product = null;
                scanState.mode = 'sn';
                scanState.items = [];

                $('#scan_product_id').val('');
                $('#scan_product_search').val('');
                $('#scanProductDropdown').addClass('hidden').empty();
                $('#scanProductSelectedInfo').addClass('hidden');
                $('#scan_batch_cost_price').val('');
                $('#scan_batch_expiry_date').val('');
                $('#scan_barcode_input').val('');
                $('#scan_stock_note').val('');
                $('#scanStatusFeedback').addClass('hidden').removeClass('bg-emerald-100 text-emerald-900 border-emerald-300 bg-amber-100 text-amber-900 border-amber-300 bg-rose-100 text-rose-900 border-rose-300 border').empty();

                $('.scan-mode-btn')
                    .removeClass('bg-emerald-50 text-emerald-700 border-emerald-300 active')
                    .addClass('text-slate-600 border-transparent');
                $('.scan-mode-btn[data-mode="sn"]')
                    .addClass('bg-emerald-50 text-emerald-700 border-emerald-300 active');

                renderScanTable();
            }

            function setScanProduct(product) {
                if (!product) return;
                scanState.productId = product.id;
                scanState.product = product;

                $('#scan_product_id').val(product.id);
                $('#scan_product_search').val(`${product.code} - ${product.name}`);
                $('#scanProductDropdown').addClass('hidden');

                $('#scanProductBadge').text(`${product.code} - ${product.name} (${product.variant || '-'})`);
                $('#scanCurrentStockText').text(`${product.stock} ${product.unit}`);
                $('#scanProductPriceText').text(money(product.price));
                $('#scanProductSelectedInfo').removeClass('hidden');

                if (Number(product.cost_price || 0) > 0) {
                    $('#scan_batch_cost_price').val(formatNumber(product.cost_price));
                }

                updateScanSummary();
            }

            function renderScanProductDropdown() {
                const query = ($('#scan_product_search').val() || '').toLowerCase();
                const filtered = regularProducts()
                    .filter((p) => !query || p.name.toLowerCase().includes(query) || p.code.toLowerCase().includes(query))
                    .slice(0, 10);

                $('#scanProductDropdown').html(filtered.length ? filtered.map((p) => `
                    <button type="button" class="search-select-item scan-product-option text-left w-full px-3 py-2 hover:bg-slate-100 transition-all border-b border-slate-100" data-id="${p.id}">
                        <div class="font-semibold text-xs text-slate-800">${p.code} - ${p.name}</div>
                        <div class="text-[11px] text-[color:var(--muted)]">Stok: ${p.stock} ${p.unit} · Modal: ${p.cost_price > 0 ? money(p.cost_price) : '-'} · Jual: ${money(p.price)}</div>
                    </button>
                `).join('') : '<div class="px-3 py-3 text-xs text-slate-400">Produk fisik tidak ditemukan.</div>').removeClass('hidden');
            }

            function showScanStatus(msg, type = 'success') {
                const $el = $('#scanStatusFeedback');
                $el.removeClass('hidden bg-emerald-100 text-emerald-900 border-emerald-300 bg-amber-100 text-amber-900 border-amber-300 bg-rose-100 text-rose-900 border-rose-300 border');
                if (type === 'success') {
                    $el.addClass('bg-emerald-100 text-emerald-900 border border-emerald-300');
                } else if (type === 'warning') {
                    $el.addClass('bg-amber-100 text-amber-900 border border-amber-300');
                } else {
                    $el.addClass('bg-rose-100 text-rose-900 border border-rose-300');
                }
                $el.html(msg);
            }

            function flashScanInput(type = 'success') {
                const $input = $('#scan_barcode_input');
                const borderClass = type === 'success' ? 'ring-2 ring-emerald-400 bg-emerald-50/50' : 'ring-2 ring-amber-400 bg-amber-50/50';
                $input.addClass(borderClass);
                window.setTimeout(() => $input.removeClass(borderClass), 350);
            }

            function updateScanSummary() {
                const totalUnits = scanState.items.reduce((sum, item) => sum + Number(item.quantity || 1), 0);
                const totalCost = scanState.items.reduce((sum, item) => sum + (Number(item.quantity || 1) * Number(item.cost_price || 0)), 0);

                $('#scanCountBadge').text(`${totalUnits} Unit`);
                $('#scanTotalCostText').text(money(totalCost));
                $('#btnSubmitScannedText').text(`Simpan Stok (${totalUnits} Unit)`);
            }

            function renderScanTable() {
                updateScanSummary();
                const $tbody = $('#scanTableBody');
                if (!scanState.items.length) {
                    $tbody.html(`
                        <tr id="scanEmptyRow">
                            <td colspan="5" class="text-center py-6 text-slate-400">
                                Belum ada item discan.
                            </td>
                        </tr>
                    `);
                    return;
                }

                $tbody.html(scanState.items.map((item, idx) => `
                    <tr class="hover:bg-slate-50 border-b border-slate-100 transition-all">
                        <td class="text-center font-mono text-[11px] text-slate-500">${scanState.items.length - idx}</td>
                        <td>
                            <div class="font-mono font-bold text-xs text-slate-900">${item.barcode}</div>
                            <div class="text-[10px] text-slate-400">Discan: ${item.scanned_at} ${item.expiry_date ? '· Exp: ' + item.expiry_date : ''}</div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-slate-100 text-slate-800 font-bold px-2 py-0.5 text-xs">${item.quantity} pcs</span>
                        </td>
                        <td class="text-right font-medium text-slate-700">
                            ${money(item.cost_price)}
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn-remove-scan-item text-rose-500 hover:text-rose-700 p-1" data-index="${idx}" title="Hapus item ini">
                                <svg class="w-4 h-4 inline-block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </button>
                        </td>
                    </tr>
                `).join(''));
            }

            function handleIncomingBarcode(rawCode) {
                const code = (rawCode || '').trim();
                if (!code) return;

                if (!scanState.productId) {
                    showScanStatus('⚠️ Pilih produk terlebih dahulu di atas sebelum memindai barcode!', 'warning');
                    playScanAudio('duplicate');
                    $('#scan_product_search').focus();
                    return;
                }

                const costPrice = maskedNumberValue('#scan_batch_cost_price');
                if (costPrice <= 0) {
                    showScanStatus('⚠️ Isi Harga Modal per pcs terlebih dahulu sebelum men-scan barang!', 'warning');
                    playScanAudio('duplicate');
                    $('#scan_batch_cost_price').focus();
                    return;
                }

                const expiryDate = $('#scan_batch_expiry_date').val() || null;
                const nowTime = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

                if (scanState.mode === 'sn') {
                    // Cek duplikasi di sesi scan ini
                    const duplicate = scanState.items.find((item) => item.barcode === code);
                    if (duplicate) {
                        showScanStatus(`⚠️ Barcode / SN "${code}" sudah discan sebelumnya!`, 'warning');
                        playScanAudio('duplicate');
                        flashScanInput('warning');
                        $('#scan_barcode_input').val('').focus();
                        return;
                    }

                    scanState.items.unshift({
                        barcode: code,
                        quantity: 1,
                        cost_price: costPrice,
                        expiry_date: expiryDate,
                        scanned_at: nowTime,
                    });
                    playScanAudio('success');
                    showScanStatus(`✓ [${nowTime}] Barcode SN "${code}" berhasil discan!`, 'success');
                    flashScanInput('success');
                } else {
                    // Mode Qty: jika barcode sama discan lagi, tambah qty
                    const existing = scanState.items.find((item) => item.barcode === code);
                    if (existing) {
                        existing.quantity += 1;
                        existing.scanned_at = nowTime;
                        playScanAudio('success');
                        showScanStatus(`✓ [${nowTime}] Qty "${code}" bertambah jadi ${existing.quantity} pcs!`, 'success');
                        flashScanInput('success');
                    } else {
                        scanState.items.unshift({
                            barcode: code,
                            quantity: 1,
                            cost_price: costPrice,
                            expiry_date: expiryDate,
                            scanned_at: nowTime,
                        });
                        playScanAudio('success');
                        showScanStatus(`✓ [${nowTime}] Barcode "${code}" berhasil discan (1 pcs)!`, 'success');
                        flashScanInput('success');
                    }
                }

                renderScanTable();
                $('#scan_barcode_input').val('').focus();
            }

            function updateScanSoundUi() {
                if (scanSoundEnabled) {
                    $('#toggleScanSound')
                        .attr('title', 'Suara Beep: ON')
                        .removeClass('border-slate-300 bg-slate-100 text-slate-400 hover:bg-slate-200')
                        .addClass('border-emerald-300 bg-white text-emerald-700 hover:bg-emerald-50');
                    $('#soundIconWrapper').html(`
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
                    `);
                    $('#soundLabel').text('Suara Beep: ON');
                } else {
                    $('#toggleScanSound')
                        .attr('title', 'Suara Beep: OFF')
                        .removeClass('border-emerald-300 bg-white text-emerald-700 hover:bg-emerald-50')
                        .addClass('border-slate-300 bg-slate-100 text-slate-400 hover:bg-slate-200');
                    $('#soundIconWrapper').html(`
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/></svg>
                    `);
                    $('#soundLabel').text('Suara Beep: OFF');
                }
            }

            $('#toggleScanSound').on('click', function () {
                scanSoundEnabled = !scanSoundEnabled;
                updateScanSoundUi();
                showAlert(scanSoundEnabled ? 'Bunyi beep scan diaktifkan.' : 'Bunyi beep scan dimatikan.');
            });

            $('.scan-mode-btn').on('click', function () {
                const mode = $(this).data('mode');
                scanState.mode = mode;
                $('.scan-mode-btn')
                    .removeClass('bg-emerald-50 text-emerald-700 border-emerald-300 active')
                    .addClass('text-slate-600 border-transparent');
                $(this)
                    .addClass('bg-emerald-50 text-emerald-700 border-emerald-300 active');
                $('#scan_barcode_input').focus();
            });

            $('#scan_product_search').on('focus input', function () {
                renderScanProductDropdown();
            });

            $(document).on('click', '.scan-product-option', function () {
                const productId = Number($(this).data('id'));
                const product = state.products.find((p) => Number(p.id) === productId);
                if (product) {
                    setScanProduct(product);
                    window.setTimeout(() => {
                        if (!maskedNumberValue('#scan_batch_cost_price')) {
                            $('#scan_batch_cost_price').focus();
                        } else {
                            $('#scan_barcode_input').focus();
                        }
                    }, 50);
                }
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('#scanStockModal .search-select').length) {
                    $('#scanProductDropdown').addClass('hidden');
                }
            });

            $('#scan_barcode_input').on('keydown', function (e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    handleIncomingBarcode($(this).val());
                }
            });

            $(document).on('click', '.btn-remove-scan-item', function () {
                const idx = Number($(this).data('index'));
                if (!isNaN(idx) && scanState.items[idx]) {
                    scanState.items.splice(idx, 1);
                    renderScanTable();
                    $('#scan_barcode_input').focus();
                }
            });

            $('#btnClearScanList').on('click', function () {
                if (!scanState.items.length) return;
                if (confirm('Bersihkan seluruh daftar hasil scan pada sesi ini?')) {
                    scanState.items = [];
                    renderScanTable();
                    $('#scanStatusFeedback').addClass('hidden').empty();
                    $('#scan_barcode_input').focus();
                }
            });

            $('#openScanStockModal').on('click', function () {
                resetScanState();
                const curProdId = Number($('#stock_product_id').val());
                if (curProdId) {
                    const prod = state.products.find((p) => Number(p.id) === curProdId);
                    if (prod) {
                        setScanProduct(prod);
                    }
                }
                $('#scanStockModal').removeClass('hidden');
                window.setTimeout(() => {
                    if (!scanState.productId) {
                        $('#scan_product_search').focus();
                    } else if (!maskedNumberValue('#scan_batch_cost_price')) {
                        $('#scan_batch_cost_price').focus();
                    } else {
                        $('#scan_barcode_input').focus();
                    }
                }, 80);
            });

            $(document).on('click', '.btn-open-scan-stock', function () {
                const productId = Number($(this).data('id'));
                const product = state.products.find((p) => Number(p.id) === productId);
                if (!product) return;

                resetScanState();
                setScanProduct(product);
                $('#scanStockModal').removeClass('hidden');
                window.setTimeout(() => {
                    if (!maskedNumberValue('#scan_batch_cost_price')) {
                        $('#scan_batch_cost_price').focus().select();
                    } else {
                        $('#scan_barcode_input').focus();
                    }
                }, 80);
            });

            $(document).on('click', '.close-scan-modal', function () {
                if (scanState.items.length > 0) {
                    if (!confirm(`Ada ${scanState.items.length} item hasil scan yang belum disimpan. Yakin ingin menutup?`)) {
                        return;
                    }
                }
                $('#scanStockModal').addClass('hidden');
                resetScanState();
            });

            $('#btnSubmitScannedStock').on('click', function () {
                if (!scanState.productId) {
                    showAlert('Pilih produk terlebih dahulu.', 'error');
                    $('#scan_product_search').focus();
                    return;
                }
                if (!scanState.items.length) {
                    showAlert('Belum ada item yang discan. Tembak barcode barang terlebih dahulu.', 'error');
                    $('#scan_barcode_input').focus();
                    return;
                }

                const costPrice = maskedNumberValue('#scan_batch_cost_price');
                if (costPrice <= 0) {
                    showAlert('Harga modal wajib diisi.', 'error');
                    $('#scan_batch_cost_price').focus();
                    return;
                }

                const totalUnits = scanState.items.reduce((sum, item) => sum + Number(item.quantity || 1), 0);
                const note = ($('#scan_stock_note').val() || '').trim() || `Stok masuk via barcode scanner (${totalUnits} pcs)`;

                const payload = {
                    items: scanState.items.map((item) => ({
                        serial_number: item.barcode,
                        quantity: item.quantity,
                        cost_price: item.cost_price || costPrice,
                        expiry_date: item.expiry_date || null,
                    })),
                    note: note,
                };

                const $btn = $(this);
                $btn.prop('disabled', true).text('Menyimpan ke database...');

                $.post(stockUrl(scanState.productId), payload)
                    .done((res) => {
                        showAlert(res.message || `✓ Sukses! ${totalUnits} unit stok berhasil ditambahkan ke database.`);
                        $('#scanStockModal').addClass('hidden');
                        resetScanState();
                        loadData();
                    })
                    .fail((xhr) => {
                        showAlert(xhr.responseJSON?.message || 'Gagal menyimpan stok masuk.', 'error');
                    })
                    .always(() => {
                        $btn.prop('disabled', false);
                        updateScanSummary();
                    });
            });

            // --- Expiring Stock Modal Handlers ---
            $('#openStockExpiringModal').on('click', function () {
                renderExpiringModalTable();
                $('#stockExpiringModal').removeClass('hidden');
            });

            $(document).on('click', '.close-expiring-modal', function () {
                $('#stockExpiringModal').addClass('hidden');
            });

            $(document).on('input', '[data-number-mask]', function () {
                $(this).val(formatNumber(parseNumber($(this).val())));
            });

            bindNumberMasks(document);
            resetForm();
            loadData();
        });
    </script>
@endsection
