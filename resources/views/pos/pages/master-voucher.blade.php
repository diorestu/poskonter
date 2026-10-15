@extends('layouts.app')

@section('content')
    <section class="space-y-4">
        <div class="pane">
            <div class="toolbar flex items-center justify-between px-4 py-3">
                <div>
                    <h1 class="font-semibold">Master Produk Voucher</h1>
                    <p class="text-[color:var(--muted)]">Kelola data produk voucher kuota & perdana, harga jual, modal, stok fisik, nomor seri, dan dompet saldo terkait.</p>
                </div>
                <a href="{{ route('voucher.index') }}" class="btn btn-secondary text-xs">
                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></span>
                    <span>Halaman Penjualan Voucher</span>
                </a>
            </div>
            <div class="pane-body p-4">
                <div class="product-master-layout">
                    <form id="voucherMasterForm" class="master-card master-card-form p-3">
                        <input type="hidden" id="voucher_master_product_id">
                        <div class="mb-3 text-sm font-semibold">Form Tambah Produk Voucher</div>
                        <div class="space-y-3">
                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block font-medium">Kode Produk</label>
                                    <div class="flex gap-1.5">
                                        <input class="field text-xs font-mono" type="text" id="voucher_master_code" placeholder="VCR-TSEL-5GB" required>
                                        <button class="btn btn-secondary py-1 px-2 text-xs" type="button" id="btnGenVoucherCode" title="Buat kode acak">Auto</button>
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1 block font-medium">Kategori / Provider</label>
                                    <input class="field text-xs" list="providerDatalist" id="voucher_master_category" placeholder="Contoh: Telkomsel / XL / Indosat / Axis" required>
                                    <datalist id="providerDatalist">
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
                                <label class="mb-1 block font-medium">Nama Produk Voucher</label>
                                <input class="field text-xs" type="text" id="voucher_master_name" placeholder="Contoh: Voucher Telkomsel Internet" required>
                            </div>

                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block font-medium">Nominal / Kuota / Varian</label>
                                    <input class="field text-xs" type="text" id="voucher_master_variant" placeholder="Contoh: 5 GB / 30 Hari">
                                </div>
                                <div>
                                    <label class="mb-1 block font-medium">Satuan</label>
                                    <input class="field text-xs" type="text" id="voucher_master_unit" value="pcs" required>
                                </div>
                            </div>

                            <div id="initialStockSection" class="rounded-lg border border-[#e2e8f0] bg-[#f8fafc] p-2.5 space-y-2">
                                <div class="text-[11px] font-semibold text-[color:var(--muted)] uppercase tracking-wider">Stok Awal (Saat Produk Baru Dibuat)</div>
                                <div class="grid gap-2 grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-medium">Stok Awal</label>
                                        <input class="field text-xs" type="text" inputmode="numeric" data-number-mask id="voucher_master_stock" value="0">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium">Stok Minimum</label>
                                        <input class="field text-xs" type="text" inputmode="numeric" data-number-mask id="voucher_master_minimum_stock" value="0">
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium">Serial Number Awal (Opsional)</label>
                                    <input class="field text-xs font-mono" type="text" id="voucher_master_serial_number" placeholder="Nomor seri / barcode voucher">
                                    <div class="mt-1 text-[10px] text-[color:var(--muted)]">Untuk input banyak nomor seri sekaligus, gunakan menu Stock.</div>
                                </div>
                            </div>

                            <div>
                                <label class="mb-1 block font-medium">Gambar Produk</label>
                                <input class="field text-xs" type="file" id="voucher_master_image" accept="image/*">
                                <div id="voucherMasterImagePreview" class="mt-2 text-[11px] text-[color:var(--muted)]">Belum ada gambar dipilih.</div>
                            </div>

                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block font-medium">Harga Jual</label>
                                    <input class="field text-xs" type="text" inputmode="numeric" data-number-mask id="voucher_master_price" value="0" required>
                                </div>
                                <div>
                                    <label class="mb-1 block font-medium">Harga Modal / Potong Saldo</label>
                                    <input class="field text-xs" type="text" inputmode="numeric" data-number-mask id="voucher_master_cost_price" placeholder="Opsional">
                                </div>
                            </div>

                            <div>
                                <label class="mb-1 block font-medium">Dompet Saldo Server (Opsional)</label>
                                <div id="voucherWalletCheckboxes" class="flex flex-wrap gap-2 text-xs">
                                    <!-- Wallet checkboxes -->
                                </div>
                                <div class="mt-1 text-[10px] text-[color:var(--muted)]">Pilih jika voucher ini dipotong dari saldo dompet server saat transaksi.</div>
                            </div>

                            <div class="flex gap-2 pt-2">
                                <button class="btn btn-primary flex-1" type="submit" id="voucherMasterSubmit">
                                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                                    <span>Simpan Produk Voucher</span>
                                </button>
                                <button class="btn btn-secondary" type="button" id="voucherMasterReset">
                                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg></span>
                                    <span>Reset</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="master-card master-card-table p-3">
                        <div class="master-card-head">
                            <div>
                                <div class="text-sm font-semibold">Daftar Produk Voucher</div>
                                <div class="text-[11px] text-[color:var(--muted)]" id="voucherMasterTotalText">Memuat data...</div>
                            </div>
                            <input id="voucherMasterSearch" class="field max-w-xs text-xs" type="text" placeholder="Cari nama / kode / kategori / SN...">
                        </div>
                        <div class="table-wrap">
                            <table id="voucherMasterTableWrap" class="table-grid min-w-full text-xs">
                                <thead class="text-left">
                                    <tr>
                                        <th>Img</th>
                                        <th data-sort="code">Kode</th>
                                        <th data-sort="category">Kategori</th>
                                        <th data-sort="name">Produk & Varian</th>
                                        <th data-sort="price">Harga</th>
                                        <th data-sort="cost_price">Modal</th>
                                        <th data-sort="stock">Stok</th>
                                        <th>SN Siap</th>
                                        <th width="80">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="voucherMasterTable"></tbody>
                            </table>
                        </div>
                        <div id="voucherMasterPagination"></div>
                    </div>
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
                wallets: [],
            };

            const { money, showAlert, updateProductUrl, maskedNumberValue, setMaskedNumberValue, bindNumberMasks } = helpers;

            const voucherTable = helpers.createTableController({
                perPage: 10,
                defaultSortKey: 'name',
                search: (product, query) => [
                    product.name,
                    product.code,
                    product.category,
                    product.provider,
                    product.variant || '',
                    product.serial_number || '',
                ].some((val) => String(val || '').toLowerCase().includes(query)),
                sorters: {
                    code: (p) => p.code,
                    name: (p) => p.name,
                    category: (p) => p.category || p.provider || '',
                    price: (p) => Number(p.price || 0),
                    cost_price: (p) => Number(p.cost_price || 0),
                    stock: (p) => Number(p.stock || 0),
                },
            });

            function imageCell(product) {
                return product.image_url
                    ? `<img src="${product.image_url}" alt="${product.name}" class="h-9 w-9 rounded-[6px] object-cover border border-[#d7e0eb]">`
                    : `<div class="flex h-9 w-9 items-center justify-center rounded-[6px] border border-dashed border-[#d7e0eb] text-[10px] text-[color:var(--muted)]">VCR</div>`;
            }

            function previewImage(url) {
                $('#voucherMasterImagePreview').html(url
                    ? `<img src="${url}" alt="Preview" class="h-14 w-14 rounded-[6px] object-cover border border-[#d7e0eb]">`
                    : 'Belum ada gambar dipilih.'
                );
            }

            function generateCode() {
                const prefix = 'VCR-';
                const cat = ($('#voucher_master_category').val() || 'GEN').replace(/[^a-zA-Z0-9]/g, '').substring(0, 4).toUpperCase();
                const rand = Math.floor(1000 + Math.random() * 9000);
                $('#voucher_master_code').val(`${prefix}${cat}-${rand}`);
            }

            $('#btnGenVoucherCode').on('click', generateCode);

            function resetForm() {
                $('#voucherMasterForm')[0].reset();
                $('#voucher_master_product_id').val('');
                $('#voucher_master_unit').val('pcs');
                setMaskedNumberValue('#voucher_master_stock', 0);
                setMaskedNumberValue('#voucher_master_minimum_stock', 0);
                setMaskedNumberValue('#voucher_master_price', 0);
                $('#voucher_master_cost_price').val('');
                previewImage(null);
                $('#initialStockSection').show();
                $('input[name="voucher_wallets[]"]').prop('checked', false);
                $('#voucherMasterSubmit').html('<span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span><span>Simpan Produk Voucher</span>');
            }

            $('#voucherMasterReset').on('click', resetForm);

            function drawTable(meta) {
                $('#voucherMasterTable').html(meta.rows.length ? meta.rows.map((product) => {
                    const serialCount = product.available_serials_count || 0;
                    return `
                        <tr>
                            <td>${imageCell(product)}</td>
                            <td class="font-semibold font-mono">${product.code}</td>
                            <td><span class="badge badge-info">${product.category || product.provider || '-'}</span></td>
                            <td>
                                <div class="font-medium">${product.name}</div>
                                <div class="text-[11px] text-[color:var(--muted)]">${product.variant || '-'} · ${product.unit}</div>
                            </td>
                            <td class="font-semibold">${money(product.price)}</td>
                            <td>${money(product.cost_price)}</td>
                            <td>
                                <span class="badge ${Number(product.stock) <= Number(product.minimum_stock) ? 'badge-warning' : 'badge-ok'}">${product.stock} ${product.unit}</span>
                            </td>
                            <td>
                                ${serialCount > 0
                                    ? `<span class="badge badge-ok">${serialCount} SN</span>`
                                    : `<span class="badge badge-danger-soft">0</span>`}
                            </td>
                            <td>
                                <button class="btn btn-secondary py-1 px-2 edit-voucher" type="button" data-id="${product.id}">
                                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="m4 20 4.5-1 9-9-3.5-3.5-9 9L4 20z"/><path d="M13.5 6.5 17 10"/></svg></span>
                                    <span>Edit</span>
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('') : '<tr><td colspan="9" class="text-center text-[color:var(--muted)] py-4">Belum ada produk voucher.</td></tr>');

                voucherTable.syncSortUi('#voucherMasterTableWrap thead');
                voucherTable.renderFooter('#voucherMasterPagination', meta);
            }

            function renderWallets() {
                const container = $('#voucherWalletCheckboxes');
                if (!state.wallets.length) {
                    container.html('<span class="text-[color:var(--muted)]">Belum ada dompet server terdaftar.</span>');
                    return;
                }
                container.html(state.wallets.map((w) => `
                    <label class="flex items-center gap-1.5 cursor-pointer bg-white px-2 py-1 rounded border border-[#e2e8f0]">
                        <input type="checkbox" name="voucher_wallets[]" value="${w.id}" class="rounded text-[color:var(--accent)]">
                        <span>${w.name} (${money(w.balance)})</span>
                    </label>
                `).join(''));
            }

            function isVoucherProduct(product) {
                const type = String(product.product_type || '').toLowerCase();
                const cat = String(product.category || '').toLowerCase();
                const name = String(product.name || '').toLowerCase();

                return type === 'voucher'
                    || cat.includes('voucher')
                    || name.includes('voucher');
            }

            function renderProducts(allProducts) {
                state.products = (allProducts || []).filter(isVoucherProduct);
                $('#voucherMasterTotalText').text(`${state.products.length} produk voucher terdaftar`);
                voucherTable.setData(state.products);
                voucherTable.setQuery($('#voucherMasterSearch').val());
                drawTable(voucherTable.pagedRows());
            }

            function loadData() {
                $.get(window.posConfig.dashboardUrl)
                    .done((response) => {
                        renderProducts(response.products || []);
                    })
                    .fail(() => showAlert('Gagal memuat master produk voucher.', 'error'));

                // Load wallets from voucher dashboard
                $.get(@json(route('ajax.voucher.dashboard')))
                    .done((response) => {
                        state.wallets = response.wallets || [];
                        renderWallets();
                    });
            }

            $('#voucherMasterSearch').on('input', function () {
                voucherTable.setQuery($(this).val());
                drawTable(voucherTable.pagedRows());
            });

            $(document).on('click', '#voucherMasterPagination .page-btn', function () {
                voucherTable.setPage(Number($(this).data('page')));
                drawTable(voucherTable.pagedRows());
            });

            $(document).on('click', '#voucherMasterTableWrap thead [data-sort]', function () {
                voucherTable.setSort($(this).data('sort'));
                drawTable(voucherTable.pagedRows());
            });

            // Edit product
            $(document).on('click', '.edit-voucher', function () {
                const id = Number($(this).data('id'));
                const product = state.products.find((p) => p.id === id);
                if (!product) return;

                $('#voucher_master_product_id').val(product.id);
                $('#voucher_master_code').val(product.code);
                $('#voucher_master_category').val(product.category || product.provider || '');
                $('#voucher_master_name').val(product.name);
                $('#voucher_master_variant').val(product.variant || '');
                $('#voucher_master_unit').val(product.unit || 'pcs');
                setMaskedNumberValue('#voucher_master_minimum_stock', product.minimum_stock || 0);
                setMaskedNumberValue('#voucher_master_price', product.price || 0);

                if (Number(product.cost_price || 0) > 0) {
                    setMaskedNumberValue('#voucher_master_cost_price', product.cost_price || 0);
                } else {
                    $('#voucher_master_cost_price').val('');
                }

                $('#initialStockSection').hide();
                previewImage(product.image_url);

                // Set wallet checkboxes
                $('input[name="voucher_wallets[]"]').prop('checked', false);
                const sourceWalletIds = product.source_wallet_ids || [];
                sourceWalletIds.forEach((wid) => {
                    $(`input[name="voucher_wallets[]"][value="${wid}"]`).prop('checked', true);
                });

                $('#voucherMasterSubmit').html('<span class="btn-icon"><svg viewBox="0 0 24 24"><path d="m4 20 4.5-1 9-9-3.5-3.5-9 9L4 20z"/><path d="M13.5 6.5 17 10"/></svg></span><span>Update Produk Voucher</span>');
                $('html, body').animate({ scrollTop: $('#voucherMasterForm').offset().top - 20 }, 300);
            });

            // Form Submit
            $('#voucherMasterForm').on('submit', function (event) {
                event.preventDefault();

                const productId = $('#voucher_master_product_id').val();
                const url = productId ? updateProductUrl(productId) : window.posConfig.storeProductUrl;
                const formData = new FormData();

                formData.append('product_type', 'voucher');
                formData.append('code', $('#voucher_master_code').val().trim());
                formData.append('name', $('#voucher_master_name').val().trim());
                formData.append('category', $('#voucher_master_category').val().trim());
                formData.append('variant', $('#voucher_master_variant').val().trim());
                formData.append('unit', $('#voucher_master_unit').val().trim() || 'pcs');
                formData.append('price', maskedNumberValue('#voucher_master_price'));
                formData.append('minimum_stock', maskedNumberValue('#voucher_master_minimum_stock'));

                if (!productId) {
                    const initialStock = maskedNumberValue('#voucher_master_stock');
                    formData.append('stock', initialStock);
                    const initialSn = $('#voucher_master_serial_number').val().trim();
                    if (initialSn) {
                        formData.append('serial_number', initialSn);
                    }
                }

                if ($('#voucher_master_cost_price').val().trim() !== '') {
                    formData.append('cost_price', maskedNumberValue('#voucher_master_cost_price'));
                }

                if ($('#voucher_master_image')[0].files[0]) {
                    formData.append('image', $('#voucher_master_image')[0].files[0]);
                }

                $('input[name="voucher_wallets[]"]:checked').each(function () {
                    formData.append('wallet_ids[]', $(this).val());
                });

                if (productId) {
                    formData.append('_method', 'PUT');
                }

                const $btn = $('#voucherMasterSubmit');
                $btn.prop('disabled', true).addClass('opacity-75');

                $.ajax({
                    url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                }).done((response) => {
                    showAlert(response.message || 'Produk voucher berhasil disimpan.');
                    resetForm();
                    loadData();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal menyimpan produk voucher.', 'error');
                }).always(() => {
                    $btn.prop('disabled', false).removeClass('opacity-75');
                });
            });

            bindNumberMasks(document);
            resetForm();
            loadData();
        });
    </script>
@endsection
