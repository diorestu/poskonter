@extends('layouts.app')

@section('content')
    <section class="space-y-4">
        <!-- Header & Ringkasan Hari Ini -->
        <div class="list-panel p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white font-bold text-xs shadow-sm">
                            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect width="16" height="20" x="4" y="2" rx="2" />
                                <line x1="8" x2="16" y1="6" y2="6" />
                                <line x1="16" x2="16" y1="14" y2="14" />
                                <path d="M16 10h.01" />
                                <path d="M12 10h.01" />
                                <path d="M8 10h.01" />
                                <path d="M12 14h.01" />
                                <path d="M8 14h.01" />
                                <path d="M12 18h.01" />
                                <path d="M8 18h.01" />
                            </svg>
                        </span>
                        <div>
                            <h1 class="text-base font-bold text-[color:var(--text)]">Penjualan Kartu Perdana</h1>
                            <p class="text-xs text-[color:var(--muted)]">Input manual nama operator, harga jual, dan harga beli (modal) kartu perdana.</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50/80 px-3 py-1.5 text-xs">
                        <span class="text-emerald-700 font-medium">Penjualan Hari Ini:</span>
                        <strong id="todayPerdanaTotal" class="ml-1 text-emerald-800 font-bold">Rp 0</strong>
                        <span id="todayPerdanaCount" class="ml-1 text-[11px] text-emerald-600">(0 kartu)</span>
                    </div>
                    <div class="rounded-lg border border-blue-200 bg-blue-50/80 px-3 py-1.5 text-xs">
                        <span class="text-blue-700 font-medium">Laba Hari Ini:</span>
                        <strong id="todayPerdanaProfit" class="ml-1 text-blue-800 font-bold">Rp 0</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2 Column Layout: Quick Operators + History on Left, Sale Form on Right -->
        <div class="grid gap-4 lg:grid-cols-[1fr_400px] xl:grid-cols-[1fr_440px] items-start">
            <!-- Left Side: Quick Operator Badges & Recent Transactions Table -->
            <div class="space-y-4 min-w-0">
                <!-- Pilihan Cepat Operator -->
                <div class="list-panel p-4 space-y-3">
                    <div>
                        <div class="text-xs font-bold text-[color:var(--text)] uppercase tracking-wider">Pilih Operator Cepat</div>
                        <div class="text-[11px] text-[color:var(--muted)]">Klik salah satu operator untuk mengisi nama secara otomatis atau ketik manual di formulir kanan.</div>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2" id="operatorChipsContainer">
                        <button type="button" class="operator-chip flex items-center gap-2 p-2 rounded-lg border border-rose-200 bg-rose-50/70 hover:bg-rose-100/80 text-rose-900 transition-all text-left text-xs font-semibold" data-operator="Telkomsel">
                            <span class="inline-block h-3 w-3 rounded-full bg-rose-600 shrink-0"></span>
                            <span>Telkomsel</span>
                        </button>
                        <button type="button" class="operator-chip flex items-center gap-2 p-2 rounded-lg border border-amber-200 bg-amber-50/70 hover:bg-amber-100/80 text-amber-900 transition-all text-left text-xs font-semibold" data-operator="Indosat IM3">
                            <span class="inline-block h-3 w-3 rounded-full bg-amber-500 shrink-0"></span>
                            <span>Indosat IM3</span>
                        </button>
                        <button type="button" class="operator-chip flex items-center gap-2 p-2 rounded-lg border border-blue-200 bg-blue-50/70 hover:bg-blue-100/80 text-blue-900 transition-all text-left text-xs font-semibold" data-operator="XL">
                            <span class="inline-block h-3 w-3 rounded-full bg-blue-600 shrink-0"></span>
                            <span>XL Axiata</span>
                        </button>
                        <button type="button" class="operator-chip flex items-center gap-2 p-2 rounded-lg border border-purple-200 bg-purple-50/70 hover:bg-purple-100/80 text-purple-900 transition-all text-left text-xs font-semibold" data-operator="AXIS">
                            <span class="inline-block h-3 w-3 rounded-full bg-purple-600 shrink-0"></span>
                            <span>AXIS</span>
                        </button>
                        <button type="button" class="operator-chip flex items-center gap-2 p-2 rounded-lg border border-orange-200 bg-orange-50/70 hover:bg-orange-100/80 text-orange-900 transition-all text-left text-xs font-semibold" data-operator="Tri (3)">
                            <span class="inline-block h-3 w-3 rounded-full bg-orange-500 shrink-0"></span>
                            <span>Tri (3)</span>
                        </button>
                        <button type="button" class="operator-chip flex items-center gap-2 p-2 rounded-lg border border-pink-200 bg-pink-50/70 hover:bg-pink-100/80 text-pink-900 transition-all text-left text-xs font-semibold" data-operator="Smartfren">
                            <span class="inline-block h-3 w-3 rounded-full bg-pink-600 shrink-0"></span>
                            <span>Smartfren</span>
                        </button>
                        <button type="button" class="operator-chip flex items-center gap-2 p-2 rounded-lg border border-sky-200 bg-sky-50/70 hover:bg-sky-100/80 text-sky-900 transition-all text-left text-xs font-semibold" data-operator="by.U">
                            <span class="inline-block h-3 w-3 rounded-full bg-sky-500 shrink-0"></span>
                            <span>by.U</span>
                        </button>
                        <button type="button" class="operator-chip flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50/70 hover:bg-slate-100/80 text-slate-800 transition-all text-left text-xs font-semibold" data-operator="Perdana Lainnya">
                            <span class="inline-block h-3 w-3 rounded-full bg-slate-400 shrink-0"></span>
                            <span>Lainnya</span>
                        </button>
                    </div>
                </div>

                <!-- Riwayat Penjualan Perdana Hari Ini -->
                <div class="list-panel p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-[color:var(--text)] uppercase tracking-wider">Riwayat Penjualan Perdana</div>
                            <div class="text-[11px] text-[color:var(--muted)]">Daftar transaksi penjualan kartu perdana terbaru.</div>
                        </div>
                        <button type="button" class="btn btn-secondary py-1 px-2.5 text-xs" id="btnRefreshHistory">
                            <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg></span>
                            <span>Segarkan</span>
                        </button>
                    </div>

                    <div class="table-wrap rounded-lg border border-[#e2e8f0] overflow-x-auto max-h-[460px] overflow-y-auto">
                        <table class="table-grid min-w-full text-xs">
                            <thead class="bg-slate-50 sticky top-0 z-10 text-left">
                                <tr>
                                    <th>Ref / Waktu</th>
                                    <th>Shift</th>
                                    <th>Operator / Nama</th>
                                    <th class="text-right">Harga Beli</th>
                                    <th class="text-right">Harga Jual</th>
                                    <th class="text-right">Laba</th>
                                    <th>Bayar</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" width="70">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="perdanaHistoryBody">
                                <tr>
                                    <td colspan="9" class="text-center py-6 text-[color:var(--muted)]">Memuat data transaksi...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Side: Dedicated Perdana Sale Form -->
            <div class="space-y-4 self-start lg:sticky lg:top-[calc(var(--header-height,0px)+1rem)]">
                <form id="perdanaForm" class="list-panel p-4 space-y-3.5 shadow-sm">
                    <div class="border-b border-[#e2e8f0] pb-2.5">
                        <div class="text-sm font-bold text-[color:var(--text)]">Form Input Penjualan Perdana</div>
                        <div class="text-[11px] text-[color:var(--muted)]">Tulis manual operator, harga jual & harga beli.</div>
                    </div>

                    <!-- 1. Nama Operator / Produk -->
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-[color:var(--text)]">Nama Operator / Kartu Perdana <span class="text-red-500">*</span></label>
                        <input class="field text-xs py-1.5" type="text" id="perdana_operator_name" placeholder="Contoh: Telkomsel 14GB / Axis Kuota 5GB" required autocomplete="off">
                        <div class="mt-0.5 text-[10px] text-[color:var(--muted)]">Bisa nama operator saja atau beserta detail kuota/paket.</div>
                    </div>

                    <!-- 2. Harga Jual & Harga Beli (Modal) -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-emerald-800">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                            <input class="field text-xs py-1.5 font-bold text-emerald-800 bg-emerald-50/30 border-emerald-300" type="text" inputmode="numeric" data-number-mask id="perdana_price" value="0" required autocomplete="off">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-blue-800">Harga Beli / Modal (Rp) <span class="text-red-500">*</span></label>
                            <input class="field text-xs py-1.5 font-bold text-blue-800 bg-blue-50/30 border-blue-300" type="text" inputmode="numeric" data-number-mask id="perdana_cost_price" value="0" required autocomplete="off">
                        </div>
                    </div>

                    <!-- Live Profit / Margin Preview Box -->
                    <div id="profitPreviewBox" class="rounded-lg border border-emerald-200 bg-emerald-50/60 p-2.5 text-xs flex items-center justify-between">
                        <span class="text-emerald-800 font-medium">Estimasi Laba / Untung:</span>
                        <div class="text-right">
                            <strong id="profitAmountText" class="text-emerald-700 font-bold text-sm">Rp 0</strong>
                            <span id="profitPercentText" class="text-[11px] text-emerald-600 block">(0%)</span>
                        </div>
                    </div>

                    <!-- 3. Nomor Perdana / SN (Opsional) -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-[color:var(--text)]">Nomor Perdana / Barcode SN <span class="text-[10px] text-[color:var(--muted)] font-normal">(Opsional)</span></label>
                        <input class="field text-xs py-1.5 font-mono" type="text" id="perdana_serial_number" placeholder="Contoh: 08123456789 atau scan barcode kartu" autocomplete="off">
                    </div>

                    <!-- 4. Shift & Metode Pembayaran -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-[color:var(--text)]">Shift</label>
                            <select class="select text-xs py-1.5" id="perdana_shift">
                                <option value="pagi" {{ now()->hour < 15 ? 'selected' : '' }}>Pagi</option>
                                <option value="sore" {{ now()->hour >= 15 ? 'selected' : '' }}>Sore</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-[color:var(--text)]">Metode Pembayaran</label>
                            <select class="select text-xs py-1.5" id="perdana_payment_method">
                                <option value="cash">Tunai (Cash)</option>
                                <option value="qris">QRIS</option>
                                <option value="transfer">Transfer Bank</option>
                                <option value="debit">Kartu Debit</option>
                                <option value="utang">Utang (Belum Lunas)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Banner Utang -->
                    <div id="perdanaDebtBanner" class="hidden p-2 rounded-lg bg-amber-50 border border-amber-300 text-amber-900 text-xs">
                        <div class="font-bold flex items-center gap-1 text-[11px]">
                            <span>⚠️ Pembayaran Utang</span>
                        </div>
                        <div class="mt-0.5 text-[11px] text-amber-800">
                            Nama pelanggan wajib diisi dan akan disinkronkan langsung ke data utang & laporan harian.
                        </div>
                    </div>

                    <!-- 5. Nama Pelanggan -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-[color:var(--text)]" id="perdanaCustomerLabel">Nama Pelanggan / Pembeli</label>
                        <input class="field text-xs py-1.5" type="text" id="perdana_customer_name" placeholder="Umum / Nama Pembeli" autocomplete="off">
                    </div>

                    <!-- 6. Uang Diterima & Kembalian -->
                    <div id="perdanaCashSection" class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-medium text-[color:var(--text)]">Uang Diterima (Rp)</label>
                            <div class="flex items-center gap-1">
                                <button type="button" class="btn btn-secondary py-0.5 px-2 text-[10px] quick-cash-btn" data-val="exact">Uang Pas</button>
                                <button type="button" class="btn btn-secondary py-0.5 px-2 text-[10px] quick-cash-btn" data-val="20000">20rb</button>
                                <button type="button" class="btn btn-secondary py-0.5 px-2 text-[10px] quick-cash-btn" data-val="50000">50rb</button>
                                <button type="button" class="btn btn-secondary py-0.5 px-2 text-[10px] quick-cash-btn" data-val="100000">100rb</button>
                            </div>
                        </div>
                        <input class="field text-xs py-1.5 font-bold text-slate-800" type="text" inputmode="numeric" data-number-mask id="perdana_cash_paid" value="0" autocomplete="off">
                        
                        <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                            <span class="text-[color:var(--muted)]">Kembalian:</span>
                            <strong id="perdanaChangeText" class="text-emerald-700 font-bold">Rp 0</strong>
                        </div>
                    </div>

                    <!-- 7. Catatan Opsional -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-[color:var(--muted)]">Catatan <span class="text-[10px] font-normal">(Opsional)</span></label>
                        <input class="field text-xs py-1" type="text" id="perdana_notes" placeholder="Catatan transaksi..." autocomplete="off">
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" class="btn btn-primary w-full py-2.5 text-xs font-bold shadow-sm justify-center" id="btnSubmitPerdana">
                            <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                            <span>Simpan & Proses Penjualan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        window.runAppPage(function ($, helpers) {
            const { money, showAlert, parseNumber, formatNumber, maskedNumberValue, setMaskedNumberValue, bindNumberMasks } = helpers;

            const urls = {
                dashboard: @json(route('ajax.perdana.dashboard')),
                storeSale: @json(route('ajax.perdana.sales.store')),
                cancelSaleTemplate: @json(url('/ajax/pulsa/sales/__ID__/cancel')),
            };

            function updateProfitCalculation() {
                const price = maskedNumberValue('#perdana_price') || 0;
                const cost = maskedNumberValue('#perdana_cost_price') || 0;
                const profit = price - cost;
                const percent = price > 0 ? ((profit / price) * 100).toFixed(1) : '0';

                const $box = $('#profitPreviewBox');
                const $amount = $('#profitAmountText');
                const $pct = $('#profitPercentText');

                if (profit >= 0) {
                    $box.removeClass('border-rose-200 bg-rose-50/60').addClass('border-emerald-200 bg-emerald-50/60');
                    $amount.removeClass('text-rose-700').addClass('text-emerald-700').text(money(profit));
                    $pct.removeClass('text-rose-600').addClass('text-emerald-600').text(`(${percent}% margin)`);
                } else {
                    $box.removeClass('border-emerald-200 bg-emerald-50/60').addClass('border-rose-200 bg-rose-50/60');
                    $amount.removeClass('text-emerald-700').addClass('text-rose-700').text(`- ${money(Math.abs(profit))}`);
                    $pct.removeClass('text-emerald-600').addClass('text-rose-600').text(`(Rugi ${percent}%)`);
                }

                updateChangeCalculation();
            }

            function updateChangeCalculation() {
                const method = $('#perdana_payment_method').val();
                if (method === 'utang') {
                    $('#perdanaChangeText').text('Rp 0 (Utang)');
                    return;
                }

                const price = maskedNumberValue('#perdana_price') || 0;
                const cash = maskedNumberValue('#perdana_cash_paid') || 0;
                const change = cash - price;

                const $changeEl = $('#perdanaChangeText');
                if (change < 0) {
                    $changeEl.removeClass('text-emerald-700').addClass('text-rose-600').text(`Kurang ${money(Math.abs(change))}`);
                } else {
                    $changeEl.removeClass('text-rose-600').addClass('text-emerald-700').text(money(change));
                }
            }

            function loadDashboard() {
                $.getJSON(urls.dashboard)
                    .done((res) => {
                        const sum = res.summary || {};
                        $('#todayPerdanaTotal').text(money(sum.today_total || 0));
                        $('#todayPerdanaCount').text(`(${sum.today_count || 0} kartu)`);
                        $('#todayPerdanaProfit').text(money(sum.today_profit || 0));

                        renderHistory(res.sales || []);
                    })
                    .fail((xhr) => {
                        console.error('Failed to load perdana dashboard', xhr);
                    });
            }

            function renderHistory(sales) {
                const $tbody = $('#perdanaHistoryBody');
                if (!sales || !sales.length) {
                    $tbody.html('<tr><td colspan="9" class="text-center py-6 text-[color:var(--muted)]">Belum ada transaksi kartu perdana.</td></tr>');
                    return;
                }

                $tbody.html(sales.map((item) => {
                    const isCancelled = item.status === 'cancelled';
                    const profit = Number(item.price || 0) - Number(item.cost_amount || 0);
                    const formattedDate = item.sale_date ? new Date(item.sale_date).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '-';

                    return `
                        <tr class="${isCancelled ? 'opacity-60 bg-slate-50' : ''}">
                            <td>
                                <strong class="text-purple-700">${item.reference_number}</strong>
                                <div class="text-[10px] text-[color:var(--muted)]">${formattedDate}</div>
                            </td>
                            <td>
                                <span class="badge ${item.shift === 'pagi' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800'} uppercase font-bold text-[10px]">
                                    ${item.shift || 'pagi'}
                                </span>
                            </td>
                            <td>
                                <div class="font-medium text-slate-900">${item.manual_product_name || 'Kartu Perdana'}</div>
                                ${item.serial_number ? `<div class="font-mono text-[10px] text-[color:var(--muted)]">SN: ${item.serial_number}</div>` : ''}
                            </td>
                            <td class="text-right text-slate-600">${money(item.cost_amount || 0)}</td>
                            <td class="text-right font-bold text-slate-900">${money(item.price || 0)}</td>
                            <td class="text-right font-semibold ${profit >= 0 ? 'text-emerald-700' : 'text-rose-600'}">${money(profit)}</td>
                            <td><span class="uppercase font-semibold text-[10px]">${item.payment_method || 'cash'}</span></td>
                            <td class="text-center">
                                <span class="badge ${isCancelled ? 'badge-danger' : 'badge-success'} uppercase text-[10px]">
                                    ${item.status || 'success'}
                                </span>
                            </td>
                            <td class="text-center">
                                ${!isCancelled ? `
                                    <button type="button" class="btn btn-danger py-0.5 px-2 text-[10px] btn-cancel-sale" data-id="${item.id}" data-ref="${item.reference_number}">
                                        Batalkan
                                    </button>
                                ` : '<span class="text-[10px] text-[color:var(--muted)]">-</span>'}
                            </td>
                        </tr>
                    `;
                }).join(''));
            }

            // Quick Operator chip click
            $(document).on('click', '.operator-chip', function () {
                const op = $(this).data('operator');
                $('.operator-chip').removeClass('ring-2 ring-emerald-500 font-bold');
                $(this).addClass('ring-2 ring-emerald-500 font-bold');

                if (op === 'Perdana Lainnya') {
                    $('#perdana_operator_name').val('').focus();
                } else {
                    $('#perdana_operator_name').val(`Perdana ${op}`).focus();
                }
            });

            // Payment method change: show/hide utang alert and cash section
            $('#perdana_payment_method').on('change', function () {
                const method = $(this).val();
                if (method === 'utang') {
                    $('#perdanaDebtBanner').removeClass('hidden');
                    $('#perdanaCustomerLabel').html('Nama Pelanggan / Penghutang <span class="text-red-500">*</span>');
                    $('#perdanaCashSection').addClass('hidden');
                } else {
                    $('#perdanaDebtBanner').addClass('hidden');
                    $('#perdanaCustomerLabel').text('Nama Pelanggan / Pembeli');
                    $('#perdanaCashSection').removeClass('hidden');
                    if (maskedNumberValue('#perdana_cash_paid') === 0) {
                        setMaskedNumberValue('#perdana_cash_paid', maskedNumberValue('#perdana_price'));
                    }
                }
                updateChangeCalculation();
            });

            // Quick cash buttons
            $(document).on('click', '.quick-cash-btn', function () {
                const val = $(this).data('val');
                if (val === 'exact') {
                    setMaskedNumberValue('#perdana_cash_paid', maskedNumberValue('#perdana_price'));
                } else {
                    setMaskedNumberValue('#perdana_cash_paid', Number(val));
                }
                updateChangeCalculation();
            });

            // Mask and inputs listener
            $('#perdana_price, #perdana_cost_price').on('input', function () {
                updateProfitCalculation();
            });

            $('#perdana_price').on('change', function () {
                const price = maskedNumberValue('#perdana_price');
                if ($('#perdana_payment_method').val() !== 'utang') {
                    const currentCash = maskedNumberValue('#perdana_cash_paid');
                    if (currentCash === 0 || currentCash < price) {
                        setMaskedNumberValue('#perdana_cash_paid', price);
                    }
                }
                updateProfitCalculation();
            });

            $('#perdana_cash_paid').on('input', updateChangeCalculation);

            // Submit Sale Form
            $('#perdanaForm').on('submit', function (e) {
                e.preventDefault();

                const operatorName = $('#perdana_operator_name').val().trim();
                if (!operatorName) {
                    showAlert('Nama operator / kartu perdana wajib diisi.', 'error');
                    $('#perdana_operator_name').focus();
                    return;
                }

                const price = maskedNumberValue('#perdana_price');
                if (price <= 0) {
                    showAlert('Harga jual kartu perdana harus lebih besar dari Rp 0.', 'error');
                    $('#perdana_price').focus();
                    return;
                }

                const costPrice = maskedNumberValue('#perdana_cost_price');
                const paymentMethod = $('#perdana_payment_method').val();
                const customerName = $('#perdana_customer_name').val().trim();

                if (paymentMethod === 'utang' && !customerName) {
                    showAlert('Nama pelanggan wajib diisi untuk transaksi utang.', 'error');
                    $('#perdana_customer_name').focus();
                    return;
                }

                const cashPaid = paymentMethod === 'utang' ? 0 : maskedNumberValue('#perdana_cash_paid');
                if (paymentMethod !== 'utang' && cashPaid < price) {
                    showAlert('Uang yang diterima kurang dari harga jual.', 'error');
                    $('#perdana_cash_paid').focus();
                    return;
                }

                const payload = {
                    operator_name: operatorName,
                    price: price,
                    cost_price: costPrice,
                    serial_number: $('#perdana_serial_number').val().trim() || null,
                    phone_number: $('#perdana_serial_number').val().trim() || null,
                    customer_name: customerName || null,
                    shift: $('#perdana_shift').val() || 'pagi',
                    payment_method: paymentMethod,
                    cash_paid: cashPaid,
                    notes: $('#perdana_notes').val().trim() || null,
                };

                const $btn = $('#btnSubmitPerdana');
                $btn.prop('disabled', true).addClass('opacity-75');

                $.post(urls.storeSale, payload)
                    .done((res) => {
                        showAlert(`${res.message} (Ref: ${res.sale.reference_number})`);
                        $('#perdanaForm')[0].reset();
                        $('#perdana_operator_name').val('');
                        setMaskedNumberValue('#perdana_price', 0);
                        setMaskedNumberValue('#perdana_cost_price', 0);
                        setMaskedNumberValue('#perdana_cash_paid', 0);
                        $('#perdana_payment_method').val('cash').trigger('change');
                        $('.operator-chip').removeClass('ring-2 ring-emerald-500 font-bold');
                        updateProfitCalculation();
                        loadDashboard();
                    })
                    .fail((xhr) => {
                        const msg = xhr.responseJSON?.message || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('<br>') : 'Gagal menyimpan transaksi kartu perdana.');
                        showAlert(msg, 'error');
                    })
                    .always(() => {
                        $btn.prop('disabled', false).removeClass('opacity-75');
                    });
            });

            // Cancel sale
            $(document).on('click', '.btn-cancel-sale', function () {
                const id = $(this).data('id');
                const ref = $(this).data('ref');

                if (!confirm(`Apakah Anda yakin ingin membatalkan transaksi kartu perdana ${ref}?`)) {
                    return;
                }

                const url = urls.cancelSaleTemplate.replace('__ID__', id);
                $.post(url, { notes: 'Dibatalkan oleh kasir di menu perdana' })
                    .done((res) => {
                        showAlert(res.message);
                        loadDashboard();
                    })
                    .fail((xhr) => {
                        const msg = xhr.responseJSON?.message || 'Gagal membatalkan transaksi.';
                        showAlert(msg, 'error');
                    });
            });

            $('#btnRefreshHistory').on('click', function () {
                loadDashboard();
            });

            // Initial load
            loadDashboard();
            updateProfitCalculation();
        });
    </script>
@endsection
