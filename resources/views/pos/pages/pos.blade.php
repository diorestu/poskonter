@extends('layouts.app')

@section('content')
    <section class="pos-shell h-[calc(100vh-105px)] flex flex-col overflow-hidden">
        <div class="px-4 py-2.5 border-b border-[#e2e8f0] flex flex-wrap items-center justify-between gap-2 shrink-0">
            <div class="inline-flex rounded-lg border border-[#e2e8f0] bg-[#f8fafc] p-0.5 gap-1">
                <button type="button" class="pos-main-tab menu-link menu-link-active text-xs font-semibold py-1 px-3" data-pos-tab="hp">HP dan Aksesoris</button>
                <button type="button" class="pos-main-tab menu-link text-xs font-semibold py-1 px-3" data-pos-tab="voucher">Voucher</button>
                <button type="button" class="pos-main-tab menu-link text-xs font-semibold py-1 px-3" data-pos-tab="pulsa">Pulsa Elektrik</button>
            </div>
            <div class="text-[11px] text-[color:var(--muted)] font-medium">Kasir Cepat POS</div>
        </div>

        <div class="pos-main-panel flex-1 overflow-hidden p-3" data-pos-panel="hp">
        <div class="grid gap-3 lg:grid-cols-[1fr_390px] xl:grid-cols-[1fr_420px] h-full overflow-hidden items-stretch">
            <!-- Left Column: Product Grid (Adapts to viewport, scrolls internally) -->
            <div class="pos-product-panel p-3 flex flex-col h-full overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-2 pb-2.5 shrink-0">
                    <div id="categoryTabs" class="category-tabs flex-1"></div>
                    <div class="w-full sm:w-52">
                        <input type="text" id="posProductSearch" class="field text-xs py-1 px-2" placeholder="Cari nama / kode...">
                    </div>
                </div>
                <div id="productPicker" class="product-grid flex-1 overflow-y-auto pr-1"></div>
            </div>

            <!-- Right Column: Checkout Sidebar (Adapts to viewport, no outer scroll) -->
            <div class="h-full overflow-hidden">
                <form id="saleForm" class="pos-product-panel p-3.5 flex flex-col h-full justify-between overflow-hidden">
                    <div class="flex flex-col flex-1 overflow-hidden min-h-0">
                        <div class="mb-2.5 grid gap-2 grid-cols-3 shrink-0">
                            <div>
                                <label class="mb-0.5 block font-medium text-[10px] text-[color:var(--muted)]">Pelanggan</label>
                                <input class="field text-xs py-1 px-2" type="text" id="customer_name" placeholder="Umum">
                            </div>
                            <div>
                                <label class="mb-0.5 block font-medium text-[10px] text-[color:var(--muted)]">Shift</label>
                                <select class="select text-xs py-1 px-2" id="shift">
                                    <option value="pagi">Pagi</option>
                                    <option value="sore">Sore</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-0.5 block font-medium text-[10px] text-[color:var(--muted)]">Metode</label>
                                <select class="select text-xs py-1 px-2" id="payment_method">
                                    <option value="cash">Tunai</option>
                                    <option value="qris">QRIS</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="debit">Debit</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-wrap flex-1 overflow-y-auto min-h-[100px] mb-2">
                            <table class="table-grid min-w-full text-xs">
                                <thead class="text-left sticky top-0 z-10">
                                    <tr>
                                        <th>Item</th>
                                        <th width="65">Qty</th>
                                        <th width="125">IMEI/SN</th>
                                        <th width="90">Subtotal</th>
                                        <th width="45"></th>
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

                    <div class="shrink-0 pt-1 border-t border-[#f1f5f9]">
                        <input type="hidden" id="discount" value="0">
                        <div class="mb-2">
                            <div class="pos-summary p-2">
                                <div class="pos-summary-row text-xs">
                                    <span class="text-[color:var(--muted)]">Item / Qty</span>
                                    <strong id="itemCountText">0 (0)</strong>
                                </div>
                                <div class="pos-summary-row text-xs">
                                    <span class="text-[color:var(--muted)]">Subtotal</span>
                                    <strong id="subtotalText">Rp 0</strong>
                                </div>
                                <div class="pos-summary-row pos-summary-total mt-0.5 pt-0.5">
                                    <span class="text-[color:var(--muted)] font-semibold text-xs">Total</span>
                                    <strong id="totalText" class="text-sm">Rp 0</strong>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button class="btn btn-primary flex-1 min-h-[34px] text-xs font-semibold" type="button" id="openPaymentModal">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M3 7h18"/><path d="M5 5h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/><path d="M16 15h2"/></svg></span>
                                <span>Bayar</span>
                            </button>
                            <button class="btn btn-secondary min-h-[34px] text-xs" type="button" id="clearCart">
                                <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg></span>
                                <span>Kosongkan</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        </div>

        <div class="pos-main-panel hidden p-4" data-pos-panel="voucher">
            <iframe
                class="min-h-[760px] w-full border-0 rounded-xl"
                src="{{ route('voucher.index', ['embedded' => 1]) }}"
                title="Voucher"
            ></iframe>
        </div>

        <div class="pos-main-panel hidden p-4" data-pos-panel="pulsa">
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
                                <div class="font-semibold text-[11px] leading-tight text-left text-[color:var(--text)] line-clamp-1">${product.name}</div>
                                <div class="mt-0.5 flex items-center justify-between text-[10px] text-[color:var(--muted)]">
                                    <span class="truncate max-w-[85px]">${product.variant || product.unit || product.code}</span>
                                    ${serialCount > 0 ? `<span class="badge badge-ok font-mono text-[9px] py-0 px-1">${serialCount} IMEI</span>` : ''}
                                </div>
                            </div>
                            <div class="mt-1.5 pt-1 border-t border-[#f1f5f9] flex items-end justify-between gap-1">
                                <span class="product-card-price text-[11px] font-bold">${money(product.price)}</span>
                                <span class="product-card-stock text-[9px]">Stok ${product.stock}</span>
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

            $('#posProductSearch').on('input', function () {
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
