@extends('layouts.app')

@section('content')
    <section class="pane">
        <div class="toolbar flex items-center justify-between px-4 py-3">
            <div>
                <h1 class="font-semibold">Report</h1>
                <p class="text-[color:var(--muted)]">Ringkasan penjualan harian dan produk yang butuh perhatian.</p>
            </div>
        </div>

        <div class="grid gap-4 p-4">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4" id="summaryCards"></div>

            <div class="grid gap-4 xl:grid-cols-2">
                <div class="list-panel p-3">
                    <div class="mb-3 text-sm font-semibold">Transaksi Terakhir</div>
                    <div id="recentSalesList" class="space-y-2"></div>
                </div>

                <div class="list-panel p-3">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div class="text-sm font-semibold">Stok Menipis</div>
                        <input id="lowStockSearch" class="field max-w-xs" type="text" placeholder="Filter produk">
                    </div>
                    <div class="table-wrap">
                        <table id="lowStockMainTable" class="table-grid min-w-full text-sm">
                            <thead class="text-left">
                                <tr>
                                    <th data-sort="code">Kode</th>
                                    <th data-sort="name">Produk</th>
                                    <th data-sort="stock">Stok</th>
                                </tr>
                            </thead>
                            <tbody id="lowStockTable"></tbody>
                        </table>
                    </div>
                    <div id="lowStockPagination"></div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        window.runAppPage(function ($, helpers) {
            const { money, showAlert } = helpers;
            const lowStockTable = helpers.createTableController({
                perPage: 6,
                defaultSortKey: 'stock',
                defaultSortDir: 'asc',
                search: (product, query) => product.name.toLowerCase().includes(query) || product.code.toLowerCase().includes(query),
                sorters: {
                    code: (product) => product.code,
                    name: (product) => product.name,
                    stock: (product) => Number(product.stock),
                },
            });

            function renderSummary(summary) {
                const items = [
                    ['Total Produk', summary.total_products],
                    ['Stok Menipis', summary.low_stock_count],
                    ['Penjualan Hari Ini', money(summary.today_sales_total)],
                    ['Pengeluaran Hari Ini', money(summary.today_expenses_total)],
                    ['Transaksi Hari Ini', summary.today_transactions],
                ];

                $('#summaryCards').html(items.map(([label, value]) => `
                    <div class="stat-box p-3">
                        <div class="text-[11px] uppercase tracking-[0.14em] text-[color:var(--muted)]">${label}</div>
                        <div class="mt-2 text-[22px] font-semibold">${value}</div>
                    </div>
                `).join(''));
            }

            function renderSales(items) {
                $('#recentSalesList').html(items.length ? items.map((item) => `
                    <div class="border border-[#c9c4bb] bg-white px-3 py-2">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-semibold">${item.invoice_number}</div>
                                <div class="text-[11px] text-[color:var(--muted)]">${item.customer_name || 'Pelanggan umum'}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold">${money(item.total)}</div>
                                <div class="text-[11px] text-[color:var(--muted)]">${new Date(item.sale_date).toLocaleString('id-ID')}</div>
                            </div>
                        </div>
                    </div>
                `).join('') : '<div class="text-[color:var(--muted)]">Belum ada transaksi.</div>');
            }

            function renderLowStock(products) {
                const filtered = products.filter((product) => Number(product.stock) <= Number(product.minimum_stock));
                lowStockTable.setData(filtered);
                lowStockTable.setQuery($('#lowStockSearch').val());
                const meta = lowStockTable.pagedRows();

                $('#lowStockTable').html(meta.rows.length ? meta.rows.map((product) => `
                    <tr>
                        <td class="font-semibold">${product.code}</td>
                        <td>${product.name}</td>
                        <td><span class="badge badge-warning">${product.stock} ${product.unit}</span></td>
                    </tr>
                `).join('') : '<tr><td colspan="3" class="text-center text-[color:var(--muted)]">Semua stok aman.</td></tr>');
                lowStockTable.syncSortUi('#lowStockMainTable thead');
                lowStockTable.renderFooter('#lowStockPagination', meta);
            }

            $('#lowStockSearch').on('input', function () {
                $.get(window.posConfig.dashboardUrl)
                    .done((response) => renderLowStock(response.products))
                    .fail(() => showAlert('Gagal memuat report.', 'error'));
            });

            $(document).on('click', '#lowStockMainTable thead [data-sort]', function () {
                lowStockTable.toggleSort($(this).data('sort'));
                const meta = lowStockTable.pagedRows();
                $('#lowStockTable').html(meta.rows.length ? meta.rows.map((product) => `
                    <tr>
                        <td class="font-semibold">${product.code}</td>
                        <td>${product.name}</td>
                        <td><span class="badge badge-warning">${product.stock} ${product.unit}</span></td>
                    </tr>
                `).join('') : '<tr><td colspan="3" class="text-center text-[color:var(--muted)]">Semua stok aman.</td></tr>');
                lowStockTable.syncSortUi('#lowStockMainTable thead');
                lowStockTable.renderFooter('#lowStockPagination', meta);
            });

            $(document).on('click', '#lowStockPagination .page-btn', function () {
                lowStockTable.setPage(Number($(this).data('page')));
                const meta = lowStockTable.pagedRows();
                $('#lowStockTable').html(meta.rows.length ? meta.rows.map((product) => `
                    <tr>
                        <td class="font-semibold">${product.code}</td>
                        <td>${product.name}</td>
                        <td><span class="badge badge-warning">${product.stock} ${product.unit}</span></td>
                    </tr>
                `).join('') : '<tr><td colspan="3" class="text-center text-[color:var(--muted)]">Semua stok aman.</td></tr>');
                lowStockTable.syncSortUi('#lowStockMainTable thead');
                lowStockTable.renderFooter('#lowStockPagination', meta);
            });

            $.get(window.posConfig.dashboardUrl)
                .done((response) => {
                    renderSummary(response.summary);
                    renderSales(response.recent_sales);
                    renderLowStock(response.products);
                })
                .fail(() => showAlert('Gagal memuat report.', 'error'));
        });
    </script>
@endsection
