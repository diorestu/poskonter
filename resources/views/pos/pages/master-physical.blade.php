@extends('layouts.app')

@section('content')
    <section class="space-y-4">
        <div class="pane">
            <div class="toolbar flex items-center justify-between px-4 py-3">
                <div>
                    <h1 class="font-semibold">Master Produk Fisik</h1>
                    <p class="text-[color:var(--muted)]">Halaman ini khusus untuk produk barang, stok minimum, harga modal, dan identitas item fisik.</p>
                </div>
            </div>
            <div class="pane-body p-4">
                <div class="product-master-layout">
                    <form id="physicalForm" class="master-card master-card-form p-3">
                    <input type="hidden" id="physical_product_id">
                    <div class="mb-3 text-sm font-semibold">Form Produk Fisik</div>
                    <div class="space-y-3">
                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block font-medium">Kode</label>
                                <input class="field" type="text" id="physical_code" placeholder="BRG-001" required>
                            </div>
                            <div>
                                <label class="mb-1 block font-medium">Kategori</label>
                                <input class="field" type="text" id="physical_category" placeholder="Minuman" required>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Nama Produk</label>
                            <input class="field" type="text" id="physical_name" placeholder="Nama produk" required>
                        </div>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block font-medium">Varian</label>
                                <input class="field" type="text" id="physical_variant" placeholder="Botol 350ml">
                            </div>
                            <div>
                                <label class="mb-1 block font-medium">Serial Number</label>
                                <input class="field" type="text" id="physical_serial_number" placeholder="Opsional">
                            </div>
                        </div>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block font-medium">Satuan</label>
                                <input class="field" type="text" id="physical_unit" placeholder="pcs / botol" required>
                            </div>
                            <div>
                                <label class="mb-1 block font-medium">Stok Minimum</label>
                                <input class="field" type="text" inputmode="numeric" data-number-mask id="physical_minimum_stock" value="0">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Gambar Produk</label>
                            <input class="field" type="file" id="physical_image" accept="image/*">
                            <div id="physicalImagePreview" class="mt-2 text-[11px] text-[color:var(--muted)]">Belum ada gambar dipilih.</div>
                        </div>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block font-medium">Harga Jual</label>
                                <input class="field" type="text" inputmode="numeric" data-number-mask id="physical_price" value="0" required>
                            </div>
                            <div>
                                <label class="mb-1 block font-medium">Harga Modal Awal</label>
                                <input class="field" type="text" inputmode="numeric" data-number-mask id="physical_cost_price" placeholder="Opsional">
                                <div class="mt-1 text-[11px] text-[color:var(--muted)]">Opsional. Modal bisa dicatat lebih akurat saat stok masuk.</div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button class="btn btn-primary flex-1" type="submit" id="physicalSubmit">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                                <span>Simpan Produk Fisik</span>
                            </button>
                            <button class="btn btn-secondary" type="button" id="physicalReset">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg></span>
                                <span>Reset</span>
                            </button>
                        </div>
                    </div>
                    </form>

                    <div class="master-card master-card-table p-3">
                        <div class="master-card-head">
                            <div class="text-sm font-semibold">Daftar Produk Fisik</div>
                            <input id="physicalSearch" class="field max-w-xs" type="text" placeholder="Cari nama / kode / kategori">
                        </div>
                        <div class="table-wrap">
                            <table id="physicalTableWrap" class="table-grid min-w-full text-sm">
                                <thead class="text-left">
                                    <tr>
                                        <th>Img</th>
                                        <th data-sort="code">Kode</th>
                                        <th data-sort="name">Produk</th>
                                        <th data-sort="category">Kategori</th>
                                        <th data-sort="price">Harga</th>
                                        <th data-sort="cost_price">Modal</th>
                                        <th data-sort="stock">Stok</th>
                                        <th width="100">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="physicalTable"></tbody>
                            </table>
                        </div>
                        <div id="physicalPagination"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        window.runAppPage(function ($, helpers) {
            const state = { products: [] };
            const { money, showAlert, updateProductUrl, maskedNumberValue, setMaskedNumberValue, bindNumberMasks, parseNumber, formatNumber } = helpers;

            const physicalTable = helpers.createTableController({
                perPage: 8,
                defaultSortKey: 'name',
                search: (product, query) => [product.name, product.code, product.category, product.variant || '', product.serial_number || ''].some((value) => String(value || '').toLowerCase().includes(query)),
                sorters: {
                    code: (product) => product.code,
                    name: (product) => product.name,
                    category: (product) => product.category,
                    price: (product) => Number(product.price),
                    cost_price: (product) => Number(product.cost_price),
                    stock: (product) => Number(product.stock),
                },
            });

            function imageCell(product) {
                return product.image_url
                    ? `<img src="${product.image_url}" alt="${product.name}" class="h-10 w-10 rounded-[6px] object-cover border border-[#d7e0eb]">`
                    : `<div class="flex h-10 w-10 items-center justify-center rounded-[6px] border border-dashed border-[#d7e0eb] text-[10px] text-[color:var(--muted)]">IMG</div>`;
            }

            function previewImage(url) {
                $('#physicalImagePreview').html(url
                    ? `<img src="${url}" alt="Preview" class="h-16 w-16 rounded-[6px] object-cover border border-[#d7e0eb]">`
                    : 'Belum ada gambar dipilih.'
                );
            }

            function resetForm() {
                $('#physicalForm')[0].reset();
                $('#physical_product_id').val('');
                setMaskedNumberValue('#physical_minimum_stock', 0);
                setMaskedNumberValue('#physical_price', 0);
                $('#physical_cost_price').val('');
                previewImage(null);
                $('#physicalSubmit').html('<span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span><span>Simpan Produk Fisik</span>');
            }

            function drawTable(meta) {
                $('#physicalTable').html(meta.rows.length ? meta.rows.map((product) => `
                    <tr>
                        <td>${imageCell(product)}</td>
                        <td class="font-semibold">${product.code}</td>
                        <td>
                            <div class="font-medium">${product.name}</div>
                            <div class="text-[11px] text-[color:var(--muted)]">${product.variant || '-'} · ${product.unit}</div>
                            ${product.serial_number ? `<div class="text-[11px] text-[color:var(--muted)]">SN: ${product.serial_number}</div>` : ''}
                        </td>
                        <td>${product.category}</td>
                        <td>${money(product.price)}</td>
                        <td>${money(product.cost_price)}</td>
                        <td><span class="badge ${Number(product.stock) <= Number(product.minimum_stock) ? 'badge-warning' : 'badge-ok'}">${product.stock} ${product.unit}</span></td>
                        <td>
                            <button class="btn btn-secondary edit-physical" type="button" data-id="${product.id}">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="m4 20 4.5-1 9-9-3.5-3.5-9 9L4 20z"/><path d="M13.5 6.5 17 10"/></svg></span>
                                <span>Edit</span>
                            </button>
                        </td>
                    </tr>
                `).join('') : '<tr><td colspan="8" class="text-center text-[color:var(--muted)]">Belum ada produk fisik.</td></tr>');
                physicalTable.syncSortUi('#physicalTableWrap thead');
                physicalTable.renderFooter('#physicalPagination', meta);
            }

            function renderProducts(products) {
                state.products = products.filter((product) => product.product_type !== 'pulsa');
                physicalTable.setData(state.products);
                physicalTable.setQuery($('#physicalSearch').val());
                drawTable(physicalTable.pagedRows());
            }

            function loadData() {
                $.get(window.posConfig.dashboardUrl)
                    .done((response) => renderProducts(response.products || []))
                    .fail(() => showAlert('Gagal memuat master produk fisik.', 'error'));
            }

            $('#physicalSearch').on('input', function () {
                physicalTable.setQuery($(this).val());
                drawTable(physicalTable.pagedRows());
            });

            $(document).on('click', '#physicalPagination .page-btn', function () {
                physicalTable.setPage(Number($(this).data('page')));
                drawTable(physicalTable.pagedRows());
            });

            $(document).on('click', '#physicalTableWrap thead [data-sort]', function () {
                physicalTable.toggleSort($(this).data('sort'));
                drawTable(physicalTable.pagedRows());
            });

            $('#physicalReset').on('click', resetForm);

            $('#physical_image').on('change', function () {
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

            $(document).on('click', '.edit-physical', function () {
                const product = state.products.find((item) => item.id === Number($(this).data('id')));
                if (!product) return;

                $('#physical_product_id').val(product.id);
                $('#physical_code').val(product.code);
                $('#physical_category').val(product.category);
                $('#physical_name').val(product.name);
                $('#physical_variant').val(product.variant || '');
                $('#physical_serial_number').val(product.serial_number || '');
                $('#physical_unit').val(product.unit);
                setMaskedNumberValue('#physical_minimum_stock', product.minimum_stock);
                setMaskedNumberValue('#physical_price', product.price);
                if (Number(product.cost_price || 0) > 0) {
                    setMaskedNumberValue('#physical_cost_price', product.cost_price || 0);
                } else {
                    $('#physical_cost_price').val('');
                }
                previewImage(product.image_url);
                $('#physicalSubmit').html('<span class="btn-icon"><svg viewBox="0 0 24 24"><path d="m4 20 4.5-1 9-9-3.5-3.5-9 9L4 20z"/><path d="M13.5 6.5 17 10"/></svg></span><span>Update Produk Fisik</span>');
            });

            $('#physicalForm').on('submit', function (event) {
                event.preventDefault();

                const productId = $('#physical_product_id').val();
                const url = productId ? updateProductUrl(productId) : window.posConfig.storeProductUrl;
                const formData = new FormData();

                formData.append('product_type', 'regular');
                formData.append('code', $('#physical_code').val());
                formData.append('name', $('#physical_name').val());
                formData.append('category', $('#physical_category').val());
                formData.append('variant', $('#physical_variant').val());
                formData.append('serial_number', $('#physical_serial_number').val());
                formData.append('unit', $('#physical_unit').val());
                formData.append('price', maskedNumberValue('#physical_price'));
                formData.append('minimum_stock', maskedNumberValue('#physical_minimum_stock'));

                if ($('#physical_cost_price').val().trim() !== '') {
                    formData.append('cost_price', maskedNumberValue('#physical_cost_price'));
                }

                if ($('#physical_image')[0].files[0]) {
                    formData.append('image', $('#physical_image')[0].files[0]);
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
                    showAlert(xhr.responseJSON?.message || 'Gagal menyimpan produk fisik.', 'error');
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
