@extends('layouts.app')

@section('content')
    <section class="pane">
        <div class="toolbar flex items-center justify-between px-4 py-3">
            <div>
                <h1 class="font-semibold">Riwayat Penjualan</h1>
                <p class="text-[color:var(--muted)]">Pantau transaksi pulsa, tandai sukses, atau cancel saat terjadi gangguan.</p>
            </div>
        </div>

        <div class="p-4">
            <div class="list-panel p-3">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div class="text-sm font-semibold">Riwayat Pulsa Elektrik</div>
                    <input id="historySearch" class="field max-w-xs" type="text" placeholder="Cari ref / provider / produk / nomor tujuan">
                </div>
                <div class="table-wrap">
                    <table id="historyTable" class="table-grid min-w-full text-sm">
                        <thead class="text-left">
                            <tr>
                                <th data-sort="reference_number">Ref</th>
                                <th data-sort="sale_date">Tanggal</th>
                                <th data-sort="provider">Provider</th>
                                <th data-sort="product">Produk</th>
                                <th data-sort="target_number">Nomor Tujuan</th>
                                <th data-sort="serial_number">Serial</th>
                                <th data-sort="wallet">Dompet</th>
                                <th data-sort="price">Harga</th>
                                <th data-sort="status">Status</th>
                                <th width="180">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="historyBody"></tbody>
                    </table>
                </div>
                <div id="historyPagination"></div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        window.runAppPage(function ($, helpers) {
            const { money, showAlert } = helpers;
            const urls = {
                dashboard: @json(route('ajax.pulsa.dashboard')),
                success: @json(url('/ajax/pulsa/sales/__ID__/success')),
                cancel: @json(url('/ajax/pulsa/sales/__ID__/cancel')),
            };

            const historyTable = helpers.createTableController({
                perPage: 12,
                defaultSortKey: 'provider',
                defaultSortDir: 'asc',
                search: (item, query) => [item.reference_number, item.product?.name || '', item.product?.category || '', item.target_number, item.serial_number || '', item.wallet?.name || '', item.customer_name || ''].some((value) => String(value || '').toLowerCase().includes(query)),
                sorters: {
                    reference_number: (item) => item.reference_number,
                    sale_date: (item) => item.sale_date,
                    provider: (item) => item.product?.category || '',
                    product: (item) => item.product?.name || '',
                    target_number: (item) => item.target_number,
                    serial_number: (item) => item.serial_number || '',
                    wallet: (item) => item.wallet?.name || '',
                    price: (item) => Number(item.price),
                    status: (item) => item.status,
                },
            });

            function statusBadge(status) {
                if (status === 'success') return '<span class="badge badge-ok">Sukses</span>';
                if (status === 'cancelled') return '<span class="badge badge-danger-soft">Cancelled</span>';
                return '<span class="badge badge-warning">Pending</span>';
            }

            function draw(meta) {
                $('#historyBody').html(meta.rows.length ? meta.rows.map((item) => `
                    <tr>
                        <td class="font-semibold">${item.reference_number}</td>
                        <td>${new Date(item.sale_date).toLocaleString('id-ID')}</td>
                        <td><span class="badge badge-info">${item.product?.category || '-'}</span></td>
                        <td>
                            <div class="font-medium">${item.product?.name || '-'}</div>
                            <div class="text-[11px] text-[color:var(--muted)]">${item.product?.variant || item.product?.category || '-'}</div>
                        </td>
                        <td>${item.target_number}</td>
                        <td><span class="text-[11px] font-mono">${item.serial_number || '-'}</span></td>
                        <td>${item.wallet?.name || '-'}</td>
                        <td>
                            <div class="font-medium">${money(item.price)}</div>
                            <div class="text-[11px] text-[color:var(--muted)]">Potong ${money(item.cost_amount)}</div>
                        </td>
                        <td>${statusBadge(item.status)}</td>
                        <td>
                            <div class="flex gap-2">
                                ${item.status === 'pending' ? `
                                    <button type="button" class="btn btn-secondary mark-success" data-id="${item.id}">
                                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/></svg></span>
                                        <span>Sukses</span>
                                    </button>
                                    <button type="button" class="btn btn-danger cancel-sale" data-id="${item.id}">
                                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg></span>
                                        <span>Cancel</span>
                                    </button>
                                ` : '<span class="text-[11px] text-[color:var(--muted)]">Tidak ada aksi</span>'}
                            </div>
                        </td>
                    </tr>
                `).join('') : '<tr><td colspan="10" class="text-center text-[color:var(--muted)]">Belum ada transaksi pulsa.</td></tr>');
                historyTable.syncSortUi('#historyTable thead');
                historyTable.renderFooter('#historyPagination', meta);
            }

            function loadData() {
                $.get(urls.dashboard)
                    .done((response) => {
                        historyTable.setData(response.sales || []);
                        historyTable.setQuery($('#historySearch').val());
                        draw(historyTable.pagedRows());
                    })
                    .fail(() => showAlert('Gagal memuat riwayat penjualan.', 'error'));
            }

            $('#historySearch').on('input', function () {
                historyTable.setQuery($(this).val());
                draw(historyTable.pagedRows());
            });

            $(document).on('click', '#historyPagination .page-btn', function () {
                historyTable.setPage(Number($(this).data('page')));
                draw(historyTable.pagedRows());
            });

            $(document).on('click', '#historyTable thead [data-sort]', function () {
                historyTable.toggleSort($(this).data('sort'));
                draw(historyTable.pagedRows());
            });

            $(document).on('click', '.mark-success', function () {
                $.post(urls.success.replace('__ID__', $(this).data('id')))
                    .done((response) => {
                        showAlert(response.message);
                        loadData();
                    })
                    .fail((xhr) => {
                        showAlert(xhr.responseJSON?.message || 'Gagal mengubah status transaksi.', 'error');
                    });
            });

            $(document).on('click', '.cancel-sale', function () {
                const reason = window.prompt('Catatan cancel transaksi:', 'Pulsa tidak masuk / gangguan server');
                if (reason === null) {
                    return;
                }

                $.post(urls.cancel.replace('__ID__', $(this).data('id')), {
                    notes: reason,
                }).done((response) => {
                    showAlert(response.message);
                    loadData();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal membatalkan transaksi.', 'error');
                });
            });

            loadData();
        });
    </script>
@endsection
