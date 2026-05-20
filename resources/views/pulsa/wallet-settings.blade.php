@extends('layouts.app')

@section('content')
    <section class="space-y-4">
        <div class="pane">
            <div class="toolbar flex items-center justify-between px-4 py-3">
                <div>
                    <h1 class="font-semibold">Dompet Saldo</h1>
                    <p class="text-[color:var(--muted)]">Dompet didaftarkan per provider. Semua produk pada provider yang dipilih otomatis memakai dompet tersebut.</p>
                </div>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-[420px_1fr]">
            <div class="space-y-4">
                <form id="walletManageForm" class="list-panel p-3">
                    <input type="hidden" id="wallet_manage_id">
                    <div class="mb-3 text-sm font-semibold">Form Dompet</div>
                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block font-medium">Nama Dompet</label>
                            <input class="field" type="text" id="wallet_manage_name" placeholder="Dompet Server A" required>
                        </div>
                        <div id="walletInitialBalanceWrap">
                            <label class="mb-1 block font-medium">Saldo Awal</label>
                            <input class="field" type="text" inputmode="numeric" data-number-mask id="wallet_manage_balance" value="0">
                        </div>
                        <div id="walletProvidersWrap">
                            <label class="mb-1 block font-medium">Provider yang Diizinkan</label>
                            <div id="walletProviderOptions" class="space-y-3 rounded-xl border border-[#d8e1ec] bg-[#f9fbfd] p-3"></div>
                            <div class="mt-2 text-[11px] text-[color:var(--muted)]">Pilih provider. Semua produk elektrik pada provider itu otomatis aktif untuk dompet ini.</div>
                        </div>
                        <div class="flex gap-2">
                            <button class="btn btn-primary flex-1" type="submit" id="walletManageSubmit">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                                <span>Simpan Dompet</span>
                            </button>
                            <button class="btn btn-secondary" type="button" id="walletManageReset">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg></span>
                                <span>Reset</span>
                            </button>
                        </div>
                    </div>
                </form>

                <form id="walletTopupForm" class="list-panel p-3">
                    <div class="mb-3 text-sm font-semibold">Top Up Dompet</div>
                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block font-medium">Pilih Dompet</label>
                            <select class="select" id="wallet_topup_id"></select>
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Nominal Top Up</label>
                            <input class="field" type="text" inputmode="numeric" data-number-mask id="wallet_topup_amount" value="0">
                        </div>
                        <button class="btn btn-secondary w-full" type="submit">
                            <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/><path d="M4 4h16v16H4z"/></svg></span>
                            <span>Tambah Saldo</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="list-panel p-3">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div class="text-sm font-semibold">Daftar Dompet Saldo</div>
                    <input id="walletSearch" class="field max-w-xs" type="text" placeholder="Cari dompet / provider terkait">
                </div>
                <div class="table-wrap">
                    <table id="walletTableWrap" class="table-grid min-w-full text-sm">
                        <thead class="text-left">
                            <tr>
                                <th data-sort="name">Dompet</th>
                                <th data-sort="balance">Saldo</th>
                                <th>Provider</th>
                                <th width="160">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="walletTable"></tbody>
                    </table>
                </div>
                <div id="walletPagination"></div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        window.runAppPage(function ($, helpers) {
            const state = { wallets: [], providers: [] };
            const { money, showAlert, maskedNumberValue, setMaskedNumberValue, bindNumberMasks, parseNumber, formatNumber } = helpers;
            const urls = {
                dashboard: @json(route('ajax.pulsa.dashboard')),
                storeWallet: @json(route('ajax.wallets.store')),
                updateWallet: @json(url('/ajax/wallets/__ID__')),
                topupWallet: @json(url('/ajax/wallets/__ID__/topup')),
            };

            const walletTable = helpers.createTableController({
                perPage: 8,
                defaultSortKey: 'name',
                search: (wallet, query) => [wallet.name, ...(wallet.child_providers || []).map((provider) => provider.name)].some((value) => String(value || '').toLowerCase().includes(query)),
                sorters: {
                    name: (wallet) => wallet.name,
                    balance: (wallet) => Number(wallet.balance),
                },
            });

            function renderProviderOptions(selectedIds = []) {
                const selected = new Set((selectedIds || []).map((id) => Number(id)));

                $('#walletProviderOptions').html(state.providers.length ? state.providers.map((provider) => `
                    <label class="flex items-start gap-2 rounded-xl border border-[#d8e1ec] bg-white px-3 py-2">
                        <input type="checkbox" class="mt-1 wallet-provider-option" value="${provider.id}" ${selected.has(Number(provider.id)) ? 'checked' : ''}>
                        <span>
                            <span class="block font-medium">${provider.name}</span>
                            <span class="text-[11px] text-[color:var(--muted)]">${provider.products_count || 0} produk aktif</span>
                        </span>
                    </label>
                `).join('') : '<div class="text-sm text-[color:var(--muted)]">Belum ada provider elektrik.</div>');
            }

            function selectedProviderIds() {
                return $('.wallet-provider-option:checked').map(function () {
                    return Number($(this).val());
                }).get();
            }

            function renderTopupOptions() {
                $('#wallet_topup_id').html(state.wallets.length ? state.wallets.map((wallet) => `
                    <option value="${wallet.id}">${wallet.name} - ${money(wallet.balance)}</option>
                `).join('') : '<option value="">Belum ada dompet</option>');
            }

            function syncFormMode() {
                const isEdit = Boolean($('#wallet_manage_id').val());
                $('#walletInitialBalanceWrap').toggleClass('hidden', isEdit);
                $('#walletManageSubmit').html(isEdit
                    ? '<span class="btn-icon"><svg viewBox="0 0 24 24"><path d="m4 20 4.5-1 9-9-3.5-3.5-9 9L4 20z"/><path d="M13.5 6.5 17 10"/></svg></span><span>Update Dompet</span>'
                    : '<span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span><span>Simpan Dompet</span>');
            }

            function resetForm() {
                $('#walletManageForm')[0].reset();
                $('#wallet_manage_id').val('');
                setMaskedNumberValue('#wallet_manage_balance', 0);
                renderProviderOptions([]);
                syncFormMode();
            }

            function drawTable(meta) {
                $('#walletTable').html(meta.rows.length ? meta.rows.map((wallet) => `
                    <tr>
                        <td>
                            <div class="font-medium">${wallet.name}</div>
                            <div class="text-[11px] text-[color:var(--muted)]">${(wallet.child_providers || []).length} provider terkait</div>
                        </td>
                        <td class="font-semibold">${money(wallet.balance)}</td>
                        <td>
                            ${(wallet.child_providers || []).length
                                ? wallet.child_providers.map((provider) => `<div class="text-[11px]">${provider.name} · ${provider.products_count} produk</div>`).join('')
                                : '<span class="text-[11px] text-[color:var(--muted)]">Belum diikat ke provider</span>'}
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <button class="btn btn-secondary edit-wallet" type="button" data-id="${wallet.id}">
                                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="m4 20 4.5-1 9-9-3.5-3.5-9 9L4 20z"/><path d="M13.5 6.5 17 10"/></svg></span>
                                    <span>Edit</span>
                                </button>
                                <button class="btn btn-secondary pick-topup-wallet" type="button" data-id="${wallet.id}">
                                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg></span>
                                    <span>Top Up</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('') : '<tr><td colspan="4" class="text-center text-[color:var(--muted)]">Belum ada dompet saldo.</td></tr>');
                walletTable.syncSortUi('#walletTableWrap thead');
                walletTable.renderFooter('#walletPagination', meta);
            }

            function renderWallets(wallets) {
                state.wallets = wallets || [];
                walletTable.setData(state.wallets);
                walletTable.setQuery($('#walletSearch').val());
                drawTable(walletTable.pagedRows());
                renderTopupOptions();
            }

            function upsertWallet(wallet) {
                if (!wallet) return;

                const existingIndex = state.wallets.findIndex((item) => Number(item.id) === Number(wallet.id));
                if (existingIndex >= 0) {
                    state.wallets.splice(existingIndex, 1, wallet);
                } else {
                    state.wallets.unshift(wallet);
                }

                renderWallets([...state.wallets]);
            }

            function loadData() {
                $.get(urls.dashboard, { _: Date.now() })
                    .done((response) => {
                        state.providers = response.providers || [];
                        renderProviderOptions(selectedProviderIds());
                        renderWallets(response.wallets || []);
                    })
                    .fail(() => showAlert('Gagal memuat pengaturan dompet.', 'error'));
            }

            $('#walletSearch').on('input', function () {
                walletTable.setQuery($(this).val());
                drawTable(walletTable.pagedRows());
            });

            $(document).on('click', '#walletPagination .page-btn', function () {
                walletTable.setPage(Number($(this).data('page')));
                drawTable(walletTable.pagedRows());
            });

            $(document).on('click', '#walletTableWrap thead [data-sort]', function () {
                walletTable.toggleSort($(this).data('sort'));
                drawTable(walletTable.pagedRows());
            });

            $('#walletManageReset').on('click', resetForm);

            $(document).on('click', '.edit-wallet', function () {
                const wallet = state.wallets.find((item) => item.id === Number($(this).data('id')));
                if (!wallet) return;

                $('#wallet_manage_id').val(wallet.id);
                $('#wallet_manage_name').val(wallet.name);
                renderProviderOptions(wallet.child_provider_ids || []);
                syncFormMode();
            });

            $(document).on('click', '.pick-topup-wallet', function () {
                $('#wallet_topup_id').val($(this).data('id'));
                $('#wallet_topup_amount').trigger('focus');
            });

            $('#walletManageForm').on('submit', function (event) {
                event.preventDefault();

                const walletId = $('#wallet_manage_id').val();
                const payload = {
                    name: $('#wallet_manage_name').val(),
                    provider_ids: selectedProviderIds(),
                };

                if (walletId) {
                    $.ajax({
                        url: urls.updateWallet.replace('__ID__', walletId),
                        method: 'PUT',
                        data: payload,
                    }).done((response) => {
                        showAlert(response.message);
                        upsertWallet(response.wallet);
                        resetForm();
                        loadData();
                    }).fail((xhr) => {
                        showAlert(xhr.responseJSON?.message || 'Gagal memperbarui dompet.', 'error');
                    });

                    return;
                }

                payload.balance = maskedNumberValue('#wallet_manage_balance');

                $.post(urls.storeWallet, payload)
                    .done((response) => {
                        showAlert(response.message);
                        upsertWallet(response.wallet);
                        resetForm();
                        loadData();
                    })
                    .fail((xhr) => {
                        showAlert(xhr.responseJSON?.message || 'Gagal menyimpan dompet.', 'error');
                    });
            });

            $('#walletTopupForm').on('submit', function (event) {
                event.preventDefault();

                const walletId = $('#wallet_topup_id').val();
                if (!walletId) {
                    showAlert('Pilih dompet terlebih dahulu.', 'error');
                    return;
                }

                $.post(urls.topupWallet.replace('__ID__', walletId), {
                    amount: maskedNumberValue('#wallet_topup_amount'),
                }).done((response) => {
                    showAlert(response.message);
                    upsertWallet(response.wallet);
                    setMaskedNumberValue('#wallet_topup_amount', 0);
                    loadData();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal menambah saldo.', 'error');
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
