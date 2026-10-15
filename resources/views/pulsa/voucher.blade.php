@extends('layouts.app')

@section('content')
    <section class="space-y-4">
        <div class="grid gap-4 lg:grid-cols-[1fr_360px] xl:grid-cols-[60%_40%]">
            <!-- Left Side: Catalog & Category Filter (60% width) -->
            <div class="space-y-4 min-w-0">
                <div class="list-panel p-3">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold">Katalog Produk Voucher</div>
                            <div class="text-[11px] text-[color:var(--muted)]">Pilih kategori voucher di bawah untuk memfilter kolom produk.</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="rounded-lg border border-[#d8e1ec] bg-white px-2.5 py-1 text-xs whitespace-nowrap">
                                <span class="text-[color:var(--muted)]">Hari Ini:</span>
                                <strong id="todayVoucherTotal" class="ml-1 text-[color:var(--accent)]">Rp 0</strong>
                            </div>
                            <button type="button" class="btn btn-primary py-1.5 px-3 text-xs font-semibold whitespace-nowrap" id="openAddVoucherModal">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg></span>
                                <span>Tambah Voucher</span>
                            </button>
                            <input id="voucherSearch" class="field max-w-xs text-xs py-1.5" type="text" placeholder="Cari nama / kode / varian / SN...">
                        </div>
                    </div>

                    <!-- Category Pills / Selector -->
                    <div class="mb-3">
                        <label class="mb-1.5 block text-xs font-medium text-[color:var(--muted)]">Kategori Voucher:</label>
                        <div id="categoryTabs" class="flex flex-wrap gap-1.5">
                            <!-- Dynamic category buttons rendered here -->
                        </div>
                    </div>

                    <div id="voucherProductGrid" class="space-y-4 max-h-[620px] overflow-y-auto pr-1"></div>
                </div>

                <div class="list-panel p-3">
                    <div class="mb-2 text-sm font-semibold">Dompet Saldo Server (Opsional untuk Voucher Elektrik)</div>
                    <div id="walletCards" class="grid gap-3 md:grid-cols-3"></div>
                </div>
            </div>

            <!-- Right Side: Action Forms (Penjualan / Tambah Produk Voucher) -->
            <div class="space-y-3 max-w-full self-start lg:sticky lg:top-[calc(var(--header-height,0px)+1rem)]">
                <!-- Panel Switcher Tabs -->
                <div class="flex items-center rounded-lg border border-[#d8e1ec] bg-[#f1f5f9] p-1 gap-1">
                    <button type="button" class="voucher-mode-tab flex-1 py-1.5 px-3 text-xs font-semibold rounded-md transition-all bg-white text-[color:var(--text)] shadow-xs" id="tabModeSale" data-mode="sale">
                        <span class="flex items-center justify-center gap-1.5">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                            <span>Form Penjualan</span>
                        </span>
                    </button>
                    <button type="button" class="voucher-mode-tab flex-1 py-1.5 px-3 text-xs font-semibold rounded-md transition-all text-[color:var(--muted)] hover:text-[color:var(--text)]" id="tabModeAddProduct" data-mode="add">
                        <span class="flex items-center justify-center gap-1.5">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                            <span>+ Form Tambah Produk</span>
                        </span>
                    </button>
                </div>

                <!-- Panel 1: Form Penjualan Voucher -->
                <div id="panelVoucherSale">
                    <form id="voucherForm" class="list-panel p-3">
                        <div class="mb-2.5 flex items-center justify-between">
                            <div class="text-sm font-semibold">Form Penjualan Voucher</div>
                            <span id="selectedProductBadge" class="badge badge-info hidden"></span>
                        </div>

                        <div class="space-y-2.5">
                            <div>
                                <label class="mb-1 block font-medium">Produk Voucher</label>
                                <input type="hidden" id="voucher_product_id">
                                <input class="field bg-[#f8fafc] py-1.5" type="text" id="voucher_product_text" placeholder="Pilih produk voucher dari katalog di sebelah kiri" readonly required>
                            </div>

                            <!-- Serial Number Picker -->
                            <div class="rounded-xl border border-[#d8e1ec] bg-[#f8fafc] p-2.5 space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block font-medium text-xs">Pilih Serial Number (SN)</label>
                                    <span id="serialCountBadge" class="text-[11px] text-[color:var(--muted)]">Pilih produk dahulu</span>
                                </div>

                                <div>
                                    <select class="select py-1 text-xs" id="voucher_serial_select">
                                        <option value="">-- Pilih Serial Number dari Stok --</option>
                                    </select>
                                </div>

                                <div>
                                    <input class="field py-1 text-xs font-mono" type="text" id="serial_number" placeholder="SN yang akan dijual (scan/ketik/opsional)">
                                </div>
                            </div>

                            <div class="grid gap-2 grid-cols-2">
                                <div>
                                    <label class="mb-1 block font-medium">Nomor HP / Tujuan</label>
                                    <input class="field py-1.5" type="text" id="target_number" placeholder="08xxxxxxxxxx (Opsional)">
                                </div>
                                <div>
                                    <label class="mb-1 block font-medium">Nama Pelanggan</label>
                                    <input class="field py-1.5" type="text" id="voucher_customer_name" placeholder="Umum / Opsional">
                                </div>
                            </div>

                            <div class="grid gap-2 grid-cols-3">
                                <div>
                                    <label class="mb-1 block font-medium">Shift</label>
                                    <select class="select py-1.5" id="voucher_shift">
                                        <option value="pagi">Pagi</option>
                                        <option value="sore">Sore</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block font-medium">Sumber Dompet</label>
                                    <select class="select py-1.5" id="wallet_id"></select>
                                </div>
                                <div>
                                    <label class="mb-1 block font-medium">Metode</label>
                                    <select class="select py-1.5" id="voucher_payment_method">
                                        <option value="cash">Tunai</option>
                                        <option value="qris">QRIS</option>
                                        <option value="transfer">Transfer</option>
                                        <option value="debit">Debit</option>
                                        <option value="utang">Utang (Belum Lunas)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid gap-2 grid-cols-2">
                                <div>
                                    <label class="mb-1 block font-medium">Uang Diterima</label>
                                    <input class="field py-1.5" type="text" inputmode="numeric" data-number-mask id="voucher_cash_paid" value="0" required>
                                </div>
                                <div>
                                    <label class="mb-1 block font-medium">Catatan</label>
                                    <input class="field py-1.5" type="text" id="voucher_notes" placeholder="Opsional">
                                </div>
                            </div>

                            <div class="pos-summary my-1 py-1.5">
                                <div class="pos-summary-row text-xs">
                                    <span class="text-[color:var(--muted)]">Harga Jual</span>
                                    <strong id="voucherPriceText">Rp 0</strong>
                                </div>
                                <div class="pos-summary-row text-xs">
                                    <span class="text-[color:var(--muted)]">Modal / Potong Saldo</span>
                                    <strong id="voucherCostText">Rp 0</strong>
                                </div>
                                <div class="pos-summary-row pos-summary-total">
                                    <span class="text-[color:var(--muted)]">Kembalian</span>
                                    <strong id="voucherChangeText">Rp 0</strong>
                                </div>
                            </div>

                            <button class="btn btn-primary w-full py-2 min-h-[38px] text-sm font-semibold" type="submit" id="btnSubmitVoucher">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                                <span>Simpan Penjualan Voucher</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Panel 2: Form Tambah Produk Voucher (INLINE & LANGSUNG TERSEDIA) -->
                <div id="panelVoucherAddProduct" class="hidden">
                    <form id="voucherInlineAddForm" class="list-panel p-3 space-y-2.5">
                        <div class="flex items-center justify-between border-b border-[#f0f4f8] pb-2">
                            <div>
                                <div class="text-sm font-semibold" id="inlineFormTitle">Form Tambah Produk Voucher</div>
                                <div class="text-[11px] text-[color:var(--muted)]">Input produk voucher baru ke katalog.</div>
                            </div>
                            <button type="button" class="btn btn-secondary py-1 px-2 text-xs" id="btnCancelInlineAdd">Batal</button>
                        </div>

                        <input type="hidden" id="inline_voucher_id">

                        <div class="grid gap-2 grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium">Kode Produk <span class="text-red-500">*</span></label>
                                <div class="flex gap-1">
                                    <input class="field py-1 text-xs font-mono" type="text" id="inline_voucher_code" placeholder="VCR-..." required>
                                    <button type="button" class="btn btn-secondary py-1 px-1.5 text-[11px]" id="btnGenInlineCode" title="Buat kode acak">Auto</button>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium">Kategori / Provider <span class="text-red-500">*</span></label>
                                <input class="field py-1 text-xs" list="inlineProviderDatalist" id="inline_voucher_category" placeholder="Telkomsel/XL..." required>
                                <datalist id="inlineProviderDatalist">
                                    <option value="Telkomsel">
                                    <option value="Indosat">
                                    <option value="XL">
                                    <option value="Axis">
                                    <option value="Smartfren">
                                    <option value="Tri">
                                    <option value="Voucher">
                                </datalist>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium">Nama Produk Voucher <span class="text-red-500">*</span></label>
                            <input class="field py-1 text-xs" type="text" id="inline_voucher_name" placeholder="Contoh: Voucher Kuota Telkomsel 5GB" required>
                        </div>

                        <div class="grid gap-2 grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium">Varian / Kuota</label>
                                <input class="field py-1 text-xs" type="text" id="inline_voucher_variant" placeholder="Contoh: 5 GB / 30 Hari">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium">Satuan</label>
                                <input class="field py-1 text-xs" type="text" id="inline_voucher_unit" value="pcs" required>
                            </div>
                        </div>

                        <div class="grid gap-2 grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                                <input class="field py-1 text-xs" type="text" inputmode="numeric" data-number-mask id="inline_voucher_price" value="0" required>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium">Harga Modal (Rp)</label>
                                <input class="field py-1 text-xs" type="text" inputmode="numeric" data-number-mask id="inline_voucher_cost_price" placeholder="Opsional">
                            </div>
                        </div>

                        <div id="inlineStockWrap" class="rounded-lg border border-[#e2e8f0] bg-[#f8fafc] p-2 space-y-1.5">
                            <div class="text-[10px] font-semibold text-[color:var(--muted)] uppercase tracking-wider">Stok Fisik Awal (Opsional)</div>
                            <div class="grid gap-2 grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-[11px] font-medium">Stok Awal (Pcs)</label>
                                    <input class="field py-1 text-xs" type="text" inputmode="numeric" data-number-mask id="inline_voucher_stock" value="0">
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] font-medium">SN Pertama</label>
                                    <input class="field py-1 text-xs font-mono" type="text" id="inline_voucher_serial_number" placeholder="Nomor seri SN">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium">Gambar Produk (Opsional)</label>
                            <input class="field py-1 text-xs" type="file" id="inline_voucher_image" accept="image/*">
                        </div>

                        <div class="pt-1.5 flex gap-2">
                            <button type="submit" class="btn btn-primary flex-1 py-1.5 text-xs font-semibold" id="btnSubmitInlineVoucher">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                                <span>Simpan Produk Voucher</span>
                            </button>
                            <button type="button" class="btn btn-secondary py-1.5 px-3 text-xs" id="btnResetInlineVoucher">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Tambah Produk Voucher -->
    <div id="addVoucherModal" class="modal-backdrop hidden">
        <div class="modal-card max-w-lg">
            <div class="modal-head flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-sm">Tambah Produk Voucher</h3>
                    <p class="text-[11px] text-[color:var(--muted)]">Buat produk voucher baru untuk katalog penjualan.</p>
                </div>
                <button type="button" class="btn-icon text-[color:var(--muted)] hover:text-[color:var(--text)]" id="closeAddVoucherModal">
                    <svg class="h-4 w-4" viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
            <form id="addVoucherForm" class="p-4 space-y-3">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium">Kode Produk <span class="text-red-500">*</span></label>
                        <div class="flex gap-1.5">
                            <input class="field py-1 text-xs font-mono" type="text" id="modal_voucher_code" placeholder="VCR-..." required>
                            <button type="button" class="btn btn-secondary py-1 px-2 text-xs" id="btnGenModalVoucherCode" title="Buat kode acak">Auto</button>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium">Kategori / Provider <span class="text-red-500">*</span></label>
                        <input class="field py-1 text-xs" list="modalProviderDatalist" id="modal_voucher_category" placeholder="Telkomsel / XL / Indosat..." required>
                        <datalist id="modalProviderDatalist">
                            <option value="Telkomsel">
                            <option value="Indosat">
                            <option value="XL">
                            <option value="Axis">
                            <option value="Smartfren">
                            <option value="Tri">
                            <option value="Voucher">
                        </datalist>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium">Nama Produk Voucher <span class="text-red-500">*</span></label>
                    <input class="field py-1 text-xs" type="text" id="modal_voucher_name" placeholder="Contoh: Voucher Kuota Telkomsel 5GB" required>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium">Varian / Kuota / Durasi</label>
                        <input class="field py-1 text-xs" type="text" id="modal_voucher_variant" placeholder="Contoh: 5 GB / 30 Hari">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium">Satuan</label>
                        <input class="field py-1 text-xs" type="text" id="modal_voucher_unit" value="pcs" required>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                        <input class="field py-1 text-xs" type="text" inputmode="numeric" data-number-mask id="modal_voucher_price" value="0" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium">Harga Modal (Rp)</label>
                        <input class="field py-1 text-xs" type="text" inputmode="numeric" data-number-mask id="modal_voucher_cost_price" placeholder="Opsional">
                    </div>
                </div>

                <div class="rounded-lg border border-[#e2e8f0] bg-[#f8fafc] p-2.5 space-y-2">
                    <div class="text-[10px] font-semibold text-[color:var(--muted)] uppercase tracking-wider">Stok Fisik Awal (Opsional)</div>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Stok Awal (Pcs)</label>
                            <input class="field py-1 text-xs" type="text" inputmode="numeric" data-number-mask id="modal_voucher_stock" value="0">
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Serial Number Pertama (Opsional)</label>
                            <input class="field py-1 text-xs font-mono" type="text" id="modal_voucher_serial_number" placeholder="Nomor seri voucher">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium">Gambar Produk (Opsional)</label>
                    <input class="field py-1 text-xs" type="file" id="modal_voucher_image" accept="image/*">
                </div>

                <div class="modal-footer flex items-center justify-end gap-2 pt-2 border-t border-[#f0f4f8]">
                    <button type="button" class="btn btn-secondary text-xs" id="btnCancelAddVoucher">Batal</button>
                    <button type="submit" class="btn btn-primary text-xs font-semibold" id="btnSubmitAddVoucher">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                        <span>Simpan Produk Voucher</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        window.runAppPage(function ($, helpers) {
            const state = {
                products: [],
                categories: [],
                wallets: [],
                sales: [],
                selectedCategory: 'all'
            };
            const { money, showAlert, parseNumber, formatNumber, maskedNumberValue, setMaskedNumberValue, bindNumberMasks } = helpers;
            const urls = {
                dashboard: @json(route('ajax.voucher.dashboard')),
                storeSale: @json(route('ajax.voucher.sales.store')),
            };

            function activeProduct() {
                const id = Number($('#voucher_product_id').val());
                return state.products.find((product) => product.id === id) || null;
            }

            function walletsForProduct(product = activeProduct()) {
                if (!product) return [];
                const ids = new Set((product.source_wallet_ids || []).map((id) => Number(id)));
                return state.wallets.filter((wallet) => ids.has(Number(wallet.id)));
            }

            function filteredProducts() {
                const query = ($('#voucherSearch').val() || '').toLowerCase().trim();
                const categoryFilter = state.selectedCategory;

                return state.products.filter((product) => {
                    const productCategory = product.category || product.provider || 'Voucher';
                    const matchesCategory = (categoryFilter === 'all') || (productCategory === categoryFilter);

                    if (!matchesCategory) return false;
                    if (!query) return true;

                    const serials = (product.available_serials || []).map((s) => s.serial_number).join(' ');
                    return [
                        product.name,
                        productCategory,
                        product.variant || '',
                        product.code || '',
                        serials,
                    ].some((value) => String(value || '').toLowerCase().includes(query));
                });
            }

            function groupedProducts() {
                return filteredProducts().reduce((groups, product) => {
                    const groupKey = product.category || product.provider || 'Lainnya';
                    if (!groups[groupKey]) {
                        groups[groupKey] = [];
                    }
                    groups[groupKey].push(product);
                    return groups;
                }, {});
            }

            function updateSummary() {
                const product = activeProduct();
                const price = Number(product?.price || 0);

                let cost = Number(product?.cost_price || 0);
                const selectedSnOption = $('#voucher_serial_select option:selected');
                const snCost = Number(selectedSnOption.data('cost') || 0);
                if (snCost > 0) {
                    cost = snCost;
                }

                const paid = maskedNumberValue('#voucher_cash_paid');
                const wallet = state.wallets.find((item) => item.id === Number($('#wallet_id').val()));

                $('#voucherPriceText').text(money(price));
                $('#voucherCostText').text(money(cost));
                $('#voucherChangeText').text(money(Math.max(paid - price, 0)));

                if (wallet && product?.product_type === 'pulsa' && Number(wallet.balance) < cost) {
                    $('#voucherCostText').addClass('text-red-600');
                } else {
                    $('#voucherCostText').removeClass('text-red-600');
                }
            }

            function renderCategoryTabs() {
                const categories = ['all', ...state.categories];

                $('#categoryTabs').html(categories.map((category) => {
                    const isActive = state.selectedCategory === category;
                    const label = category === 'all' ? 'Semua Voucher' : category;
                    return `
                        <button type="button"
                            class="px-3 py-1 text-xs font-medium rounded-lg border transition-colors ${isActive ? 'border-[color:var(--accent)] bg-[color:var(--accent)] text-white shadow-sm' : 'border-[#d8e1ec] bg-white text-[color:var(--foreground)] hover:bg-[#f1f5f9]'}"
                            data-category="${category}">
                            ${label}
                        </button>
                    `;
                }).join(''));
            }

            function renderProducts() {
                const groups = groupedProducts();
                const groupKeys = Object.keys(groups).sort((left, right) => left.localeCompare(right, 'id'));

                if (!groupKeys.length) {
                    $('#voucherProductGrid').html(`
                        <div class="rounded-xl border border-dashed border-[#cad8e8] bg-white px-4 py-8 text-center text-[color:var(--muted)]">
                            Tidak ada produk voucher yang sesuai filter.
                        </div>
                    `);
                    return;
                }

                $('#voucherProductGrid').html(groupKeys.map((groupName) => `
                    <div class="rounded-xl border border-[#d8e1ec] bg-white p-3 space-y-3 shadow-xs">
                        <div class="flex items-center justify-between border-b border-[#edf2f7] pb-2">
                            <div class="font-semibold text-sm flex items-center gap-2">
                                <span class="inline-block w-2.5 h-2.5 rounded-full bg-[color:var(--accent)]"></span>
                                <span>${groupName}</span>
                            </div>
                            <div class="text-[11px] text-[color:var(--muted)]">${groups[groupName].length} produk</div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2">
                            ${groups[groupName].map((product) => {
                                const selected = Number($('#voucher_product_id').val()) === Number(product.id);
                                const serialCount = product.available_serials_count || 0;
                                const stockCount = Number(product.stock || 0);

                                let stockBadge = '';
                                if (serialCount > 0) {
                                    stockBadge = `<span class="badge badge-ok text-[11px] py-0.5 px-1.5">${serialCount} SN</span>`;
                                } else if (stockCount > 0) {
                                    stockBadge = `<span class="badge badge-info text-[11px] py-0.5 px-1.5">Stok: ${stockCount}</span>`;
                                } else if (product.product_type === 'pulsa') {
                                    stockBadge = `<span class="badge badge-info text-[11px] py-0.5 px-1.5">Elektrik</span>`;
                                } else {
                                    stockBadge = `<span class="badge badge-danger-soft text-[11px] py-0.5 px-1.5">Stok: 0</span>`;
                                }

                                return `
                                    <div class="product-card p-2 select-voucher-product cursor-pointer relative ${selected ? 'ring-2 ring-[color:var(--accent)] bg-[#f8fbff]' : ''}" data-id="${product.id}">
                                        <div class="flex items-start justify-between gap-1">
                                            <div class="flex-1 min-w-0">
                                                ${product.image_url ? `<img src="${product.image_url}" alt="${product.name}" class="mb-1.5 h-14 w-full rounded-[6px] object-cover border border-[#d7e0eb]">` : ''}
                                                <div class="font-semibold text-left text-xs leading-tight line-clamp-2">${product.name}</div>
                                            </div>
                                            <button type="button" class="btn-icon p-1 text-[color:var(--muted)] hover:text-[color:var(--text)] edit-voucher-inline shrink-0" data-id="${product.id}" title="Edit Produk Voucher">
                                                <svg class="h-3 w-3" viewBox="0 0 24 24"><path d="m4 20 4.5-1 9-9-3.5-3.5-9 9L4 20z"/><path d="M13.5 6.5 17 10"/></svg>
                                            </button>
                                        </div>
                                        <div class="mt-1 flex items-center justify-between gap-1">
                                            <span class="text-[11px] text-[color:var(--muted)] truncate max-w-[70px]">${product.variant || groupName}</span>
                                            ${stockBadge}
                                        </div>
                                        <div class="mt-2 pt-1.5 border-t border-[#f0f4f8] text-left flex items-baseline justify-between gap-1">
                                            <div class="product-card-price text-xs font-bold">${money(product.price)}</div>
                                            <div class="text-[10px] text-[color:var(--muted)] truncate">M: ${money(product.cost_price)}</div>
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                `).join(''));
            }

            function populateSerialDropdown(product) {
                if (!product) {
                    $('#voucher_serial_select').html('<option value="">-- Pilih Serial Number dari Stok --</option>');
                    $('#serialCountBadge').text('Pilih produk dahulu');
                    return;
                }

                const serials = product.available_serials || [];
                const count = serials.length;

                let options = `<option value="">-- Pilih Serial Number dari Stok (${count} tersedia) --</option>`;

                serials.forEach((s) => {
                    const expText = s.expiry_date ? ` (Exp: ${s.expiry_date})` : '';
                    const costText = s.cost_price > 0 ? ` [Modal: ${money(s.cost_price)}]` : '';
                    options += `<option value="${s.serial_number}" data-cost="${s.cost_price}" data-exp="${s.expiry_date || ''}">SN: ${s.serial_number}${expText}${costText}</option>`;
                });

                options += `<option value="__custom__">Input Manual / Barcode Scan</option>`;

                $('#voucher_serial_select').html(options);

                if (count > 0) {
                    $('#serialCountBadge').html(`<span class="text-emerald-700 font-medium">${count} SN tersedia di stok</span>`);
                } else if (product.stock > 0) {
                    $('#serialCountBadge').html(`<span class="text-amber-700 font-medium">Stok: ${product.stock} (Tanpa SN terdaftar)</span>`);
                } else {
                    $('#serialCountBadge').html(`<span class="text-stone-500">Input SN manual</span>`);
                }
            }

            function syncWalletOptions() {
                const product = activeProduct();
                const productWallets = walletsForProduct(product);
                const currentWalletId = Number($('#wallet_id').val());

                let options = '';
                if (product?.product_type !== 'pulsa') {
                    options += '<option value="">Stok Fisik Konter</option>';
                }

                if (productWallets.length) {
                    productWallets.forEach((wallet) => {
                        options += `<option value="${wallet.id}">Dompet: ${wallet.name} (${money(wallet.balance)})</option>`;
                    });
                } else if (state.wallets.length && product?.product_type === 'pulsa') {
                    state.wallets.forEach((wallet) => {
                        options += `<option value="${wallet.id}">Dompet: ${wallet.name} (${money(wallet.balance)})</option>`;
                    });
                }

                $('#wallet_id').html(options || '<option value="">Stok Fisik</option>');

                if (product && product.product_type === 'pulsa' && productWallets.length) {
                    if (!productWallets.some((wallet) => Number(wallet.id) === currentWalletId)) {
                        $('#wallet_id').val(productWallets[0].id);
                    }
                }
            }

            function renderWallets() {
                syncWalletOptions();

                $('#walletCards').html(state.wallets.length ? state.wallets.map((wallet) => `
                    <div class="stat-box p-2.5">
                        <div class="text-[11px] uppercase tracking-wider text-[color:var(--muted)]">Dompet</div>
                        <div class="mt-0.5 font-semibold text-xs truncate">${wallet.name}</div>
                        <div class="mt-1 text-sm font-semibold text-[color:var(--accent)]">${money(wallet.balance)}</div>
                    </div>
                `).join('') : '<div class="col-span-full text-xs text-[color:var(--muted)]">Belum ada dompet saldo.</div>');
            }

            function selectProduct(product) {
                if (!product) return;

                $('#voucher_product_id').val(product.id);
                $('#voucher_product_text').val(`${product.name}${product.variant ? ` - ${product.variant}` : ''}`);

                $('#selectedProductBadge').text(product.category || product.provider || 'Voucher').removeClass('hidden');

                populateSerialDropdown(product);

                // If available serials exist, pick the first one by default
                if (product.available_serials && product.available_serials.length > 0) {
                    const firstSn = product.available_serials[0];
                    $('#voucher_serial_select').val(firstSn.serial_number);
                    $('#serial_number').val(firstSn.serial_number);
                } else {
                    $('#voucher_serial_select').val('');
                    $('#serial_number').val('');
                }

                syncWalletOptions();
                setMaskedNumberValue('#voucher_cash_paid', product.price);
                renderProducts();
                updateSummary();
            }

            function fillDefaultProduct() {
                if (!$('#voucher_product_id').val() && state.products.length > 0) {
                    selectProduct(state.products[0]);
                }
            }

            function loadData() {
                $.get(urls.dashboard)
                    .done((response) => {
                        state.products = response.products || [];
                        state.categories = response.categories || [];
                        state.wallets = response.wallets || [];
                        state.sales = response.sales || [];

                        $('#todayVoucherTotal').text(money(response.summary?.today_voucher_total || 0));

                        renderCategoryTabs();
                        renderWallets();
                        renderProducts();
                        fillDefaultProduct();
                        updateSummary();
                    })
                    .fail(() => showAlert('Gagal memuat data voucher.', 'error'));
            }

            // Category Tab Click
            $(document).on('click', '#categoryTabs button', function () {
                state.selectedCategory = $(this).data('category');
                renderCategoryTabs();
                renderProducts();
            });

            // Product Selection Click
            $(document).on('click', '.select-voucher-product', function () {
                const id = Number($(this).data('id'));
                const product = state.products.find((p) => p.id === id);
                if (product) {
                    selectProduct(product);
                }
            });

            // Serial Number Dropdown Change
            $('#voucher_serial_select').on('change', function () {
                const val = $(this).val();
                if (val === '__custom__') {
                    $('#serial_number').val('').focus();
                } else if (val) {
                    $('#serial_number').val(val);
                }
                updateSummary();
            });

            // Serial Number Text Input Change
            $('#serial_number').on('input', function () {
                const textVal = $(this).val().trim();
                const matchedOption = $(`#voucher_serial_select option[value="${textVal}"]`);
                if (matchedOption.length) {
                    $('#voucher_serial_select').val(textVal);
                } else {
                    $('#voucher_serial_select').val('__custom__');
                }
                updateSummary();
            });

            $('#voucherSearch').on('input', renderProducts);

            $('#wallet_id').on('change', updateSummary);

            // Form Submission
            $('#voucherForm').on('submit', function (event) {
                event.preventDefault();

                const product = activeProduct();
                if (!product) {
                    showAlert('Pilih produk voucher terlebih dahulu dari katalog.', 'error');
                    return;
                }

                const isDebt = $('#voucher_payment_method').val() === 'utang';
                const cashPaid = isDebt ? 0 : maskedNumberValue('#voucher_cash_paid');
                const price = Number(product.price || 0);

                if (!isDebt && cashPaid < price) {
                    showAlert('Uang yang diterima kurang dari total harga jual.', 'error');
                    return;
                }

                const $btn = $('#btnSubmitVoucher');
                $btn.prop('disabled', true).addClass('opacity-75');

                $.post(urls.storeSale, {
                    product_id: product.id,
                    wallet_id: $('#wallet_id').val() || null,
                    serial_number: $('#serial_number').val() || null,
                    customer_name: $('#voucher_customer_name').val() || null,
                    target_number: $('#target_number').val() || null,
                    shift: $('#voucher_shift').val() || 'pagi',
                    payment_method: $('#voucher_payment_method').val() || 'cash',
                    cash_paid: cashPaid,
                    notes: $('#voucher_notes').val() || null,
                }).done((response) => {
                    showAlert(`${response.message} (Ref: ${response.sale.reference_number})`);
                    $('#voucher_notes').val('');
                    $('#target_number').val('');
                    $('#voucher_customer_name').val('');
                    loadData();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal menyimpan penjualan voucher.', 'error');
                }).always(() => {
                    $btn.prop('disabled', false).removeClass('opacity-75');
                });
            });

            // Mode Switcher (Sale vs Add Product)
            function switchVoucherMode(mode) {
                if (mode === 'add') {
                    $('#tabModeSale').removeClass('bg-white text-[color:var(--text)] shadow-xs').addClass('text-[color:var(--muted)]');
                    $('#tabModeAddProduct').addClass('bg-white text-[color:var(--text)] shadow-xs').removeClass('text-[color:var(--muted)]');
                    $('#panelVoucherSale').addClass('hidden');
                    $('#panelVoucherAddProduct').removeClass('hidden');
                    if (!$('#inline_voucher_id').val() && !$('#inline_voucher_code').val()) {
                        generateInlineVoucherCode();
                    }
                    $('#inline_voucher_name').focus();
                } else {
                    $('#tabModeAddProduct').removeClass('bg-white text-[color:var(--text)] shadow-xs').addClass('text-[color:var(--muted)]');
                    $('#tabModeSale').addClass('bg-white text-[color:var(--text)] shadow-xs').removeClass('text-[color:var(--muted)]');
                    $('#panelVoucherAddProduct').addClass('hidden');
                    $('#panelVoucherSale').removeClass('hidden');
                }
            }

            $('.voucher-mode-tab').on('click', function () {
                switchVoucherMode($(this).data('mode'));
            });

            $('#btnCancelInlineAdd').on('click', function () {
                switchVoucherMode('sale');
            });

            function generateInlineVoucherCode() {
                const cat = ($('#inline_voucher_category').val() || 'GEN').replace(/[^a-zA-Z0-9]/g, '').substring(0, 4).toUpperCase();
                const rand = Math.floor(1000 + Math.random() * 9000);
                $('#inline_voucher_code').val(`VCR-${cat}-${rand}`);
            }

            $('#btnGenInlineCode').on('click', generateInlineVoucherCode);

            function resetInlineAddForm() {
                $('#voucherInlineAddForm')[0].reset();
                $('#inline_voucher_id').val('');
                $('#inline_voucher_unit').val('pcs');
                setMaskedNumberValue('#inline_voucher_price', 0);
                $('#inline_voucher_cost_price').val('');
                setMaskedNumberValue('#inline_voucher_stock', 0);
                $('#inlineStockWrap').show();
                $('#inlineFormTitle').text('Form Tambah Produk Voucher');
                $('#btnSubmitInlineVoucher').html('<span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span><span>Simpan Produk Voucher</span>');
                generateInlineVoucherCode();
            }

            $('#btnResetInlineVoucher').on('click', resetInlineAddForm);

            // Open Add Voucher: switches directly to the inline form!
            $('#openAddVoucherModal').on('click', function () {
                resetInlineAddForm();
                switchVoucherMode('add');
            });

            // Inline Form Submission
            $('#voucherInlineAddForm').on('submit', function (e) {
                e.preventDefault();
                const productId = $('#inline_voucher_id').val();
                const url = productId ? helpers.updateProductUrl(productId) : window.posConfig.storeProductUrl;
                const formData = new FormData();

                formData.append('product_type', 'voucher');
                formData.append('code', $('#inline_voucher_code').val().trim());
                formData.append('category', $('#inline_voucher_category').val().trim());
                formData.append('name', $('#inline_voucher_name').val().trim());
                formData.append('variant', $('#inline_voucher_variant').val().trim());
                formData.append('unit', $('#inline_voucher_unit').val().trim() || 'pcs');
                formData.append('price', maskedNumberValue('#inline_voucher_price'));

                const costPrice = maskedNumberValue('#inline_voucher_cost_price');
                if (costPrice > 0) {
                    formData.append('cost_price', costPrice);
                }

                if (!productId) {
                    const initialStock = maskedNumberValue('#inline_voucher_stock');
                    formData.append('stock', initialStock);
                    const initialSn = $('#inline_voucher_serial_number').val().trim();
                    if (initialSn) {
                        formData.append('serial_number', initialSn);
                    }
                }

                if ($('#inline_voucher_image')[0].files[0]) {
                    formData.append('image', $('#inline_voucher_image')[0].files[0]);
                }

                if (productId) {
                    formData.append('_method', 'PUT');
                }

                const $btn = $('#btnSubmitInlineVoucher');
                $btn.prop('disabled', true).addClass('opacity-75');

                $.ajax({
                    url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                }).done((response) => {
                    showAlert(response.message || 'Produk voucher berhasil disimpan.');
                    resetInlineAddForm();
                    switchVoucherMode('sale');
                    loadData();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal menyimpan produk voucher.', 'error');
                }).always(() => {
                    $btn.prop('disabled', false).removeClass('opacity-75');
                });
            });

            // Edit product from card
            $(document).on('click', '.edit-voucher-inline', function (e) {
                e.stopPropagation();
                const id = Number($(this).data('id'));
                const product = state.products.find((p) => p.id === id);
                if (!product) return;

                $('#inline_voucher_id').val(product.id);
                $('#inline_voucher_code').val(product.code);
                $('#inline_voucher_category').val(product.category || product.provider || '');
                $('#inline_voucher_name').val(product.name);
                $('#inline_voucher_variant').val(product.variant || '');
                $('#inline_voucher_unit').val(product.unit || 'pcs');
                setMaskedNumberValue('#inline_voucher_price', product.price || 0);

                if (Number(product.cost_price || 0) > 0) {
                    setMaskedNumberValue('#inline_voucher_cost_price', product.cost_price || 0);
                } else {
                    $('#inline_voucher_cost_price').val('');
                }

                $('#inlineStockWrap').hide();
                $('#inlineFormTitle').text('Form Edit Produk Voucher');
                $('#btnSubmitInlineVoucher').html('<span class="btn-icon"><svg viewBox="0 0 24 24"><path d="m4 20 4.5-1 9-9-3.5-3.5-9 9L4 20z"/><path d="M13.5 6.5 17 10"/></svg></span><span>Update Produk Voucher</span>');
                switchVoucherMode('add');
            });

            // Modal fallback logic
            function generateModalVoucherCode() {
                const cat = ($('#modal_voucher_category').val() || 'GEN').replace(/[^a-zA-Z0-9]/g, '').substring(0, 4).toUpperCase();
                const rand = Math.floor(1000 + Math.random() * 9000);
                $('#modal_voucher_code').val(`VCR-${cat}-${rand}`);
            }

            $('#closeAddVoucherModal, #btnCancelAddVoucher').on('click', function () {
                $('#addVoucherModal').addClass('hidden');
            });

            $('#btnGenModalVoucherCode').on('click', generateModalVoucherCode);

            $('#addVoucherForm').on('submit', function (e) {
                e.preventDefault();
                const formData = new FormData();
                formData.append('product_type', 'voucher');
                formData.append('code', $('#modal_voucher_code').val().trim());
                formData.append('category', $('#modal_voucher_category').val().trim());
                formData.append('name', $('#modal_voucher_name').val().trim());
                formData.append('variant', $('#modal_voucher_variant').val().trim());
                formData.append('unit', $('#modal_voucher_unit').val().trim() || 'pcs');
                formData.append('price', maskedNumberValue('#modal_voucher_price'));

                const costPrice = maskedNumberValue('#modal_voucher_cost_price');
                if (costPrice > 0) {
                    formData.append('cost_price', costPrice);
                }

                const initialStock = maskedNumberValue('#modal_voucher_stock');
                formData.append('stock', initialStock);

                const initialSn = $('#modal_voucher_serial_number').val().trim();
                if (initialSn) {
                    formData.append('serial_number', initialSn);
                }

                if ($('#modal_voucher_image')[0].files[0]) {
                    formData.append('image', $('#modal_voucher_image')[0].files[0]);
                }

                const $btn = $('#btnSubmitAddVoucher');
                $btn.prop('disabled', true).addClass('opacity-75');

                $.ajax({
                    url: window.posConfig.storeProductUrl,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                }).done((response) => {
                    showAlert(response.message || 'Produk voucher berhasil ditambahkan.');
                    $('#addVoucherModal').addClass('hidden');
                    loadData();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal menambahkan produk voucher.', 'error');
                }).always(() => {
                    $btn.prop('disabled', false).removeClass('opacity-75');
                });
            });

            $(document).on('input', '[data-number-mask]', function () {
                $(this).val(formatNumber(parseNumber($(this).val())));
                updateSummary();
            });

            bindNumberMasks(document);
            loadData();
        });
    </script>
@endsection
