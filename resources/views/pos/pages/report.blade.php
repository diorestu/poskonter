@extends('layouts.app')

@section('content')
    <section class="space-y-4">
        <!-- Date Filter & PDF Download Card -->
        <div class="list-panel p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="font-semibold text-lg">Laporan & Rekapitulasi Shift</h1>
                    <p class="text-[color:var(--muted)] text-xs">Pilih rentang tanggal untuk melihat statistik dan mengunduh laporan PDF per kategori & shift.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative flex items-center">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-[#64748b]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
                        </div>
                        <input type="text" id="reportDateRange" class="field py-1.5 pl-8 pr-3 text-xs w-64 bg-white font-medium cursor-pointer" placeholder="Pilih Rentang Tanggal..." readonly>
                    </div>
                    <button type="button" class="btn btn-secondary text-xs py-1.5" id="btnFilterToday">Hari Ini</button>
                    <button type="button" class="btn btn-secondary text-xs py-1.5" id="btnFilterThisMonth">Bulan Ini</button>
                    <a href="#" target="_blank" class="btn btn-secondary text-xs py-1.5" id="btnPreviewPdf">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></span>
                        <span>Lihat PDF</span>
                    </a>
                    <a href="#" class="btn btn-primary text-xs py-1.5 font-medium" id="btnDownloadPdf">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg></span>
                        <span>Unduh PDF</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid gap-4">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5" id="summaryCards"></div>

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

            let startDate = new Date();
            startDate.setDate(1);
            let startDateStr = startDate.toISOString().split('T')[0];
            let endDateStr = new Date().toISOString().split('T')[0];

            function updatePdfLinks(start, end) {
                const baseUrl = @json(route('report.pdf'));
                $('#btnDownloadPdf').attr('href', `${baseUrl}?start_date=${start}&end_date=${end}&download=1`);
                $('#btnPreviewPdf').attr('href', `${baseUrl}?start_date=${start}&end_date=${end}&view=preview`);
            }

            const fp = window.flatpickr ? window.flatpickr('#reportDateRange', {
                mode: 'range',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd M Y',
                defaultDate: [startDateStr, endDateStr],
                locale: window.flatpickrIndonesian || 'default',
                onChange: function (selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        const s = instance.formatDate(selectedDates[0], 'Y-m-d');
                        const e = instance.formatDate(selectedDates[1], 'Y-m-d');
                        startDateStr = s;
                        endDateStr = e;
                        updatePdfLinks(s, e);
                    } else if (selectedDates.length === 1) {
                        const s = instance.formatDate(selectedDates[0], 'Y-m-d');
                        startDateStr = s;
                        endDateStr = s;
                        updatePdfLinks(s, s);
                    }
                }
            }) : null;

            $('#btnFilterToday').on('click', function () {
                const today = new Date().toISOString().split('T')[0];
                startDateStr = today;
                endDateStr = today;
                if (fp) fp.setDate([today, today], true);
                updatePdfLinks(today, today);
            });

            $('#btnFilterThisMonth').on('click', function () {
                const now = new Date();
                const firstDay = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
                const today = now.toISOString().split('T')[0];
                startDateStr = firstDay;
                endDateStr = today;
                if (fp) fp.setDate([firstDay, today], true);
                updatePdfLinks(firstDay, today);
            });

            updatePdfLinks(startDateStr, endDateStr);

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
