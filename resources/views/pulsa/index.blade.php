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

            <div class="space-y-4 xl:sticky xl:top-4 self-start max-h-[calc(100vh-2rem)] overflow-y-auto pr-1">
                <form id="pulsaForm" class="list-panel p-3">
                    <div class="mb-3 text-sm font-semibold">Form Penjualan Pulsa</div>
                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block font-medium">Produk Pulsa</label>
                            <input type="hidden" id="pulsa_product_id">
                            <input class="field" type="text" id="pulsa_product_text" placeholder="Pilih produk dari kartu di kiri" readonly>
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Nomor Tujuan</label>
                            <input class="field" type="text" id="target_number" placeholder="08xxxxxxxxxx" required>
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
                                <label class="mb-1 block font-medium">Dompet Saldo</label>
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
                                <label class="mb-1 block font-medium">Uang Diterima</label>
                                <input class="field" type="text" inputmode="numeric" data-number-mask id="pulsa_cash_paid" value="0" required>
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
                                <span class="text-[color:var(--muted)]">Potong Saldo</span>
                                <strong id="pulsaCostText">Rp 0</strong>
                            </div>
                            <div class="pos-summary-row">
                                <span class="text-[color:var(--muted)]">Kembalian</span>
                                <strong id="pulsaChangeText">Rp 0</strong>
                            </div>
                        </div>

                        <button class="btn btn-primary w-full" type="submit">
                            <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                            <span>Simpan Penjualan Pulsa</span>
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

            function updateSummary() {
                const product = activeProduct();
                const price = Number(product?.price || 0);
                const cost = Number(product?.cost_price || 0);
                const paid = maskedNumberValue('#pulsa_cash_paid');
                const wallet = state.wallets.find((item) => item.id === Number($('#wallet_id').val()));
                $('#pulsaPriceText').text(money(price));
                $('#pulsaCostText').text(money(cost));
                $('#pulsaChangeText').text(money(Math.max(paid - price, 0)));
                $('#pulsaCostText').toggleClass('text-red-600', !!wallet && Number(wallet.balance) < cost);
            }

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

            function selectedWalletBalance() {
                const wallet = state.wallets.find((item) => item.id === Number($('#wallet_id').val()));
                return Number(wallet?.balance || 0);
            }

            function walletOptions(selector, wallets = state.wallets) {
                $(selector).html(wallets.length ? wallets.map((wallet) => `
                    <option value="${wallet.id}">${wallet.name} - ${money(wallet.balance)}</option>
                `).join('') : '<option value="">Belum ada dompet sumber</option>');
            }

            function syncWalletOptions() {
                const product = activeProduct();
                const productWallets = walletsForProduct(product);
                const currentWalletId = Number($('#wallet_id').val());

                walletOptions('#wallet_id', product ? productWallets : state.wallets);
                if (product && !productWallets.some((wallet) => Number(wallet.id) === currentWalletId)) {
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
                const product = state.products.find((item) => (item.source_wallet_ids || []).length) || state.products[0];
                if (!product) return;
                if (!$('#pulsa_product_id').val()) {
                    $('#pulsa_product_id').val(product.id);
                    $('#pulsa_product_text').val(`${product.name}${product.variant ? ` - ${product.variant}` : ''}`);
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

                $('#pulsa_product_id').val(product.id);
                $('#pulsa_product_text').val(`${product.name}${product.variant ? ` - ${product.variant}` : ''}`);
                syncWalletOptions();
                setMaskedNumberValue('#pulsa_cash_paid', product.price);
                renderProducts();
                updateSummary();
            });

            $('#pulsaForm').on('submit', function (event) {
                event.preventDefault();

                if (!$('#pulsa_product_id').val()) {
                    showAlert('Pilih produk pulsa terlebih dahulu.', 'error');
                    return;
                }

                if (!$('#wallet_id').val()) {
                    showAlert('Produk ini belum memiliki dompet sumber yang bisa dipakai.', 'error');
                    return;
                }

                $.post(urls.storeSale, {
                    product_id: $('#pulsa_product_id').val(),
                    wallet_id: $('#wallet_id').val(),
                    customer_name: $('#pulsa_customer_name').val(),
                    target_number: $('#target_number').val(),
                    shift: $('#pulsa_shift').val(),
                    payment_method: $('#pulsa_payment_method').val(),
                    cash_paid: $('#pulsa_payment_method').val() === 'utang' ? 0 : maskedNumberValue('#pulsa_cash_paid'),
                    notes: $('#pulsa_notes').val(),
                }).done((response) => {
                    showAlert(`${response.message} Ref ${response.sale.reference_number}`);
                    $('#pulsaForm')[0].reset();
                    $('#pulsa_product_id').val('');
                    $('#pulsa_product_text').val('');
                    setMaskedNumberValue('#pulsa_cash_paid', 0);
                    loadData();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal menyimpan penjualan pulsa.', 'error');
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
