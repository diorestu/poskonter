@extends('layouts.app')

@section('content')
    <section class="pane">
        <div class="toolbar flex items-center justify-between px-4 py-3">
            <div>
                <h1 class="font-semibold">Voucher Pulsa</h1>
                <p class="text-[color:var(--muted)]">Penjualan voucher pulsa mengurangi saldo dompet sesuai potong saldo tiap produk.</p>
            </div>
        </div>

        <div class="grid gap-4 p-4 xl:grid-cols-[1.05fr_0.95fr]">
            <div class="space-y-4">
                <div class="list-panel p-3">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div class="text-sm font-semibold">Produk Voucher per Provider</div>
                        <input id="voucherSearch" class="field max-w-xs" type="text" placeholder="Cari provider / produk voucher">
                    </div>
                    <div id="voucherProductGrid" class="product-grid"></div>
                </div>

                <div class="list-panel p-3">
                    <div class="mb-3 text-sm font-semibold">Dompet Saldo</div>
                    <div id="walletCards" class="grid gap-3 md:grid-cols-2"></div>
                </div>
            </div>

            <div class="space-y-4">
                <form id="voucherForm" class="list-panel p-3">
                    <div class="mb-3 text-sm font-semibold">Form Penjualan Voucher</div>
                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block font-medium">Produk Voucher</label>
                            <input type="hidden" id="voucher_product_id">
                            <input class="field" type="text" id="voucher_product_text" placeholder="Pilih produk dari kartu di kiri" readonly>
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Serial Number</label>
                            <input class="field" type="text" id="serial_number" placeholder="Masukkan serial number voucher" required>
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Nomor Tujuan</label>
                            <input class="field" type="text" id="target_number" placeholder="08xxxxxxxxxx" required>
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
                                <label class="mb-1 block font-medium">Dompet Saldo</label>
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
                                <span class="text-[color:var(--muted)]">Potong Saldo</span>
                                <strong id="voucherCostText">Rp 0</strong>
                            </div>
                            <div class="pos-summary-row">
                                <span class="text-[color:var(--muted)]">Kembalian</span>
                                <strong id="voucherChangeText">Rp 0</strong>
                            </div>
                        </div>

                        <button class="btn btn-primary w-full" type="submit">
                            <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                            <span>Simpan Penjualan Voucher</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        window.runAppPage(function ($, helpers) {
            const state = { products: [], wallets: [] };
            const { money, showAlert, parseNumber, formatNumber, maskedNumberValue, setMaskedNumberValue, bindNumberMasks } = helpers;
            const urls = {
                dashboard: @json(route('ajax.voucher.dashboard')),
                storeSale: @json(route('ajax.voucher.sales.store')),
            };

            function activeProduct() {
                return state.products.find((product) => product.id === Number($('#voucher_product_id').val())) || null;
            }

            function walletsForProduct(product = activeProduct()) {
                if (!product) return [];
                const ids = new Set((product.source_wallet_ids || []).map((id) => Number(id)));
                return state.wallets.filter((wallet) => ids.has(Number(wallet.id)));
            }

            function filteredProducts() {
                const query = ($('#voucherSearch').val() || '').toLowerCase();
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
                const paid = maskedNumberValue('#voucher_cash_paid');
                const wallet = state.wallets.find((item) => item.id === Number($('#wallet_id').val()));
                $('#voucherPriceText').text(money(price));
                $('#voucherCostText').text(money(cost));
                $('#voucherChangeText').text(money(Math.max(paid - price, 0)));
                $('#voucherCostText').toggleClass('text-red-600', !!wallet && Number(wallet.balance) < cost);
            }

            function renderProducts() {
                const groups = groupedProducts();
                const providers = Object.keys(groups).sort((left, right) => left.localeCompare(right, 'id'));

                $('#voucherProductGrid').html(providers.length ? providers.map((provider) => `
                    <div class="col-span-full space-y-3">
                        <div class="flex items-center justify-between rounded-xl border border-[#d8e1ec] bg-[#f4f8fb] px-4 py-2">
                            <div class="font-semibold">${provider}</div>
                            <div class="text-[11px] text-[color:var(--muted)]">${groups[provider].length} produk</div>
                        </div>
                        <div class="product-grid">
                            ${groups[provider].map((product) => {
                                const sourceWallets = product.source_wallets || [];
                                const selected = Number($('#voucher_product_id').val()) === Number(product.id);
                                return `
                                    <button type="button" class="product-card select-voucher-product ${sourceWallets.length ? '' : 'product-card-disabled'} ${selected ? 'ring-2 ring-[color:var(--accent)]' : ''}" data-id="${product.id}">
                                        <div>
                                            ${product.image_url ? `<img src="${product.image_url}" alt="${product.name}" class="mb-3 h-24 w-full rounded-[6px] object-cover border border-[#d7e0eb]">` : ''}
                                            <div class="font-semibold">${product.name}</div>
                                            <div class="mt-1 text-[11px] text-[color:var(--muted)]">${product.variant || provider}</div>
                                            <div class="mt-2 text-[10px] text-[color:var(--muted)]">
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
                `).join('') : '<div class="col-span-full rounded-xl border border-dashed border-[#cad8e8] bg-white px-4 py-8 text-center text-[color:var(--muted)]">Belum ada produk voucher.</div>');
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

                $('#walletCards').html(state.wallets.length ? state.wallets.map((wallet) => `
                    <div class="stat-box p-3">
                        <div class="text-[11px] text-[color:var(--muted)]">Dompet</div>
                        <div class="mt-1 font-semibold">${wallet.name}</div>
                        <div class="mt-2 text-lg font-semibold text-[color:var(--accent)]">${money(wallet.balance)}</div>
                    </div>
                `).join('') : '<div class="text-[color:var(--muted)]">Belum ada dompet saldo.</div>');
            }

            function fillDefaultProduct() {
                const product = state.products.find((item) => (item.source_wallet_ids || []).length) || state.products[0];
                if (!product) return;
                if (!$('#voucher_product_id').val()) {
                    $('#voucher_product_id').val(product.id);
                    $('#voucher_product_text').val(`${product.name}${product.variant ? ` - ${product.variant}` : ''}`);
                    syncWalletOptions();
                }
            }

            function loadData() {
                $.get(urls.dashboard)
                    .done((response) => {
                        state.products = response.products || [];
                        state.wallets = response.wallets || [];
                        fillDefaultProduct();
                        renderWallets();
                        renderProducts();
                        updateSummary();
                    })
                    .fail(() => showAlert('Gagal memuat data voucher.', 'error'));
            }

            $('#voucherSearch').on('input', renderProducts);

            $('#wallet_id').on('change', function () {
                updateSummary();
            });

            $(document).on('click', '.select-voucher-product', function () {
                const product = state.products.find((item) => item.id === Number($(this).data('id')));
                if (!product) return;

                if (!(product.source_wallet_ids || []).length) {
                    showAlert('Produk ini belum memiliki sumber dompet saldo.', 'error');
                    return;
                }

                $('#voucher_product_id').val(product.id);
                $('#voucher_product_text').val(`${product.name}${product.variant ? ` - ${product.variant}` : ''}`);
                syncWalletOptions();
                setMaskedNumberValue('#voucher_cash_paid', product.price);
                renderProducts();
                updateSummary();
            });

            $('#voucherForm').on('submit', function (event) {
                event.preventDefault();

                if (!$('#voucher_product_id').val()) {
                    showAlert('Pilih produk voucher terlebih dahulu.', 'error');
                    return;
                }

                if (!$('#wallet_id').val()) {
                    showAlert('Produk ini belum memiliki dompet sumber yang bisa dipakai.', 'error');
                    return;
                }

                if (!$('#serial_number').val()) {
                    showAlert('Serial number voucher wajib diisi.', 'error');
                    return;
                }

                $.post(urls.storeSale, {
                    product_id: $('#voucher_product_id').val(),
                    wallet_id: $('#wallet_id').val(),
                    serial_number: $('#serial_number').val(),
                    customer_name: $('#voucher_customer_name').val(),
                    target_number: $('#target_number').val(),
                    shift: $('#voucher_shift').val(),
                    payment_method: $('#voucher_payment_method').val(),
                    cash_paid: maskedNumberValue('#voucher_cash_paid'),
                    notes: $('#voucher_notes').val(),
                }).done((response) => {
                    showAlert(`${response.message} Ref ${response.sale.reference_number}`);
                    $('#voucherForm')[0].reset();
                    $('#voucher_product_id').val('');
                    $('#voucher_product_text').val('');
                    $('#serial_number').val('');
                    setMaskedNumberValue('#voucher_cash_paid', 0);
                    loadData();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal menyimpan penjualan voucher.', 'error');
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
