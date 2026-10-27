@extends('layouts.app')

@section('content')
    <section class="space-y-4">
        <div class="grid gap-4 xl:grid-cols-[1.05fr_0.95fr] items-start">
            <div class="space-y-4">
                <div class="list-panel p-3">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div class="text-sm font-semibold">Produk Pulsa per Provider</div>
                        <input id="pulsaSearch" class="field max-w-xs" type="text" placeholder="Cari provider / produk pulsa">
                    </div>
                    <div id="pulsaProductGrid" class="product-grid"></div>
                </div>

                <div class="list-panel p-3">
                    <div class="mb-3 text-sm font-semibold">Dompet Saldo</div>
                    <div id="walletCards" class="grid gap-2.5 grid-cols-2 sm:grid-cols-3 xl:grid-cols-4"></div>
                </div>
            </div>

            <div class="space-y-4 xl:sticky xl:top-[calc(var(--header-height,0px)+1rem)] self-start max-h-[calc(100vh-var(--header-height,0px)-2rem)] overflow-y-auto pr-1">
                <form id="pulsaForm" class="list-panel p-3">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2 border-b border-[#e2e8f0] pb-2.5">
                        <div>
                            <div class="text-sm font-semibold text-[color:var(--text)]">Form Penjualan Pulsa</div>
                            <div class="text-[11px] text-[color:var(--muted)]" id="formModeDesc">Pilih katalog atau input manual</div>
                        </div>
                        <div class="inline-flex rounded-lg border border-[#cbd5e1] bg-[#f1f5f9] p-0.5 text-xs font-semibold">
                            <button type="button" id="btnModeCatalog" class="px-2.5 py-1 rounded-md transition-all bg-white text-blue-700 shadow-xs">
                                Katalog Produk
                            </button>
                            <button type="button" id="btnModeManual" class="px-2.5 py-1 rounded-md transition-all text-[#64748b] hover:text-[#0f172a]">
                                ✍️ Input Manual
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <!-- Mode 1: Katalog Produk -->
                        <div id="sectionCatalogProduct">
                            <label class="mb-1 block font-medium">Produk Pulsa (Katalog)</label>
                            <input type="hidden" id="pulsa_product_id">
                            <input class="field" type="text" id="pulsa_product_text" placeholder="Pilih produk dari kartu di kiri" readonly>
                            <div class="mt-1.5 flex items-center justify-between text-xs">
                                <label class="inline-flex items-center gap-1.5 cursor-pointer text-[#475569] hover:text-[#0f172a] select-none">
                                    <input type="checkbox" id="chkCustomPrice" class="rounded border-[#cbd5e1] text-blue-600 focus:ring-blue-500">
                                    <span>Tulis manual harga jual & potong saldo</span>
                                </label>
                            </div>
                        </div>

                        <!-- Mode 2: Input Manual Bebas -->
                        <div id="sectionManualProduct" class="hidden space-y-1">
                            <label class="mb-1 block font-medium">Nama Produk Elektrik <span class="text-red-500">*</span></label>
                            <input class="field" type="text" id="manual_product_name" placeholder="Contoh: Token PLN 50.000 / Topup Gopay 25.000">
                            <div class="text-[11px] text-[color:var(--muted)]">Tulis bebas nama produk elektrik, token listrik PLN, e-wallet, data custom, dll.</div>
                        </div>

                        <!-- Kotak Input Harga Jual & Nilai Potong Saldo -->
                        <div id="customPriceFields" class="hidden rounded-lg border border-blue-200 bg-blue-50/50 p-2.5 space-y-2">
                            <div class="text-[11px] font-semibold text-blue-800 uppercase tracking-wider flex items-center gap-1">
                                <svg class="h-3.5 w-3.5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                Harga Jual & Nilai Potong Saldo
                            </div>
                            <div class="grid gap-2.5 grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-[#1e40af]">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                                    <input class="field py-1 text-xs font-mono font-bold" type="text" inputmode="numeric" data-number-mask id="pulsa_custom_price" value="0">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-[#1e40af]">Potong Saldo (Modal) <span class="text-red-500">*</span></label>
                                    <input class="field py-1 text-xs font-mono font-bold" type="text" inputmode="numeric" data-number-mask id="pulsa_custom_cost" value="0">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block font-medium">Nomor Tujuan / No. Meter / ID <span class="text-red-500">*</span></label>
                            <input class="field font-mono" type="text" id="target_number" placeholder="08xxxxxxxxxx / No. Meter PLN / ID Pelanggan" required>
                        </div>

                        <div class="grid gap-3 md:grid-cols-3">
                            <div>
                                <label class="mb-1 block font-medium">Nama Pelanggan</label>
                                <input class="field" type="text" id="pulsa_customer_name" placeholder="Opsional">
                            </div>
                            <div>
                                <label class="mb-1 block font-medium">Shift</label>
                                <select class="select" id="pulsa_shift">
                                    <option value="pagi">Pagi</option>
                                    <option value="sore">Sore</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block font-medium">Dompet Saldo <span class="text-red-500">*</span></label>
                                <select class="select" id="wallet_id"></select>
                            </div>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block font-medium">Metode Pembayaran</label>
                                <select class="select" id="pulsa_payment_method">
                                    <option value="cash">Tunai</option>
                                    <option value="qris">QRIS</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="debit">Debit</option>
                                    <option value="utang">Utang (Belum Lunas)</option>
                                </select>
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block font-medium">Uang Diterima</label>
                                    <button type="button" id="btnPayExact" class="text-[11px] font-semibold text-blue-600 hover:text-blue-800">Uang Pas</button>
                                </div>
                                <input class="field font-mono" type="text" inputmode="numeric" data-number-mask id="pulsa_cash_paid" value="0" required>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block font-medium">Catatan</label>
                            <input class="field" type="text" id="pulsa_notes" placeholder="Opsional">
                        </div>

                        <div class="pos-summary">
                            <div class="pos-summary-row">
                                <span class="text-[color:var(--muted)]">Harga Jual</span>
                                <strong id="pulsaPriceText">Rp 0</strong>
                            </div>
                            <div class="pos-summary-row">
                                <span class="text-[color:var(--muted)]">Potong Saldo (Modal)</span>
                                <strong id="pulsaCostText">Rp 0</strong>
                            </div>
                            <div class="pos-summary-row">
                                <span class="text-[color:var(--muted)]">Kembalian</span>
                                <strong id="pulsaChangeText">Rp 0</strong>
                            </div>
                        </div>

                        <button class="btn btn-primary w-full" type="submit">
                            <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                            <span id="btnSubmitText">Simpan Penjualan Pulsa</span>
                        </button>
                    </div>
                </form>

                <div class="list-panel p-3">
                    <div class="mb-3 text-sm font-semibold">Tambah Dompet / Top Up</div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <form id="walletForm" class="space-y-3">
                            <div>
                                <label class="mb-1 block font-medium">Nama Dompet</label>
                                <input class="field" type="text" id="wallet_name" placeholder="Dompet Utama">
                            </div>
                            <div>
                                <label class="mb-1 block font-medium">Saldo Awal</label>
                                <input class="field" type="text" inputmode="numeric" data-number-mask id="wallet_balance" value="0">
                            </div>
                            <button class="btn btn-secondary w-full" type="submit">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg></span>
                                <span>Tambah Dompet</span>
                            </button>
                        </form>

                        <form id="topupForm" class="space-y-3">
                            <div>
                                <label class="mb-1 block font-medium">Pilih Dompet</label>
                                <select class="select" id="topup_wallet_id"></select>
                            </div>
                            <div>
                                <label class="mb-1 block font-medium">Nominal Top Up</label>
                                <input class="field" type="text" inputmode="numeric" data-number-mask id="topup_amount" value="0">
                            </div>
                            <button class="btn btn-secondary w-full" type="submit">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/><path d="M4 4h16v16H4z"/></svg></span>
                                <span>Tambah Saldo</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        window.runAppPage(function ($, helpers) {
            const state = { products: [], wallets: [], sales: [] };
            let formMode = 'catalog'; // 'catalog' or 'manual'
            const { money, showAlert, parseNumber, formatNumber, maskedNumberValue, setMaskedNumberValue, bindNumberMasks } = helpers;
            const urls = {
                dashboard: @json(route('ajax.pulsa.dashboard')),
                storeSale: @json(route('ajax.pulsa.sales.store')),
                storeWallet: @json(route('ajax.wallets.store')),
                topupWallet: @json(url('/ajax/wallets/__ID__/topup')),
            };

            function activeProduct() {
                return state.products.find((product) => product.id === Number($('#pulsa_product_id').val())) || null;
            }

            function walletsForProduct(product = activeProduct()) {
                if (!product) return [];
                const ids = new Set((product.source_wallet_ids || []).map((id) => Number(id)));
                return state.wallets.filter((wallet) => ids.has(Number(wallet.id)));
            }

            function filteredProducts() {
                const query = ($('#pulsaSearch').val() || '').toLowerCase();
                return state.products.filter((product) => !query || [
                    product.name,
                    product.provider || product.category,
                    product.variant || '',
                    ...(product.source_wallets || []).map((wallet) => wallet.name),
                ].some((value) => String(value || '').toLowerCase().includes(query)));
            }

            function groupedProducts() {
                return filteredProducts().reduce((groups, product) => {
                    const provider = product.provider || product.category || 'Lainnya';
                    if (!groups[provider]) {
                        groups[provider] = [];
                    }
                    groups[provider].push(product);
                    return groups;
                }, {});
            }

            function getEffectivePrice() {
                if (formMode === 'manual' || $('#chkCustomPrice').is(':checked')) {
                    return maskedNumberValue('#pulsa_custom_price');
                }
                const product = activeProduct();
                return Number(product?.price || 0);
            }

            function getEffectiveCost() {
                if (formMode === 'manual' || $('#chkCustomPrice').is(':checked')) {
                    return maskedNumberValue('#pulsa_custom_cost');
                }
                const product = activeProduct();
                return Number(product?.cost_price || 0);
            }

            function updateSummary() {
                const price = getEffectivePrice();
                const cost = getEffectiveCost();
                const isUtang = $('#pulsa_payment_method').val() === 'utang';
                const paid = isUtang ? 0 : maskedNumberValue('#pulsa_cash_paid');
                const wallet = state.wallets.find((item) => item.id === Number($('#wallet_id').val()));

                $('#pulsaPriceText').text(money(price));
                $('#pulsaCostText').text(money(cost));
                $('#pulsaChangeText').text(isUtang ? 'Rp 0 (Utang)' : money(Math.max(paid - price, 0)));
                $('#pulsaCostText').toggleClass('text-red-600', !!wallet && Number(wallet.balance) < cost);
            }

            function setFormMode(mode) {
                formMode = mode;
                if (mode === 'manual') {
                    $('#btnModeManual').addClass('bg-white text-blue-700 shadow-xs').removeClass('text-[#64748b]');
                    $('#btnModeCatalog').removeClass('bg-white text-blue-700 shadow-xs').addClass('text-[#64748b]');
                    $('#sectionCatalogProduct').addClass('hidden');
                    $('#sectionManualProduct').removeClass('hidden');
                    $('#customPriceFields').removeClass('hidden');
                    $('#formModeDesc').text('Tulis bebas nama produk elektrik, harga jual, dan modal potong saldo');
                    $('#btnSubmitText').text('Simpan Transaksi Manual');
                    syncWalletOptions();
                    updateSummary();
                    $('#manual_product_name').focus();
                } else {
                    $('#btnModeCatalog').addClass('bg-white text-blue-700 shadow-xs').removeClass('text-[#64748b]');
                    $('#btnModeManual').removeClass('bg-white text-blue-700 shadow-xs').addClass('text-[#64748b]');
                    $('#sectionCatalogProduct').removeClass('hidden');
                    $('#sectionManualProduct').addClass('hidden');
                    if ($('#chkCustomPrice').is(':checked')) {
                        $('#customPriceFields').removeClass('hidden');
                    } else {
                        $('#customPriceFields').addClass('hidden');
                    }
                    $('#formModeDesc').text('Pilih produk dari kartu katalog di sebelah kiri');
                    $('#btnSubmitText').text('Simpan Penjualan Pulsa');
                    syncWalletOptions();
                    updateSummary();
                }
            }

            $('#btnModeCatalog').on('click', () => setFormMode('catalog'));
            $('#btnModeManual').on('click', () => setFormMode('manual'));

            $('#chkCustomPrice').on('change', function () {
                if (formMode === 'catalog') {
                    if ($(this).is(':checked')) {
                        const product = activeProduct();
                        if (product) {
                            setMaskedNumberValue('#pulsa_custom_price', product.price);
                            setMaskedNumberValue('#pulsa_custom_cost', product.cost_price);
                        }
                        $('#customPriceFields').removeClass('hidden');
                    } else {
                        $('#customPriceFields').addClass('hidden');
                    }
                    updateSummary();
                }
            });

            $('#btnPayExact').on('click', function () {
                const price = getEffectivePrice();
                setMaskedNumberValue('#pulsa_cash_paid', price);
                updateSummary();
            });

            $('#pulsa_custom_price, #pulsa_custom_cost').on('input', function () {
                updateSummary();
            });

            $('#pulsa_payment_method').on('change', function () {
                const isUtang = $(this).val() === 'utang';
                if (isUtang) {
                    setMaskedNumberValue('#pulsa_cash_paid', 0);
                    $('#pulsa_cash_paid').prop('disabled', true).addClass('opacity-50');
                } else {
                    $('#pulsa_cash_paid').prop('disabled', false).removeClass('opacity-50');
                    if (maskedNumberValue('#pulsa_cash_paid') <= 0) {
                        setMaskedNumberValue('#pulsa_cash_paid', getEffectivePrice());
                    }
                }
                updateSummary();
            });

            function renderProducts() {
                const groups = groupedProducts();
                const providers = Object.keys(groups).sort((left, right) => left.localeCompare(right, 'id'));

                $('#pulsaProductGrid').html(providers.length ? providers.map((provider) => `
                    <div class="col-span-full space-y-3">
                        <div class="flex items-center justify-between rounded-xl border border-[#d8e1ec] bg-[#f4f8fb] px-4 py-2">
                            <div class="font-semibold">${provider}</div>
                            <div class="text-[11px] text-[color:var(--muted)]">${groups[provider].length} produk</div>
                        </div>
                        <div class="product-grid">
                            ${groups[provider].map((product) => {
                                const sourceWallets = product.source_wallets || [];
                                const selected = Number($('#pulsa_product_id').val()) === Number(product.id);
                                return `
                                    <button type="button" class="product-card select-pulsa-product ${sourceWallets.length ? '' : 'product-card-disabled'} ${selected ? 'ring-2 ring-[color:var(--accent)]' : ''}" data-id="${product.id}">
                                        <div>
                                            ${product.image_url ? `<img src="${product.image_url}" alt="${product.name}" class="mb-3 h-24 w-full rounded-[6px] object-cover border border-[#d7e0eb]">` : ''}
                                            <div class="font-semibold">${product.name}</div>
                                            <div class="mt-1 text-[11px] text-[color:var(--muted)]">${product.variant || provider}</div>
                                            <div class="mt-2 text-[11px] text-[color:var(--muted)]">
                                                ${sourceWallets.length ? `Sumber: ${sourceWallets.map((wallet) => wallet.name).join(', ')}` : 'Belum ada sumber dompet'}
                                            </div>
                                        </div>
                                        <div class="mt-3 space-y-1 text-left">
                                            <div class="product-card-price">${money(product.price)}</div>
                                            <div class="product-card-stock">Potong saldo ${money(product.cost_price)}</div>
                                        </div>
                                    </button>
                                `;
                            }).join('')}
                        </div>
                    </div>
                `).join('') : '<div class="col-span-full rounded-xl border border-dashed border-[#cad8e8] bg-white px-4 py-8 text-center text-[color:var(--muted)]">Belum ada produk pulsa.</div>');
            }

            function walletOptions(selector, wallets = state.wallets) {
                $(selector).html(wallets.length ? wallets.map((wallet) => `
                    <option value="${wallet.id}">${wallet.name} - ${money(wallet.balance)}</option>
                `).join('') : '<option value="">Belum ada dompet sumber</option>');
            }

            function syncWalletOptions() {
                if (formMode === 'manual') {
                    walletOptions('#wallet_id', state.wallets);
                    return;
                }
                const product = activeProduct();
                const productWallets = walletsForProduct(product);
                const currentWalletId = Number($('#wallet_id').val());

                walletOptions('#wallet_id', (product && productWallets.length) ? productWallets : state.wallets);
                if (product && productWallets.length && !productWallets.some((wallet) => Number(wallet.id) === currentWalletId)) {
                    $('#wallet_id').val(productWallets[0]?.id || '');
                }
            }

            function renderWallets() {
                syncWalletOptions();
                walletOptions('#topup_wallet_id');

                $('#walletCards').html(state.wallets.length ? state.wallets.map((wallet) => `
                    <div class="stat-box p-2.5">
                        <div class="text-[11px] text-[color:var(--muted)] truncate">Dompet</div>
                        <div class="mt-0.5 font-semibold text-xs truncate" title="${wallet.name}">${wallet.name}</div>
                        <div class="mt-1.5 text-sm font-semibold font-mono text-[color:var(--accent)] truncate">${money(wallet.balance)}</div>
                    </div>
                `).join('') : '<div class="col-span-full text-xs text-[color:var(--muted)]">Belum ada dompet saldo.</div>');
            }

            function fillDefaultProduct() {
                if (formMode === 'manual') return;
                const product = state.products.find((item) => (item.source_wallet_ids || []).length) || state.products[0];
                if (!product) return;
                if (!$('#pulsa_product_id').val()) {
                    $('#pulsa_product_id').val(product.id);
                    $('#pulsa_product_text').val(`${product.name}${product.variant ? ` - ${product.variant}` : ''}`);
                    setMaskedNumberValue('#pulsa_custom_price', product.price);
                    setMaskedNumberValue('#pulsa_custom_cost', product.cost_price);
                    syncWalletOptions();
                }
            }

            function loadData() {
                $.get(urls.dashboard)
                    .done((response) => {
                        state.products = response.products || [];
                        state.wallets = response.wallets || [];
                        state.sales = response.sales || [];
                        fillDefaultProduct();
                        renderWallets();
                        renderProducts();
                        updateSummary();
                    })
                    .fail(() => showAlert('Gagal memuat data pulsa elektrik.', 'error'));
            }

            $('#pulsaSearch').on('input', renderProducts);

            $('#wallet_id').on('change', function () {
                updateSummary();
            });

            $(document).on('click', '.select-pulsa-product', function () {
                const product = state.products.find((item) => item.id === Number($(this).data('id')));
                if (!product) return;

                if (!(product.source_wallet_ids || []).length) {
                    showAlert('Produk ini belum memiliki sumber dompet saldo.', 'error');
                    return;
                }

                if (formMode === 'manual') {
                    $('#manual_product_name').val(`${product.name}${product.variant ? ` - ${product.variant}` : ''}`);
                    setMaskedNumberValue('#pulsa_custom_price', product.price);
                    setMaskedNumberValue('#pulsa_custom_cost', product.cost_price);
                    setMaskedNumberValue('#pulsa_cash_paid', product.price);
                    const pWallets = walletsForProduct(product);
                    if (pWallets.length) {
                        $('#wallet_id').val(pWallets[0].id);
                    }
                    updateSummary();
                    showAlert(`Data ${product.name} dimuat ke form input manual.`);
                    return;
                }

                $('#pulsa_product_id').val(product.id);
                $('#pulsa_product_text').val(`${product.name}${product.variant ? ` - ${product.variant}` : ''}`);
                setMaskedNumberValue('#pulsa_custom_price', product.price);
                setMaskedNumberValue('#pulsa_custom_cost', product.cost_price);
                syncWalletOptions();
                setMaskedNumberValue('#pulsa_cash_paid', product.price);
                renderProducts();
                updateSummary();
            });

            $('#pulsaForm').on('submit', function (event) {
                event.preventDefault();

                const isManual = formMode === 'manual';
                const isCustomPrice = isManual || $('#chkCustomPrice').is(':checked');

                if (!isManual && !$('#pulsa_product_id').val()) {
                    showAlert('Pilih produk pulsa dari katalog atau pilih tab Input Manual.', 'error');
                    return;
                }

                if (isManual && !$('#manual_product_name').val().trim()) {
                    showAlert('Nama produk manual wajib diisi.', 'error');
                    $('#manual_product_name').focus();
                    return;
                }

                if (!$('#target_number').val().trim()) {
                    showAlert('Nomor tujuan / ID pelanggan wajib diisi.', 'error');
                    $('#target_number').focus();
                    return;
                }

                if (!$('#wallet_id').val()) {
                    showAlert('Pilih dompet saldo sumber yang akan dipotong.', 'error');
                    return;
                }

                const price = getEffectivePrice();
                const cost = getEffectiveCost();

                if (price <= 0) {
                    showAlert('Harga jual harus lebih besar dari 0.', 'error');
                    if (isCustomPrice) $('#pulsa_custom_price').focus();
                    return;
                }

                const paymentMethod = $('#pulsa_payment_method').val();
                const cashPaid = paymentMethod === 'utang' ? 0 : maskedNumberValue('#pulsa_cash_paid');

                if (paymentMethod !== 'utang' && cashPaid < price) {
                    showAlert('Uang yang diterima kurang dari total harga jual.', 'error');
                    $('#pulsa_cash_paid').focus();
                    return;
                }

                const payload = {
                    is_manual: isManual ? 1 : 0,
                    product_id: isManual ? null : ($('#pulsa_product_id').val() || null),
                    manual_product_name: isManual ? $('#manual_product_name').val().trim() : null,
                    price: isCustomPrice ? price : null,
                    cost_price: isCustomPrice ? cost : null,
                    wallet_id: $('#wallet_id').val(),
                    customer_name: $('#pulsa_customer_name').val() || null,
                    target_number: $('#target_number').val().trim(),
                    shift: $('#pulsa_shift').val() || 'pagi',
                    payment_method: paymentMethod,
                    cash_paid: cashPaid,
                    notes: $('#pulsa_notes').val() || null,
                };

                const $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).addClass('opacity-75');

                $.post(urls.storeSale, payload)
                    .done((response) => {
                        showAlert(`${response.message} (Ref: ${response.sale.reference_number})`);
                        $('#pulsaForm')[0].reset();
                        $('#pulsa_product_id').val('');
                        $('#pulsa_product_text').val('');
                        $('#manual_product_name').val('');
                        setMaskedNumberValue('#pulsa_custom_price', 0);
                        setMaskedNumberValue('#pulsa_custom_cost', 0);
                        setMaskedNumberValue('#pulsa_cash_paid', 0);
                        $('#chkCustomPrice').prop('checked', false);
                        if (formMode === 'catalog') {
                            $('#customPriceFields').addClass('hidden');
                        }
                        loadData();
                    })
                    .fail((xhr) => {
                        showAlert(xhr.responseJSON?.message || 'Gagal menyimpan penjualan pulsa.', 'error');
                    })
                    .always(() => {
                        $btn.prop('disabled', false).removeClass('opacity-75');
                    });
            });

            $('#walletForm').on('submit', function (event) {
                event.preventDefault();

                $.post(urls.storeWallet, {
                    name: $('#wallet_name').val(),
                    balance: maskedNumberValue('#wallet_balance'),
                }).done((response) => {
                    showAlert(response.message);
                    $('#walletForm')[0].reset();
                    setMaskedNumberValue('#wallet_balance', 0);
                    loadData();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal menambah dompet.', 'error');
                });
            });

            $('#topupForm').on('submit', function (event) {
                event.preventDefault();

                const walletId = $('#topup_wallet_id').val();
                if (!walletId) {
                    showAlert('Pilih dompet terlebih dahulu.', 'error');
                    return;
                }

                $.post(urls.topupWallet.replace('__ID__', walletId), {
                    amount: maskedNumberValue('#topup_amount'),
                }).done((response) => {
                    showAlert(response.message);
                    setMaskedNumberValue('#topup_amount', 0);
                    loadData();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal menambah saldo.', 'error');
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
