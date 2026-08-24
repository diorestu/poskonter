@extends('layouts.app')

@section('content')
    <section class="pane">
        <div class="toolbar flex flex-wrap items-center justify-between gap-3 px-4 py-3">
            <div>
                <h1 class="font-semibold text-lg">Penjualan Voucher</h1>
                <p class="text-[color:var(--muted)] text-sm">Pilih kategori voucher, tentukan serial number dari stok atau manual, dan proses transaksi kasir.</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="rounded-lg border border-[#d8e1ec] bg-white px-3 py-1.5 text-xs">
                    <span class="text-[color:var(--muted)]">Total Voucher Hari Ini:</span>
                    <strong id="todayVoucherTotal" class="ml-1 text-[color:var(--accent)]">Rp 0</strong>
                </div>
            </div>
        </div>

        <div class="grid gap-4 p-4 xl:grid-cols-[1.05fr_0.95fr]">
            <!-- Left Side: Catalog & Category Filter -->
            <div class="space-y-4">
                <div class="list-panel p-3">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold">Katalog Produk Voucher</div>
                            <div class="text-[11px] text-[color:var(--muted)]">Pilih kategori voucher di bawah untuk memfilter kolom produk.</div>
                        </div>
                        <input id="voucherSearch" class="field max-w-xs" type="text" placeholder="Cari nama / kode / varian / SN...">
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

            <!-- Right Side: Selling Form & Recent Transactions -->
            <div class="space-y-4">
                <form id="voucherForm" class="list-panel p-3">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="text-sm font-semibold">Form Penjualan Voucher</div>
                        <span id="selectedProductBadge" class="badge badge-info hidden"></span>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block font-medium">Produk Voucher</label>
                            <input type="hidden" id="voucher_product_id">
                            <input class="field bg-[#f8fafc]" type="text" id="voucher_product_text" placeholder="Pilih produk voucher dari katalog di sebelah kiri" readonly required>
                        </div>

                        <!-- Serial Number Picker -->
                        <div class="rounded-xl border border-[#d8e1ec] bg-[#f8fafc] p-3 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-semibold">Pilih Serial Number (SN)</label>
                                <span id="serialCountBadge" class="text-[11px] text-[color:var(--muted)]">Pilih produk dahulu</span>
                            </div>

                            <div>
                                <select class="select" id="voucher_serial_select">
                                    <option value="">-- Pilih Serial Number dari Stok --</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs text-[color:var(--muted)]">Nomor Seri / SN yang akan dijual (bisa diedit / di-scan barcode):</label>
                                <input class="field" type="text" id="serial_number" placeholder="Contoh: 1234567890 (Bisa dikosongkan jika tanpa SN)">
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block font-medium">Nomor Tujuan / HP Pelanggan</label>
                            <input class="field" type="text" id="target_number" placeholder="08xxxxxxxxxx (Opsional jika voucher fisik)">
                        </div>

                        <div class="grid gap-3 md:grid-cols-3">
                            <div>
                                <label class="mb-1 block font-medium">Nama Pelanggan</label>
                                <input class="field" type="text" id="voucher_customer_name" placeholder="Opsional">
                            </div>
                            <div>
                                <label class="mb-1 block font-medium">Shift</label>
                                <select class="select" id="voucher_shift">
                                    <option value="pagi">Pagi</option>
                                    <option value="sore">Sore</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block font-medium">Sumber / Dompet</label>
                                <select class="select" id="wallet_id"></select>
                            </div>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block font-medium">Metode Pembayaran</label>
                                <select class="select" id="voucher_payment_method">
                                    <option value="cash">Tunai</option>
                                    <option value="qris">QRIS</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="debit">Debit</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block font-medium">Uang Diterima</label>
                                <input class="field" type="text" inputmode="numeric" data-number-mask id="voucher_cash_paid" value="0" required>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block font-medium">Catatan</label>
                            <input class="field" type="text" id="voucher_notes" placeholder="Opsional">
                        </div>

                        <div class="pos-summary">
                            <div class="pos-summary-row">
                                <span class="text-[color:var(--muted)]">Harga Jual</span>
                                <strong id="voucherPriceText">Rp 0</strong>
                            </div>
                            <div class="pos-summary-row">
                                <span class="text-[color:var(--muted)]">Harga Modal / Potong Saldo</span>
                                <strong id="voucherCostText">Rp 0</strong>
                            </div>
                            <div class="pos-summary-row pos-summary-total">
                                <span class="text-[color:var(--muted)]">Kembalian</span>
                                <strong id="voucherChangeText">Rp 0</strong>
                            </div>
                        </div>

                        <button class="btn btn-primary w-full py-2.5" type="submit" id="btnSubmitVoucher">
                            <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                            <span>Simpan Penjualan Voucher</span>
                        </button>
                    </div>
                </form>

                <div class="list-panel p-3">
                    <div class="mb-2 text-sm font-semibold">Transaksi Voucher Terakhir</div>
                    <div id="recentVoucherSalesList" class="space-y-2 max-h-[260px] overflow-y-auto"></div>
                </div>
            </div>
        </div>
    </section>
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

                        <div class="product-grid">
                            ${groups[groupName].map((product) => {
                                const selected = Number($('#voucher_product_id').val()) === Number(product.id);
                                const serialCount = product.available_serials_count || 0;
                                const stockCount = Number(product.stock || 0);

                                let stockBadge = '';
                                if (serialCount > 0) {
                                    stockBadge = `<span class="badge badge-ok text-[10px]">${serialCount} SN Siap</span>`;
                                } else if (stockCount > 0) {
                                    stockBadge = `<span class="badge badge-info text-[10px]">Stok: ${stockCount}</span>`;
                                } else if (product.product_type === 'pulsa') {
                                    stockBadge = `<span class="badge badge-info text-[10px]">Elektrik</span>`;
                                } else {
                                    stockBadge = `<span class="badge badge-danger-soft text-[10px]">Stok: 0</span>`;
                                }

                                return `
                                    <button type="button" class="product-card select-voucher-product ${selected ? 'ring-2 ring-[color:var(--accent)] bg-[#f8fbff]' : ''}" data-id="${product.id}">
                                        <div>
                                            ${product.image_url ? `<img src="${product.image_url}" alt="${product.name}" class="mb-2 h-20 w-full rounded-[6px] object-cover border border-[#d7e0eb]">` : ''}
                                            <div class="font-semibold text-left text-sm leading-tight">${product.name}</div>
                                            <div class="mt-1 flex items-center justify-between gap-1">
                                                <span class="text-[11px] text-[color:var(--muted)]">${product.variant || groupName}</span>
                                                ${stockBadge}
                                            </div>
                                        </div>
                                        <div class="mt-2.5 pt-2 border-t border-[#f0f4f8] text-left">
                                            <div class="product-card-price text-sm">${money(product.price)}</div>
                                            <div class="text-[10px] text-[color:var(--muted)] mt-0.5">Modal: ${money(product.cost_price)}</div>
                                        </div>
                                    </button>
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
                        <div class="text-[10px] uppercase tracking-wider text-[color:var(--muted)]">Dompet</div>
                        <div class="mt-0.5 font-semibold text-xs truncate">${wallet.name}</div>
                        <div class="mt-1 text-sm font-semibold text-[color:var(--accent)]">${money(wallet.balance)}</div>
                    </div>
                `).join('') : '<div class="col-span-full text-xs text-[color:var(--muted)]">Belum ada dompet saldo.</div>');
            }

            function renderRecentSales() {
                if (!state.sales || !state.sales.length) {
                    $('#recentVoucherSalesList').html('<div class="text-xs text-[color:var(--muted)] py-2 text-center">Belum ada transaksi voucher hari ini.</div>');
                    return;
                }

                $('#recentVoucherSalesList').html(state.sales.map((sale) => `
                    <div class="flex items-center justify-between p-2 rounded-lg border border-[#e2e8f0] bg-[#f8fafc] text-xs">
                        <div>
                            <div class="font-semibold">${sale.reference_number}</div>
                            <div class="text-[color:var(--muted)] mt-0.5">${sale.product?.name || '-'} ${sale.product?.variant ? `(${sale.product.variant})` : ''}</div>
                            ${sale.serial_number ? `<div class="font-mono text-[10px] text-indigo-600 mt-0.5">SN: ${sale.serial_number}</div>` : ''}
                        </div>
                        <div class="text-right">
                            <div class="font-semibold">${money(sale.price)}</div>
                            <div class="text-[10px] text-[color:var(--muted)] mt-0.5">${new Date(sale.sale_date).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}</div>
                        </div>
                    </div>
                `).join(''));
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
                        renderRecentSales();
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

                const cashPaid = maskedNumberValue('#voucher_cash_paid');
                const price = Number(product.price || 0);

                if (cashPaid < price) {
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

            $(document).on('input', '[data-number-mask]', function () {
                $(this).val(formatNumber(parseNumber($(this).val())));
                updateSummary();
            });

            bindNumberMasks(document);
            loadData();
        });
    </script>
@endsection
