<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Zeni POS' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="m-0">
        @php
            $productMenuActive = request()->routeIs('master.physical.*') || request()->routeIs('master.electric.*');
            $embedded = request()->boolean('embedded');
        @endphp
        <div class="{{ $embedded ? 'w-full' : 'window-shell w-full overflow-hidden' }}">
            @unless ($embedded)
            <header class="title-bar flex items-center justify-between px-4 py-2">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center border border-white/40 bg-white/15 text-sm font-bold">Z</div>
                    <div>
                        <div class="text-sm font-semibold">Zeni POS</div>
                        <div class="text-[11px] text-white/80">Point of Sale & Stock Control</div>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-[11px] text-white/90">
                    <div class="user-chip">
                        <span class="user-chip-name">{{ auth()->user()->name }}</span>
                        <span class="user-chip-role">{{ strtoupper(auth()->user()->role) }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout-btn" type="submit">
                            <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M10 17 15 12 10 7"/><path d="M4 12h11"/><path d="M20 4v16"/></svg></span>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </header>

            <nav class="border-b border-[#d7e0eb] bg-white px-3">
                <div class="flex flex-wrap items-center justify-between gap-3 py-1.5">
                    <div class="flex flex-wrap gap-1">
                        <a href="{{ route('pos.index') }}" class="menu-link {{ request()->routeIs('pos.*') ? 'menu-link-active' : '' }}">POS</a>
                        <a href="{{ route('stock.index') }}" class="menu-link {{ request()->routeIs('stock.*') ? 'menu-link-active' : '' }}">Stock</a>
                        <a href="{{ route('expenses.index') }}" class="menu-link {{ request()->routeIs('expenses.*') ? 'menu-link-active' : '' }}">Pengeluaran</a>
                        <a href="{{ route('sales-history.index') }}" class="menu-link {{ request()->routeIs('sales-history.*') ? 'menu-link-active' : '' }}">Riwayat Penjualan</a>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('report.index') }}" class="menu-link {{ request()->routeIs('report.*') ? 'menu-link-active' : '' }}">Report</a>
                            <div class="menu-dropdown">
                                <button type="button" class="menu-link menu-dropdown-toggle {{ $productMenuActive ? 'menu-link-active' : '' }}">
                                    Produk
                                </button>
                                <div class="menu-dropdown-panel">
                                    <a href="{{ route('master.physical.index') }}" class="menu-dropdown-item {{ request()->routeIs('master.physical.*') ? 'menu-dropdown-item-active' : '' }}">Produk Fisik</a>
                                    <a href="{{ route('master.electric.index') }}" class="menu-dropdown-item {{ request()->routeIs('master.electric.*') ? 'menu-dropdown-item-active' : '' }}">Produk Elektrik</a>
                                </div>
                            </div>
                            <a href="{{ route('master.wallets.index') }}" class="menu-link {{ request()->routeIs('master.wallets.*') ? 'menu-link-active' : '' }}">Dompet Saldo</a>
                            <a href="{{ route('users.index') }}" class="menu-link {{ request()->routeIs('users.*') ? 'menu-link-active' : '' }}">User Management</a>
                        @endif
                    </div>
                </div>
            </nav>
            @endunless

            <main class="{{ $embedded ? 'p-0' : 'workspace p-4' }}">
                @yield('content')
            </main>

            @unless ($embedded)
            <footer class="statusbar flex items-center justify-between px-3 py-1.5 text-[11px] text-[color:var(--muted)]">
                <div>Copyright PT Numa Teknologi Nusantara</div>
                <div>{{ now()->format('d M Y') }}</div>
            </footer>
            @endunless
        </div>

        <script>
            window.posConfig = {
                dashboardUrl: @json(route('ajax.dashboard')),
                productsUrl: @json(route('ajax.products')),
                storeProductUrl: @json(route('ajax.products.store')),
                salesUrl: @json(route('ajax.sales.store')),
                stockUrlTemplate: @json(url('/ajax/products/__ID__/stock')),
                updateProductUrlTemplate: @json(url('/ajax/products/__ID__')),
            };

            window.runAppPage = function (callback) {
                document.addEventListener('DOMContentLoaded', function () {
                    const waitForJQuery = function () {
                        if (!window.$) {
                            window.setTimeout(waitForJQuery, 15);
                            return;
                        }

                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        $(document).ajaxError(function (event, jqxhr) {
                            if (jqxhr.status === 401 || jqxhr.status === 419) {
                                window.location.href = @json(route('login'));
                            }
                        });

                        if (!window.posHelpers) {
                            window.posHelpers = {
                                money(value) {
                                    return new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR',
                                        maximumFractionDigits: 0,
                                    }).format(Number(value || 0));
                                },
                                showAlert(message, type = 'success') {
                                    window.Toastify({
                                        text: message,
                                        duration: 2800,
                                        gravity: 'top',
                                        position: 'left',
                                        className: `toastify-center ${type === 'error' ? 'toast-error' : 'toast-success'}`,
                                        stopOnFocus: true,
                                    }).showToast();
                                },
                                stockUrl(id) {
                                    return window.posConfig.stockUrlTemplate.replace('__ID__', id);
                                },
                                updateProductUrl(id) {
                                    return window.posConfig.updateProductUrlTemplate.replace('__ID__', id);
                                },
                                parseNumber(value) {
                                    const normalized = String(value ?? '').replace(/[^\d]/g, '');
                                    return normalized ? Number(normalized) : 0;
                                },
                                formatNumber(value) {
                                    return new Intl.NumberFormat('id-ID', {
                                        maximumFractionDigits: 0,
                                    }).format(Number(value || 0));
                                },
                                maskedNumberValue(selector) {
                                    return window.posHelpers.parseNumber($(selector).val());
                                },
                                setMaskedNumberValue(selector, value) {
                                    $(selector).val(window.posHelpers.formatNumber(value));
                                },
                                bindNumberMasks(scope = document) {
                                    const helpers = window.posHelpers;
                                    $(scope).find('[data-number-mask]').each(function () {
                                        const $input = $(this);
                                        $input.addClass('field-number');
                                        $input.val(helpers.formatNumber(helpers.parseNumber($input.val())));
                                    });
                                },
                                createTableController(config = {}) {
                                    const controller = {
                                        data: [],
                                        query: '',
                                        page: 1,
                                        perPage: config.perPage || 10,
                                        sortKey: config.defaultSortKey || null,
                                        sortDir: config.defaultSortDir || 'asc',
                                        setData(rows) {
                                            this.data = Array.isArray(rows) ? rows : [];
                                            this.page = 1;
                                        },
                                        setQuery(query) {
                                            this.query = String(query || '').trim().toLowerCase();
                                            this.page = 1;
                                        },
                                        toggleSort(key) {
                                            if (this.sortKey === key) {
                                                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                                            } else {
                                                this.sortKey = key;
                                                this.sortDir = 'asc';
                                            }
                                            this.page = 1;
                                        },
                                        setPage(page) {
                                            this.page = page;
                                        },
                                        filteredRows() {
                                            if (!this.query || !config.search) {
                                                return [...this.data];
                                            }
                                            return this.data.filter((row) => config.search(row, this.query));
                                        },
                                        sortedRows() {
                                            const rows = this.filteredRows();
                                            if (!this.sortKey || !config.sorters?.[this.sortKey]) {
                                                return rows;
                                            }

                                            const sorter = config.sorters[this.sortKey];
                                            return rows.sort((a, b) => {
                                                const left = sorter(a);
                                                const right = sorter(b);
                                                const first = typeof left === 'string' ? left.toLowerCase() : left;
                                                const second = typeof right === 'string' ? right.toLowerCase() : right;

                                                if (first === second) return 0;
                                                const result = first > second ? 1 : -1;
                                                return this.sortDir === 'asc' ? result : -result;
                                            });
                                        },
                                        pagedRows() {
                                            const rows = this.sortedRows();
                                            const totalPages = Math.max(1, Math.ceil(rows.length / this.perPage));
                                            this.page = Math.min(this.page, totalPages);
                                            const start = (this.page - 1) * this.perPage;
                                            return {
                                                rows: rows.slice(start, start + this.perPage),
                                                totalRows: rows.length,
                                                totalPages,
                                                page: this.page,
                                                start: rows.length ? start + 1 : 0,
                                                end: Math.min(start + this.perPage, rows.length),
                                            };
                                        },
                                        renderFooter(targetSelector, meta) {
                                            const buttons = [];
                                            for (let page = 1; page <= meta.totalPages; page += 1) {
                                                buttons.push(`
                                                    <button type="button" class="page-btn ${page === meta.page ? 'page-btn-active' : ''}" data-page="${page}" data-target="${targetSelector}">
                                                        ${page}
                                                    </button>
                                                `);
                                            }

                                            $(targetSelector).html(`
                                                <div class="table-footer">
                                                    <div class="table-meta">Menampilkan ${meta.start}-${meta.end} dari ${meta.totalRows} data</div>
                                                    <div class="pagination">${buttons.join('')}</div>
                                                </div>
                                            `);
                                        },
                                        syncSortUi(scopeSelector) {
                                            const $scope = $(scopeSelector);
                                            $scope.find('[data-sort]').removeClass('table-sort-active-asc table-sort-active-desc').addClass('table-sort');
                                            if (this.sortKey) {
                                                $scope.find(`[data-sort="${this.sortKey}"]`)
                                                    .removeClass('table-sort')
                                                    .addClass(this.sortDir === 'asc' ? 'table-sort-active-asc' : 'table-sort-active-desc');
                                            }
                                        },
                                    };

                                    return controller;
                                },
                            };
                        }

                        callback($, window.posHelpers);
                    };

                    waitForJQuery();
                });
            };
        </script>
        @if (session('status'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const waitForHelpers = function () {
                        if (!window.posHelpers) {
                            window.setTimeout(waitForHelpers, 15);
                            return;
                        }
                        window.posHelpers.showAlert(@json(session('status')), 'success');
                    };
                    waitForHelpers();
                });
            </script>
        @endif
        @if ($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const waitForHelpers = function () {
                        if (!window.posHelpers) {
                            window.setTimeout(waitForHelpers, 15);
                            return;
                        }
                        window.posHelpers.showAlert(@json($errors->first()), 'error');
                    };
                    waitForHelpers();
                });
            </script>
        @endif
        @yield('scripts')
    </body>
</html>
