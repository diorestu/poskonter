@extends('layouts.app')

@section('content')
    <section class="pane">
        <div class="toolbar flex items-center justify-between px-4 py-3">
            <div>
                <h1 class="font-semibold">Pengeluaran</h1>
                <p class="text-[color:var(--muted)]">Catat biaya operasional, pembelian kebutuhan toko, dan pengeluaran lain di luar transaksi penjualan.</p>
            </div>
        </div>

        <div class="grid gap-4 p-4">
            <div class="grid gap-3 md:grid-cols-3" id="expenseSummaryCards"></div>

            <div class="product-master-layout">
                <form id="expenseForm" class="master-card master-card-form p-3">
                    <div class="mb-3 text-sm font-semibold">Form Pengeluaran</div>
                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block font-medium">Tanggal</label>
                            <input class="field" type="datetime-local" id="expense_date">
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Kategori</label>
                            <input class="field" type="text" id="expense_category" placeholder="Operasional / Transport / Konsumsi" required>
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Deskripsi</label>
                            <input class="field" type="text" id="expense_description" placeholder="Deskripsi pengeluaran" required>
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Nominal</label>
                            <input class="field" type="text" inputmode="numeric" data-number-mask id="expense_amount" value="0" required>
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Catatan</label>
                            <textarea class="textarea" id="expense_notes" rows="4" placeholder="Opsional"></textarea>
                        </div>
                        <div class="flex gap-2">
                            <button class="btn btn-primary flex-1" type="submit">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                                <span>Simpan Pengeluaran</span>
                            </button>
                            <button class="btn btn-secondary" type="button" id="expenseReset">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg></span>
                                <span>Reset</span>
                            </button>
                        </div>
                    </div>
                </form>

                <div class="master-card master-card-table p-3">
                    <div class="master-card-head">
                        <div class="text-sm font-semibold">Riwayat Pengeluaran</div>
                        <input id="expenseSearch" class="field max-w-xs" type="text" placeholder="Cari kategori / deskripsi / ref">
                    </div>
                    <div class="table-wrap">
                        <table id="expenseTableWrap" class="table-grid min-w-full text-sm">
                            <thead class="text-left">
                                <tr>
                                    <th data-sort="reference_number">Ref</th>
                                    <th data-sort="expense_date">Tanggal</th>
                                    <th data-sort="category">Kategori</th>
                                    <th data-sort="description">Deskripsi</th>
                                    <th data-sort="amount">Nominal</th>
                                    <th>Pencatat</th>
                                </tr>
                            </thead>
                            <tbody id="expenseTable"></tbody>
                        </table>
                    </div>
                    <div id="expensePagination"></div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        window.runAppPage(function ($, helpers) {
            const state = { expenses: [] };
            const { money, showAlert, maskedNumberValue, setMaskedNumberValue, bindNumberMasks, parseNumber, formatNumber } = helpers;
            const urls = {
                dashboard: @json(route('ajax.expenses.dashboard')),
                store: @json(route('ajax.expenses.store')),
            };

            const expenseTable = helpers.createTableController({
                perPage: 10,
                defaultSortKey: 'expense_date',
                defaultSortDir: 'desc',
                search: (expense, query) => [
                    expense.reference_number,
                    expense.category,
                    expense.description,
                    expense.notes || '',
                    expense.creator?.name || '',
                ].some((value) => String(value || '').toLowerCase().includes(query)),
                sorters: {
                    reference_number: (expense) => expense.reference_number,
                    expense_date: (expense) => expense.expense_date,
                    category: (expense) => expense.category,
                    description: (expense) => expense.description,
                    amount: (expense) => Number(expense.amount),
                },
            });

            function resetForm() {
                $('#expenseForm')[0].reset();
                $('#expense_date').val(new Date().toISOString().slice(0, 16));
                setMaskedNumberValue('#expense_amount', 0);
            }

            function renderSummary(summary) {
                const items = [
                    ['Pengeluaran Hari Ini', money(summary.today_total)],
                    ['Pengeluaran Bulan Ini', money(summary.month_total)],
                    ['Jumlah Hari Ini', summary.today_count],
                ];

                $('#expenseSummaryCards').html(items.map(([label, value]) => `
                    <div class="stat-box p-3">
                        <div class="text-[11px] uppercase tracking-[0.14em] text-[color:var(--muted)]">${label}</div>
                        <div class="mt-2 text-[22px] font-semibold">${value}</div>
                    </div>
                `).join(''));
            }

            function drawTable(meta) {
                $('#expenseTable').html(meta.rows.length ? meta.rows.map((expense) => `
                    <tr>
                        <td class="font-semibold">${expense.reference_number}</td>
                        <td>${new Date(expense.expense_date).toLocaleString('id-ID')}</td>
                        <td><span class="badge badge-info">${expense.category}</span></td>
                        <td>
                            <div class="font-medium">${expense.description}</div>
                            <div class="text-[11px] text-[color:var(--muted)]">${expense.notes || '-'}</div>
                        </td>
                        <td class="font-semibold">${money(expense.amount)}</td>
                        <td>${expense.creator?.name || '-'}</td>
                    </tr>
                `).join('') : '<tr><td colspan="6" class="text-center text-[color:var(--muted)]">Belum ada data pengeluaran.</td></tr>');
                expenseTable.syncSortUi('#expenseTableWrap thead');
                expenseTable.renderFooter('#expensePagination', meta);
            }

            function renderExpenses(expenses) {
                state.expenses = expenses || [];
                expenseTable.setData(state.expenses);
                expenseTable.setQuery($('#expenseSearch').val());
                drawTable(expenseTable.pagedRows());
            }

            function upsertExpense(expense) {
                if (!expense) return;

                const existingIndex = state.expenses.findIndex((item) => Number(item.id) === Number(expense.id));
                if (existingIndex >= 0) {
                    state.expenses.splice(existingIndex, 1, expense);
                } else {
                    state.expenses.unshift(expense);
                }

                renderExpenses([...state.expenses]);
            }

            function loadData() {
                $.get(urls.dashboard, { _: Date.now() })
                    .done((response) => {
                        renderSummary(response.summary || {});
                        renderExpenses(response.expenses || []);
                    })
                    .fail(() => showAlert('Gagal memuat data pengeluaran.', 'error'));
            }

            $('#expenseSearch').on('input', function () {
                expenseTable.setQuery($(this).val());
                drawTable(expenseTable.pagedRows());
            });

            $(document).on('click', '#expenseTableWrap thead [data-sort]', function () {
                expenseTable.toggleSort($(this).data('sort'));
                drawTable(expenseTable.pagedRows());
            });

            $(document).on('click', '#expensePagination .page-btn', function () {
                expenseTable.setPage(Number($(this).data('page')));
                drawTable(expenseTable.pagedRows());
            });

            $('#expenseReset').on('click', resetForm);

            $('#expenseForm').on('submit', function (event) {
                event.preventDefault();

                $.post(urls.store, {
                    expense_date: $('#expense_date').val() ? $('#expense_date').val().replace('T', ' ') : '',
                    category: $('#expense_category').val(),
                    description: $('#expense_description').val(),
                    amount: maskedNumberValue('#expense_amount'),
                    notes: $('#expense_notes').val(),
                }).done((response) => {
                    showAlert(response.message);
                    upsertExpense(response.expense);
                    resetForm();
                    loadData();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal menyimpan pengeluaran.', 'error');
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
