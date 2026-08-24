@extends('layouts.app')

@section('content')
    <section class="pane">
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
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <label class="block font-medium">Detail Item Masuk</label>
                                <button class="btn btn-secondary py-1 px-2.5 min-h-[30px]" type="button" id="addStockRow">
                                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg></span>
                                    <span>Tambah Baris</span>
                                </button>
                            </div>
                            <div id="stockItemRows" class="space-y-2"></div>
                            <div class="mt-2 text-[11px] text-[color:var(--muted)]">Satu baris mewakili satu unit stok masuk. Serial/IMEI opsional jika produk non-serial.</div>
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

                <div class="list-panel p-4">
                    <div class="mb-3 text-xs font-semibold uppercase tracking-wider text-[color:var(--muted)]">Pergerakan Stok Terakhir</div>
                    <div id="stockMovementList" class="space-y-2"></div>
                </div>
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
                            </tr>
                        </thead>
                        <tbody id="stockTable"></tbody>
                    </table>
                </div>
                <div id="stockPagination"></div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        window.runAppPage(function ($, helpers) {
            const state = { products: [] };
            const { money, showAlert, stockUrl, bindNumberMasks, parseNumber, formatNumber, maskedNumberValue } = helpers;

            const stockTable = helpers.createTableController({
                perPage: 10,
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
                            <div class="text-xs font-semibold uppercase tracking-[0.12em] text-[color:var(--muted)]">Item ${index + 1}</div>
                            <button class="btn btn-secondary remove-stock-row" type="button">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg></span>
                                <span>Hapus</span>
                            </button>
                        </div>
                        <div class="grid gap-3">
                            <div>
                                <label class="mb-1 block font-medium">Serial Number</label>
                                <input class="field stock-serial-number" type="text" value="${values.serial_number || ''}" placeholder="Opsional">
                            </div>
                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block font-medium">Expiry Date</label>
                                    <input class="field stock-expiry-date" type="date" value="${values.expiry_date || ''}">
                                </div>
                                <div>
                                    <label class="mb-1 block font-medium">Harga Modal</label>
                                    <input class="field stock-cost-price" type="text" inputmode="numeric" data-number-mask value="${values.cost_price || ''}" placeholder="Wajib diisi">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            function reindexRows() {
                $('#stockItemRows .stock-item-row').each(function (index) {
                    $(this).attr('data-index', index);
                    $(this).find('.text-xs').text(`Item ${index + 1}`);
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
                    return {
                        serial_number: $row.find('.stock-serial-number').val().trim(),
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
                    </tr>
                `).join('') : '<tr><td colspan="5" class="text-center text-[color:var(--muted)]">Tidak ada data.</td></tr>');
                stockTable.syncSortUi('#stockMainTable thead');
                stockTable.renderFooter('#stockPagination', meta);
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

            function renderMovements(items) {
                const movements = items || [];
                $('#stockMovementList').html(movements.length ? movements.map((item) => {
                    const createdDate = item.created_at ? new Date(item.created_at).toLocaleString('id-ID') : '-';
                    const expiryDate = item.expiry_date ? `Expiry ${new Date(item.expiry_date).toLocaleDateString('id-ID')}` : 'Tanpa expiry';
                    return `
                        <div class="border border-[#d8e1ec] rounded-xl bg-white px-3 py-2">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="font-semibold">${item.product?.name ?? '-'}</div>
                                    <div class="text-[11px] text-[color:var(--muted)]">${item.note ?? '-'}</div>
                                    <div class="text-[11px] text-[color:var(--muted)]">${item.serial_number ? `SN ${item.serial_number}` : 'Tanpa serial'} · ${expiryDate} · Modal ${money(item.cost_price || 0)}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold">${item.type === 'in' ? '+' : '-'}${item.quantity}</div>
                                    <div class="text-[11px] text-[color:var(--muted)]">${createdDate}</div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('') : '<div class="text-[color:var(--muted)]">Belum ada riwayat stok.</div>');
            }

            function loadData() {
                $.getJSON(window.posConfig.dashboardUrl, { _: Date.now() })
                    .done((response) => {
                        try {
                            renderProducts(response.products || []);
                            renderMovements((response.stock_movements || []).filter((item) => item && item.product));
                        } catch (err) {
                            console.error('Error rendering stock data:', err);
                        }
                    })
                    .fail((xhr, status, error) => {
                        console.error('AJAX Error Stock Dashboard:', status, error, xhr);
                        showAlert('Gagal memuat data stok.', 'error');
                    });
            }

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
                stockTable.setPage(Number($(this).data('page')));
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

            $(document).on('input', '[data-number-mask]', function () {
                $(this).val(formatNumber(parseNumber($(this).val())));
            });

            bindNumberMasks(document);
            resetForm();
            loadData();
        });
    </script>
@endsection
