@extends('layouts.app')

@section('content')
    <section class="pane">
        <div class="toolbar flex flex-wrap items-center justify-between gap-3 px-4 py-3">
            <div>
                <h1 class="font-semibold text-base">Riwayat Penjualan</h1>
                <p class="text-xs text-[color:var(--muted)]">Daftar seluruh riwayat transaksi penjualan POS (produk fisik & voucher) dan pulsa elektrik.</p>
            </div>
            <div class="flex items-center gap-1.5 rounded-lg bg-[#f1f5f9] p-1 border border-[#cbd5e1]">
                <button type="button" class="history-tab-btn active px-3 py-1 text-xs font-semibold rounded-md transition-all bg-white shadow-sm text-blue-600" data-tab="pos">
                    Penjualan POS (Fisik & Voucher)
                </button>
                <button type="button" class="history-tab-btn px-3 py-1 text-xs font-semibold rounded-md transition-all text-[#64748b] hover:text-[#0f172a]" data-tab="pulsa">
                    Pulsa Elektrik
                </button>
            </div>
        </div>

        <div class="p-4 space-y-4">
            <!-- TAB 1: PENJUALAN POS (Fisik & Voucher) -->
            <div id="tabPosSection" class="list-panel p-4">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold text-[color:var(--text)]">Daftar Transaksi Kasir POS</div>
                        <div class="text-xs text-[color:var(--muted)]">Penjualan aksesoris, barang fisik, dan voucher fisik.</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="posHistorySearch" class="field max-w-xs text-xs" type="text" placeholder="Cari invoice / produk / serial / pelanggan...">
                    </div>
                </div>
                <div class="table-wrap">
                    <table id="posHistoryTable" class="table-grid min-w-full text-xs">
                        <thead class="text-left">
                            <tr>
                                <th data-sort="invoice_number">Invoice</th>
                                <th data-sort="sale_date">Tanggal & Waktu</th>
                                <th data-sort="shift">Shift</th>
                                <th data-sort="customer_name">Pelanggan</th>
                                <th>Rincian Item (Produk, Qty, Serial)</th>
                                <th data-sort="payment_method">Pembayaran</th>
                                <th data-sort="total" class="text-right">Total Transaksi</th>
                                <th data-sort="status" class="text-center">Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="posHistoryBody">
                            <tr>
                                <td colspan="9" class="text-center py-6 text-[color:var(--muted)]">Memuat data transaksi POS...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="posHistoryPagination"></div>
            </div>

            <!-- TAB 2: PULSA ELEKTRIK -->
            <div id="tabPulsaSection" class="list-panel p-4 hidden">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold text-[color:var(--text)]">Daftar Transaksi Pulsa Elektrik</div>
                        <div class="text-xs text-[color:var(--muted)]">Pantau status transaksi pulsa elektrik, tandai sukses, atau batalkan.</div>
                    </div>
                    <input id="pulsaHistorySearch" class="field max-w-xs text-xs" type="text" placeholder="Cari ref / provider / nomor / serial...">
                </div>
                <div class="table-wrap">
                    <table id="pulsaHistoryTable" class="table-grid min-w-full text-xs">
                        <thead class="text-left">
                            <tr>
                                <th data-sort="reference_number">Ref</th>
                                <th data-sort="sale_date">Tanggal & Waktu</th>
                                <th data-sort="provider">Provider</th>
                                <th data-sort="product">Produk</th>
                                <th data-sort="target_number">Nomor Tujuan</th>
                                <th data-sort="serial_number">Serial / SN</th>
                                <th data-sort="wallet">Dompet</th>
                                <th data-sort="price">Harga Jual</th>
                                <th data-sort="status" class="text-center">Status</th>
                                <th width="140" class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="pulsaHistoryBody">
                            <tr>
                                <td colspan="10" class="text-center py-6 text-[color:var(--muted)]">Memuat data pulsa...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="pulsaHistoryPagination"></div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        window.runAppPage(function ($, helpers) {
            const { money, showAlert } = helpers;
            const urls = {
                historyData: @json(route('ajax.sales.history-data')),
                pulsaSuccess: @json(url('/ajax/pulsa/sales/__ID__/success')),
                pulsaCancel: @json(url('/ajax/pulsa/sales/__ID__/cancel')),
                posCancel: @json(url('/ajax/sales/__ID__/cancel')),
            };

            function paymentBadge(method) {
                const map = {
                    cash: '<span class="badge badge-ok">Tunai</span>',
                    qris: '<span class="badge badge-info">QRIS</span>',
                    transfer: '<span class="badge badge-warning">Transfer</span>',
                    debit: '<span class="badge badge-info">Debit</span>',
                    utang: '<span class="badge badge-danger-soft">Utang</span>',
                };
                return map[method] || `<span class="badge">${method}</span>`;
            }

            function shiftBadge(shift) {
                if (shift === 'pagi') return '<span class="badge badge-ok">Shift Pagi</span>';
                if (shift === 'sore') return '<span class="badge badge-warning">Shift Sore</span>';
                return `<span class="badge">${shift || '-'}</span>`;
            }

            function statusBadge(status) {
                if (status === 'success' || status === 'completed') return '<span class="badge badge-ok">Sukses</span>';
                if (status === 'cancelled') return '<span class="badge border border-red-300 bg-red-100 text-red-700">Dibatalkan</span>';
                return '<span class="badge badge-warning">Pending</span>';
            }

            // POS History Table Controller
            const posTable = helpers.createTableController({
                perPage: 10,
                defaultSortKey: 'sale_date',
                defaultSortDir: 'desc',
                search: (item, query) => {
                    const itemNames = (item.items || []).map((si) => si.product?.name || '').join(' ');
                    const itemSerials = (item.items || []).map((si) => si.serial_number || '').join(' ');
                    return [item.invoice_number, item.customer_name || '', item.payment_method || '', item.shift || '', item.status || '', itemNames, itemSerials].some((value) => String(value || '').toLowerCase().includes(query));
                },
                sorters: {
                    invoice_number: (item) => item.invoice_number,
                    sale_date: (item) => new Date(item.sale_date).getTime(),
                    shift: (item) => item.shift || '',
                    customer_name: (item) => item.customer_name || '',
                    payment_method: (item) => item.payment_method || '',
                    total: (item) => Number(item.total),
                    status: (item) => item.status || 'completed',
                },
            });

            // Pulsa History Table Controller
            const pulsaTable = helpers.createTableController({
                perPage: 10,
                defaultSortKey: 'sale_date',
                defaultSortDir: 'desc',
                search: (item, query) => [item.reference_number, item.product?.name || '', item.product?.category || '', item.target_number, item.serial_number || '', item.wallet?.name || '', item.customer_name || ''].some((value) => String(value || '').toLowerCase().includes(query)),
                sorters: {
                    reference_number: (item) => item.reference_number,
                    sale_date: (item) => new Date(item.sale_date).getTime(),
                    provider: (item) => item.product?.category || '',
                    product: (item) => item.product?.name || '',
                    target_number: (item) => item.target_number,
                    serial_number: (item) => item.serial_number || '',
                    wallet: (item) => item.wallet?.name || '',
                    price: (item) => Number(item.price),
                    status: (item) => item.status,
                },
            });

            function drawPosTable(meta) {
                $('#posHistoryBody').html(meta.rows.length ? meta.rows.map((item) => {
                    const saleDate = item.sale_date ? new Date(item.sale_date).toLocaleString('id-ID') : '-';
                    const items = item.items || [];
                    const isCancelled = item.status === 'cancelled';
                    return `
                        <tr class="${isCancelled ? 'opacity-60 bg-slate-50' : ''}">
                            <td class="font-bold font-mono text-blue-700">${item.invoice_number}</td>
                            <td class="whitespace-nowrap">${saleDate}</td>
                            <td>${shiftBadge(item.shift)}</td>
                            <td>
                                <div class="font-medium">${item.customer_name || 'Umum'}</div>
                            </td>
                            <td>
                                <div class="space-y-1 py-0.5">
                                    ${items.map((si) => `
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="font-medium">${si.product?.name || 'Produk'}</span>
                                            ${si.product?.variant ? `<span class="text-[11px] text-[color:var(--muted)]">(${si.product.variant})</span>` : ''}
                                            <span class="badge font-bold">x${si.quantity}</span>
                                            ${si.serial_number ? `<span class="badge badge-ok font-mono text-[10px]">SN: ${si.serial_number}</span>` : ''}
                                            <span class="text-[11px] text-[color:var(--muted)] font-medium">@ ${money(si.price)}</span>
                                        </div>
                                    `).join('')}
                                </div>
                            </td>
                            <td>${paymentBadge(item.payment_method)}</td>
                            <td class="text-right font-bold text-emerald-700">
                                <div>${money(item.total)}</div>
                                ${item.discount > 0 ? `<div class="text-[10px] text-red-500 font-normal">Diskon ${money(item.discount)}</div>` : ''}
                                <div class="text-[10px] text-[color:var(--muted)] font-normal">Bayar ${money(item.cash_paid)}</div>
                            </td>
                            <td class="text-center">${statusBadge(item.status || 'completed')}</td>
                            <td class="text-right whitespace-nowrap">
                                ${!isCancelled ? `
                                    <button type="button" class="btn btn-danger py-1 px-2.5 text-xs cancel-pos-sale" data-id="${item.id}" data-invoice="${item.invoice_number}" title="Batalkan Transaksi POS & Kembalikan Stok">
                                        <span>Batalkan</span>
                                    </button>
                                ` : '<span class="text-[11px] text-[color:var(--muted)] font-normal">Dibatalkan</span>'}
                            </td>
                        </tr>
                    `;
                }).join('') : '<tr><td colspan="9" class="text-center py-6 text-[color:var(--muted)]">Belum ada transaksi penjualan fisik / voucher.</td></tr>');
                posTable.syncSortUi('#posHistoryTable thead');
                posTable.renderFooter('#posHistoryPagination', meta);
            }

            function drawPulsaTable(meta) {
                $('#pulsaHistoryBody').html(meta.rows.length ? meta.rows.map((item) => {
                    const isCancelled = item.status === 'cancelled';
                    return `
                        <tr class="${isCancelled ? 'opacity-60 bg-slate-50' : ''}">
                            <td class="font-bold font-mono text-blue-700">${item.reference_number}</td>
                            <td class="whitespace-nowrap">${new Date(item.sale_date).toLocaleString('id-ID')}</td>
                            <td><span class="badge badge-info">${item.product?.category || '-'}</span></td>
                            <td>
                                <div class="font-medium">${item.product?.name || '-'}</div>
                                <div class="text-[11px] text-[color:var(--muted)]">${item.product?.variant || item.product?.category || '-'}</div>
                            </td>
                            <td class="font-mono">${item.target_number}</td>
                            <td><span class="text-[11px] font-mono">${item.serial_number || '-'}</span></td>
                            <td>${item.wallet?.name || '-'}</td>
                            <td>
                                <div class="font-semibold text-emerald-700">${money(item.price)}</div>
                                <div class="text-[10px] text-[color:var(--muted)]">Modal ${money(item.cost_amount)}</div>
                            </td>
                            <td class="text-center">${statusBadge(item.status)}</td>
                            <td class="text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    ${item.status === 'pending' ? `
                                        <button type="button" class="btn btn-secondary py-1 px-2 text-xs mark-pulsa-success" data-id="${item.id}" title="Tandai Sukses">
                                            <span>Sukses</span>
                                        </button>
                                    ` : ''}
                                    ${!isCancelled ? `
                                        <button type="button" class="btn btn-danger py-1 px-2.5 text-xs cancel-pulsa-sale" data-id="${item.id}" data-ref="${item.reference_number}" data-wallet="${item.wallet?.name || ''}" title="Batalkan Transaksi & Kembalikan Saldo">
                                            <span>Batalkan</span>
                                        </button>
                                    ` : '<span class="text-[11px] text-[color:var(--muted)] font-normal">Dibatalkan</span>'}
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('') : '<tr><td colspan="10" class="text-center py-6 text-[color:var(--muted)]">Belum ada transaksi pulsa.</td></tr>');
                pulsaTable.syncSortUi('#pulsaHistoryTable thead');
                pulsaTable.renderFooter('#pulsaHistoryPagination', meta);
            }

            function loadData() {
                $.get(urls.historyData)
                    .done((response) => {
                        posTable.setData(response.pos_sales || []);
                        posTable.setQuery($('#posHistorySearch').val());
                        drawPosTable(posTable.pagedRows());

                        pulsaTable.setData(response.pulsa_sales || []);
                        pulsaTable.setQuery($('#pulsaHistorySearch').val());
                        drawPulsaTable(pulsaTable.pagedRows());
                    })
                    .fail(() => showAlert('Gagal memuat riwayat penjualan.', 'error'));
            }

            // Tab Switching
            $('.history-tab-btn').on('click', function () {
                $('.history-tab-btn').removeClass('active bg-white shadow-sm text-blue-600').addClass('text-[#64748b]');
                $(this).addClass('active bg-white shadow-sm text-blue-600').removeClass('text-[#64748b]');

                const targetTab = $(this).data('tab');
                if (targetTab === 'pos') {
                    $('#tabPosSection').removeClass('hidden');
                    $('#tabPulsaSection').addClass('hidden');
                } else {
                    $('#tabPosSection').addClass('hidden');
                    $('#tabPulsaSection').removeClass('hidden');
                }
            });

            // POS Search & Pagination & Sort
            $('#posHistorySearch').on('input', function () {
                posTable.setQuery($(this).val());
                drawPosTable(posTable.pagedRows());
            });

            $(document).on('click', '#posHistoryPagination .page-btn', function () {
                posTable.setPage(Number($(this).data('page')));
                drawPosTable(posTable.pagedRows());
            });

            $(document).on('click', '#posHistoryTable thead [data-sort]', function () {
                posTable.toggleSort($(this).data('sort'));
                drawPosTable(posTable.pagedRows());
            });

            // Pulsa Search & Pagination & Sort
            $('#pulsaHistorySearch').on('input', function () {
                pulsaTable.setQuery($(this).val());
                drawPulsaTable(pulsaTable.pagedRows());
            });

            $(document).on('click', '#pulsaHistoryPagination .page-btn', function () {
                pulsaTable.setPage(Number($(this).data('page')));
                drawPulsaTable(pulsaTable.pagedRows());
            });

            $(document).on('click', '#pulsaHistoryTable thead [data-sort]', function () {
                pulsaTable.toggleSort($(this).data('sort'));
                drawPulsaTable(pulsaTable.pagedRows());
            });

            // Pulsa Actions
            $(document).on('click', '.mark-pulsa-success', function () {
                $.post(urls.pulsaSuccess.replace('__ID__', $(this).data('id')))
                    .done((response) => {
                        showAlert(response.message);
                        loadData();
                    })
                    .fail((xhr) => {
                        showAlert(xhr.responseJSON?.message || 'Gagal mengubah status transaksi.', 'error');
                    });
            });

            $(document).on('click', '.cancel-pulsa-sale', function () {
                const ref = $(this).data('ref') || 'transaksi ini';
                const wallet = $(this).data('wallet');
                const promptMsg = wallet
                    ? `Batalkan transaksi ${ref}?\nSaldo modal akan otomatis dikembalikan ke dompet: ${wallet}.\n\nMasukkan catatan pembatalan (opsional):`
                    : `Batalkan transaksi ${ref}?\n\nMasukkan catatan pembatalan (opsional):`;
                const reason = window.prompt(promptMsg, 'Pulsa tidak masuk / gangguan server');
                if (reason === null) return;

                $.post(urls.pulsaCancel.replace('__ID__', $(this).data('id')), {
                    notes: reason,
                }).done((response) => {
                    showAlert(response.message);
                    loadData();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal membatalkan transaksi.', 'error');
                });
            });

            $(document).on('click', '.cancel-pos-sale', function () {
                const invoice = $(this).data('invoice') || 'transaksi ini';
                const reason = window.prompt(`Batalkan transaksi ${invoice}?\nStok produk akan otomatis dikembalikan.\n\nMasukkan catatan pembatalan (opsional):`, 'Pembatalan transaksi');
                if (reason === null) return;

                $.post(urls.posCancel.replace('__ID__', $(this).data('id')), {
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
