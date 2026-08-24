@extends('layouts.app')

@section('content')
    <section class="space-y-4">
        <div class="list-panel p-4">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-[color:var(--muted)]">Daftar Dompet Saldo Server</div>
                    <div class="text-[11px] text-[color:var(--muted)]">Kelola dompet saldo server untuk transaksi pulsa elektrik dan voucher.</div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <input id="walletSearch" class="field w-48 text-xs py-1.5" type="text" placeholder="Cari dompet...">
                    <button type="button" class="btn btn-secondary text-xs py-1.5 px-3 min-h-[34px] open-wallet-modal whitespace-nowrap">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg></span>
                        <span>Tambah Dompet</span>
                    </button>
                    <button type="button" class="btn btn-primary text-xs py-1.5 px-3 min-h-[34px] open-generic-topup whitespace-nowrap">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/><path d="M4 4h16v16H4z"/></svg></span>
                        <span>Top Up Saldo</span>
                    </button>
                </div>
            </div>
            <div class="table-wrap">
                <table id="walletTableWrap" class="table-grid min-w-full text-xs">
                    <thead class="text-left">
                        <tr>
                            <th data-sort="name">Dompet</th>
                            <th data-sort="balance">Saldo</th>
                            <th>Provider Terhubung</th>
                            <th width="150" class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="walletTable"></tbody>
                </table>
            </div>
            <div id="walletPagination"></div>
        </div>
    </section>

    <!-- Wallet Manage Modal Popup -->
    <div id="walletModal" class="modal-backdrop hidden">
        <div class="modal-card">
            <div class="modal-head flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-sm" id="walletModalTitle">Tambah Dompet Baru</h3>
                    <p class="text-[11px] text-[color:var(--muted)]">Daftarkan dompet server dan hubungkan dengan provider terkait.</p>
                </div>
                <button type="button" class="btn-icon text-[color:var(--muted)] hover:text-[color:var(--text)] close-wallet-modal">
                    <svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg>
                </button>
            </div>
            <form id="walletManageForm" class="modal-body space-y-3.5">
                <input type="hidden" id="wallet_manage_id">
                <div>
                    <label class="mb-1 block font-medium">Nama Dompet</label>
                    <input class="field" type="text" id="wallet_manage_name" placeholder="Contoh: Server Digipos / Otomax" required>
                </div>
                <div id="walletInitialBalanceWrap">
                    <label class="mb-1 block font-medium">Saldo Awal (Rp)</label>
                    <input class="field" type="text" inputmode="numeric" data-number-mask id="wallet_manage_balance" value="0">
                </div>
                <div id="walletProvidersWrap">
                    <label class="mb-1 block font-medium">Provider yang Terhubung</label>
                    <div id="walletProviderOptions" class="space-y-2 rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-3 max-h-[200px] overflow-y-auto"></div>
                    <div class="mt-1.5 text-[11px] text-[color:var(--muted)]">Produk pada provider terpilih akan otomatis memakai saldo dompet ini.</div>
                </div>
                <div class="mt-4 flex gap-2 pt-2 border-t border-[#f1f5f9]">
                    <button class="btn btn-primary flex-1" type="submit" id="walletManageSubmit">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                        <span id="walletManageSubmitText">Simpan Dompet</span>
                    </button>
                    <button class="btn btn-secondary close-wallet-modal" type="button">
                        <span>Batal</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Top Up Modal Popup -->
    <div id="topupModal" class="modal-backdrop hidden">
        <div class="modal-card">
            <div class="modal-head flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-sm">Top Up Saldo Dompet</h3>
                    <p class="text-[11px] text-[color:var(--muted)]">Tambah saldo pada server dompet pulsa elektrik / voucher.</p>
                </div>
                <button type="button" class="btn-icon text-[color:var(--muted)] hover:text-[color:var(--text)] close-topup-modal">
                    <svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg>
                </button>
            </div>
            <form id="modalTopupForm" class="modal-body space-y-3.5">
                <div>
                    <label class="mb-1 block font-medium">Pilih Dompet</label>
                    <select class="select" id="modal_wallet_id" required></select>
                </div>

                <div id="modalWalletInfo" class="rounded-lg border border-[#e2e8f0] bg-[#f8fafc] p-2.5 flex items-center justify-between text-xs">
                    <span class="text-[color:var(--muted)]">Saldo Saat Ini:</span>
                    <strong id="modalWalletCurrentBalance" class="text-blue-600 font-semibold font-mono">Rp 0</strong>
                </div>

                <div>
                    <label class="mb-1 block font-medium">Nominal Top Up (Rp)</label>
                    <input class="field font-mono font-semibold" type="text" inputmode="numeric" data-number-mask id="modal_wallet_amount" value="0" required>
                </div>

                <!-- Quick Nominal Buttons in 1 Row -->
                <div class="space-y-1">
                    <div class="text-[11px] text-[color:var(--muted)]">Nominal Cepat:</div>
                    <div class="grid grid-cols-5 gap-1.5">
                        <button type="button" class="quick-amount-btn btn btn-secondary text-[11px] py-1 px-1 min-h-[28px] text-center w-full whitespace-nowrap" data-amount="50000">+50rb</button>
                        <button type="button" class="quick-amount-btn btn btn-secondary text-[11px] py-1 px-1 min-h-[28px] text-center w-full whitespace-nowrap" data-amount="100000">+100rb</button>
                        <button type="button" class="quick-amount-btn btn btn-secondary text-[11px] py-1 px-1 min-h-[28px] text-center w-full whitespace-nowrap" data-amount="200000">+200rb</button>
                        <button type="button" class="quick-amount-btn btn btn-secondary text-[11px] py-1 px-1 min-h-[28px] text-center w-full whitespace-nowrap" data-amount="500000">+500rb</button>
                        <button type="button" class="quick-amount-btn btn btn-secondary text-[11px] py-1 px-1 min-h-[28px] text-center w-full whitespace-nowrap" data-amount="1000000">+1jt</button>
                    </div>
                </div>

                <div class="mt-4 flex gap-2 pt-2 border-t border-[#f1f5f9]">
                    <button class="btn btn-primary flex-1" type="submit">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg></span>
                        <span>Konfirmasi Tambah Saldo</span>
                    </button>
                    <button class="btn btn-secondary close-topup-modal" type="button">
                        <span>Batal</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
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
                $('#modal_wallet_id').html(state.wallets.length ? state.wallets.map((wallet) => `
                    <option value="${wallet.id}">${wallet.name} (${money(wallet.balance)})</option>
                `).join('') : '<option value="">Belum ada dompet</option>');
                updateModalBalanceInfo();
            }

            function updateModalBalanceInfo() {
                const walletId = Number($('#modal_wallet_id').val());
                const wallet = state.wallets.find((w) => Number(w.id) === walletId);
                if (wallet) {
                    $('#modalWalletCurrentBalance').text(money(wallet.balance));
                } else {
                    $('#modalWalletCurrentBalance').text('Rp 0');
                }
            }

            function openWalletModal(wallet = null) {
                if (wallet) {
                    $('#walletModalTitle').text('Edit Dompet Saldo');
                    $('#walletManageSubmitText').text('Update Dompet');
                    $('#wallet_manage_id').val(wallet.id);
                    $('#wallet_manage_name').val(wallet.name);
                    $('#walletInitialBalanceWrap').addClass('hidden');
                    renderProviderOptions(wallet.child_provider_ids || []);
                } else {
                    $('#walletModalTitle').text('Tambah Dompet Baru');
                    $('#walletManageSubmitText').text('Simpan Dompet');
                    $('#walletManageForm')[0].reset();
                    $('#wallet_manage_id').val('');
                    setMaskedNumberValue('#wallet_manage_balance', 0);
                    $('#walletInitialBalanceWrap').removeClass('hidden');
                    renderProviderOptions([]);
                }
                $('#walletModal').removeClass('hidden');
                window.setTimeout(() => $('#wallet_manage_name').trigger('focus'), 50);
            }

            function closeWalletModal() {
                $('#walletModal').addClass('hidden');
                $('#walletManageForm')[0].reset();
                $('#wallet_manage_id').val('');
            }

            function openTopupModal(walletId = null) {
                renderTopupOptions();
                if (walletId) {
                    $('#modal_wallet_id').val(walletId);
                }
                updateModalBalanceInfo();
                setMaskedNumberValue('#modal_wallet_amount', 0);
                $('#topupModal').removeClass('hidden');
                window.setTimeout(() => $('#modal_wallet_amount').trigger('focus').select(), 50);
            }

            function closeTopupModal() {
                $('#topupModal').addClass('hidden');
                setMaskedNumberValue('#modal_wallet_amount', 0);
            }

            function drawTable(meta) {
                $('#walletTable').html(meta.rows.length ? meta.rows.map((wallet) => `
                    <tr>
                        <td>
                            <div class="font-medium text-xs">${wallet.name}</div>
                            <div class="text-[11px] text-[color:var(--muted)]">${(wallet.child_providers || []).length} provider terkait</div>
                        </td>
                        <td class="font-semibold text-xs font-mono text-blue-600">${money(wallet.balance)}</td>
                        <td>
                            ${(wallet.child_providers || []).length
                                ? wallet.child_providers.map((provider) => `<span class="badge badge-info text-[11px] mr-1 mb-1">${provider.name}</span>`).join('')
                                : '<span class="text-[11px] text-[color:var(--muted)]">Belum diikat ke provider</span>'}
                        </td>
                        <td class="text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <button class="btn btn-secondary text-xs py-1 px-2.5 min-h-[30px] edit-wallet whitespace-nowrap" type="button" data-id="${wallet.id}">
                                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="m4 20 4.5-1 9-9-3.5-3.5-9 9L4 20z"/><path d="M13.5 6.5 17 10"/></svg></span>
                                    <span>Edit</span>
                                </button>
                                <button class="btn btn-primary text-xs py-1 px-2.5 min-h-[30px] pick-topup-wallet whitespace-nowrap" type="button" data-id="${wallet.id}">
                                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg></span>
                                    <span>Top Up</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('') : '<tr><td colspan="4" class="text-center text-[color:var(--muted)] py-6 text-xs">Belum ada dompet saldo.</td></tr>');
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

            $(document).on('click', '.open-wallet-modal', function () {
                openWalletModal();
            });

            $('.close-wallet-modal').on('click', closeWalletModal);

            $('#walletModal').on('click', function (e) {
                if (e.target === this) closeWalletModal();
            });

            $(document).on('click', '.edit-wallet', function () {
                const wallet = state.wallets.find((item) => item.id === Number($(this).data('id')));
                if (!wallet) return;
                openWalletModal(wallet);
            });

            $(document).on('click', '.pick-topup-wallet', function () {
                openTopupModal($(this).data('id'));
            });

            $('.open-generic-topup').on('click', function () {
                openTopupModal();
            });

            $('.close-topup-modal').on('click', closeTopupModal);

            $('#topupModal').on('click', function (e) {
                if (e.target === this) closeTopupModal();
            });

            $('#modal_wallet_id').on('change', updateModalBalanceInfo);

            $('.quick-amount-btn').on('click', function () {
                const addAmount = Number($(this).data('amount')) || 0;
                const current = maskedNumberValue('#modal_wallet_amount') || 0;
                setMaskedNumberValue('#modal_wallet_amount', current + addAmount);
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
                        closeWalletModal();
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
                        closeWalletModal();
                        loadData();
                    })
                    .fail((xhr) => {
                        showAlert(xhr.responseJSON?.message || 'Gagal menyimpan dompet.', 'error');
                    });
            });

            $('#modalTopupForm').on('submit', function (event) {
                event.preventDefault();

                const walletId = $('#modal_wallet_id').val();
                if (!walletId) {
                    showAlert('Pilih dompet terlebih dahulu.', 'error');
                    return;
                }

                const amount = maskedNumberValue('#modal_wallet_amount');
                if (amount <= 0) {
                    showAlert('Masukkan nominal top up lebih dari 0.', 'error');
                    return;
                }

                $.post(urls.topupWallet.replace('__ID__', walletId), {
                    amount: amount,
                }).done((response) => {
                    showAlert(response.message);
                    upsertWallet(response.wallet);
                    closeTopupModal();
                    loadData();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal menambah saldo.', 'error');
                });
            });

            $(document).on('input', '[data-number-mask]', function () {
                $(this).val(formatNumber(parseNumber($(this).val())));
            });

            bindNumberMasks(document);
            loadData();
        });
    </script>
@endsection
