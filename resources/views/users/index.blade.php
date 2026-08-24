@extends('layouts.app')

@section('content')
    <section class="space-y-4">
        <div class="grid gap-4 xl:grid-cols-[420px_1fr]">
            <div class="space-y-4">
                <form id="userForm" class="list-panel p-3">
                    <input type="hidden" id="user_id">
                    <div class="mb-3 text-sm font-semibold">Form User</div>
                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block font-medium">Nama</label>
                            <input class="field" type="text" id="user_name" required>
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Email</label>
                            <input class="field" type="email" id="user_email" required>
                        </div>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block font-medium">Password</label>
                                <div class="relative flex items-center">
                                    <input class="field pr-10" type="password" id="user_password" placeholder="Minimal 8 karakter">
                                    <button type="button" class="absolute right-3 flex items-center justify-center text-[color:var(--muted)] hover:text-[color:var(--text)] transition cursor-pointer" id="toggleUserPassword" title="Lihat / Sembunyikan Password">
                                        <svg id="eyeOpenUserIcon" class="w-4 h-4 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        <svg id="eyeClosedUserIcon" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                            <line x1="1" y1="1" x2="23" y2="23"></line>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block font-medium">Role</label>
                                <select class="select" id="user_role">
                                    <option value="admin">Admin</option>
                                    <option value="cashier">Cashier</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button class="btn btn-primary flex-1" type="submit" id="userSubmit">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                                <span>Simpan User</span>
                            </button>
                            <button class="btn btn-secondary" type="button" id="userReset">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg></span>
                                <span>Reset</span>
                            </button>
                        </div>
                    </div>
                </form>

                <div class="list-panel p-3">
                    <div class="mb-3 text-sm font-semibold">Backup & Restore Database</div>
                    <div class="space-y-3 text-sm text-[color:var(--muted)]">
                        <p>Gunakan export untuk memindahkan data SQLite ke perangkat lain. Import akan mengganti data inti aplikasi pada perangkat ini.</p>
                        <a href="{{ route('database.export') }}" class="btn btn-secondary w-full">
                            <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 4v11"/><path d="m7 10 5 5 5-5"/><path d="M4 20h16"/></svg></span>
                            <span>Export Database</span>
                        </a>
                        <form method="POST" action="{{ route('database.import') }}" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <div>
                                <label class="mb-1 block font-medium text-[color:var(--text)]">File Database</label>
                                <input class="field" type="file" name="database_file" accept=".sqlite,.db,.sqlite3" required>
                            </div>
                            <button class="btn btn-primary w-full" type="submit">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 20V9"/><path d="m17 14-5-5-5 5"/><path d="M4 4h16"/></svg></span>
                                <span>Import Database</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="list-panel p-3">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div class="text-sm font-semibold">Daftar User</div>
                    <input id="userSearch" class="field max-w-xs" type="text" placeholder="Cari nama / email">
                </div>
                <div class="table-wrap">
                    <table id="userTable" class="table-grid min-w-full text-sm">
                        <thead class="text-left">
                            <tr>
                                <th data-sort="name">Nama</th>
                                <th data-sort="email">Email</th>
                                <th data-sort="role">Role</th>
                                <th data-sort="created_at">Dibuat</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody"></tbody>
                    </table>
                </div>
                <div id="userPagination"></div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        window.runAppPage(function ($, helpers) {
            const state = { users: [] };
            const { showAlert } = helpers;
            const userTable = helpers.createTableController({
                perPage: 8,
                defaultSortKey: 'name',
                search: (user, query) => [user.name, user.email, user.role].some((value) => value.toLowerCase().includes(query)),
                sorters: {
                    name: (user) => user.name,
                    email: (user) => user.email,
                    role: (user) => user.role,
                    created_at: (user) => user.created_at,
                },
            });

            function resetForm() {
                $('#userForm')[0].reset();
                $('#user_id').val('');
                $('#user_role').val('admin');
                $('#userSubmit').html('<span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span><span>Simpan User</span>');
            }

            function drawTable(meta) {
                $('#userTableBody').html(meta.rows.length ? meta.rows.map((user) => `
                    <tr>
                        <td class="font-semibold">${user.name}</td>
                        <td>${user.email}</td>
                        <td><span class="badge ${user.role === 'admin' ? 'badge-warning' : 'badge-ok'}">${user.role}</span></td>
                        <td>${new Date(user.created_at).toLocaleDateString('id-ID')}</td>
                        <td>
                            <button class="btn btn-secondary edit-user" type="button" data-id="${user.id}">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="m4 20 4.5-1 9-9-3.5-3.5-9 9L4 20z"/><path d="M13.5 6.5 17 10"/></svg></span>
                                <span>Edit</span>
                            </button>
                        </td>
                    </tr>
                `).join('') : '<tr><td colspan="5" class="text-center text-[color:var(--muted)]">Belum ada user.</td></tr>');
                userTable.syncSortUi('#userTable thead');
                userTable.renderFooter('#userPagination', meta);
            }

            function renderUsers(users) {
                state.users = users;
                if (userTable.data !== users) {
                    userTable.setData(users);
                }
                userTable.setQuery($('#userSearch').val());
                drawTable(userTable.pagedRows());
            }

            function loadUsers() {
                $.get(@json(route('ajax.users.list')))
                    .done((response) => renderUsers(response))
                    .fail(() => showAlert('Gagal memuat user.', 'error'));
            }

            $('#userSearch').on('input', function () {
                renderUsers(state.users);
            });

            $(document).on('click', '#userTable thead [data-sort]', function () {
                userTable.toggleSort($(this).data('sort'));
                renderUsers(state.users);
            });

            $(document).on('click', '#userPagination .page-btn', function () {
                userTable.setPage(Number($(this).data('page')));
                drawTable(userTable.pagedRows());
            });

            $('#userReset').on('click', resetForm);

            $(document).on('click', '.edit-user', function () {
                const user = state.users.find((item) => item.id === Number($(this).data('id')));
                if (!user) return;
                $('#user_id').val(user.id);
                $('#user_name').val(user.name);
                $('#user_email').val(user.email);
                $('#user_password').val('');
                $('#user_role').val(user.role);
                $('#userSubmit').html('<span class="btn-icon"><svg viewBox="0 0 24 24"><path d="m4 20 4.5-1 9-9-3.5-3.5-9 9L4 20z"/><path d="M13.5 6.5 17 10"/></svg></span><span>Update User</span>');
            });

            $('#userForm').on('submit', function (event) {
                event.preventDefault();
                const userId = $('#user_id').val();
                const url = userId ? @json(url('/ajax/users/__ID__')).replace('__ID__', userId) : @json(route('ajax.users.store'));
                const method = userId ? 'PUT' : 'POST';

                $.ajax({
                    url,
                    method,
                    data: {
                        name: $('#user_name').val(),
                        email: $('#user_email').val(),
                        password: $('#user_password').val(),
                        role: $('#user_role').val(),
                    },
                }).done((response) => {
                    showAlert(response.message);
                    resetForm();
                    loadUsers();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal menyimpan user.', 'error');
                });
            });

            $('#toggleUserPassword').on('click', function () {
                const passInput = document.getElementById('user_password');
                const isPassword = passInput.type === 'password';
                passInput.type = isPassword ? 'text' : 'password';
                $('#eyeOpenUserIcon').toggleClass('hidden', !isPassword);
                $('#eyeClosedUserIcon').toggleClass('hidden', isPassword);
            });

            loadUsers();
        });
    </script>
@endsection
