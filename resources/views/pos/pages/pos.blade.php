@extends('layouts.app')

@section('content')
    <section class="pos-shell min-h-[calc(100vh-105px)] flex flex-col overflow-x-hidden max-w-full mb-4">
        <div class="px-5 py-3 border-b border-[#e2e8f0] flex flex-wrap items-center justify-between gap-3 shrink-0">
            <div class="inline-flex rounded-lg border border-[#e2e8f0] bg-[#f8fafc] p-1 gap-1.5">
                <button type="button" class="pos-main-tab menu-link menu-link-active text-xs font-semibold py-1.5 px-3.5" data-pos-tab="hp">HP dan Aksesoris</button>
                <button type="button" class="pos-main-tab menu-link text-xs font-semibold py-1.5 px-3.5" data-pos-tab="perdana">Kartu Perdana</button>
                <button type="button" class="pos-main-tab menu-link text-xs font-semibold py-1.5 px-3.5" data-pos-tab="voucher">Voucher</button>
                <button type="button" class="pos-main-tab menu-link text-xs font-semibold py-1.5 px-3.5" data-pos-tab="pulsa">Pulsa Elektrik</button>
            </div>
            <div class="text-[11px] text-[color:var(--muted)] font-medium">Kasir Cepat POS</div>
        </div>

        <!-- Banner Peringatan Kadaluarsa Produk di POS Dashboard -->
        <div id="posExpiringAlertBanner" class="hidden mx-4 mt-3 mb-0 rounded-xl border border-amber-300 bg-amber-50 p-3 text-xs shrink-0">
            <div class="flex flex-wrap items-center justify-between gap-2.5">
                <div class="flex items-center gap-2">
                    <span class="text-amber-700 font-bold text-sm">⚠️</span>
                    <span class="text-amber-900" id="posExpiringAlertText">Terdapat stok produk yang kadaluarsa atau mendekati masa kadaluarsa (&le; 30 hari).</span>
                </div>
                <button type="button" class="btn btn-secondary border-amber-300 bg-white text-amber-900 hover:bg-amber-100 py-1 px-2.5 text-xs font-semibold" id="btnOpenPosExpiringModal">
                    Lihat Produk Expired
                </button>
            </div>
        </div>

        <div class="pos-main-panel flex-1 overflow-x-hidden p-4 sm:p-5 max-w-full" data-pos-panel="hp">
        <div class="grid gap-4 lg:grid-cols-[1fr_390px] xl:grid-cols-[1fr_420px] max-w-full min-w-0 items-stretch">
            <!-- Left Column: Product Grid (Adapts to viewport, scrolls vertically) -->
            <div class="pos-product-panel p-4 flex flex-col min-w-0 overflow-x-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-3 shrink-0">
                    <div id="categoryTabs" class="category-tabs flex-1 min-w-0"></div>
                    <div class="flex items-center gap-2">
                        <div class="w-full sm:w-64 relative flex items-center">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-[#64748b]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 5v14"/><path d="M8 5v14"/><path d="M12 5v14"/><path d="M17 5v14"/><path d="M21 5v14"/></svg>
                            </div>
                            <input type="text" id="posProductSearch" class="field py-1.5 pl-8 pr-2.5 text-xs" placeholder="Scan Barcode / Cari nama..." autocomplete="off">
                        </div>
                        <button type="button" class="btn btn-secondary py-1.5 px-2.5 text-xs shrink-0" id="openRecentSalesModal" title="Lihat transaksi terakhir kasir">
                            <span class="btn-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
                            <span class="hidden sm:inline font-medium">Riwayat POS</span>
                        </button>
                    </div>
                </div>
                <div id="productPicker" class="product-grid flex-1 overflow-y-auto overflow-x-hidden pr-1.5 pb-2"></div>
            </div>

            <!-- Right Column: Checkout Sidebar (Scrolls vertically only) -->
            <div class="min-w-0 overflow-x-hidden">
                <form id="saleForm" class="pos-product-panel p-4 sm:p-4.5 flex flex-col h-full justify-between overflow-hidden">
                    <div class="flex flex-col flex-1 overflow-hidden min-h-0">
                        <div class="mb-3 grid gap-2.5 grid-cols-3 shrink-0">
                            <div>
                                <label class="mb-1 block font-medium text-[color:var(--muted)]" id="customerNameLabel">Pelanggan</label>
                                <input class="field py-1 px-2" type="text" id="customer_name" placeholder="Umum">
                            </div>
                            <div>
                                <label class="mb-1 block font-medium text-[color:var(--muted)]">Shift</label>
                                <select class="select py-1 px-2" id="shift">
                                    <option value="pagi">Pagi</option>
                                    <option value="sore">Sore</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block font-medium text-[color:var(--muted)]">Metode</label>
                                <select class="select py-1 px-2" id="payment_method">
                                    <option value="cash">Tunai</option>
                                    <option value="qris">QRIS</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="debit">Debit</option>
                                    <option value="utang">Utang (Belum Lunas)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Banner Peringatan Utang di Sidebar -->
                        <div id="debtSidebarBanner" class="hidden mb-3 p-2 bg-amber-50 border border-amber-300 rounded text-amber-900 text-xs">
                            <div class="font-bold flex items-center gap-1 text-[11px]">
                                <span>⚠️ Transaksi Utang / Bon</span>
                            </div>
                            <div class="mt-0.5 text-[11px] text-amber-800">
                                Nama pelanggan wajib diisi dan akan disinkronkan otomatis ke data utang.
                            </div>
                        </div>

                        <div class="table-wrap flex-1 overflow-y-auto min-h-[110px] mb-3">
                            <table class="table-grid min-w-full text-xs">
                                <thead class="text-left sticky top-0 z-10">
                                    <tr>
                                        <th>Item</th>
                                        <th width="105" class="text-center">Qty</th>
                                        <th width="125">IMEI/SN</th>
                                        <th width="85" class="text-right">Subtotal</th>
                                        <th width="35"></th>
                                    </tr>
                                </thead>
                                <tbody id="cartTable">
                                    <tr>
                                        <td colspan="5" class="text-center text-[color:var(--muted)] py-6 text-xs">Keranjang masih kosong.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="shrink-0 pt-2 border-t border-[#f1f5f9]">
                        <input type="hidden" id="discount" value="0">
                        <div class="mb-2.5">
                            <div class="pos-summary p-2.5">
                                <div class="pos-summary-row text-xs">
                                    <span class="text-[color:var(--muted)]">Item / Qty</span>
                                    <strong id="itemCountText">0 (0)</strong>
                                </div>
                                <div class="pos-summary-row text-xs">
                                    <span class="text-[color:var(--muted)]">Subtotal</span>
                                    <strong id="subtotalText">Rp 0</strong>
                                </div>
                                <div class="pos-summary-row pos-summary-total mt-1 pt-1">
                                    <span class="text-[color:var(--muted)] font-semibold text-xs">Total</span>
                                    <strong id="totalText" class="text-sm">Rp 0</strong>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button class="btn btn-primary flex-1 min-h-[36px] text-xs font-semibold" type="button" id="openPaymentModal">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M3 7h18"/><path d="M5 5h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/><path d="M16 15h2"/></svg></span>
                                <span>Bayar</span>
                            </button>
                            <button class="btn btn-secondary min-h-[36px] text-xs" type="button" id="clearCart">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg></span>
                                <span>Kosongkan</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        </div>

        <div class="pos-main-panel hidden p-4 sm:p-5" data-pos-panel="perdana">
            <iframe
                class="min-h-[760px] w-full border-0 rounded-xl"
                src="{{ route('perdana.index', ['embedded' => 1]) }}"
                title="Kartu Perdana"
            ></iframe>
        </div>

        <div class="pos-main-panel hidden p-4 sm:p-5" data-pos-panel="voucher">
            <iframe
                class="min-h-[760px] w-full border-0 rounded-xl"
                src="{{ route('voucher.index', ['embedded' => 1]) }}"
                title="Voucher"
            ></iframe>
        </div>

        <div class="pos-main-panel hidden p-4 sm:p-5" data-pos-panel="pulsa">
            <iframe
                class="min-h-[760px] w-full border-0 rounded-xl"
                src="{{ route('pulsa.index', ['embedded' => 1]) }}"
                title="Pulsa Elektrik"
            ></iframe>
        </div>
    </section>

    <div id="paymentModal" class="modal-backdrop hidden">
        <div class="modal-card">
            <div class="modal-head flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-semibold" id="modalPaymentTitle">Pembayaran</h2>
                    <p class="mt-1 text-[12px] text-[color:var(--muted)]" id="modalPaymentSubtitle">Input nominal yang diberikan customer, lalu simpan transaksi.</p>
                </div>
                <button type="button" class="btn btn-secondary" id="closePaymentModal">
                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg></span>
                    <span>Tutup</span>
                </button>
            </div>
            <div class="modal-body space-y-4">
                <!-- Banner Notifikasi Sinkronisasi Utang -->
                <div id="modalDebtBanner" class="hidden p-3 bg-amber-50 border border-amber-300 rounded text-amber-900 text-xs">
                    <div class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <span>Transaksi Penjualan Utang (Bon)</span>
                    </div>
                    <div class="mt-1 text-[11px] text-amber-800">
                        Nama pelanggan wajib diisi. Data penjualan ini akan langsung disinkronkan ke rekapitulasi & buku transaksi utang toko.
                    </div>
                </div>

                <!-- Field Nama Pelanggan & No Telepon -->
                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block font-medium text-xs" id="modalCustomerNameLabel">
                            Nama Pelanggan <span id="modalCustomerRequired" class="text-red-500 hidden">* (Wajib Diisi)</span>
                        </label>
                        <input class="field text-sm" type="text" id="modal_customer_name" placeholder="Nama pelanggan / penghutang">
                    </div>
                    <div id="modalCustomerPhoneWrapper" class="hidden">
                        <label class="mb-1 block font-medium text-xs">No. HP / WA Pelanggan (Opsional)</label>
                        <input class="field text-sm" type="text" id="modal_customer_phone" placeholder="08xxxxxxxxxx">
                    </div>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <div class="pos-summary">
                        <div class="pos-summary-row">
                            <span class="text-[color:var(--muted)]">Metode</span>
                            <strong id="modalPaymentMethodText">Tunai</strong>
                        </div>
                        <div class="pos-summary-row">
                            <span class="text-[color:var(--muted)]">Total Belanja</span>
                            <strong id="modalTotalText">Rp 0</strong>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block font-medium" id="modalCashPaidLabel">Uang Diberikan</label>
                        <input class="field" type="text" inputmode="numeric" data-number-mask id="cash_paid_modal" value="0">
                        <div class="mt-3 pos-summary">
                            <div class="pos-summary-row">
                                <span class="text-[color:var(--muted)]" id="modalChangeLabel">Kembalian</span>
                                <strong id="modalChangeText">Rp 0</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="button" class="btn btn-primary flex-1" id="savePayment">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                        <span id="savePaymentText">Simpan Transaksi</span>
                    </button>
                    <button type="button" class="btn btn-secondary flex-1" id="saveAndNewPayment">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/><path d="M4 4h16v16H4z"/></svg></span>
                        <span>Simpan & Baru</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Riwayat Transaksi Terakhir POS -->
    <div id="recentSalesModal" class="modal-backdrop hidden">
        <div class="modal-card max-w-xl">
            <div class="modal-head flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-base">Riwayat Transaksi Kasir POS</h2>
                    <p class="mt-1 text-xs text-[color:var(--muted)]">10 transaksi POS terbaru yang baru saja diproses.</p>
                </div>
                <button type="button" class="btn btn-secondary" id="closeRecentSalesModal">
                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg></span>
                    <span>Tutup</span>
                </button>
            </div>
            <div class="modal-body p-4 space-y-3">
                <div id="recentSalesList" class="space-y-2.5 max-h-[60vh] overflow-y-auto pr-1">
                    <div class="text-center py-6 text-xs text-[color:var(--muted)]">Memuat riwayat transaksi...</div>
                </div>
                <div class="pt-2 border-t border-[#edf2f7] text-right">
                    <a href="{{ route('sales-history.index') }}" class="text-xs font-semibold text-blue-600 hover:underline inline-flex items-center gap-1">
                        <span>Buka Riwayat Penjualan Lengkap</span>
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Produk Expired di Dashboard POS -->
    <div id="posExpiringModal" class="modal-backdrop hidden">
        <div class="modal-card max-w-3xl">
            <div class="modal-head flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-sm text-amber-900 flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Peringatan Kadaluarsa Voucher & Kartu Perdana (&le; 30 Hari)
                    </h3>
                    <p class="text-[11px] text-[color:var(--muted)]">Daftar item voucher fisik & kartu perdana yang memerlukan penanganan sebelum atau sesudah kadaluarsa.</p>
                </div>
                <button type="button" class="btn-icon text-[color:var(--muted)] hover:text-[color:var(--text)] close-pos-expiring-modal">
                    <svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg>
                </button>
            </div>
            <div class="modal-body p-4 space-y-3">
                <div class="table-wrap max-h-[380px] overflow-y-auto">
                    <table class="table-grid min-w-full text-xs">
                        <thead class="text-left sticky top-0 bg-[#f8fafc] z-10">
                            <tr>
                                <th>Produk</th>
                                <th>Tipe</th>
                                <th>Serial Number</th>
                                <th class="text-center">Sisa Qty</th>
                                <th>Tgl Kadaluarsa</th>
                                <th>Status / Sisa Hari</th>
                            </tr>
                        </thead>
                        <tbody id="posExpiringTableBody"></tbody>
                    </table>
                </div>
                <div class="pt-2 border-t border-[#edf2f7] flex justify-between items-center text-xs">
                    <span class="text-[color:var(--muted)]">Untuk menyesuaikan stok atau mengedit serial number, buka menu Stock.</span>
                    <a href="{{ route('stock.index') }}" class="btn btn-secondary py-1 px-3 text-xs font-semibold">Buka Menu Stock</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        window.runAppPage(function ($, helpers) {
            const state = { products: [], cart: [], activeCategory: 'all' };
            const { money, showAlert, maskedNumberValue, setMaskedNumberValue, bindNumberMasks, parseNumber, formatNumber } = helpers;

            function paymentMethodLabel(value) {
                return {
                    cash: 'Tunai',
                    qris: 'QRIS',
                    transfer: 'Transfer',
                    debit: 'Debit',
                    utang: 'Utang (Belum Lunas)',
                }[value] || value;
            }

            function cartTotals() {
                const subtotal = state.cart.reduce((sum, item) => sum + (Number(item.price) * Number(item.quantity)), 0);
                const totalQty = state.cart.reduce((sum, item) => sum + Number(item.quantity), 0);
                const discount = maskedNumberValue('#discount');
                const total = Math.max(subtotal - discount, 0);
                const cashPaid = maskedNumberValue('#cash_paid_modal');
                return { subtotal, discount, total, totalQty, itemCount: state.cart.length, change: Math.max(cashPaid - total, 0) };
            }

            function resetSaleForm(fullReset = true) {
                state.cart = [];
                if (fullReset) {
                    $('#saleForm')[0].reset();
                    $('#payment_method').val('cash');
                }
                $('#customer_name').val('');
                $('#modal_customer_name').val('');
                $('#modal_customer_phone').val('');
                setMaskedNumberValue('#discount', 0);
                setMaskedNumberValue('#cash_paid_modal', 0);
                $('#paymentModal').addClass('hidden');
                renderCart();
                updatePaymentLabels();
            }

            function updatePaymentLabels() {
                const method = $('#payment_method').val();
                const isDebt = method === 'utang';
                const methodLabel = paymentMethodLabel(method);
                $('#paymentMethodText').text(methodLabel);
                $('#modalPaymentMethodText').text(methodLabel);
                $('#modalTotalText').text($('#totalText').text());

                const totals = cartTotals();
                const paid = maskedNumberValue('#cash_paid_modal');

                if (isDebt) {
                    $('#debtSidebarBanner').removeClass('hidden');
                    $('#customerNameLabel').html('Nama Penghutang <span class="text-red-500 font-bold">*</span>');
                    $('#customer_name').attr('placeholder', 'Nama penghutang (Wajib)').addClass('border-amber-400 bg-amber-50/20');

                    $('#modalPaymentTitle').text('Konfirmasi Penjualan Utang');
                    $('#modalPaymentSubtitle').text('Pastikan nama penghutang terisi untuk disinkronkan ke data utang.');
                    $('#modalDebtBanner').removeClass('hidden');
                    $('#modalCustomerRequired').removeClass('hidden');
                    $('#modalCustomerPhoneWrapper').removeClass('hidden');
                    $('#modalCustomerNameLabel').html('Nama Penghutang <span class="text-red-500 font-bold">*</span>');
                    $('#modal_customer_name').attr('placeholder', 'Masukkan nama yang berutang').addClass('border-amber-400');
                    $('#modalCashPaidLabel').text('Uang Muka / DP (Opsional, isi 0 jika utang penuh)');
                    $('#modalChangeLabel').text('Sisa Utang');

                    const remainingDebt = Math.max(0, totals.total - paid);
                    $('#modalChangeText').text(money(remainingDebt));
                    $('#modalChangeText').removeClass('text-slate-800').addClass('text-amber-800 font-bold');
                    $('#savePaymentText').text('Simpan Penjualan Utang');
                } else {
                    $('#debtSidebarBanner').addClass('hidden');
                    $('#customerNameLabel').text('Pelanggan');
                    $('#customer_name').attr('placeholder', 'Umum').removeClass('border-amber-400 bg-amber-50/20');

                    $('#modalPaymentTitle').text('Pembayaran');
                    $('#modalPaymentSubtitle').text('Input nominal yang diberikan customer, lalu simpan transaksi.');
                    $('#modalDebtBanner').addClass('hidden');
                    $('#modalCustomerRequired').addClass('hidden');
                    $('#modalCustomerPhoneWrapper').addClass('hidden');
                    $('#modalCustomerNameLabel').text('Nama Pelanggan (Opsional)');
                    $('#modal_customer_name').attr('placeholder', 'Nama pelanggan / umum').removeClass('border-amber-400');
                    $('#modalCashPaidLabel').text('Uang Diberikan');
                    $('#modalChangeLabel').text('Kembalian');

                    $('#modalChangeText').text(money(totals.change));
                    $('#modalChangeText').removeClass('text-amber-800 font-bold').addClass('text-slate-800');
                    $('#savePaymentText').text('Simpan Transaksi');
                }
            }

            function renderRecentSales(items) {
                if (!$('#recentSalesList').length) return;
                const sales = items || [];
                $('#recentSalesList').html(sales.length ? sales.map((item) => {
                    const saleDate = item.sale_date ? new Date(item.sale_date).toLocaleString('id-ID') : '-';
                    const saleItems = item.items || [];
                    return `
                        <div class="recent-card p-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="font-semibold">${item.invoice_number || '-'}</div>
                                    <div class="text-[11px] text-[color:var(--muted)]">${item.customer_name || 'Pelanggan umum'} · ${paymentMethodLabel(item.payment_method)}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-emerald-700">${money(item.total)}</div>
                                    <div class="text-[11px] text-[color:var(--muted)]">${saleDate}</div>
                                </div>
                            </div>
                            ${saleItems.length ? `
                                <div class="mt-2 pt-2 border-t border-[#edf2f7] space-y-1">
                                    ${saleItems.map((si) => `
                                        <div class="flex items-center justify-between text-xs text-[color:var(--muted)]">
                                            <span>${si.product?.name || 'Produk'} ${si.product?.variant ? `(${si.product.variant})` : ''} <span class="text-[color:var(--foreground)] font-medium">x${si.quantity}</span></span>
                                            ${si.serial_number ? `<span class="badge badge-ok font-mono text-[11px]">IMEI: ${si.serial_number}</span>` : ''}
                                        </div>
                                    `).join('')}
                                </div>
                            ` : ''}
                        </div>
                    `;
                }).join('') : '<div class="text-[color:var(--muted)]">Belum ada transaksi.</div>');
            }

            function renderCategoryTabs(products) {
                const categories = ['all'].concat([...new Set((products || []).map((product) => product.category).filter(Boolean))]);
                $('#categoryTabs').html(categories.map((category) => {
                    const isActive = state.activeCategory === category;
                    const label = category === 'all' ? 'Semua' : category;
                    return `
                        <button type="button" class="category-tab ${isActive ? 'category-tab-active' : ''}" data-category="${category}">
                            ${label}
                        </button>
                    `;
                }).join(''));
            }

            function isVoucherProduct(product) {
                if (!product) return false;
                const type = (product.product_type || '').toLowerCase();
                const cat = (product.category || '').toLowerCase();
                const name = (product.name || '').toLowerCase();
                return type === 'voucher' || cat.includes('voucher') || name.startsWith('voucher');
            }

            function renderProducts(products) {
                state.products = (products || []).filter((product) => {
                    return product.is_active && product.product_type !== 'pulsa' && !isVoucherProduct(product);
                });
                renderCategoryTabs(state.products);

                const searchQuery = ($('#posProductSearch').val() || '').toLowerCase().trim();

                let visibleProducts = state.activeCategory === 'all'
                    ? state.products
                    : state.products.filter((product) => product.category === state.activeCategory);

                if (searchQuery) {
                    visibleProducts = visibleProducts.filter((p) =>
                        (p.name && p.name.toLowerCase().includes(searchQuery)) ||
                        (p.code && p.code.toLowerCase().includes(searchQuery)) ||
                        (p.variant && p.variant.toLowerCase().includes(searchQuery)) ||
                        (p.serial_number && p.serial_number.toLowerCase().includes(searchQuery))
                    );
                }

                $('#productPicker').html(visibleProducts.length ? visibleProducts.map((product) => {
                    const serialCount = product.available_serials_count || 0;
                    return `
                        <button type="button" class="product-card add-to-cart ${Number(product.stock) < 1 ? 'product-card-disabled' : ''}" data-id="${product.id}" ${Number(product.stock) < 1 ? 'disabled' : ''}>
                            <div>
                                <div class="font-semibold text-xs leading-tight text-left text-[color:var(--text)] line-clamp-1">${product.name}</div>
                                <div class="mt-0.5 flex items-center justify-between text-[11px] text-[color:var(--muted)]">
                                    <span class="truncate max-w-[85px]">${product.variant || product.unit || product.code}</span>
                                    ${serialCount > 0 ? `<span class="badge badge-ok font-mono text-[11px] py-0 px-1">${serialCount} IMEI</span>` : ''}
                                </div>
                            </div>
                            <div class="mt-1.5 pt-1 border-t border-[#f1f5f9] flex items-end justify-between gap-1">
                                <span class="product-card-price text-xs font-bold">${money(product.price)}</span>
                                <span class="product-card-stock text-[11px]">Stok ${product.stock}</span>
                            </div>
                        </button>
                    `;
                }).join('') : '<div class="col-span-full rounded-xl border border-dashed border-[#cbd5e1] bg-white px-4 py-8 text-center text-xs text-[color:var(--muted)]">Tidak ada produk yang cocok.</div>');
            }

            function renderCart() {
                if (!state.cart.length) {
                    $('#cartTable').html('<tr><td colspan="5" class="text-center text-[color:var(--muted)]">Keranjang masih kosong.</td></tr>');
                } else {
                    $('#cartTable').html(state.cart.map((item) => {
                        const serials = item.available_serials || [];
                        let serialCell = '';

                        if (serials.length > 0) {
                            serialCell = `
                                <div class="space-y-1 min-w-[150px]">
                                    <select class="select cart-serial-select text-xs py-1" data-id="${item.id}">
                                        <option value="">-- Pilih IMEI (${serials.length} ada) --</option>
                                        ${serials.map((s) => `
                                            <option value="${s.serial_number}" ${item.serial_number === s.serial_number ? 'selected' : ''}>
                                                ${s.serial_number} ${s.expiry_date ? `(Exp: ${s.expiry_date})` : ''}
                                            </option>
                                        `).join('')}
                                        <option value="__custom__" ${item.serial_number && !serials.some((s) => s.serial_number === item.serial_number) ? 'selected' : ''}>Ketik Manual / Scan</option>
                                    </select>
                                    <input class="field cart-serial text-xs py-1 font-mono" type="text" data-id="${item.id}" value="${item.serial_number || ''}" placeholder="Ketik / Scan IMEI">
                                </div>
                            `;
                        } else {
                            serialCell = `<input class="field cart-serial text-xs py-1 font-mono" type="text" data-id="${item.id}" value="${item.serial_number || ''}" placeholder="IMEI / Serial (opsional)">`;
                        }

                        return `
                            <tr>
                                <td>
                                    <div class="font-medium text-xs text-[color:var(--text)]">${item.name}</div>
                                    <div class="text-[11px] text-[color:var(--muted)]">${item.variant || item.unit || ''}</div>
                                    <div class="text-[11px] text-[color:var(--muted)]">${money(item.price)}</div>
                                </td>
                                <td class="text-center whitespace-nowrap">
                                    <div class="inline-flex items-center justify-center gap-1">
                                        <button type="button" class="cart-qty-btn cart-qty-minus" data-id="${item.id}" title="Kurangi">−</button>
                                        <input class="cart-qty" type="number" min="1" max="${item.stock}" data-id="${item.id}" value="${item.quantity}">
                                        <button type="button" class="cart-qty-btn cart-qty-plus" data-id="${item.id}" title="Tambah">+</button>
                                    </div>
                                </td>
                                <td>${serialCell}</td>
                                <td class="font-semibold text-xs text-right">${money(item.price * item.quantity)}</td>
                                <td class="text-center">
                                    <button type="button" class="remove-cart inline-flex items-center justify-center text-[color:var(--danger)] text-xs p-1" data-id="${item.id}" title="Hapus Item">
                                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg></span>
                                    </button>
                                </td>
                            </tr>
                        `;
                    }).join(''));
                }

                const totals = cartTotals();
                $('#itemCountText').text(`${totals.itemCount} item`);
                $('#qtyCountText').text(totals.totalQty);
                $('#subtotalText').text(money(totals.subtotal));
                $('#discountText').text(money(totals.discount));
                $('#totalText').text(money(totals.total));
                updatePaymentLabels();
            }

            function renderExpiringAlert(expiringProducts) {
                state.expiringProducts = expiringProducts || [];
                if (state.expiringProducts.length > 0) {
                    const expiredCount = state.expiringProducts.filter((p) => p.is_expired).length;
                    const soonCount = state.expiringProducts.length - expiredCount;

                    let desc = `Terdapat ${state.expiringProducts.length} voucher & kartu perdana yang kadaluarsa atau mendekati masa kadaluarsa (&le; 30 hari).`;
                    if (expiredCount > 0 && soonCount > 0) {
                        desc = `Perhatian: ${expiredCount} voucher/perdana SUDAH KADALUARSA, dan ${soonCount} item akan kadaluarsa dalam 30 hari ke depan.`;
                    } else if (expiredCount > 0) {
                        desc = `Peringatan: ${expiredCount} voucher/perdana SUDAH KADALUARSA! Segera periksa di menu Stock.`;
                    }

                    $('#posExpiringAlertText').text(desc);
                    $('#posExpiringAlertBanner').removeClass('hidden');
                } else {
                    $('#posExpiringAlertBanner').addClass('hidden');
                }
            }

            function renderPosExpiringModalTable() {
                const list = state.expiringProducts || [];
                $('#posExpiringTableBody').html(list.length ? list.map((item) => `
                    <tr>
                        <td>
                            <div class="font-medium">${item.product_name}</div>
                            <div class="text-[11px] text-[color:var(--muted)]">${item.product_code} · ${item.variant || '-'}</div>
                        </td>
                        <td>
                            <span class="badge ${item.item_type === 'Kartu Perdana' ? 'badge-info' : 'badge-primary'} font-semibold text-[11px]">
                                ${item.item_type || 'Voucher'}
                            </span>
                        </td>
                        <td class="font-mono text-xs font-semibold">${item.serial_number || '<span class="text-[color:var(--muted)] font-normal">Non-Serial</span>'}</td>
                        <td class="font-bold text-center">${item.quantity}</td>
                        <td class="font-mono">${item.expiry_date || '-'}</td>
                        <td>
                            <span class="badge ${item.is_expired ? 'border border-red-300 bg-red-100 text-red-700' : 'border border-amber-300 bg-amber-100 text-amber-800'}">
                                ${item.status_text}
                            </span>
                        </td>
                    </tr>
                `).join('') : '<tr><td colspan="6" class="text-center py-6 text-[color:var(--muted)]">Tidak ada voucher & kartu perdana yang kadaluarsa.</td></tr>');
            }

            function loadData() {
                $.getJSON(window.posConfig.dashboardUrl, { _: Date.now() })
                    .done((response) => {
                        try {
                            renderProducts(response.products || []);
                            renderRecentSales(response.recent_sales || []);
                            renderExpiringAlert(response.expiring_products || []);
                            renderCart();
                        } catch (err) {
                            console.error('Error rendering POS data:', err);
                        }
                    })
                    .fail((xhr, status, error) => {
                        console.error('AJAX Error POS Dashboard:', status, error, xhr);
                        showAlert('Gagal memuat halaman POS.', 'error');
                    });
            }

            function submitPayment(createNew) {
                if (!state.cart.length) {
                    showAlert('Keranjang masih kosong.', 'error');
                    return;
                }

                const isDebt = $('#payment_method').val() === 'utang';
                const customerName = ($('#modal_customer_name').val() || $('#customer_name').val() || '').trim();

                if (isDebt && !customerName) {
                    showAlert('Nama pelanggan / penghutang wajib diisi untuk transaksi utang.', 'error');
                    if (!$('#paymentModal').hasClass('hidden')) {
                        $('#modal_customer_name').focus();
                    } else {
                        $('#customer_name').focus();
                    }
                    return;
                }

                const cashPaidValue = maskedNumberValue('#cash_paid_modal');
                const totals = cartTotals();

                if (!isDebt && cashPaidValue < totals.total) {
                    showAlert('Uang bayar kurang dari total belanja.', 'error');
                    $('#cash_paid_modal').focus();
                    return;
                }

                $.post(window.posConfig.salesUrl, {
                    customer_name: customerName,
                    customer_phone: isDebt ? ($('#modal_customer_phone').val() || null) : null,
                    shift: $('#shift').val(),
                    payment_method: $('#payment_method').val(),
                    discount: maskedNumberValue('#discount'),
                    cash_paid: isDebt ? cashPaidValue : maskedNumberValue('#cash_paid_modal'),
                    items: state.cart.map((item) => ({ product_id: item.id, quantity: item.quantity, serial_number: item.serial_number || null })),
                }).done((response) => {
                    showAlert(response.message || `Transaksi berhasil disimpan. Invoice ${response.sale.invoice_number}`);
                    resetSaleForm(createNew);
                    loadData();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal menyimpan transaksi.', 'error');
                });
            }

            function playScanBeep() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(1760, ctx.currentTime);
                    gain.gain.setValueAtTime(0.15, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.08);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.08);
                } catch (e) {
                    // AudioContext optional
                }
            }

            function addToCart(product, specificSerial = null) {
                if (!product) return false;

                if (Number(product.stock) < 1) {
                    showAlert(`Stok produk ${product.name} kosong!`, 'error');
                    return false;
                }

                const existing = state.cart.find((item) => item.id === product.id);
                if (existing) {
                    if (existing.quantity >= product.stock) {
                        showAlert('Jumlah melebihi stok tersedia.', 'error');
                        return false;
                    }
                    existing.quantity += 1;
                    if (specificSerial) {
                        existing.serial_number = specificSerial;
                    }
                } else {
                    const availableSerials = product.available_serials || [];
                    const defaultSerial = specificSerial || (availableSerials.length > 0 ? availableSerials[0].serial_number : '');

                    state.cart.push({
                        id: product.id,
                        name: product.name,
                        category: product.category,
                        variant: product.variant,
                        unit: product.unit,
                        price: Number(product.price),
                        stock: Number(product.stock),
                        quantity: 1,
                        serial_number: defaultSerial,
                        available_serials: availableSerials,
                    });
                }

                renderCart();
                return true;
            }

            function handleBarcodeScan(code) {
                const rawCode = (code || '').trim();
                if (!rawCode) return false;
                const lowerCode = rawCode.toLowerCase();

                // 1. Check exact match on product code or serial_number
                let matchedProduct = state.products.find((p) => 
                    (p.code && p.code.toLowerCase() === lowerCode) || 
                    (p.serial_number && p.serial_number.toLowerCase() === lowerCode)
                );
                let matchedSerial = null;

                // 2. If not matched, check available_serials inside each product
                if (!matchedProduct) {
                    for (const p of state.products) {
                        if (p.available_serials && p.available_serials.length) {
                            const foundSn = p.available_serials.find((s) => s.serial_number && s.serial_number.toLowerCase() === lowerCode);
                            if (foundSn) {
                                matchedProduct = p;
                                matchedSerial = foundSn.serial_number;
                                break;
                            }
                        }
                    }
                }

                if (matchedProduct) {
                    const success = addToCart(matchedProduct, matchedSerial);
                    if (success) {
                        playScanBeep();
                        showAlert(`[Scan] ${matchedProduct.name} ditambahkan!`);
                    }
                    $('#posProductSearch').val('').focus();
                    renderProducts(state.products);
                    return true;
                } else {
                    showAlert(`Barcode/Serial "${rawCode}" tidak ditemukan.`, 'error');
                    return false;
                }
            }

            $(document).on('click', '.add-to-cart', function () {
                const product = state.products.find((item) => item.id === Number($(this).data('id')));
                if (product) {
                    addToCart(product);
                }
            });

            $('#posProductSearch').on('keydown', function (e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    const val = $(this).val().trim();
                    if (val) {
                        handleBarcodeScan(val);
                    }
                }
            });

            let barcodeBuffer = '';
            let lastKeyTime = 0;

            $(document).on('keydown', function (e) {
                const tag = (e.target.tagName || '').toLowerCase();
                const isInput = tag === 'input' || tag === 'textarea' || tag === 'select';
                if (isInput && e.target.id === 'posProductSearch') {
                    return;
                }

                const currentTime = Date.now();
                const isScannerFast = (currentTime - lastKeyTime) < 65;
                lastKeyTime = currentTime;

                if (e.key === 'Enter') {
                    if (barcodeBuffer.length >= 2 && isScannerFast) {
                        e.preventDefault();
                        handleBarcodeScan(barcodeBuffer);
                        barcodeBuffer = '';
                    }
                } else if (e.key && e.key.length === 1) {
                    if (!isScannerFast && barcodeBuffer.length > 0) {
                        barcodeBuffer = '';
                    }
                    barcodeBuffer += e.key;
                }
            });

            $(document).on('click', '.category-tab', function () {
                state.activeCategory = $(this).data('category');
                renderProducts(state.products);
            });

            $('#posProductSearch').on('input', function () {
                renderProducts(state.products);
            });

            $(document).on('click', '.cart-qty-plus', function () {
                const id = Number($(this).data('id'));
                const item = state.cart.find((entry) => entry.id === id);
                if (!item) return;

                if (item.quantity >= item.stock) {
                    showAlert('Jumlah melebihi stok tersedia.', 'error');
                    return;
                }
                item.quantity += 1;
                renderCart();
            });

            $(document).on('click', '.cart-qty-minus', function () {
                const id = Number($(this).data('id'));
                const item = state.cart.find((entry) => entry.id === id);
                if (!item) return;

                if (item.quantity > 1) {
                    item.quantity -= 1;
                    renderCart();
                }
            });

            $(document).on('change', '.cart-qty', function () {
                const id = Number($(this).data('id'));
                const item = state.cart.find((entry) => entry.id === id);
                if (!item) return;

                let val = parseInt($(this).val(), 10);
                if (isNaN(val) || val < 1) val = 1;
                if (val > item.stock) {
                    val = item.stock;
                    showAlert('Jumlah melebihi stok tersedia.', 'error');
                }
                item.quantity = val;
                renderCart();
            });

            $(document).on('change', '.cart-serial-select', function () {
                const id = Number($(this).data('id'));
                const item = state.cart.find((entry) => entry.id === id);
                if (!item) return;
                const val = $(this).val();
                if (val === '__custom__') {
                    item.serial_number = '';
                    $(`.cart-serial[data-id="${id}"]`).val('').focus();
                } else {
                    item.serial_number = val;
                    $(`.cart-serial[data-id="${id}"]`).val(val);
                }
            });

            $(document).on('input', '.cart-serial', function () {
                const id = Number($(this).data('id'));
                const item = state.cart.find((entry) => entry.id === id);
                if (!item) return;
                const val = $(this).val().trim();
                item.serial_number = val;
                const $sel = $(`.cart-serial-select[data-id="${id}"]`);
                if ($sel.length) {
                    const hasOption = $sel.find(`option[value="${val}"]`).length > 0;
                    if (hasOption) {
                        $sel.val(val);
                    } else {
                        $sel.val(val ? '__custom__' : '');
                    }
                }
            });

            $(document).on('click', '.remove-cart', function () {
                state.cart = state.cart.filter((item) => item.id !== Number($(this).data('id')));
                renderCart();
            });

            $(document).on('input', '[data-number-mask]', function () {
                $(this).val(formatNumber(parseNumber($(this).val())));
                updatePaymentLabels();
                renderCart();
            });

            $('#payment_method').on('change', updatePaymentLabels);

            $('.pos-main-tab').on('click', function () {
                const tab = $(this).data('pos-tab');
                $('.pos-main-tab').removeClass('menu-link-active');
                $(this).addClass('menu-link-active');
                $('.pos-main-panel').addClass('hidden');
                $(`.pos-main-panel[data-pos-panel="${tab}"]`).removeClass('hidden');
            });

            $('#customer_name').on('input', function () {
                $('#modal_customer_name').val($(this).val());
            });

            $('#modal_customer_name').on('input', function () {
                $('#customer_name').val($(this).val());
            });

            $('#openPaymentModal').on('click', function () {
                if (!state.cart.length) {
                    showAlert('Keranjang masih kosong.', 'error');
                    return;
                }

                const isDebt = $('#payment_method').val() === 'utang';
                $('#modal_customer_name').val($('#customer_name').val());

                if (isDebt) {
                    setMaskedNumberValue('#cash_paid_modal', 0);
                } else {
                    setMaskedNumberValue('#cash_paid_modal', cartTotals().total);
                }

                $('#paymentModal').removeClass('hidden');
                updatePaymentLabels();

                setTimeout(() => {
                    if (isDebt && !$('#modal_customer_name').val().trim()) {
                        $('#modal_customer_name').focus();
                    } else if (!isDebt) {
                        $('#cash_paid_modal').focus();
                    }
                }, 50);
            });

            $('#closePaymentModal').on('click', function () {
                $('#paymentModal').addClass('hidden');
            });

            $('#openRecentSalesModal').on('click', function () {
                $('#recentSalesModal').removeClass('hidden');
            });

            $('#closeRecentSalesModal').on('click', function () {
                $('#recentSalesModal').addClass('hidden');
            });

            $('#clearCart').on('click', function () {
                resetSaleForm(true);
            });

            $('#savePayment').on('click', function () {
                submitPayment(false);
            });

            $('#saveAndNewPayment').on('click', function () {
                submitPayment(true);
            });

            $('#btnOpenPosExpiringModal').on('click', function () {
                renderPosExpiringModalTable();
                $('#posExpiringModal').removeClass('hidden');
            });

            $(document).on('click', '.close-pos-expiring-modal', function () {
                $('#posExpiringModal').addClass('hidden');
            });

            bindNumberMasks(document);
            updatePaymentLabels();
            loadData();
        });
    </script>
@endsection
