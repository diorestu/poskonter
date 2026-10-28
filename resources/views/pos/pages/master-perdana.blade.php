@extends('layouts.app')

@section('content')
    <section class="space-y-4">
        <div class="pane">
            <div class="toolbar flex items-center justify-between px-4 py-3">
                <div>
                    <h1 class="font-semibold text-base">Master Produk Kartu Perdana</h1>
                    <p class="text-xs text-[color:var(--muted)]">Kelola katalog produk kartu perdana fisik, nama operator, kuota/paket, harga modal, harga jual, stok, dan nomor seri/ICCID.</p>
                </div>
                <a href="{{ route('perdana.index') }}" class="btn btn-secondary text-xs">
                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></span>
                    <span>Halaman Penjualan Perdana</span>
                </a>
            </div>
            <div class="pane-body p-4">
                <div class="product-master-layout">
                    <!-- Form Input Produk Kartu Perdana -->
                    <form id="perdanaMasterForm" class="master-card master-card-form p-3.5 space-y-3">
                        <input type="hidden" id="perdana_master_product_id">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div class="text-sm font-bold text-slate-800" id="perdanaFormHeading">Form Tambah Produk Kartu Perdana</div>
                            <span class="badge badge-info text-[10px]" id="perdanaModeBadge">Mode Baru</span>
                        </div>

                        <div class="space-y-3">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-800">Kode Produk <span class="text-red-500">*</span></label>
                                    <div class="flex gap-1.5">
                                        <input class="field text-xs font-mono" type="text" id="perdana_master_code" placeholder="PRD-TSEL-14GB" required>
                                        <button class="btn btn-secondary py-1 px-2 text-xs font-bold" type="button" id="btnGenPerdanaCode" title="Buat kode acak otomatis">Auto</button>
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-800">Operator / Provider <span class="text-red-500">*</span></label>
                                    <input class="field text-xs" list="perdanaProviderDatalist" id="perdana_master_category" placeholder="Pilih atau ketik operator" required>
                                    <datalist id="perdanaProviderDatalist">
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
                                <input class="field text-xs" type="text" id="perdana_master_name" placeholder="Contoh: Perdana Telkomsel Kuota 14GB" required>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-800">Varian / Kuota / Masa Aktif</label>
                                    <input class="field text-xs" type="text" id="perdana_master_variant" placeholder="Contoh: 14 GB / 30 Hari">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-800">Satuan <span class="text-red-500">*</span></label>
                                    <input class="field text-xs" type="text" id="perdana_master_unit" value="pcs" required>
                                </div>
                            </div>

                            <!-- Harga Jual & Harga Modal -->
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-800">Harga Modal (Harga Beli) <span class="text-red-500">*</span></label>
                                    <input class="field text-xs font-semibold" type="text" inputmode="numeric" data-number-mask id="perdana_master_cost_price" placeholder="Contoh: 28.000" required>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-800">Harga Jual <span class="text-red-500">*</span></label>
                                    <input class="field text-xs font-bold text-emerald-800" type="text" inputmode="numeric" data-number-mask id="perdana_master_price" placeholder="Contoh: 35.000" required>
                                </div>
                            </div>

                            <!-- Indikator Margin Laba -->
                            <div class="rounded-lg border border-slate-200 bg-slate-50/70 p-2.5 text-xs flex items-center justify-between">
                                <span class="text-slate-600 font-medium">Estimasi Margin / Laba:</span>
                                <strong class="text-emerald-700 font-bold" id="perdanaMarginPreview">Rp 0 (0%)</strong>
                            </div>

                            <!-- Stok Awal Section (Hanya tampil saat create) -->
                            <div id="initialPerdanaStockSection" class="rounded-lg border border-[#e2e8f0] bg-[#f8fafc] p-2.5 space-y-2">
                                <div class="text-[11px] font-bold text-[color:var(--muted)] uppercase tracking-wider">Stok Awal Fisik (Opsional)</div>
                                <div class="grid gap-2 grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-medium">Stok Awal (pcs)</label>
                                        <input class="field text-xs font-bold text-center" type="text" inputmode="numeric" data-number-mask id="perdana_master_stock" value="0">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium">Stok Minimum (pcs)</label>
                                        <input class="field text-xs font-bold text-center" type="text" inputmode="numeric" data-number-mask id="perdana_master_minimum_stock" value="2">
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium">Nomor Seri / ICCID / Barcode Awal (Opsional)</label>
                                    <input class="field text-xs font-mono" type="text" id="perdana_master_serial_number" placeholder="Nomor barcode / ICCID kartu perdana">
                                    <div class="mt-0.5 text-[10px] text-[color:var(--muted)]">Bisa langsung ditembak scanner barcode. Untuk input banyak serial sekaligus gunakan menu Stock.</div>
                                </div>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium">Gambar Produk (Opsional)</label>
                                <input class="field text-xs" type="file" id="perdana_master_image" accept="image/*">
                                <div id="perdanaMasterImagePreview" class="mt-1.5 text-[11px] text-[color:var(--muted)]">Belum ada gambar dipilih.</div>
                            </div>

                            <div class="flex gap-2 pt-2">
                                <button class="btn btn-primary flex-1 font-bold bg-emerald-600 hover:bg-emerald-700 border-emerald-600 text-white" type="submit" id="perdanaMasterSubmit">
                                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                                    <span>Simpan Produk Kartu Perdana</span>
                                </button>
                                <button class="btn btn-secondary text-xs" type="button" id="perdanaMasterReset">
                                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg></span>
                                    <span>Reset</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Daftar Produk Kartu Perdana -->
                    <div class="master-card master-card-table p-3.5 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-bold text-slate-800">Daftar Produk Kartu Perdana</div>
                                <div class="text-[11px] text-[color:var(--muted)]" id="perdanaMasterTotalText">Memuat data kartu perdana...</div>
                            </div>
                            <input id="perdanaMasterSearch" class="field max-w-xs text-xs py-1.5" type="text" placeholder="Cari nama, kode, operator, SN...">
                        </div>

                        <!-- Filter Operator Chips -->
                        <div class="flex flex-wrap gap-1.5" id="perdanaFilterChips">
                            <button type="button" class="perdana-filter-chip active py-1 px-2.5 rounded-md text-xs font-semibold bg-emerald-600 text-white border border-emerald-600" data-filter="all">Semua Operator</button>
                            <button type="button" class="perdana-filter-chip py-1 px-2.5 rounded-md text-xs font-medium text-slate-600 hover:bg-slate-100 border border-slate-200" data-filter="Telkomsel">Telkomsel</button>
                            <button type="button" class="perdana-filter-chip py-1 px-2.5 rounded-md text-xs font-medium text-slate-600 hover:bg-slate-100 border border-slate-200" data-filter="Indosat">Indosat</button>
                            <button type="button" class="perdana-filter-chip py-1 px-2.5 rounded-md text-xs font-medium text-slate-600 hover:bg-slate-100 border border-slate-200" data-filter="XL">XL</button>
                            <button type="button" class="perdana-filter-chip py-1 px-2.5 rounded-md text-xs font-medium text-slate-600 hover:bg-slate-100 border border-slate-200" data-filter="Axis">AXIS</button>
                            <button type="button" class="perdana-filter-chip py-1 px-2.5 rounded-md text-xs font-medium text-slate-600 hover:bg-slate-100 border border-slate-200" data-filter="Tri">Tri (3)</button>
                            <button type="button" class="perdana-filter-chip py-1 px-2.5 rounded-md text-xs font-medium text-slate-600 hover:bg-slate-100 border border-slate-200" data-filter="Smartfren">Smartfren</button>
                            <button type="button" class="perdana-filter-chip py-1 px-2.5 rounded-md text-xs font-medium text-slate-600 hover:bg-slate-100 border border-slate-200" data-filter="by.U">by.U</button>
                        </div>

                        <div class="table-wrap rounded-lg border border-[#e2e8f0] overflow-x-auto">
                            <table id="perdanaMasterTableWrap" class="table-grid min-w-full text-xs">
                                <thead class="text-left bg-slate-50 sticky top-0">
                                    <tr>
                                        <th width="42">Img</th>
                                        <th data-sort="code">Kode</th>
                                        <th data-sort="category">Operator</th>
                                        <th data-sort="name">Nama Produk & Kuota</th>
                                        <th data-sort="cost_price" class="text-right">Modal</th>
                                        <th data-sort="price" class="text-right">Harga Jual</th>
                                        <th data-sort="stock" class="text-center">Stok</th>
                                        <th class="text-center">SN Siap</th>
                                        <th width="80" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="perdanaMasterTable">
                                    <tr>
                                        <td colspan="9" class="text-center py-6 text-slate-400">Memuat katalog kartu perdana...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="perdanaMasterPagination"></div>
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
                activeFilter: 'all',
            };

            const { money, showAlert, updateProductUrl, maskedNumberValue, setMaskedNumberValue, formatNumber, parseNumber, bindNumberMasks } = helpers;

            function isPerdanaProduct(p) {
                if (p.product_type === 'pulsa') return false;
                if (p.product_type === 'perdana') return true;
                const type = (p.product_type || '').toLowerCase();
                const cat = (p.category || '').toLowerCase();
                const name = (p.name || '').toLowerCase();
                return type.includes('perdana') || cat.includes('perdana') || name.includes('perdana') || name.includes('sim card');
            }

            const perdanaTable = helpers.createTableController({
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
                    cost_price: (p) => Number(p.cost_price || 0),
                    price: (p) => Number(p.price || 0),
                    stock: (p) => Number(p.stock || 0),
                },
            });

            function imageCell(product) {
                return product.image_url
                    ? `<img src="${product.image_url}" alt="${product.name}" class="h-8 w-8 rounded-[6px] object-cover border border-[#d7e0eb]">`
                    : `<div class="flex h-8 w-8 items-center justify-center rounded-[6px] border border-dashed border-[#d7e0eb] text-[9px] font-bold text-emerald-700 bg-emerald-50">SIM</div>`;
            }

            function previewImage(url) {
                $('#perdanaMasterImagePreview').html(url
                    ? `<img src="${url}" alt="Preview" class="h-12 w-12 rounded-[6px] object-cover border border-[#d7e0eb]">`
                    : 'Belum ada gambar dipilih.'
                );
            }

            function generateCode() {
                const prefix = 'PRD-';
                const cat = ($('#perdana_master_category').val() || 'GEN').replace(/[^a-zA-Z0-9]/g, '').substring(0, 4).toUpperCase();
                const rand = Math.floor(1000 + Math.random() * 9000);
                $('#perdana_master_code').val(`${prefix}${cat}-${rand}`);
            }

            $('#btnGenPerdanaCode').on('click', generateCode);

            function updateMarginPreview() {
                const cost = maskedNumberValue('#perdana_master_cost_price');
                const price = maskedNumberValue('#perdana_master_price');
                const margin = price - cost;
                const percent = cost > 0 ? Math.round((margin / cost) * 100) : 0;

                if (margin > 0) {
                    $('#perdanaMarginPreview').html(`<span class="text-emerald-700 font-bold">+${money(margin)} (+${percent}%)</span>`);
                } else if (margin < 0) {
                    $('#perdanaMarginPreview').html(`<span class="text-rose-600 font-bold">Rugi ${money(Math.abs(margin))} (${percent}%)</span>`);
                } else {
                    $('#perdanaMarginPreview').html(`<span class="text-slate-500 font-bold">Rp 0 (0%)</span>`);
                }
            }

            $('#perdana_master_cost_price, #perdana_master_price').on('input', updateMarginPreview);

            function resetForm() {
                $('#perdanaMasterForm')[0].reset();
                $('#perdana_master_product_id').val('');
                $('#perdana_master_unit').val('pcs');
                setMaskedNumberValue('#perdana_master_stock', 0);
                setMaskedNumberValue('#perdana_master_minimum_stock', 2);
                setMaskedNumberValue('#perdana_master_cost_price', 0);
                setMaskedNumberValue('#perdana_master_price', 0);
                previewImage(null);
                $('#initialPerdanaStockSection').show();
                $('#perdanaFormHeading').text('Form Tambah Produk Kartu Perdana');
                $('#perdanaModeBadge').text('Mode Baru').removeClass('badge-warning').addClass('badge-info');
                $('#perdanaMasterSubmit').html('<span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span><span>Simpan Produk Kartu Perdana</span>');
                updateMarginPreview();
            }

            $('#perdanaMasterReset').on('click', resetForm);

            function filteredProducts() {
                let list = state.products.filter(isPerdanaProduct);
                if (state.activeFilter !== 'all') {
                    const filterLower = state.activeFilter.toLowerCase();
                    list = list.filter((p) => {
                        const cat = (p.category || '').toLowerCase();
                        const name = (p.name || '').toLowerCase();
                        const prov = (p.provider || '').toLowerCase();
                        return cat.includes(filterLower) || name.includes(filterLower) || prov.includes(filterLower);
                    });
                }
                return list;
            }

            function drawTable(meta) {
                $('#perdanaMasterTable').html(meta.rows.length ? meta.rows.map((product) => {
                    const serialCount = product.available_serials_count || 0;
                    const margin = Number(product.price || 0) - Number(product.cost_price || 0);
                    return `
                        <tr class="hover:bg-slate-50 border-b border-slate-100 transition-all">
                            <td>${imageCell(product)}</td>
                            <td class="font-semibold font-mono text-slate-800">${product.code}</td>
                            <td><span class="badge bg-emerald-100 text-emerald-800 font-semibold text-[10px]">${product.category || product.provider || 'Perdana'}</span></td>
                            <td>
                                <div class="font-bold text-slate-900">${product.name}</div>
                                <div class="text-[11px] text-[color:var(--muted)]">${product.variant || '-'} · ${product.unit}</div>
                            </td>
                            <td class="text-right text-slate-600">${money(product.cost_price)}</td>
                            <td class="text-right font-bold text-emerald-800">${money(product.price)}</td>
                            <td class="text-center">
                                <span class="badge ${Number(product.stock) <= Number(product.minimum_stock) ? 'badge-warning' : 'badge-ok'}">${product.stock} ${product.unit}</span>
                            </td>
                            <td class="text-center">
                                ${serialCount > 0
                                    ? `<span class="badge badge-ok">${serialCount} SN</span>`
                                    : `<span class="badge badge-danger-soft text-[10px]">0</span>`}
                            </td>
                            <td class="text-center whitespace-nowrap">
                                <button class="btn btn-secondary py-1 px-2.5 text-xs font-semibold edit-perdana" type="button" data-id="${product.id}" title="Edit data produk">
                                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="m4 20 4.5-1 9-9-3.5-3.5-9 9L4 20z"/><path d="M13.5 6.5 17 10"/></svg></span>
                                    <span>Edit</span>
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('') : '<tr><td colspan="9" class="text-center py-6 text-[color:var(--muted)]">Belum ada produk kartu perdana yang sesuai.</td></tr>');

                perdanaTable.syncSortUi('#perdanaMasterTableWrap thead');
                perdanaTable.renderFooter('#perdanaMasterPagination', meta);
            }

            function renderProducts() {
                const list = filteredProducts();
                $('#perdanaMasterTotalText').text(`Total ${list.length} produk kartu perdana terdaftar`);
                perdanaTable.setData(list);
                drawTable(perdanaTable.pagedRows());
            }

            function loadData() {
                $.getJSON(window.posConfig.dashboardUrl, { _: Date.now() })
                    .done((response) => {
                        state.products = response.products || [];
                        renderProducts();
                    })
                    .fail(() => showAlert('Gagal memuat data master produk.', 'error'));
            }

            $('#perdanaMasterSearch').on('input', function () {
                perdanaTable.setQuery($(this).val());
                drawTable(perdanaTable.pagedRows());
            });

            $('.perdana-filter-chip').on('click', function () {
                $('.perdana-filter-chip')
                    .removeClass('active bg-emerald-600 text-white border-emerald-600')
                    .addClass('text-slate-600 border-slate-200');
                $(this)
                    .addClass('active bg-emerald-600 text-white border-emerald-600')
                    .removeClass('text-slate-600 border-slate-200');

                state.activeFilter = $(this).data('filter');
                renderProducts();
            });

            $(document).on('click', '#perdanaMasterTableWrap thead [data-sort]', function () {
                perdanaTable.toggleSort($(this).data('sort'));
                drawTable(perdanaTable.pagedRows());
            });

            $(document).on('click', '#perdanaMasterPagination .page-btn', function () {
                if ($(this).prop('disabled') || $(this).attr('disabled')) return;
                const page = Number($(this).data('page'));
                if (!page) return;
                perdanaTable.setPage(page);
                drawTable(perdanaTable.pagedRows());
            });

            $(document).on('click', '.edit-perdana', function () {
                const id = Number($(this).data('id'));
                const product = state.products.find((p) => Number(p.id) === id);
                if (!product) return;

                $('#perdana_master_product_id').val(product.id);
                $('#perdana_master_code').val(product.code);
                $('#perdana_master_category').val(product.category || product.provider || 'Telkomsel');
                $('#perdana_master_name').val(product.name);
                $('#perdana_master_variant').val(product.variant || '');
                $('#perdana_master_unit').val(product.unit || 'pcs');
                setMaskedNumberValue('#perdana_master_cost_price', product.cost_price || 0);
                setMaskedNumberValue('#perdana_master_price', product.price || 0);
                setMaskedNumberValue('#perdana_master_minimum_stock', product.minimum_stock || 0);
                previewImage(product.image_url);

                $('#initialPerdanaStockSection').hide();
                $('#perdanaFormHeading').text(`Edit Produk: ${product.name}`);
                $('#perdanaModeBadge').text('Mode Edit').removeClass('badge-info').addClass('badge-warning');
                $('#perdanaMasterSubmit').html('<span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span><span>Perbarui Produk Perdana</span>');
                updateMarginPreview();

                $('html, body').animate({ scrollTop: $('#perdanaMasterForm').offset().top - 80 }, 250);
                $('#perdana_master_name').focus();
            });

            $('#perdana_master_image').on('change', function () {
                const file = this.files?.[0];
                if (!file) {
                    previewImage(null);
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => previewImage(e.target?.result);
                reader.readAsDataURL(file);
            });

            $('#perdanaMasterForm').on('submit', function (e) {
                e.preventDefault();

                const productId = $('#perdana_master_product_id').val();
                const code = $('#perdana_master_code').val().trim();
                const name = $('#perdana_master_name').val().trim();
                const category = $('#perdana_master_category').val().trim();
                const price = maskedNumberValue('#perdana_master_price');
                const costPrice = maskedNumberValue('#perdana_master_cost_price');

                if (!code || !name) {
                    showAlert('Kode produk dan nama produk wajib diisi.', 'error');
                    return;
                }
                if (!category) {
                    showAlert('Operator / Provider wajib diisi.', 'error');
                    $('#perdana_master_category').focus();
                    return;
                }
                if (price <= 0) {
                    showAlert('Harga jual harus lebih besar dari Rp 0.', 'error');
                    $('#perdana_master_price').focus();
                    return;
                }
                if (costPrice <= 0) {
                    showAlert('Harga modal (harga beli) wajib diisi.', 'error');
                    $('#perdana_master_cost_price').focus();
                    return;
                }

                const formData = new FormData();
                formData.append('code', code);
                formData.append('name', name);
                formData.append('product_type', 'perdana');
                formData.append('category', category);
                formData.append('variant', $('#perdana_master_variant').val().trim());
                formData.append('unit', $('#perdana_master_unit').val().trim() || 'pcs');
                formData.append('price', price);
                formData.append('cost_price', costPrice);
                formData.append('minimum_stock', maskedNumberValue('#perdana_master_minimum_stock'));

                if (!productId) {
                    formData.append('stock', maskedNumberValue('#perdana_master_stock'));
                    const sn = $('#perdana_master_serial_number').val().trim();
                    if (sn) {
                        formData.append('serial_number', sn);
                    }
                }

                const imgFile = $('#perdana_master_image')[0].files?.[0];
                if (imgFile) {
                    formData.append('image', imgFile);
                }

                const url = productId ? updateProductUrl(productId) : window.posConfig.storeProductUrl;
                if (productId) {
                    formData.append('_method', 'PUT');
                }

                const $btn = $('#perdanaMasterSubmit');
                $btn.prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                }).done((response) => {
                    showAlert(response.message || 'Produk kartu perdana berhasil disimpan.');
                    resetForm();
                    loadData();
                }).fail((xhr) => {
                    const msg = xhr.responseJSON?.message || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('<br>') : 'Gagal menyimpan produk kartu perdana.');
                    showAlert(msg, 'error');
                }).always(() => {
                    $btn.prop('disabled', false);
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
