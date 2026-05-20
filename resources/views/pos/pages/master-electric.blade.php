@extends('layouts.app')

@section('content')
    <section class="space-y-4">
        <div class="pane">
            <div class="toolbar flex items-center justify-between px-4 py-3">
                <div>
                    <h1 class="font-semibold">Master Produk Elektrik</h1>
                    <p class="text-[color:var(--muted)]">Kelola produk pulsa/data per provider. Provider dipilih dari master, dan dompet aktif mengikuti provider yang terdaftar.</p>
                </div>
            </div>
            <div class="pane-body p-4">
                <div class="product-master-layout">
                    <form id="pulsaMasterForm" class="master-card master-card-form p-3">
                        <input type="hidden" id="pulsa_master_product_id">
                        <div class="mb-3 text-sm font-semibold">Form Produk Elektrik</div>
                        <div class="space-y-3">
                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block font-medium">Kode</label>
                                    <input class="field" type="text" id="pulsa_master_code" placeholder="PLS-TSEL-10" required>
                                </div>
                                <div>
                                    <label class="mb-1 block font-medium">Provider</label>
                                    <div class="flex gap-2">
                                        <select class="select" id="pulsa_master_provider_id" required></select>
                                        <button class="btn btn-secondary" type="button" id="openProviderModal">
                                            <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg></span>
                                            <span>Baru</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block font-medium">Nama Produk</label>
                                <input class="field" type="text" id="pulsa_master_name" placeholder="Pulsa Reguler / Paket Data" required>
                            </div>
                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block font-medium">Nominal / Varian</label>
                                    <input class="field" type="text" id="pulsa_master_variant" placeholder="10.000 / 2 GB">
                                </div>
                                <div>
                                    <label class="mb-1 block font-medium">Satuan</label>
                                    <input class="field" type="text" id="pulsa_master_unit" value="trx" required>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block font-medium">Gambar Produk</label>
                                <input class="field" type="file" id="pulsa_master_image" accept="image/*">
                                <div id="pulsaMasterImagePreview" class="mt-2 text-[11px] text-[color:var(--muted)]">Belum ada gambar dipilih.</div>
                            </div>
                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block font-medium">Harga Jual</label>
                                    <input class="field" type="text" inputmode="numeric" data-number-mask id="pulsa_master_price" value="0" required>
                                </div>
                                <div>
                                    <label class="mb-1 block font-medium">Potong Saldo</label>
                                    <input class="field" type="text" inputmode="numeric" data-number-mask id="pulsa_master_cost_price" value="0">
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block font-medium">Dompet dari Provider</label>
                                <div id="pulsaWalletSourceInfo" class="rounded-xl border border-[#d8e1ec] bg-white p-3 text-sm text-[color:var(--muted)]">
                                    Pilih provider untuk melihat dompet aktif yang otomatis bisa dipakai produk ini.
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button class="btn btn-primary flex-1" type="submit" id="pulsaMasterSubmit">
                                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                                    <span>Simpan Produk Elektrik</span>
                                </button>
                                <button class="btn btn-secondary" type="button" id="pulsaMasterReset">
                                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg></span>
                                    <span>Reset</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="master-card master-card-table p-3">
                        <div class="master-card-head">
                            <div class="text-sm font-semibold">Daftar Produk Elektrik</div>
                            <input id="pulsaMasterSearch" class="field max-w-xs" type="text" placeholder="Cari provider / produk / dompet">
                        </div>
                        <div class="table-wrap">
                            <table id="pulsaMasterTableWrap" class="table-grid min-w-full text-sm">
                                <thead class="text-left">
                                    <tr>
                                        <th>Img</th>
                                        <th data-sort="code">Kode</th>
                                        <th data-sort="provider">Provider</th>
                                        <th data-sort="name">Produk</th>
                                        <th data-sort="price">Harga Jual</th>
                                        <th data-sort="cost_price">Potong Saldo</th>
                                        <th>Dompet Aktif</th>
                                        <th width="100">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="pulsaMasterTable"></tbody>
                            </table>
                        </div>
                        <div id="pulsaMasterPagination"></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="providerModal" class="modal-backdrop hidden">
            <div class="modal-card">
                <div class="modal-head flex items-start justify-between gap-3">
                    <div>
                        <div class="text-base font-semibold">Tambah Provider Baru</div>
                        <div class="mt-1 text-sm text-[color:var(--muted)]">Provider baru akan langsung masuk ke pilihan master produk.</div>
                    </div>
                    <button class="btn btn-secondary" type="button" id="closeProviderModal">Tutup</button>
                </div>
                <div class="modal-body">
                    <form id="providerForm" class="space-y-3">
                        <div>
                            <label class="mb-1 block font-medium">Nama Provider</label>
                            <input class="field" type="text" id="provider_name" placeholder="Telkomsel" required>
                        </div>
                        <button class="btn btn-primary w-full" type="submit">
                            <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                            <span>Simpan Provider</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        window.runAppPage(function ($, helpers) {
            const state = { products: [], wallets: [], providers: [] };
            const { money, showAlert, updateProductUrl, maskedNumberValue, setMaskedNumberValue, bindNumberMasks, parseNumber, formatNumber } = helpers;
            const urls = {
                dashboard: @json(route('ajax.pulsa.dashboard')),
                storeProvider: @json(route('ajax.providers.store')),
            };

            const pulsaTable = helpers.createTableController({
                perPage: 8,
                defaultSortKey: 'provider',
                search: (product, query) => [
                    product.code,
                    product.name,
                    product.provider || product.category,
                    product.variant || '',
                    ...(product.source_wallets || []).map((wallet) => wallet.name),
                ].some((value) => String(value || '').toLowerCase().includes(query)),
                sorters: {
                    code: (product) => product.code,
                    provider: (product) => product.provider || product.category || '',
                    name: (product) => product.name,
                    price: (product) => Number(product.price),
                    cost_price: (product) => Number(product.cost_price),
                },
            });

            function imageCell(product) {
                return product.image_url
                    ? `<img src="${product.image_url}" alt="${product.name}" class="h-10 w-10 rounded-[6px] object-cover border border-[#d7e0eb]">`
                    : `<div class="flex h-10 w-10 items-center justify-center rounded-[6px] border border-dashed border-[#d7e0eb] text-[10px] text-[color:var(--muted)]">IMG</div>`;
            }

            function previewImage(url) {
                $('#pulsaMasterImagePreview').html(url
                    ? `<img src="${url}" alt="Preview" class="h-16 w-16 rounded-[6px] object-cover border border-[#d7e0eb]">`
                    : 'Belum ada gambar dipilih.'
                );
            }

            function selectedProvider() {
                return state.providers.find((provider) => Number(provider.id) === Number($('#pulsa_master_provider_id').val())) || null;
            }

            function providerOptions(selectedId = '') {
                $('#pulsa_master_provider_id').html(state.providers.length
                    ? state.providers.map((provider) => `<option value="${provider.id}" ${Number(provider.id) === Number(selectedId) ? 'selected' : ''}>${provider.name}</option>`).join('')
                    : '<option value="">Belum ada provider</option>'
                );
            }

            function renderWalletSourceInfo(provider = selectedProvider(), fallbackWallets = null) {
                const providerWalletIds = provider?.wallet_ids || [];
                const wallets = Array.isArray(fallbackWallets)
                    ? fallbackWallets
                    : state.wallets.filter((wallet) => providerWalletIds.includes(Number(wallet.id)));

                $('#pulsaWalletSourceInfo').html(wallets.length
                    ? wallets.map((wallet) => `
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-[#e3ebf5] bg-[#f8fbff] px-3 py-2">
                            <span class="font-medium text-[color:var(--text)]">${wallet.name}</span>
                            <span class="text-[11px] text-[color:var(--muted)]">Terdaftar pada provider</span>
                        </div>
                    `).join('')
                    : 'Provider ini belum memiliki dompet aktif. Atur dari menu Dompet Saldo.'
                );
            }

            function resetForm() {
                $('#pulsaMasterForm')[0].reset();
                $('#pulsa_master_product_id').val('');
                $('#pulsa_master_unit').val('trx');
                setMaskedNumberValue('#pulsa_master_price', 0);
                setMaskedNumberValue('#pulsa_master_cost_price', 0);
                providerOptions(state.providers[0]?.id || '');
                previewImage(null);
                renderWalletSourceInfo();
                $('#pulsaMasterSubmit').html('<span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span><span>Simpan Produk Elektrik</span>');
            }

            function drawTable(meta) {
                $('#pulsaMasterTable').html(meta.rows.length ? meta.rows.map((product) => `
                    <tr>
                        <td>${imageCell(product)}</td>
                        <td class="font-semibold">${product.code}</td>
                        <td><span class="badge badge-info">${product.provider || product.category}</span></td>
                        <td>
                            <div class="font-medium">${product.name}</div>
                            <div class="text-[11px] text-[color:var(--muted)]">${product.variant || '-'} · ${product.unit}</div>
                        </td>
                        <td>${money(product.price)}</td>
                        <td>${money(product.cost_price)}</td>
                        <td>
                            ${(product.source_wallets || []).length
                                ? product.source_wallets.map((wallet) => `<div class="text-[11px]">${wallet.name}</div>`).join('')
                                : '<span class="text-[11px] text-[color:var(--muted)]">Belum ada dompet</span>'}
                        </td>
                        <td>
                            <button class="btn btn-secondary edit-electric" type="button" data-id="${product.id}">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="m4 20 4.5-1 9-9-3.5-3.5-9 9L4 20z"/><path d="M13.5 6.5 17 10"/></svg></span>
                                <span>Edit</span>
                            </button>
                        </td>
                    </tr>
                `).join('') : '<tr><td colspan="8" class="text-center text-[color:var(--muted)]">Belum ada produk elektrik.</td></tr>');
                pulsaTable.syncSortUi('#pulsaMasterTableWrap thead');
                pulsaTable.renderFooter('#pulsaMasterPagination', meta);
            }

            function renderProducts(products) {
                state.products = products || [];
                pulsaTable.setData(state.products);
                pulsaTable.setQuery($('#pulsaMasterSearch').val());
                drawTable(pulsaTable.pagedRows());
            }

            function upsertProvider(provider) {
                if (!provider) return;
                const existingIndex = state.providers.findIndex((item) => Number(item.id) === Number(provider.id));
                if (existingIndex >= 0) {
                    state.providers.splice(existingIndex, 1, {
                        ...state.providers[existingIndex],
                        ...provider,
                    });
                } else {
                    state.providers.push(provider);
                }
                state.providers.sort((left, right) => left.name.localeCompare(right.name, 'id'));
                providerOptions(provider.id);
                renderWalletSourceInfo();
            }

            function loadData() {
                $.get(urls.dashboard, { _: Date.now() })
                    .done((response) => {
                        state.wallets = response.wallets || [];
                        state.providers = response.providers || [];
                        providerOptions($('#pulsa_master_provider_id').val() || state.providers[0]?.id || '');
                        renderProducts(response.products || []);
                        renderWalletSourceInfo();
                    })
                    .fail(() => showAlert('Gagal memuat master produk elektrik.', 'error'));
            }

            $('#pulsaMasterSearch').on('input', function () {
                pulsaTable.setQuery($(this).val());
                drawTable(pulsaTable.pagedRows());
            });

            $('#pulsa_master_provider_id').on('change', function () {
                renderWalletSourceInfo();
            });

            $(document).on('click', '#pulsaMasterPagination .page-btn', function () {
                pulsaTable.setPage(Number($(this).data('page')));
                drawTable(pulsaTable.pagedRows());
            });

            $(document).on('click', '#pulsaMasterTableWrap thead [data-sort]', function () {
                pulsaTable.toggleSort($(this).data('sort'));
                drawTable(pulsaTable.pagedRows());
            });

            $('#pulsaMasterReset').on('click', resetForm);

            $('#pulsa_master_image').on('change', function () {
                const file = this.files[0];
                if (!file) {
                    previewImage(null);
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (event) {
                    previewImage(event.target.result);
                };
                reader.readAsDataURL(file);
            });

            $('#openProviderModal').on('click', function () {
                $('#providerModal').removeClass('hidden');
                $('#provider_name').trigger('focus');
            });

            $('#closeProviderModal').on('click', function () {
                $('#providerModal').addClass('hidden');
            });

            $('#providerModal').on('click', function (event) {
                if (event.target.id === 'providerModal') {
                    $('#providerModal').addClass('hidden');
                }
            });

            $('#providerForm').on('submit', function (event) {
                event.preventDefault();

                $.post(urls.storeProvider, {
                    name: $('#provider_name').val(),
                }).done((response) => {
                    showAlert(response.message);
                    upsertProvider(response.provider);
                    $('#providerForm')[0].reset();
                    $('#providerModal').addClass('hidden');
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal menyimpan provider.', 'error');
                });
            });

            $(document).on('click', '.edit-electric', function () {
                const product = state.products.find((item) => item.id === Number($(this).data('id')));
                if (!product) return;

                $('#pulsa_master_product_id').val(product.id);
                $('#pulsa_master_code').val(product.code);
                providerOptions(product.provider_id);
                $('#pulsa_master_name').val(product.name);
                $('#pulsa_master_variant').val(product.variant || '');
                $('#pulsa_master_unit').val(product.unit || 'trx');
                setMaskedNumberValue('#pulsa_master_price', product.price);
                setMaskedNumberValue('#pulsa_master_cost_price', product.cost_price || 0);
                previewImage(product.image_url);
                renderWalletSourceInfo(selectedProvider(), product.source_wallets || []);
                $('#pulsaMasterSubmit').html('<span class="btn-icon"><svg viewBox="0 0 24 24"><path d="m4 20 4.5-1 9-9-3.5-3.5-9 9L4 20z"/><path d="M13.5 6.5 17 10"/></svg></span><span>Update Produk Elektrik</span>');
            });

            $('#pulsaMasterForm').on('submit', function (event) {
                event.preventDefault();

                const productId = $('#pulsa_master_product_id').val();
                const url = productId ? updateProductUrl(productId) : window.posConfig.storeProductUrl;
                const formData = new FormData();

                formData.append('product_type', 'pulsa');
                formData.append('provider_id', $('#pulsa_master_provider_id').val());
                formData.append('code', $('#pulsa_master_code').val());
                formData.append('name', $('#pulsa_master_name').val());
                formData.append('category', '');
                formData.append('variant', $('#pulsa_master_variant').val());
                formData.append('serial_number', '');
                formData.append('unit', $('#pulsa_master_unit').val() || 'trx');
                formData.append('price', maskedNumberValue('#pulsa_master_price'));
                formData.append('cost_price', maskedNumberValue('#pulsa_master_cost_price'));
                formData.append('minimum_stock', 0);

                if ($('#pulsa_master_image')[0].files[0]) {
                    formData.append('image', $('#pulsa_master_image')[0].files[0]);
                }

                if (productId) {
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                }).done((response) => {
                    showAlert(response.message);
                    resetForm();
                    loadData();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal menyimpan produk elektrik.', 'error');
                });
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
