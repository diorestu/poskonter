@extends('layouts.app')

@section('content')
    <section class="pane pos-shell">
        <div class="toolbar flex items-center justify-between px-4 py-3">
            <div>
                <h1 class="font-semibold">POS</h1>
                <p class="text-[color:var(--muted)]">Halaman kasir untuk HP dan aksesoris, voucher, dan pulsa elektrik.</p>
            </div>
            <div class="text-[11px] text-[color:var(--muted)]">Input cepat, satu transaksi per layar</div>
        </div>

        <div class="px-4 pt-4">
            <div class="inline-flex flex-wrap gap-1 rounded-[8px] border border-[#d7e0eb] bg-white p-1">
                <button type="button" class="pos-main-tab menu-link menu-link-active" data-pos-tab="hp">HP dan Aksesoris</button>
                <button type="button" class="pos-main-tab menu-link" data-pos-tab="voucher">Voucher</button>
                <button type="button" class="pos-main-tab menu-link" data-pos-tab="pulsa">Pulsa Elektrik</button>
            </div>
        </div>

        <div class="pos-main-panel" data-pos-panel="hp">
        <div class="grid gap-4 p-4 xl:grid-cols-[0.95fr_1.05fr]">
            <div class="space-y-4">
                <div class="pos-product-panel p-4">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold">Pilih Produk</div>
                            <div class="text-[11px] text-[color:var(--muted)]">Kelompokkan per kategori agar input kasir lebih cepat.</div>
                        </div>
                    </div>
                    <div id="categoryTabs" class="category-tabs mb-3"></div>
                    <div id="productPicker" class="product-grid max-h-[560px] overflow-y-auto pr-1"></div>
                </div>
            </div>

            <div class="space-y-4">
                <form id="saleForm" class="pos-product-panel p-4">
                    <div class="mb-3 grid gap-3 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block font-medium">Nama Pelanggan</label>
                            <input class="field" type="text" id="customer_name" placeholder="Opsional">
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Shift</label>
                            <select class="select" id="shift">
                                <option value="pagi">Pagi</option>
                                <option value="sore">Sore</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block font-medium">Metode Pembayaran</label>
                            <select class="select" id="payment_method">
                                <option value="cash">Tunai</option>
                                <option value="qris">QRIS</option>
                                <option value="transfer">Transfer</option>
                                <option value="debit">Debit</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="table-grid min-w-full text-sm">
                            <thead class="text-left">
                                <tr>
                                    <th>Item</th>
                                    <th width="80">Qty</th>
                                    <th width="130">IMEI / Serial</th>
                                    <th width="130">Subtotal</th>
                                    <th width="80"></th>
                                </tr>
                            </thead>
                            <tbody id="cartTable">
                                <tr>
                                    <td colspan="5" class="text-center text-[color:var(--muted)]">Keranjang masih kosong.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block font-medium">Diskon</label>
                            <input class="field" type="text" inputmode="numeric" data-number-mask id="discount" value="0">
                        </div>
                        <div class="pos-summary">
                            <div class="pos-summary-row">
                                <span class="text-[color:var(--muted)]">Jumlah Item</span>
                                <strong id="itemCountText">0 item</strong>
                            </div>
                            <div class="pos-summary-row">
                                <span class="text-[color:var(--muted)]">Qty Total</span>
                                <strong id="qtyCountText">0</strong>
                            </div>
                            <div class="pos-summary-row">
                                <span class="text-[color:var(--muted)]">Subtotal</span>
                                <strong id="subtotalText">Rp 0</strong>
                            </div>
                            <div class="pos-summary-row">
                                <span class="text-[color:var(--muted)]">Diskon</span>
                                <strong id="discountText">Rp 0</strong>
                            </div>
                            <div class="pos-summary-row">
                                <span class="text-[color:var(--muted)]">Metode</span>
                                <strong id="paymentMethodText">Tunai</strong>
                            </div>
                            <div class="pos-summary-row pos-summary-total">
                                <span class="text-[color:var(--muted)]">Total</span>
                                <strong id="totalText">Rp 0</strong>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 flex gap-2">
                        <button class="btn btn-primary flex-1" type="button" id="openPaymentModal">
                            <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M3 7h18"/><path d="M5 5h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/><path d="M16 15h2"/></svg></span>
                            <span>Bayar</span>
                        </button>
                        <button class="btn btn-secondary" type="button" id="clearCart">
                            <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg></span>
                            <span>Kosongkan</span>
                        </button>
                    </div>
                </form>

                <div class="pos-product-panel p-4">
                    <div class="mb-2 text-sm font-semibold">Transaksi Terakhir</div>
                    <div id="recentSalesList" class="space-y-2"></div>
                </div>
            </div>
        </div>
        </div>

        <div class="pos-main-panel hidden p-4" data-pos-panel="voucher">
            <iframe
                class="min-h-[760px] w-full border-0"
                src="{{ route('voucher.index', ['embedded' => 1]) }}"
                title="Voucher"
            ></iframe>
        </div>

        <div class="pos-main-panel hidden p-4" data-pos-panel="pulsa">
            <iframe
                class="min-h-[760px] w-full border-0"
                src="{{ route('pulsa.index', ['embedded' => 1]) }}"
                title="Pulsa Elektrik"
            ></iframe>
        </div>
    </section>

    <div id="paymentModal" class="modal-backdrop hidden">
        <div class="modal-card">
            <div class="modal-head flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-semibold">Pembayaran</h2>
                    <p class="mt-1 text-[12px] text-[color:var(--muted)]">Input nominal yang diberikan customer, lalu simpan transaksi.</p>
                </div>
                <button type="button" class="btn btn-secondary" id="closePaymentModal">
                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg></span>
                    <span>Tutup</span>
                </button>
            </div>
            <div class="modal-body space-y-4">
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
                        <label class="mb-1 block font-medium">Uang Diberikan</label>
                        <input class="field" type="text" inputmode="numeric" data-number-mask id="cash_paid_modal" value="0">
                        <div class="mt-3 pos-summary">
                            <div class="pos-summary-row">
                                <span class="text-[color:var(--muted)]">Kembalian</span>
                                <strong id="modalChangeText">Rp 0</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="button" class="btn btn-primary flex-1" id="savePayment">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M5 12.5 10 17l9-10"/><path d="M4 4h16v16H4z"/></svg></span>
                        <span>Simpan</span>
                    </button>
                    <button type="button" class="btn btn-secondary flex-1" id="saveAndNewPayment">
                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/><path d="M4 4h16v16H4z"/></svg></span>
                        <span>Simpan & Baru</span>
                    </button>
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
                } else {
                    $('#customer_name').val('');
                }
                setMaskedNumberValue('#discount', 0);
                setMaskedNumberValue('#cash_paid_modal', 0);
                $('#paymentModal').addClass('hidden');
                renderCart();
                updatePaymentLabels();
            }

            function updatePaymentLabels() {
                const methodLabel = paymentMethodLabel($('#payment_method').val());
                $('#paymentMethodText').text(methodLabel);
                $('#modalPaymentMethodText').text(methodLabel);
                $('#modalTotalText').text($('#totalText').text());
                $('#modalChangeText').text(money(cartTotals().change));
            }

            function renderRecentSales(items) {
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
                                            ${si.serial_number ? `<span class="badge badge-ok font-mono text-[10px]">IMEI: ${si.serial_number}</span>` : ''}
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

            function renderProducts(products) {
                state.products = (products || []).filter((product) => product.is_active && product.product_type !== 'pulsa');
                renderCategoryTabs(state.products);

                const visibleProducts = state.activeCategory === 'all'
                    ? state.products
                    : state.products.filter((product) => product.category === state.activeCategory);

                $('#productPicker').html(visibleProducts.length ? visibleProducts.map((product) => {
                    const serialCount = product.available_serials_count || 0;
                    return `
                        <button type="button" class="product-card add-to-cart ${Number(product.stock) < 1 ? 'product-card-disabled' : ''}" data-id="${product.id}" ${Number(product.stock) < 1 ? 'disabled' : ''}>
                            <div>
                                ${product.image_url ? `<img src="${product.image_url}" alt="${product.name}" class="mb-3 h-24 w-full rounded-[6px] object-cover border border-[#d7e0eb]">` : ''}
                                <div class="font-semibold text-left">${product.name}</div>
                                <div class="mt-1 flex items-center justify-between text-[11px] text-[color:var(--muted)]">
                                    <span>${product.variant || product.unit}</span>
                                    ${serialCount > 0 ? `<span class="badge badge-ok text-[10px]">${serialCount} IMEI</span>` : ''}
                                </div>
                            </div>
                            <div class="mt-3 flex items-end justify-between gap-3">
                                <span class="product-card-price">${money(product.price)}</span>
                                <span class="product-card-stock">Stok ${product.stock}</span>
                            </div>
                        </button>
                    `;
                }).join('') : '<div class="col-span-full rounded-2xl border border-dashed border-[#cad8e8] bg-white px-4 py-8 text-center text-[color:var(--muted)]">Tidak ada produk pada kategori ini.</div>');
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
                                    <div class="font-medium">${item.name}</div>
                                    <div class="text-[11px] text-[color:var(--muted)]">${item.variant || item.unit || ''}</div>
                                    <div class="text-[11px] text-[color:var(--muted)]">${money(item.price)}</div>
                                </td>
                                <td><input class="field cart-qty field-number text-xs" type="text" inputmode="numeric" data-number-mask data-id="${item.id}" value="${item.quantity}"></td>
                                <td>${serialCell}</td>
                                <td class="font-semibold text-xs">${money(item.price * item.quantity)}</td>
                                <td>
                                    <button type="button" class="remove-cart inline-flex items-center gap-1 text-[color:var(--danger)] text-xs" data-id="${item.id}">
                                        <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg></span>
                                        <span>Hapus</span>
                                    </button>
                                </td>
                            </tr>
                        `;
                    }).join(''));
                    bindNumberMasks(document.getElementById('cartTable'));
                }

                const totals = cartTotals();
                $('#itemCountText').text(`${totals.itemCount} item`);
                $('#qtyCountText').text(totals.totalQty);
                $('#subtotalText').text(money(totals.subtotal));
                $('#discountText').text(money(totals.discount));
                $('#totalText').text(money(totals.total));
                updatePaymentLabels();
            }

            function loadData() {
                $.getJSON(window.posConfig.dashboardUrl, { _: Date.now() })
                    .done((response) => {
                        try {
                            renderProducts(response.products || []);
                            renderRecentSales(response.recent_sales || []);
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

                $.post(window.posConfig.salesUrl, {
                    customer_name: $('#customer_name').val(),
                    shift: $('#shift').val(),
                    payment_method: $('#payment_method').val(),
                    discount: maskedNumberValue('#discount'),
                    cash_paid: maskedNumberValue('#cash_paid_modal'),
                    items: state.cart.map((item) => ({ product_id: item.id, quantity: item.quantity, serial_number: item.serial_number || null })),
                }).done((response) => {
                    showAlert(`${response.message} Invoice ${response.sale.invoice_number}`);
                    resetSaleForm(createNew);
                    loadData();
                }).fail((xhr) => {
                    showAlert(xhr.responseJSON?.message || 'Gagal menyimpan transaksi.', 'error');
                });
            }

            $(document).on('click', '.add-to-cart', function () {
                const product = state.products.find((item) => item.id === Number($(this).data('id')));
                if (!product) return;

                const existing = state.cart.find((item) => item.id === product.id);
                if (existing) {
                    if (existing.quantity >= product.stock) {
                        showAlert('Jumlah melebihi stok tersedia.', 'error');
                        return;
                    }
                    existing.quantity += 1;
                } else {
                    if (Number(product.stock) < 1) {
                        showAlert('Stok produk kosong.', 'error');
                        return;
                    }

                    const availableSerials = product.available_serials || [];
                    const defaultSerial = availableSerials.length > 0 ? availableSerials[0].serial_number : '';

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
            });

            $(document).on('click', '.category-tab', function () {
                state.activeCategory = $(this).data('category');
                renderProducts(state.products);
            });

            $(document).on('input', '.cart-qty', function () {
                const item = state.cart.find((entry) => entry.id === Number($(this).data('id')));
                if (!item) return;

                item.quantity = Math.min(Math.max(1, parseNumber($(this).val())), item.stock);
                $(this).val(formatNumber(item.quantity));
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

            $('#openPaymentModal').on('click', function () {
                if (!state.cart.length) {
                    showAlert('Keranjang masih kosong.', 'error');
                    return;
                }
                $('#paymentModal').removeClass('hidden');
                setMaskedNumberValue('#cash_paid_modal', cartTotals().total);
                updatePaymentLabels();
            });

            $('#closePaymentModal').on('click', function () {
                $('#paymentModal').addClass('hidden');
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

            bindNumberMasks(document);
            updatePaymentLabels();
            loadData();
        });
    </script>
@endsection
