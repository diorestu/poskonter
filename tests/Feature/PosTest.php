<?php

namespace Tests\Feature;

use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Product;
use App\Models\Provider;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    protected function signIn(string $role = 'admin'): User
    {
        $user = User::factory()->create(['role' => $role]);
        $this->actingAs($user);

        return $user;
    }

    public function test_pos_page_can_be_opened(): void
    {
        $this->signIn();

        $response = $this->get('/pos');

        $response->assertOk();
        $response->assertSee('POS');
        $response->assertSee('HP dan Aksesoris');
        $response->assertSee('Voucher');
        $response->assertSee('Pulsa Elektrik');
    }

    public function test_guest_is_redirected_to_login_page(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_product_can_be_created_from_ajax_endpoint(): void
    {
        $this->signIn();

        $response = $this->postJson('/ajax/products', [
            'code' => 'BRG-001',
            'name' => 'Teh Botol',
            'product_type' => 'regular',
            'category' => 'Minuman',
            'variant' => 'Original',
            'unit' => 'botol',
            'price' => 5000,
            'cost_price' => 3500,
            'minimum_stock' => 3,
        ]);

        $response->assertCreated()
            ->assertJsonPath('product.code', 'BRG-001');

        $this->assertDatabaseHas('products', [
            'code' => 'BRG-001',
            'name' => 'Teh Botol',
            'category' => 'Minuman',
            'variant' => 'Original',
            'stock' => 0,
        ]);
    }

    public function test_physical_product_can_be_created_without_initial_cost_price(): void
    {
        $this->signIn();

        $response = $this->postJson('/ajax/products', [
            'code' => 'BRG-OPS',
            'name' => 'Keripik Singkong',
            'product_type' => 'regular',
            'category' => 'Snack',
            'variant' => '80gr',
            'unit' => 'pcs',
            'price' => 8000,
            'minimum_stock' => 2,
        ]);

        $response->assertCreated()
            ->assertJsonPath('product.code', 'BRG-OPS');

        $this->assertDatabaseHas('products', [
            'code' => 'BRG-OPS',
            'cost_price' => 0,
        ]);
    }

    public function test_pulsa_product_can_be_created_with_multiple_wallet_sources(): void
    {
        $this->signIn();

        $provider = Provider::create(['name' => 'Telkomsel']);

        $walletA = Wallet::create([
            'name' => 'Dompet Server A',
            'balance' => 50000,
            'is_active' => true,
        ]);

        $walletB = Wallet::create([
            'name' => 'Dompet Server B',
            'balance' => 35000,
            'is_active' => true,
        ]);

        $response = $this->postJson('/ajax/products', [
            'code' => 'PLS-20',
            'name' => 'Pulsa Nasional',
            'product_type' => 'pulsa',
            'provider_id' => $provider->id,
            'category' => '',
            'variant' => '20.000',
            'unit' => 'trx',
            'price' => 22000,
            'cost_price' => 20500,
            'wallet_ids' => [$walletA->id, $walletB->id],
        ]);

        $response->assertCreated()
            ->assertJsonPath('product.provider', 'Telkomsel');

        $productId = $response->json('product.id');

        $this->assertDatabaseHas('provider_wallet', [
            'provider_id' => $provider->id,
            'wallet_id' => $walletA->id,
        ]);

        $this->assertDatabaseHas('provider_wallet', [
            'provider_id' => $provider->id,
            'wallet_id' => $walletB->id,
        ]);
    }

    public function test_pulsa_product_can_be_created_without_wallet_assignment_from_product_form(): void
    {
        $this->signIn();

        $response = $this->postJson('/ajax/products', [
            'code' => 'PLS-30',
            'name' => 'Pulsa XL',
            'product_type' => 'pulsa',
            'category' => 'XL',
            'variant' => '30.000',
            'unit' => 'trx',
            'price' => 32000,
            'cost_price' => 30100,
        ]);

        $response->assertCreated()
            ->assertJsonPath('product.name', 'Pulsa XL');

        $this->assertDatabaseMissing('product_wallet', [
            'product_id' => $response->json('product.id'),
        ]);
    }

    public function test_sale_reduces_product_stock(): void
    {
        $this->signIn('cashier');

        $product = Product::create([
            'code' => 'BRG-002',
            'name' => 'Air Mineral',
            'product_type' => 'regular',
            'category' => 'Minuman',
            'variant' => '600ml',
            'unit' => 'botol',
            'price' => 3000,
            'cost_price' => 1800,
            'stock' => 10,
            'minimum_stock' => 2,
            'is_active' => true,
        ]);

        $response = $this->postJson('/ajax/sales', [
            'customer_name' => 'Pelanggan',
            'payment_method' => 'cash',
            'discount' => 1000,
            'cash_paid' => 10000,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('sale.total', '5000.00');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 8,
        ]);

        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseCount('sale_items', 1);
        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_stock_in_is_recorded_per_item_with_serial_expiry_and_cost(): void
    {
        $this->signIn('cashier');

        $product = Product::create([
            'code' => 'BRG-STK',
            'name' => 'Susu Kotak',
            'product_type' => 'regular',
            'category' => 'Minuman',
            'variant' => '250ml',
            'unit' => 'pcs',
            'price' => 6500,
            'cost_price' => 0,
            'stock' => 0,
            'minimum_stock' => 2,
            'is_active' => true,
        ]);

        $response = $this->postJson("/ajax/products/{$product->id}/stock", [
            'items' => [
                [
                    'serial_number' => 'SN-001',
                    'expiry_date' => '2026-04-01',
                    'cost_price' => 4200,
                ],
                [
                    'serial_number' => 'SN-002',
                    'expiry_date' => '2026-04-02',
                    'cost_price' => 4300,
                ],
            ],
            'note' => 'Barang datang',
        ]);

        $response->assertOk()
            ->assertJsonPath('product.stock', 2);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 2,
            'cost_price' => 4300,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'serial_number' => 'SN-001',
            'expiry_date' => '2026-04-01 00:00:00',
            'cost_price' => 4200,
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'serial_number' => 'SN-002',
            'expiry_date' => '2026-04-02 00:00:00',
            'cost_price' => 4300,
            'quantity' => 1,
        ]);
    }

    public function test_stock_in_supports_custom_quantity_for_same_serial(): void
    {
        $this->signIn('cashier');

        $product = Product::create([
            'code' => 'KBL-TYPEC',
            'name' => 'Kabel Data Type-C Kaleng',
            'product_type' => 'regular',
            'category' => 'Aksesoris',
            'variant' => '1 Kaleng',
            'unit' => 'pcs',
            'price' => 15000,
            'cost_price' => 0,
            'stock' => 0,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->postJson("/ajax/products/{$product->id}/stock", [
            'items' => [
                [
                    'serial_number' => 'SN-KLNG-20PCS',
                    'quantity' => 20,
                    'expiry_date' => '2027-01-01',
                    'cost_price' => 8000,
                ],
            ],
            'note' => '1 kaleng isi 20 kabel data',
        ]);

        $response->assertOk()
            ->assertJsonPath('product.stock', 20);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 20,
            'cost_price' => 8000,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'serial_number' => 'SN-KLNG-20PCS',
            'expiry_date' => '2027-01-01 00:00:00',
            'cost_price' => 8000,
            'quantity' => 20,
        ]);
    }

    public function test_cashier_cannot_access_admin_pages(): void
    {
        $this->signIn('cashier');

        $this->get('/report')->assertForbidden();
        $this->get('/master-data')->assertForbidden();
        $this->get('/master-data/produk-fisik')->assertForbidden();
        $this->get('/master-data/produk-elektrik')->assertForbidden();
        $this->get('/master-data/dompet-saldo')->assertForbidden();
        $this->get('/user-management')->assertForbidden();
        $this->get('/database/export')->assertForbidden();
    }

    public function test_admin_master_menu_routes_can_be_opened(): void
    {
        $this->signIn('admin');

        $this->get('/master-data')->assertRedirect(route('master.physical.index'));
        $this->get('/master-data/produk-fisik')->assertOk()->assertSee('Master Produk Fisik');
        $this->get('/master-data/produk-elektrik')->assertOk()->assertSee('Master Produk Elektrik');
        $this->get('/master-data/produk-voucher')->assertOk()->assertSee('Master Produk Voucher');
        $this->get('/master-data/dompet-saldo')->assertOk()->assertSee('Dompet Saldo');
    }

    public function test_voucher_product_can_be_created_from_ajax_endpoint(): void
    {
        $this->signIn('admin');

        $response = $this->postJson('/ajax/products', [
            'code' => 'VCR-TSEL-10GB',
            'name' => 'Voucher Telkomsel 10GB',
            'product_type' => 'voucher',
            'category' => 'Telkomsel',
            'variant' => '10 GB / 30 Hari',
            'unit' => 'pcs',
            'price' => 35000,
            'cost_price' => 30000,
            'stock' => 5,
            'serial_number' => 'SN-VCR-001',
        ]);

        $response->assertCreated()
            ->assertJsonPath('product.code', 'VCR-TSEL-10GB')
            ->assertJsonPath('product.product_type', 'voucher');

        $this->assertDatabaseHas('products', [
            'code' => 'VCR-TSEL-10GB',
            'product_type' => 'voucher',
            'category' => 'Telkomsel',
            'stock' => 5,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'type' => 'in',
            'quantity' => 5,
            'serial_number' => 'SN-VCR-001',
        ]);

        $dashboardResponse = $this->getJson('/ajax/voucher/dashboard');
        $dashboardResponse->assertOk()
            ->assertJsonFragment(['code' => 'VCR-TSEL-10GB']);
    }

    public function test_cashier_can_access_pulsa_pages(): void
    {
        $this->signIn('cashier');

        $this->get('/pengeluaran')->assertOk();
        $this->get('/pulsa-elektrik')->assertOk();
        $this->get('/riwayat-penjualan')->assertOk();
    }

    public function test_pulsa_sale_reduces_wallet_balance_and_cancel_restores_it(): void
    {
        $this->signIn('cashier');

        $product = Product::create([
            'code' => 'PLS-10',
            'name' => 'Pulsa Telkomsel',
            'product_type' => 'pulsa',
            'category' => 'Telkomsel',
            'variant' => '10.000',
            'unit' => 'trx',
            'price' => 12000,
            'cost_price' => 10500,
            'stock' => 0,
            'minimum_stock' => 0,
            'is_active' => true,
        ]);

        $wallet = Wallet::create([
            'name' => 'Dompet Server A',
            'balance' => 50000,
            'is_active' => true,
        ]);

        $product->wallets()->attach($wallet->id);

        $saleResponse = $this->postJson('/ajax/pulsa/sales', [
            'product_id' => $product->id,
            'wallet_id' => $wallet->id,
            'target_number' => '081234567890',
            'payment_method' => 'cash',
            'cash_paid' => 15000,
        ]);

        $saleResponse->assertCreated()
            ->assertJsonPath('sale.status', 'pending');

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => 39500,
        ]);

        $saleId = $saleResponse->json('sale.id');

        $cancelResponse = $this->postJson("/ajax/pulsa/sales/{$saleId}/cancel", [
            'notes' => 'Gangguan provider',
        ]);

        $cancelResponse->assertOk()
            ->assertJsonPath('sale.status', 'cancelled');

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => 50000,
        ]);
    }

    public function test_pulsa_sale_rejects_wallet_outside_product_sources(): void
    {
        $this->signIn('cashier');

        $product = Product::create([
            'code' => 'PLS-25',
            'name' => 'Pulsa Data',
            'product_type' => 'pulsa',
            'category' => 'Indosat',
            'variant' => '25.000',
            'unit' => 'trx',
            'price' => 27000,
            'cost_price' => 25200,
            'stock' => 0,
            'minimum_stock' => 0,
            'is_active' => true,
        ]);

        $allowedWallet = Wallet::create([
            'name' => 'Dompet Allowed',
            'balance' => 50000,
            'is_active' => true,
        ]);

        $blockedWallet = Wallet::create([
            'name' => 'Dompet Blocked',
            'balance' => 50000,
            'is_active' => true,
        ]);

        $product->wallets()->attach($allowedWallet->id);

        $response = $this->postJson('/ajax/pulsa/sales', [
            'product_id' => $product->id,
            'wallet_id' => $blockedWallet->id,
            'target_number' => '081234567890',
            'payment_method' => 'cash',
            'cash_paid' => 30000,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('wallet_id');

        $this->assertDatabaseHas('wallets', [
            'id' => $blockedWallet->id,
            'balance' => 50000,
        ]);
    }

    public function test_wallet_can_be_updated_with_selected_electric_products(): void
    {
        $this->signIn('admin');

        $providerA = Provider::create(['name' => 'Telkomsel']);
        $providerB = Provider::create(['name' => 'Indosat']);

        $wallet = Wallet::create([
            'name' => 'Dompet Server A',
            'balance' => 50000,
            'is_active' => true,
        ]);

        $productA = Product::create([
            'code' => 'PLS-A',
            'name' => 'Pulsa Telkomsel',
            'product_type' => 'pulsa',
            'provider_id' => $providerA->id,
            'category' => 'Telkomsel',
            'variant' => '10.000',
            'unit' => 'trx',
            'price' => 12000,
            'cost_price' => 10500,
            'stock' => 0,
            'minimum_stock' => 0,
            'is_active' => true,
        ]);

        $productB = Product::create([
            'code' => 'PLS-B',
            'name' => 'Pulsa Indosat',
            'product_type' => 'pulsa',
            'provider_id' => $providerB->id,
            'category' => 'Indosat',
            'variant' => '15.000',
            'unit' => 'trx',
            'price' => 17000,
            'cost_price' => 15500,
            'stock' => 0,
            'minimum_stock' => 0,
            'is_active' => true,
        ]);

        $response = $this->putJson("/ajax/wallets/{$wallet->id}", [
            'name' => 'Dompet Server A',
            'provider_ids' => [$providerA->id, $providerB->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('wallet.name', 'Dompet Server A');

        $this->assertDatabaseHas('provider_wallet', [
            'provider_id' => $providerA->id,
            'wallet_id' => $wallet->id,
        ]);

        $this->assertDatabaseHas('provider_wallet', [
            'provider_id' => $providerB->id,
            'wallet_id' => $wallet->id,
        ]);
    }

    public function test_updating_electric_product_without_wallet_ids_keeps_existing_wallet_relations(): void
    {
        $this->signIn('admin');

        $wallet = Wallet::create([
            'name' => 'Dompet Server A',
            'balance' => 50000,
            'is_active' => true,
        ]);

        $product = Product::create([
            'code' => 'PLS-KEEP',
            'name' => 'Pulsa Smartfren',
            'product_type' => 'pulsa',
            'category' => 'Smartfren',
            'variant' => '10.000',
            'unit' => 'trx',
            'price' => 12000,
            'cost_price' => 10900,
            'stock' => 0,
            'minimum_stock' => 0,
            'is_active' => true,
        ]);

        $product->wallets()->attach($wallet->id);

        $response = $this->putJson("/ajax/products/{$product->id}", [
            'code' => 'PLS-KEEP',
            'name' => 'Pulsa Smartfren Updated',
            'product_type' => 'pulsa',
            'category' => 'Smartfren',
            'variant' => '10.000',
            'unit' => 'trx',
            'price' => 12500,
            'cost_price' => 11000,
        ]);

        $response->assertOk()
            ->assertJsonPath('product.name', 'Pulsa Smartfren Updated');

        $this->assertDatabaseHas('product_wallet', [
            'product_id' => $product->id,
            'wallet_id' => $wallet->id,
        ]);
    }

    public function test_cashier_can_record_expense(): void
    {
        $this->signIn('cashier');

        $response = $this->postJson('/ajax/expenses', [
            'expense_date' => now()->toDateTimeString(),
            'category' => 'Operasional',
            'description' => 'Beli plastik kemasan',
            'amount' => 25000,
            'notes' => 'Untuk kebutuhan outlet',
        ]);

        $response->assertCreated()
            ->assertJsonPath('expense.category', 'Operasional');

        $this->assertDatabaseHas('expenses', [
            'category' => 'Operasional',
            'description' => 'Beli plastik kemasan',
            'amount' => 25000,
        ]);
    }

    public function test_wallet_created_without_child_selection_is_assigned_to_all_active_electric_products(): void
    {
        $this->signIn('cashier');

        $providerA = Provider::create(['name' => 'Telkomsel']);
        $providerB = Provider::create(['name' => 'Indosat']);

        $productA = Product::create([
            'code' => 'PLS-AUTO-1',
            'name' => 'Pulsa Telkomsel',
            'product_type' => 'pulsa',
            'provider_id' => $providerA->id,
            'category' => 'Telkomsel',
            'variant' => '10.000',
            'unit' => 'trx',
            'price' => 12000,
            'cost_price' => 10500,
            'stock' => 0,
            'minimum_stock' => 0,
            'is_active' => true,
        ]);

        $productB = Product::create([
            'code' => 'PLS-AUTO-2',
            'name' => 'Pulsa Indosat',
            'product_type' => 'pulsa',
            'provider_id' => $providerB->id,
            'category' => 'Indosat',
            'variant' => '20.000',
            'unit' => 'trx',
            'price' => 22000,
            'cost_price' => 20500,
            'stock' => 0,
            'minimum_stock' => 0,
            'is_active' => true,
        ]);

        $response = $this->postJson('/ajax/wallets', [
            'name' => 'Dompet Baru',
            'balance' => 50000,
        ]);

        $response->assertCreated()
            ->assertJsonPath('wallet.name', 'Dompet Baru');

        $walletId = $response->json('wallet.id');

        $this->assertDatabaseHas('provider_wallet', [
            'provider_id' => $providerA->id,
            'wallet_id' => $walletId,
        ]);

        $this->assertDatabaseHas('provider_wallet', [
            'provider_id' => $providerB->id,
            'wallet_id' => $walletId,
        ]);
    }

    public function test_report_data_includes_vouchers_pulsa_and_accessories(): void
    {
        $this->signIn('admin');

        // Create wallet and provider
        $wallet = Wallet::create(['name' => 'Dompet Saldo Test', 'balance' => 500000, 'is_active' => true]);
        $provider = Provider::create(['name' => 'TELKOMSEL']);
        $provider->wallets()->attach($wallet->id);

        // 1. Voucher product
        $voucherProduct = Product::create([
            'code' => 'VCR-XL-01',
            'name' => 'Voucher XL Go 5000',
            'product_type' => 'regular',
            'category' => 'Voucher',
            'unit' => 'pcs',
            'price' => 85000,
            'cost_price' => 70000,
            'stock' => 10,
            'is_active' => true,
        ]);

        // 2. Pulsa product
        $pulsaProduct = Product::create([
            'code' => 'PLS-TS-01',
            'name' => 'TELKOMSEL 5K',
            'product_type' => 'pulsa',
            'provider_id' => $provider->id,
            'category' => 'TELKOMSEL',
            'unit' => 'trx',
            'price' => 7000,
            'cost_price' => 5500,
            'stock' => 0,
            'is_active' => true,
        ]);

        // 3. Accessory product
        $accProduct = Product::create([
            'code' => 'ACC-01',
            'name' => 'Kabel Data Type C',
            'product_type' => 'regular',
            'category' => 'Kabel',
            'unit' => 'pcs',
            'price' => 25000,
            'cost_price' => 15000,
            'stock' => 20,
            'is_active' => true,
        ]);

        // Record a Pulsa sale
        \App\Models\PulsaSale::create([
            'reference_number' => 'PLS-20261011-001',
            'sale_date' => now(),
            'product_id' => $pulsaProduct->id,
            'wallet_id' => $wallet->id,
            'customer_name' => 'Budi',
            'target_number' => '08123456789',
            'shift' => 'pagi',
            'payment_method' => 'cash',
            'price' => 7000,
            'cost_amount' => 5500,
            'cash_paid' => 10000,
            'change_amount' => 3000,
            'status' => 'success',
        ]);

        // Record a Voucher sale via PulsaSale
        \App\Models\PulsaSale::create([
            'reference_number' => 'VCR-20261011-001',
            'sale_date' => now(),
            'product_id' => $voucherProduct->id,
            'wallet_id' => null,
            'customer_name' => 'Siti',
            'target_number' => '08512345678',
            'serial_number' => 'SN-XL-999',
            'shift' => 'pagi',
            'payment_method' => 'cash',
            'price' => 85000,
            'cost_amount' => 70000,
            'cash_paid' => 85000,
            'change_amount' => 0,
            'status' => 'success',
        ]);

        // Record an Accessory sale via POS Sale
        $sale = \App\Models\Sale::create([
            'invoice_number' => 'INV-20261011-001',
            'sale_date' => now(),
            'customer_name' => 'Andi',
            'shift' => 'sore',
            'payment_method' => 'qris',
            'subtotal' => 25000,
            'discount' => 0,
            'total' => 25000,
            'cash_paid' => 25000,
            'change_amount' => 0,
        ]);
        $sale->items()->create([
            'product_id' => $accProduct->id,
            'serial_number' => null,
            'quantity' => 1,
            'price' => 25000,
            'subtotal' => 25000,
        ]);

        // 1. Report page view
        $viewResponse = $this->get('/report');
        $viewResponse->assertOk();
        $viewResponse->assertSee('Laporan & Rekapan Penjualan Harian', false);
        $viewResponse->assertSee('Penjualan Voucher');
        $viewResponse->assertSee('Pulsa Elektrik');
        $viewResponse->assertSee('Barang / Acesories');

        // 2. Report data endpoint
        $today = now()->toDateString();
        $dataResponse = $this->getJson("/ajax/report/data?start_date={$today}&end_date={$today}");
        $dataResponse->assertOk()
            ->assertJsonPath('summary.grand_total_sales', 117000)
            ->assertJsonPath('summary.total_voucher_sales', 85000)
            ->assertJsonPath('summary.total_pulsa_sales', 7000)
            ->assertJsonPath('summary.total_accessories_sales', 25000)
            ->assertJsonPath('summary.total_transactions', 3)
            ->assertJsonCount(1, 'details.vouchers')
            ->assertJsonCount(1, 'details.pulsa')
            ->assertJsonCount(1, 'details.accessories');

        // 3. Report PDF export stream/download
        $pdfResponse = $this->get("/report/pdf?start_date={$today}&end_date={$today}&view=preview");
        $pdfResponse->assertOk();
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('content-type'));
    }

    public function test_cashier_can_record_manual_debt_and_repayment(): void
    {
        $this->signIn('cashier');

        // 1. Create debt
        $debtResponse = $this->postJson('/ajax/debts', [
            'customer_name' => 'Surya',
            'customer_phone' => '08123456789',
            'amount' => 1415000,
            'shift' => 'pagi',
            'description' => 'Utang kuota dan pulsa',
            'notes' => 'Janji bayar sore',
        ]);

        $debtResponse->assertCreated()
            ->assertJsonPath('debt.customer_name', 'Surya')
            ->assertJsonPath('debt.amount', '1415000.00')
            ->assertJsonPath('debt.status', 'unpaid');

        $debtId = $debtResponse->json('debt.id');
        $this->assertDatabaseHas('debts', [
            'id' => $debtId,
            'customer_name' => 'Surya',
            'amount' => 1415000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        // 2. Unpaid debts listing
        $unpaidResponse = $this->getJson('/ajax/debts/unpaid');
        $unpaidResponse->assertOk();
        $this->assertTrue(collect($unpaidResponse->json())->contains('id', $debtId));

        // 3. Partial repayment
        $pay1 = $this->postJson('/ajax/debts/payments', [
            'debt_id' => $debtId,
            'customer_name' => 'Surya',
            'amount' => 415000,
            'payment_method' => 'cash',
            'shift' => 'pagi',
            'notes' => 'Cicilan pertama',
        ]);

        $pay1->assertCreated()
            ->assertJsonPath('payment.amount', '415000.00');

        $this->assertDatabaseHas('debts', [
            'id' => $debtId,
            'paid_amount' => 415000,
            'status' => 'partial',
        ]);

        // 4. Final repayment (full settlement)
        $pay2 = $this->postJson('/ajax/debts/payments', [
            'debt_id' => $debtId,
            'customer_name' => 'Surya',
            'amount' => 1000000,
            'payment_method' => 'transfer',
            'shift' => 'sore',
            'notes' => 'Pelunasan transfer',
        ]);

        $pay2->assertCreated()
            ->assertJsonPath('payment.amount', '1000000.00');

        $this->assertDatabaseHas('debts', [
            'id' => $debtId,
            'paid_amount' => 1415000,
            'status' => 'paid',
        ]);
    }

    public function test_sale_with_utang_payment_method_records_debt_and_shows_in_daily_report(): void
    {
        $this->signIn('admin');

        $product = Product::create([
            'code' => 'BRG-UTANG',
            'name' => 'Kabel Data Type-C',
            'product_type' => 'regular',
            'unit' => 'pcs',
            'price' => 35000,
            'cost_price' => 20000,
            'stock' => 10,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);

        // 1. POS Sale on debt (utang)
        $saleResponse = $this->postJson('/ajax/sales', [
            'customer_name' => 'Asep',
            'shift' => 'pagi',
            'payment_method' => 'utang',
            'discount' => 0,
            'cash_paid' => 0,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => 35000,
                ],
            ],
        ]);

        $saleResponse->assertCreated()
            ->assertJsonPath('sale.payment_method', 'utang');

        $this->assertDatabaseHas('debts', [
            'customer_name' => 'Asep',
            'amount' => 35000,
            'shift' => 'pagi',
            'status' => 'unpaid',
        ]);

        // 2. Fetch daily report data
        $today = now()->toDateString();
        $reportResponse = $this->getJson("/ajax/report/data?start_date={$today}&end_date={$today}");
        $reportResponse->assertOk()
            ->assertJsonPath('summary.grand_total_debt', 35000)
            ->assertJsonPath('summary.total_debt_count', 1)
            ->assertJsonPath('shifts.pagi.total_debt', 35000);

        $this->assertTrue(collect($reportResponse->json('details.debts'))->contains('customer_name', 'Asep'));

        // 3. PDF report renders successfully
        $pdfResponse = $this->get("/report/pdf?start_date={$today}&end_date={$today}&view=preview");
        $pdfResponse->assertOk();
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('content-type'));
    }

    public function test_stock_can_be_adjusted_with_add_reduce_and_set(): void
    {
        $this->signIn();

        $product = Product::create([
            'code' => 'ADJ-001',
            'name' => 'Kabel Type C Fast',
            'product_type' => 'regular',
            'category' => 'Aksesoris',
            'unit' => 'pcs',
            'price' => 30000,
            'cost_price' => 15000,
            'stock' => 10,
            'minimum_stock' => 2,
            'is_active' => true,
        ]);

        // 1. Add stock adjustment
        $responseAdd = $this->postJson("/ajax/products/{$product->id}/stock-adjustment", [
            'type' => 'add',
            'quantity' => 5,
            'note' => 'Stok opname penambahan',
        ]);

        $responseAdd->assertOk()
            ->assertJsonPath('product.stock', 15);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 15,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'adjustment',
            'quantity' => 5,
            'note' => 'Stok opname penambahan',
        ]);

        // 2. Reduce stock adjustment
        $responseReduce = $this->postJson("/ajax/products/{$product->id}/stock-adjustment", [
            'type' => 'reduce',
            'quantity' => 3,
            'note' => 'Barang rusak/hilang',
        ]);

        $responseReduce->assertOk()
            ->assertJsonPath('product.stock', 12);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 12,
        ]);

        // 3. Reject reduce if exceeding current stock
        $responseExcess = $this->postJson("/ajax/products/{$product->id}/stock-adjustment", [
            'type' => 'reduce',
            'quantity' => 50,
        ]);

        $responseExcess->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);

        // 4. Set stock adjustment (stock opname final count)
        $responseSet = $this->postJson("/ajax/products/{$product->id}/stock-adjustment", [
            'type' => 'set',
            'quantity' => 20,
            'note' => 'Opname fisik akhir bulan',
        ]);

        $responseSet->assertOk()
            ->assertJsonPath('product.stock', 20);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 20,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'adjustment',
            'quantity' => 8, // difference from 12 to 20
        ]);
    }

    public function test_product_serials_can_be_listed_and_updated(): void
    {
        $this->signIn();

        $product = Product::create([
            'code' => 'SN-PROD',
            'name' => 'HP Zeni Phone 1',
            'product_type' => 'regular',
            'category' => 'HP',
            'unit' => 'unit',
            'price' => 1500000,
            'cost_price' => 1200000,
            'stock' => 1,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 1,
            'serial_number' => 'IMEI-TYPO-1234',
            'expiry_date' => '2026-12-31',
            'cost_price' => 1200000,
            'note' => 'Barang masuk awal',
        ]);

        // 1. Get product serials list
        $responseList = $this->getJson("/ajax/products/{$product->id}/serials");
        $responseList->assertOk()
            ->assertJsonPath('serials.0.serial_number', 'IMEI-TYPO-1234')
            ->assertJsonPath('serials.0.expiry_date', '2026-12-31');

        // 2. Update serial number and expiry
        $responseUpdate = $this->putJson("/ajax/products/{$product->id}/update-serial", [
            'movement_id' => $movement->id,
            'serial_number' => 'IMEI-CORRECT-5678',
            'expiry_date' => '2027-01-15',
            'cost_price' => 1250000,
        ]);

        $responseUpdate->assertOk()
            ->assertJsonPath('message', 'Serial number berhasil diperbarui.');

        $this->assertDatabaseHas('stock_movements', [
            'id' => $movement->id,
            'serial_number' => 'IMEI-CORRECT-5678',
            'expiry_date' => '2027-01-15 00:00:00',
            'cost_price' => 1250000,
        ]);
    }

    public function test_wallet_balance_can_be_adjusted_by_adding_and_reducing(): void
    {
        $this->signIn();

        $wallet = Wallet::create([
            'name' => 'Server Digipos Test',
            'balance' => 100000,
            'is_active' => true,
        ]);

        // 1. Add balance
        $responseAdd = $this->postJson("/ajax/wallets/{$wallet->id}/topup", [
            'type' => 'add',
            'amount' => 50000,
        ]);

        $responseAdd->assertOk();
        $this->assertEquals(150000, (float) $wallet->fresh()->balance);

        // 2. Reduce balance
        $responseReduce = $this->postJson("/ajax/wallets/{$wallet->id}/topup", [
            'type' => 'reduce',
            'amount' => 30000,
            'note' => 'Koreksi saldo server',
        ]);

        $responseReduce->assertOk();
        $this->assertEquals(120000, (float) $wallet->fresh()->balance);

        // 3. Reject reduce if balance insufficient
        $responseFail = $this->postJson("/ajax/wallets/{$wallet->id}/adjust", [
            'type' => 'reduce',
            'amount' => 200000,
        ]);

        $responseFail->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
        $this->assertEquals(120000, (float) $wallet->fresh()->balance);
    }

    public function test_dashboard_includes_expiring_products_within_30_days(): void
    {
        $this->signIn();

        $today = Carbon::today();

        // Product A: Expiring in 10 days (should be reported)
        $prodA = Product::create([
            'code' => 'EXP-001',
            'name' => 'Voucher Game 10 Hari',
            'product_type' => 'voucher',
            'category' => 'Voucher',
            'unit' => 'pcs',
            'price' => 25000,
            'cost_price' => 20000,
            'stock' => 5,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);
        StockMovement::create([
            'product_id' => $prodA->id,
            'type' => 'in',
            'quantity' => 5,
            'serial_number' => 'VCH-10DAYS',
            'expiry_date' => $today->copy()->addDays(10)->toDateString(),
            'cost_price' => 20000,
        ]);

        // Product B: Expiring in 45 days (> 30 days, should NOT be reported)
        $prodB = Product::create([
            'code' => 'EXP-002',
            'name' => 'Voucher Game 45 Hari',
            'product_type' => 'voucher',
            'category' => 'Voucher',
            'unit' => 'pcs',
            'price' => 50000,
            'cost_price' => 45000,
            'stock' => 5,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);
        StockMovement::create([
            'product_id' => $prodB->id,
            'type' => 'in',
            'quantity' => 5,
            'serial_number' => 'VCH-45DAYS',
            'expiry_date' => $today->copy()->addDays(45)->toDateString(),
            'cost_price' => 45000,
        ]);

        // Product C: Already expired 2 days ago (should be reported as is_expired = true)
        $prodC = Product::create([
            'code' => 'EXP-003',
            'name' => 'Perdana Expired',
            'product_type' => 'regular',
            'category' => 'Perdana',
            'unit' => 'pcs',
            'price' => 15000,
            'cost_price' => 10000,
            'stock' => 2,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);
        StockMovement::create([
            'product_id' => $prodC->id,
            'type' => 'in',
            'quantity' => 2,
            'serial_number' => 'SIM-EXPIRED',
            'expiry_date' => $today->copy()->subDays(2)->toDateString(),
            'cost_price' => 10000,
        ]);

        // Product D: Regular accessory expiring in 5 days (non-voucher and non-perdana, should NOT be alerted)
        $prodD = Product::create([
            'code' => 'EXP-004',
            'name' => 'Kabel Data Type-C',
            'product_type' => 'regular',
            'category' => 'Aksesoris',
            'unit' => 'pcs',
            'price' => 20000,
            'cost_price' => 12000,
            'stock' => 10,
            'minimum_stock' => 2,
            'is_active' => true,
        ]);
        StockMovement::create([
            'product_id' => $prodD->id,
            'type' => 'in',
            'quantity' => 10,
            'serial_number' => 'KBL-001',
            'expiry_date' => $today->copy()->addDays(5)->toDateString(),
            'cost_price' => 12000,
        ]);

        $dashboardResponse = $this->getJson('/ajax/dashboard');
        $dashboardResponse->assertOk()
            ->assertJsonPath('summary.expiring_count', 2)
            ->assertJsonPath('expiring_count', 2);

        $expiring = collect($dashboardResponse->json('expiring_products'));
        $this->assertTrue($expiring->contains('product_code', 'EXP-001'));
        $this->assertTrue($expiring->contains('product_code', 'EXP-003'));
        $this->assertFalse($expiring->contains('product_code', 'EXP-002'));
        $this->assertFalse($expiring->contains('product_code', 'EXP-004'));

        $itemA = $expiring->firstWhere('product_code', 'EXP-001');
        $this->assertEquals('Voucher', $itemA['item_type']);

        $itemC = $expiring->firstWhere('product_code', 'EXP-003');
        $this->assertTrue($itemC['is_expired']);
        $this->assertEquals('Kartu Perdana', $itemC['item_type']);
    }

    public function test_electric_data_package_product_is_not_categorized_as_voucher_in_report(): void
    {
        $this->signIn('admin');

        $wallet = Wallet::create(['name' => 'Dompet Indosat', 'balance' => 500000, 'is_active' => true]);
        $provider = Provider::create(['name' => 'INDOSAT']);
        $provider->wallets()->attach($wallet->id);

        // Electric data package product containing keywords "Paket Data" and "Kuota"
        $electricProduct = Product::create([
            'code' => 'PLS-ISAT-DATA',
            'name' => 'Paket Data Freedom Internet 10GB',
            'product_type' => 'pulsa',
            'provider_id' => $provider->id,
            'category' => 'Paket Data',
            'variant' => '10GB',
            'unit' => 'trx',
            'price' => 50000,
            'cost_price' => 45000,
            'stock' => 0,
            'is_active' => true,
        ]);

        $this->assertTrue($electricProduct->isPulsa());
        $this->assertFalse($electricProduct->isVoucher());
        $this->assertFalse($electricProduct->isPerdana());

        // Process an electric sale
        \App\Models\PulsaSale::create([
            'reference_number' => 'PLS-20261019-999',
            'sale_date' => now(),
            'product_id' => $electricProduct->id,
            'wallet_id' => $wallet->id,
            'customer_name' => 'Doni',
            'target_number' => '085712345678',
            'shift' => 'pagi',
            'payment_method' => 'cash',
            'price' => 50000,
            'cost_amount' => 45000,
            'cash_paid' => 50000,
            'change_amount' => 0,
            'status' => 'success',
        ]);

        $today = now()->toDateString();
        $response = $this->getJson("/ajax/report/data?start_date={$today}&end_date={$today}");

        $response->assertOk()
            ->assertJsonPath('summary.total_pulsa_count', 1)
            ->assertJsonPath('summary.total_pulsa_sales', 50000)
            ->assertJsonPath('summary.total_voucher_count', 0)
            ->assertJsonPath('summary.total_voucher_sales', 0)
            ->assertJsonCount(1, 'details.pulsa')
            ->assertJsonCount(0, 'details.vouchers');

        $this->assertEquals('PLS-20261019-999', $response->json('details.pulsa.0.reference_number'));
        $this->assertEquals('Paket Data Freedom Internet 10GB', $response->json('details.pulsa.0.product_name'));
    }

    public function test_cancel_electric_pulsa_sale_refunds_wallet_balance_and_shows_in_sales_history(): void
    {
        $this->signIn('cashier');

        $wallet = Wallet::create([
            'name' => 'Dompet Digipos',
            'balance' => 100000,
            'is_active' => true,
        ]);

        $product = Product::create([
            'code' => 'TSEL-50K',
            'name' => 'Telkomsel 50K Elektrik',
            'product_type' => 'pulsa',
            'category' => 'Telkomsel',
            'variant' => '50.000',
            'unit' => 'trx',
            'price' => 52000,
            'cost_price' => 48000,
            'stock' => 0,
            'is_active' => true,
        ]);
        $product->wallets()->attach($wallet->id);

        $saleResponse = $this->postJson('/ajax/pulsa/sales', [
            'product_id' => $product->id,
            'wallet_id' => $wallet->id,
            'target_number' => '081234567899',
            'payment_method' => 'cash',
            'cash_paid' => 55000,
        ]);

        $saleResponse->assertCreated();
        $saleId = $saleResponse->json('sale.id');

        // Check wallet balance was deducted
        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => 52000, // 100000 - 48000
        ]);

        // Cancel the sale from sales history
        $cancelResponse = $this->postJson("/ajax/pulsa/sales/{$saleId}/cancel", [
            'notes' => 'Nomor salah input, saldo dikembalikan',
        ]);

        $cancelResponse->assertOk()
            ->assertJsonPath('sale.status', 'cancelled');

        // Assert wallet balance is refunded back to 100000
        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => 100000,
        ]);

        // Verify history data includes the cancelled status
        $historyResponse = $this->getJson('/ajax/sales/history-data');
        $historyResponse->assertOk();
        $pulsaSales = collect($historyResponse->json('pulsa_sales'));
        $cancelledItem = $pulsaSales->firstWhere('id', $saleId);
        $this->assertNotNull($cancelledItem);
        $this->assertEquals('cancelled', $cancelledItem['status']);
    }

    public function test_cancel_pos_sale_restores_product_stock_and_records_stock_movement(): void
    {
        $this->signIn('cashier');

        $product = Product::create([
            'code' => 'ACC-001',
            'name' => 'Kabel Data Type-C',
            'product_type' => 'regular',
            'category' => 'Aksesoris',
            'price' => 25000,
            'cost_price' => 15000,
            'stock' => 10,
            'minimum_stock' => 2,
            'is_active' => true,
        ]);

        $saleResponse = $this->postJson('/ajax/sales', [
            'customer_name' => 'Budi',
            'payment_method' => 'cash',
            'cash_paid' => 50000,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $saleResponse->assertCreated();
        $saleId = $saleResponse->json('sale.id');

        // Stock decreased to 8
        $this->assertEquals(8, $product->fresh()->stock);

        // Cancel POS sale
        $cancelResponse = $this->postJson("/ajax/sales/{$saleId}/cancel", [
            'notes' => 'Pelanggan salah tipe charger',
        ]);

        $cancelResponse->assertOk()
            ->assertJsonPath('sale.status', 'cancelled');

        // Stock is restored to 10
        $this->assertEquals(10, $product->fresh()->stock);

        // Stock movement adjustment recorded
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'adjustment',
            'quantity' => 2,
        ]);

        // History data reflects cancelled status
        $historyResponse = $this->getJson('/ajax/sales/history-data');
        $historyResponse->assertOk();
        $posSales = collect($historyResponse->json('pos_sales'));
        $cancelledSale = $posSales->firstWhere('id', $saleId);
        $this->assertNotNull($cancelledSale);
        $this->assertEquals('cancelled', $cancelledSale['status']);
    }

    public function test_transaction_with_pls_code_is_recorded_under_digipost_and_not_voucher_telkomsel(): void
    {
        $this->signIn('admin');

        $provider = Provider::create(['name' => 'TELKOMSEL']);
        $wallet = Wallet::create([
            'name' => 'Digipos Dompet',
            'balance' => 200000,
            'is_active' => true,
        ]);

        // Product with word "Voucher" in its name and category, but sold as pulsa elektrik with PLS code
        $electricVoucherProduct = Product::create([
            'code' => 'PLS-TSEL-VCR14',
            'name' => 'Voucher Telkomsel 14GB Inject Data',
            'product_type' => 'pulsa',
            'provider_id' => $provider->id,
            'category' => 'Voucher Telkomsel',
            'variant' => '14GB',
            'unit' => 'trx',
            'price' => 50000,
            'cost_price' => 45000,
            'stock' => 0,
            'is_active' => true,
        ]);
        $electricVoucherProduct->wallets()->attach($wallet->id);

        $this->assertTrue($electricVoucherProduct->isPulsa());
        $this->assertFalse($electricVoucherProduct->isVoucher());

        // Create an electric sale (has reference starting with PLS-)
        \App\Models\PulsaSale::create([
            'reference_number' => 'PLS-20261019-1234',
            'sale_date' => now(),
            'product_id' => $electricVoucherProduct->id,
            'wallet_id' => $wallet->id,
            'customer_name' => 'Rian',
            'target_number' => '081234567890',
            'shift' => 'pagi',
            'payment_method' => 'cash',
            'price' => 50000,
            'cost_amount' => 45000,
            'cash_paid' => 50000,
            'change_amount' => 0,
            'status' => 'success',
        ]);

        $today = now()->toDateString();
        $response = $this->getJson("/ajax/report/data?start_date={$today}&end_date={$today}");

        $response->assertOk();

        // 1. Verify in the 14 Paper Table (voucher_pulsa in shift pagi)
        $pagiVoucherPulsa = collect($response->json('shifts.pagi.voucher_pulsa'));

        $digipostRow = $pagiVoucherPulsa->firstWhere('name', 'digipost (Telkomsel)');
        $voucherTelkomselRow = $pagiVoucherPulsa->firstWhere('name', 'voucher telkomsel');

        $this->assertNotNull($digipostRow);
        $this->assertNotNull($voucherTelkomselRow);

        // MUST be recorded in digipost (Telkomsel)
        $this->assertEquals(1, $digipostRow['qty']);
        $this->assertEquals(50000, $digipostRow['total']);

        // MUST NOT be recorded in voucher telkomsel
        $this->assertEquals(0, $voucherTelkomselRow['qty']);
        $this->assertEquals(0, $voucherTelkomselRow['total']);

        // 2. Verify in details
        $response->assertJsonPath('summary.total_pulsa_count', 1)
            ->assertJsonPath('summary.total_pulsa_sales', 50000)
            ->assertJsonPath('summary.total_voucher_count', 0)
            ->assertJsonPath('summary.total_voucher_sales', 0)
            ->assertJsonCount(1, 'details.pulsa')
            ->assertJsonCount(0, 'details.vouchers');

        $this->assertEquals('PLS-20261019-1234', $response->json('details.pulsa.0.reference_number'));
        $this->assertEquals('TELKOMSEL', $response->json('details.pulsa.0.provider'));
    }

    public function test_sales_history_can_be_filtered_by_period(): void
    {
        $this->signIn('cashier');

        $product = Product::create([
            'code' => 'ACC-CASE',
            'name' => 'Casing HP',
            'product_type' => 'regular',
            'category' => 'Aksesoris',
            'price' => 30000,
            'cost_price' => 15000,
            'stock' => 20,
            'is_active' => true,
        ]);

        $electricProduct = Product::create([
            'code' => 'PLS-AXIS',
            'name' => 'Pulsa Axis 25k',
            'product_type' => 'pulsa',
            'category' => 'Axis',
            'price' => 26000,
            'cost_price' => 24500,
            'stock' => 0,
            'is_active' => true,
        ]);

        // 1. Transaction on 2026-10-10
        $oldDate = Carbon::parse('2026-10-10 10:00:00');
        $oldSale = \App\Models\Sale::create([
            'invoice_number' => 'INV-20261010-001',
            'sale_date' => $oldDate,
            'customer_name' => 'Pelanggan Lama',
            'payment_method' => 'cash',
            'shift' => 'pagi',
            'status' => 'completed',
            'subtotal' => 30000,
            'discount' => 0,
            'total' => 30000,
            'cash_paid' => 30000,
            'change_amount' => 0,
        ]);
        \App\Models\SaleItem::create([
            'sale_id' => $oldSale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 30000,
            'subtotal' => 30000,
        ]);

        \App\Models\PulsaSale::create([
            'reference_number' => 'PLS-20261010-001',
            'sale_date' => $oldDate,
            'product_id' => $electricProduct->id,
            'customer_name' => 'Pelanggan Pulsa Lama',
            'target_number' => '083812345678',
            'shift' => 'pagi',
            'payment_method' => 'cash',
            'price' => 26000,
            'cost_amount' => 24500,
            'cash_paid' => 26000,
            'change_amount' => 0,
            'status' => 'success',
        ]);

        // 2. Transaction on 2026-10-19 (Today)
        $todayDate = Carbon::parse('2026-10-19 14:00:00');
        $newSale = \App\Models\Sale::create([
            'invoice_number' => 'INV-20261019-002',
            'sale_date' => $todayDate,
            'customer_name' => 'Pelanggan Hari Ini',
            'payment_method' => 'cash',
            'shift' => 'sore',
            'status' => 'completed',
            'subtotal' => 30000,
            'discount' => 0,
            'total' => 30000,
            'cash_paid' => 30000,
            'change_amount' => 0,
        ]);
        \App\Models\SaleItem::create([
            'sale_id' => $newSale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 30000,
            'subtotal' => 30000,
        ]);

        \App\Models\PulsaSale::create([
            'reference_number' => 'PLS-20261019-002',
            'sale_date' => $todayDate,
            'product_id' => $electricProduct->id,
            'customer_name' => 'Pelanggan Pulsa Hari Ini',
            'target_number' => '083898765432',
            'shift' => 'sore',
            'payment_method' => 'cash',
            'price' => 26000,
            'cost_amount' => 24500,
            'cash_paid' => 26000,
            'change_amount' => 0,
            'status' => 'success',
        ]);

        // Filter: Today only (2026-10-19)
        $resToday = $this->getJson('/ajax/sales/history-data?start_date=2026-10-19&end_date=2026-10-19');
        $resToday->assertOk();
        $this->assertCount(1, $resToday->json('pos_sales'));
        $this->assertCount(1, $resToday->json('pulsa_sales'));
        $this->assertEquals('INV-20261019-002', $resToday->json('pos_sales.0.invoice_number'));
        $this->assertEquals('PLS-20261019-002', $resToday->json('pulsa_sales.0.reference_number'));

        // Filter: Past period (2026-10-01 to 2026-10-15)
        $resOld = $this->getJson('/ajax/sales/history-data?start_date=2026-10-01&end_date=2026-10-15');
        $resOld->assertOk();
        $this->assertCount(1, $resOld->json('pos_sales'));
        $this->assertCount(1, $resOld->json('pulsa_sales'));
        $this->assertEquals('INV-20261010-001', $resOld->json('pos_sales.0.invoice_number'));
        $this->assertEquals('PLS-20261010-001', $resOld->json('pulsa_sales.0.reference_number'));

        // Without filter: All transactions returned
        $resAll = $this->getJson('/ajax/sales/history-data');
        $resAll->assertOk();
        $this->assertCount(2, $resAll->json('pos_sales'));
        $this->assertCount(2, $resAll->json('pulsa_sales'));
    }

    public function test_voucher_product_with_provider_id_can_receive_stock_and_adjust_stock(): void
    {
        $this->signIn();

        $provider = Provider::create(['name' => 'Axis']);

        $product = Product::create([
            'code' => 'VCR-AXIS-5GB',
            'name' => 'AIGO 5GB',
            'product_type' => 'voucher',
            'provider_id' => $provider->id,
            'category' => 'AXIS',
            'variant' => '5GB / 3 Hari',
            'unit' => 'pcs',
            'price' => 18000,
            'cost_price' => 15000,
            'stock' => 0,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        $this->assertFalse($product->isPulsa());
        $this->assertTrue($product->isVoucher());

        // Test add stock
        $addStockResponse = $this->postJson("/ajax/products/{$product->id}/stock", [
            'reference_type' => 'manual',
            'notes' => 'Restock voucher',
            'items' => [
                [
                    'quantity' => 5,
                    'cost_price' => 15000,
                    'serial_number' => 'AXIS1234567890',
                ],
            ],
        ]);

        $addStockResponse->assertOk()
            ->assertJsonPath('message', 'Stok berhasil ditambahkan.');

        $this->assertEquals(5, $product->fresh()->stock);

        // Test adjust stock
        $adjustResponse = $this->postJson("/ajax/products/{$product->id}/stock-adjustment", [
            'type' => 'add',
            'quantity' => 2,
            'note' => 'Penyesuaian stok voucher',
        ]);

        $adjustResponse->assertOk()
            ->assertJsonPath('message', 'Stok berhasil disesuaikan menjadi 7.');

        $this->assertEquals(7, $product->fresh()->stock);
    }

    public function test_electric_pulsa_product_rejects_add_stock_and_adjust_stock(): void
    {
        $this->signIn();

        $provider = Provider::create(['name' => 'Telkomsel']);

        $product = Product::create([
            'code' => 'PLS-10',
            'name' => 'Pulsa Telkomsel 10k',
            'product_type' => 'pulsa',
            'provider_id' => $provider->id,
            'category' => 'TELKOMSEL',
            'variant' => '10.000',
            'unit' => 'trx',
            'price' => 12000,
            'cost_price' => 10500,
            'stock' => 0,
            'minimum_stock' => 0,
            'is_active' => true,
        ]);

        $this->assertTrue($product->isPulsa());
        $this->assertFalse($product->isVoucher());

        // Attempt add stock -> should fail with 422
        $addStockResponse = $this->postJson("/ajax/products/{$product->id}/stock", [
            'items' => [
                [
                    'quantity' => 5,
                    'cost_price' => 10000,
                ],
            ],
        ]);

        $addStockResponse->assertStatus(422)
            ->assertJsonValidationErrors(['product']);

        // Attempt adjust stock -> should fail with 422
        $adjustResponse = $this->postJson("/ajax/products/{$product->id}/stock-adjustment", [
            'type' => 'add',
            'quantity' => 2,
            'note' => 'Tes',
        ]);

        $adjustResponse->assertStatus(422)
            ->assertJsonValidationErrors(['product']);
    }

    public function test_cancel_voucher_sale_restores_stock_and_available_serials(): void
    {
        $this->signIn('cashier');

        $provider = Provider::create(['name' => 'Telkomsel']);

        $product = Product::create([
            'code' => 'VCR-TSEL-10GB',
            'name' => 'Voucher Kuota Telkomsel 10GB',
            'product_type' => 'voucher',
            'provider_id' => $provider->id,
            'category' => 'TELKOMSEL',
            'variant' => '10GB / 30 Hari',
            'unit' => 'pcs',
            'price' => 35000,
            'cost_price' => 30000,
            'stock' => 2,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);

        // Add 1 stock with a serial number
        $this->postJson("/ajax/products/{$product->id}/stock", [
            'items' => [
                [
                    'quantity' => 1,
                    'cost_price' => 30000,
                    'serial_number' => 'TSEL999888777',
                ],
            ],
        ])->assertOk();

        // Product stock is now 3 (2 initial + 1)
        $this->assertEquals(3, $product->fresh()->stock);

        // Check voucher dashboard: 1 available serial
        $dashboardRes = $this->getJson('/ajax/voucher/dashboard');
        $dashboardRes->assertOk();
        $pData = collect($dashboardRes->json('products'))->firstWhere('id', $product->id);
        $this->assertEquals(1, $pData['available_serials_count']);

        // Sell the voucher
        $sellResponse = $this->postJson('/ajax/voucher/sales', [
            'product_id' => $product->id,
            'serial_number' => 'TSEL999888777',
            'payment_method' => 'cash',
            'cash_paid' => 35000,
        ]);

        $sellResponse->assertCreated();
        $saleId = $sellResponse->json('sale.id');
        $refNumber = $sellResponse->json('sale.reference_number');
        $this->assertStringStartsWith('VCR-', $refNumber);

        // Stock decreased to 2
        $this->assertEquals(2, $product->fresh()->stock);

        // Available serial count is now 0
        $dashboardResAfterSale = $this->getJson('/ajax/voucher/dashboard');
        $pDataAfterSale = collect($dashboardResAfterSale->json('products'))->firstWhere('id', $product->id);
        $this->assertEquals(0, $pDataAfterSale['available_serials_count']);

        // Cancel the voucher transaction
        $cancelResponse = $this->postJson("/ajax/pulsa/sales/{$saleId}/cancel", [
            'notes' => 'Pembatalan transaksi voucher',
        ]);

        $cancelResponse->assertOk()
            ->assertJsonPath('sale.status', 'cancelled');

        // Stock is restored to 3
        $this->assertEquals(3, $product->fresh()->stock);

        // Stock movement adjustment recorded
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'adjustment',
            'quantity' => 1,
            'serial_number' => 'TSEL999888777',
            'note' => 'Pembatalan Transaksi ' . $refNumber,
        ]);

        // Serial number is back to available serials
        $dashboardResAfterCancel = $this->getJson('/ajax/voucher/dashboard');
        $pDataAfterCancel = collect($dashboardResAfterCancel->json('products'))->firstWhere('id', $product->id);
        $this->assertEquals(1, $pDataAfterCancel['available_serials_count']);
        $this->assertEquals('TSEL999888777', $pDataAfterCancel['available_serials'][0]['serial_number']);
    }

    public function test_manual_electric_sale_can_be_created_with_custom_name_price_and_cost(): void
    {
        $this->signIn('admin');

        $wallet = Wallet::create([
            'name' => 'Dompet Multi Server',
            'balance' => 100000,
            'is_active' => true,
        ]);

        $response = $this->postJson('/ajax/pulsa/sales', [
            'is_manual' => true,
            'manual_product_name' => 'Token Listrik PLN 50.000',
            'price' => 52500,
            'cost_price' => 50500,
            'wallet_id' => $wallet->id,
            'target_number' => '14234567890',
            'customer_name' => 'Pak Budi',
            'shift' => 'pagi',
            'payment_method' => 'cash',
            'cash_paid' => 52500,
            'notes' => 'Token PLN manual',
        ]);

        $response->assertCreated()
            ->assertJsonPath('sale.manual_product_name', 'Token Listrik PLN 50.000')
            ->assertJsonPath('sale.price', '52500.00')
            ->assertJsonPath('sale.cost_amount', '50500.00');

        $saleId = $response->json('sale.id');

        // Wallet balance deducted from 100,000 to 49,500
        $this->assertEquals(49500, (float) $wallet->fresh()->balance);

        $this->assertDatabaseHas('pulsa_sales', [
            'id' => $saleId,
            'product_id' => null,
            'manual_product_name' => 'Token Listrik PLN 50.000',
            'price' => 52500,
            'cost_amount' => 50500,
            'wallet_id' => $wallet->id,
            'target_number' => '14234567890',
        ]);

        // Check report data includes this manual sale
        $today = now()->format('Y-m-d');
        $reportRes = $this->getJson("/ajax/report/data?start_date={$today}&end_date={$today}");
        $reportRes->assertOk();
        $detailedPulsa = collect($reportRes->json('details.pulsa'));
        $found = $detailedPulsa->firstWhere('id', 'PLS-' . $saleId);
        $this->assertNotNull($found);
        $this->assertEquals('Token Listrik PLN 50.000', $found['product_name']);
        $this->assertEquals(52500, $found['price']);
        $this->assertEquals(50500, $found['cost_price']);

        // Check history data includes this manual sale
        $historyRes = $this->getJson('/ajax/sales/history-data');
        $historyRes->assertOk();
        $historyPulsa = collect($historyRes->json('pulsa_sales'))->firstWhere('id', $saleId);
        $this->assertNotNull($historyPulsa);
        $this->assertEquals('Token Listrik PLN 50.000', $historyPulsa['manual_product_name']);

        // Cancel the manual transaction and verify wallet balance is refunded
        $cancelRes = $this->postJson("/ajax/pulsa/sales/{$saleId}/cancel", [
            'notes' => 'Salah no meter',
        ]);

        $cancelRes->assertOk()
            ->assertJsonPath('sale.status', 'cancelled');

        // Wallet refunded back to 100,000
        $this->assertEquals(100000, (float) $wallet->fresh()->balance);
    }

    public function test_catalog_electric_sale_with_custom_price_and_cost(): void
    {
        $this->signIn('cashier');

        $provider = Provider::create(['name' => 'Telkomsel']);
        $wallet = Wallet::create([
            'name' => 'Dompet Digipos',
            'balance' => 80000,
            'is_active' => true,
        ]);
        $provider->wallets()->attach($wallet->id);

        $product = Product::create([
            'code' => 'PLS-10',
            'name' => 'Pulsa 10.000',
            'product_type' => 'pulsa',
            'provider_id' => $provider->id,
            'category' => 'TELKOMSEL',
            'price' => 12000,
            'cost_price' => 10500,
            'stock' => 0,
            'is_active' => true,
        ]);

        // Sale with price and cost overridden
        $response = $this->postJson('/ajax/pulsa/sales', [
            'product_id' => $product->id,
            'price' => 13500, // custom price
            'cost_price' => 11000, // custom cost
            'wallet_id' => $wallet->id,
            'target_number' => '081234567890',
            'payment_method' => 'cash',
            'cash_paid' => 13500,
        ]);

        $response->assertCreated()
            ->assertJsonPath('sale.price', '13500.00')
            ->assertJsonPath('sale.cost_amount', '11000.00');

        // Wallet deducted by 11,000 (80,000 - 11,000 = 69,000)
        $this->assertEquals(69000, (float) $wallet->fresh()->balance);
    }

    public function test_expenses_can_be_recorded_with_shift_and_are_separated_in_reports(): void
    {
        $this->signIn('admin');
        $today = today()->toDateString();

        // 1. Create morning expense
        $responsePagi = $this->postJson('/ajax/expenses', [
            'expense_date' => now()->setTime(9, 0)->toDateTimeString(),
            'shift' => 'pagi',
            'category' => 'Operasional',
            'description' => 'Beli token listrik pagi',
            'amount' => 20000,
        ]);
        $responsePagi->assertCreated()
            ->assertJsonPath('expense.shift', 'pagi');

        // 2. Create evening expense
        $responseSore = $this->postJson('/ajax/expenses', [
            'expense_date' => now()->setTime(17, 0)->toDateTimeString(),
            'shift' => 'sore',
            'category' => 'Operasional',
            'description' => 'Makan malam kasir sore',
            'amount' => 35000,
        ]);
        $responseSore->assertCreated()
            ->assertJsonPath('expense.shift', 'sore');

        // 3. Create POS sales for morning and evening
        $product = Product::create([
            'code' => 'ACC-01',
            'name' => 'Kabel Data',
            'product_type' => 'regular',
            'price' => 50000,
            'stock' => 10,
            'is_active' => true,
        ]);

        // Sale morning (cash 50,000)
        $this->postJson('/ajax/sales', [
            'shift' => 'pagi',
            'payment_method' => 'cash',
            'cash_paid' => 50000,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => 50000,
                ],
            ],
        ])->assertCreated();

        // Sale evening (cash 100,000)
        $this->postJson('/ajax/sales', [
            'shift' => 'sore',
            'payment_method' => 'cash',
            'cash_paid' => 100000,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'price' => 50000,
                ],
            ],
        ])->assertCreated();

        // 4. Fetch daily report
        $reportRes = $this->getJson("/ajax/report/data?start_date={$today}&end_date={$today}");
        $reportRes->assertOk();

        $data = $reportRes->json();

        // Verify shift pagi
        $this->assertEquals(20000, $data['shifts']['pagi']['total_expenses']);
        $this->assertEquals(50000, $data['shifts']['pagi']['total_cash']);
        // Net cash pagi = 50,000 - 20,000 = 30,000
        $this->assertEquals(30000, $data['shifts']['pagi']['net_cash']);

        // Verify shift sore
        $this->assertEquals(35000, $data['shifts']['sore']['total_expenses']);
        $this->assertEquals(100000, $data['shifts']['sore']['total_cash']);
        // Net cash sore = 100,000 - 35,000 = 65,000
        $this->assertEquals(65000, $data['shifts']['sore']['net_cash']);

        // Verify global expenses
        $this->assertEquals(55000, $data['expenses']['total']);

        // 5. Test PDF export works
        $pdfRes = $this->get("/report/pdf?start_date={$today}&end_date={$today}&view=preview");
        $pdfRes->assertOk();
    }

    public function test_pos_sale_on_debt_requires_customer_name_and_syncs_with_debt_records(): void
    {
        $this->signIn('cashier');

        $product = Product::create([
            'code' => 'BRG-UTG-01',
            'name' => 'Headset Bluetooth',
            'product_type' => 'regular',
            'unit' => 'pcs',
            'price' => 75000,
            'cost_price' => 50000,
            'stock' => 10,
            'is_active' => true,
        ]);

        // 1. Utang without customer_name must be rejected
        $responseFailed = $this->postJson('/ajax/sales', [
            'customer_name' => '',
            'shift' => 'pagi',
            'payment_method' => 'utang',
            'discount' => 0,
            'cash_paid' => 0,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => 75000,
                ],
            ],
        ]);

        $responseFailed->assertStatus(422)
            ->assertJsonValidationErrors(['customer_name']);

        // 2. Utang with customer_name and phone succeeds and synchronizes with debts
        $responseSuccess = $this->postJson('/ajax/sales', [
            'customer_name' => 'Pak Haji Komar',
            'customer_phone' => '081299887766',
            'shift' => 'pagi',
            'payment_method' => 'utang',
            'discount' => 0,
            'cash_paid' => 25000, // DP 25.000
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => 75000,
                ],
            ],
        ]);

        $responseSuccess->assertCreated()
            ->assertJsonPath('sale.customer_name', 'Pak Haji Komar')
            ->assertJsonPath('sale.payment_method', 'utang')
            ->assertJsonPath('sale.total', '75000.00');

        $saleId = $responseSuccess->json('sale.id');

        // Verify debt record created and synchronized
        $this->assertDatabaseHas('debts', [
            'sale_id' => $saleId,
            'customer_name' => 'Pak Haji Komar',
            'customer_phone' => '081299887766',
            'amount' => 75000,
            'paid_amount' => 25000,
            'status' => 'partial',
            'shift' => 'pagi',
        ]);

        // Verify DP payment created in debt_payments
        $this->assertDatabaseHas('debt_payments', [
            'customer_name' => 'Pak Haji Komar',
            'amount' => 25000,
            'payment_method' => 'cash',
            'shift' => 'pagi',
        ]);

        // Verify debt shows up in unpaid debts endpoint
        $unpaidResponse = $this->getJson('/ajax/debts/unpaid');
        $unpaidResponse->assertOk();
        $debtsList = collect($unpaidResponse->json());
        $debtItem = $debtsList->firstWhere('customer_name', 'Pak Haji Komar');
        $this->assertNotNull($debtItem);
        $this->assertEquals(75000, (float) $debtItem['amount']);
        $this->assertEquals(25000, (float) $debtItem['paid_amount']);

        // 3. Cancelling the sale cancels the linked debt
        $cancelResponse = $this->postJson("/ajax/sales/{$saleId}/cancel", [
            'notes' => 'Pelanggan membatalkan pesanan',
        ]);
        $cancelResponse->assertOk();

        $this->assertDatabaseHas('debts', [
            'sale_id' => $saleId,
            'status' => 'cancelled',
        ]);

        // Cancelled debt must no longer appear in unpaid debts list
        $unpaidAfterCancel = $this->getJson('/ajax/debts/unpaid');
        $this->assertNull(collect($unpaidAfterCancel->json())->firstWhere('sale_id', $saleId));
    }

    public function test_daily_report_separates_rita_voucher_tri_and_sisimple_indosat_and_renames_voucher_axis(): void
    {
        $this->signIn('admin');

        $triProvider = Provider::create(['name' => 'THREE']);
        $triWallet = Wallet::create(['name' => 'RITA Tri', 'balance' => 500000, 'is_active' => true]);

        $indosatProvider = Provider::create(['name' => 'INDOSAT']);
        $indosatWallet = Wallet::create(['name' => 'Sisimple Indosat', 'balance' => 500000, 'is_active' => true]);

        // 1. Tri Electric (Rita)
        $triElectricProduct = Product::create([
            'code' => 'PLS-TRI-10K',
            'name' => 'Tri Rita 10K Elektrik',
            'product_type' => 'pulsa',
            'provider_id' => $triProvider->id,
            'category' => 'THREE',
            'unit' => 'trx',
            'price' => 12000,
            'cost_price' => 10500,
            'stock' => 0,
            'is_active' => true,
        ]);
        $triElectricProduct->wallets()->attach($triWallet->id);

        \App\Models\PulsaSale::create([
            'reference_number' => 'PLS-20261027-001',
            'sale_date' => now(),
            'product_id' => $triElectricProduct->id,
            'wallet_id' => $triWallet->id,
            'customer_name' => 'Pelanggan Tri Elektrik',
            'target_number' => '089612345678',
            'shift' => 'pagi',
            'payment_method' => 'cash',
            'price' => 12000,
            'cost_amount' => 10500,
            'cash_paid' => 12000,
            'change_amount' => 0,
            'status' => 'success',
        ]);

        // 2. Voucher Tri
        $voucherTriProduct = Product::create([
            'code' => 'VCR-TRI-6GB',
            'name' => 'Voucher Tri Happy 6GB',
            'product_type' => 'regular',
            'category' => 'Voucher',
            'unit' => 'pcs',
            'price' => 25000,
            'cost_price' => 22000,
            'stock' => 5,
            'is_active' => true,
        ]);

        \App\Models\PulsaSale::create([
            'reference_number' => 'VCR-20261027-001',
            'sale_date' => now(),
            'product_id' => $voucherTriProduct->id,
            'wallet_id' => null,
            'customer_name' => 'Pelanggan Voucher Tri',
            'target_number' => '089699998888',
            'shift' => 'pagi',
            'payment_method' => 'cash',
            'price' => 25000,
            'cost_amount' => 22000,
            'cash_paid' => 25000,
            'change_amount' => 0,
            'status' => 'success',
        ]);

        // 3. Indosat Electric (Sisimple)
        $isatElectricProduct = Product::create([
            'code' => 'PLS-ISAT-25K',
            'name' => 'Indosat Sisimple 25K Elektrik',
            'product_type' => 'pulsa',
            'provider_id' => $indosatProvider->id,
            'category' => 'INDOSAT',
            'unit' => 'trx',
            'price' => 26000,
            'cost_price' => 25200,
            'stock' => 0,
            'is_active' => true,
        ]);
        $isatElectricProduct->wallets()->attach($indosatWallet->id);

        \App\Models\PulsaSale::create([
            'reference_number' => 'PLS-20261027-002',
            'sale_date' => now(),
            'product_id' => $isatElectricProduct->id,
            'wallet_id' => $indosatWallet->id,
            'customer_name' => 'Pelanggan Sisimple',
            'target_number' => '085712345678',
            'shift' => 'pagi',
            'payment_method' => 'cash',
            'price' => 26000,
            'cost_amount' => 25200,
            'cash_paid' => 26000,
            'change_amount' => 0,
            'status' => 'success',
        ]);

        // 4. Voucher Indosat
        $voucherIsatProduct = Product::create([
            'code' => 'VCR-ISAT-5GB',
            'name' => 'Voucher Indosat IM3 Freedom 5GB',
            'product_type' => 'regular',
            'category' => 'Voucher',
            'unit' => 'pcs',
            'price' => 30000,
            'cost_price' => 27000,
            'stock' => 5,
            'is_active' => true,
        ]);

        \App\Models\PulsaSale::create([
            'reference_number' => 'VCR-20261027-002',
            'sale_date' => now(),
            'product_id' => $voucherIsatProduct->id,
            'wallet_id' => null,
            'customer_name' => 'Pelanggan Voucher Indosat',
            'target_number' => '085788887777',
            'shift' => 'pagi',
            'payment_method' => 'cash',
            'price' => 30000,
            'cost_amount' => 27000,
            'cash_paid' => 30000,
            'change_amount' => 0,
            'status' => 'success',
        ]);

        // 5. Voucher Axis
        $voucherAxisProduct = Product::create([
            'code' => 'VCR-AXIS-AIGO',
            'name' => 'Voucher Axis AIGO 3GB',
            'product_type' => 'regular',
            'category' => 'Voucher',
            'unit' => 'pcs',
            'price' => 18000,
            'cost_price' => 15000,
            'stock' => 10,
            'is_active' => true,
        ]);

        \App\Models\PulsaSale::create([
            'reference_number' => 'VCR-20261027-003',
            'sale_date' => now(),
            'product_id' => $voucherAxisProduct->id,
            'wallet_id' => null,
            'customer_name' => 'Pelanggan Voucher Axis',
            'target_number' => '083811112222',
            'shift' => 'pagi',
            'payment_method' => 'cash',
            'price' => 18000,
            'cost_amount' => 15000,
            'cash_paid' => 18000,
            'change_amount' => 0,
            'status' => 'success',
        ]);

        // 6. Perdana Axis (must go to 'perdana ALL OPERATOR')
        $perdanaAxisProduct = Product::create([
            'code' => 'SP-AXIS-01',
            'name' => 'Perdana AXIS Siap Pakai 10GB',
            'product_type' => 'regular',
            'category' => 'Perdana',
            'unit' => 'pcs',
            'price' => 35000,
            'cost_price' => 28000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $saleAxis = \App\Models\Sale::create([
            'invoice_number' => 'INV-20261027-AXIS',
            'sale_date' => now(),
            'customer_name' => 'Pelanggan Perdana Axis',
            'shift' => 'pagi',
            'payment_method' => 'cash',
            'subtotal' => 35000,
            'discount' => 0,
            'total' => 35000,
            'cash_paid' => 35000,
            'change_amount' => 0,
        ]);
        $saleAxis->items()->create([
            'product_id' => $perdanaAxisProduct->id,
            'serial_number' => 'SN-PERDANA-AXIS-001',
            'quantity' => 1,
            'price' => 35000,
            'subtotal' => 35000,
        ]);

        $today = now()->toDateString();
        $response = $this->getJson("/ajax/report/data?start_date={$today}&end_date={$today}");
        $response->assertOk();

        $pagiVoucherPulsa = collect($response->json('shifts.pagi.voucher_pulsa'));

        // Check old categories are gone
        $this->assertNull($pagiVoucherPulsa->firstWhere('name', 'rita/voucher tree (Three)'));
        $this->assertNull($pagiVoucherPulsa->firstWhere('name', 'voucher/sisimple indosat (Indosat)'));
        $this->assertNull($pagiVoucherPulsa->firstWhere('name', 'voucher/perdana axis'));

        // Check 1: rita (stock elektrik)
        $ritaRow = $pagiVoucherPulsa->firstWhere('name', 'rita (stock elektrik)');
        $this->assertNotNull($ritaRow);
        $this->assertEquals(1, $ritaRow['qty']);
        $this->assertEquals(12000, $ritaRow['total']);

        // Check 2: voucher tri
        $voucherTriRow = $pagiVoucherPulsa->firstWhere('name', 'voucher tri');
        $this->assertNotNull($voucherTriRow);
        $this->assertEquals(1, $voucherTriRow['qty']);
        $this->assertEquals(25000, $voucherTriRow['total']);

        // Check 3: sisimple indosat (elektrik)
        $sisimpleRow = $pagiVoucherPulsa->firstWhere('name', 'sisimple indosat (elektrik)');
        $this->assertNotNull($sisimpleRow);
        $this->assertEquals(1, $sisimpleRow['qty']);
        $this->assertEquals(26000, $sisimpleRow['total']);

        // Check 4: voucher indosat
        $voucherIsatRow = $pagiVoucherPulsa->firstWhere('name', 'voucher indosat');
        $this->assertNotNull($voucherIsatRow);
        $this->assertEquals(1, $voucherIsatRow['qty']);
        $this->assertEquals(30000, $voucherIsatRow['total']);

        // Check 5: voucher axis
        $voucherAxisRow = $pagiVoucherPulsa->firstWhere('name', 'voucher axis');
        $this->assertNotNull($voucherAxisRow);
        $this->assertEquals(1, $voucherAxisRow['qty']);
        $this->assertEquals(18000, $voucherAxisRow['total']);

        // Check 6: perdana ALL OPERATOR (receives perdana axis)
        $perdanaAllRow = $pagiVoucherPulsa->firstWhere('name', 'perdana ALL OPERATOR');
        $this->assertNotNull($perdanaAllRow);
        $this->assertEquals(1, $perdanaAllRow['qty']);
        $this->assertEquals(35000, $perdanaAllRow['total']);
    }

    public function test_cashier_can_access_perdana_page(): void
    {
        $this->signIn('cashier');

        $response = $this->get('/kartu-perdana');
        $response->assertOk();
        $response->assertSee('Penjualan Kartu Perdana');
        $response->assertSee('Pilih Operator Cepat');

        // Check POS tab
        $posResponse = $this->get('/pos');
        $posResponse->assertOk();
        $posResponse->assertSee('data-pos-tab="perdana"', false);
        $posResponse->assertSee('Kartu Perdana');
    }

    public function test_manual_perdana_sale_can_be_stored_and_recorded_under_perdana_all_operator_in_report(): void
    {
        $this->signIn('cashier');

        // 1. Store manual Perdana sale with operator name "Telkomsel 14GB"
        $response = $this->postJson('/ajax/perdana/sales', [
            'operator_name' => 'Telkomsel 14GB',
            'price' => 35000,
            'cost_price' => 28000,
            'serial_number' => 'SN-TS-998877',
            'phone_number' => '081234567890',
            'customer_name' => 'Bambang',
            'shift' => 'pagi',
            'payment_method' => 'cash',
            'cash_paid' => 50000,
            'notes' => 'Perdana segel',
        ]);

        $response->assertCreated()
            ->assertJsonPath('sale.manual_product_name', 'Perdana Telkomsel 14GB')
            ->assertJsonPath('sale.status', 'success');

        $this->assertEquals(35000, (float) $response->json('sale.price'));
        $this->assertEquals(28000, (float) $response->json('sale.cost_amount'));
        $this->assertEquals(15000, (float) $response->json('sale.change_amount'));

        $saleId = $response->json('sale.id');
        $refNumber = $response->json('sale.reference_number');
        $this->assertStringStartsWith('PDN-', $refNumber);

        $this->assertDatabaseHas('pulsa_sales', [
            'id' => $saleId,
            'reference_number' => $refNumber,
            'manual_product_name' => 'Perdana Telkomsel 14GB',
            'price' => 35000,
            'cost_amount' => 28000,
            'cash_paid' => 50000,
            'change_amount' => 15000,
            'payment_method' => 'cash',
            'shift' => 'pagi',
        ]);

        // 2. Dashboard perdana endpoint
        $dashResponse = $this->getJson('/ajax/perdana/dashboard');
        $dashResponse->assertOk()
            ->assertJsonPath('summary.today_total', 35000)
            ->assertJsonPath('summary.today_count', 1)
            ->assertJsonPath('summary.today_cost', 28000)
            ->assertJsonPath('summary.today_profit', 7000);

        // 3. Daily Report verification: MUST be in 'perdana ALL OPERATOR' and NOT in 'voucher telkomsel'
        $this->signIn('admin');
        $today = now()->toDateString();
        $reportResponse = $this->getJson("/ajax/report/data?start_date={$today}&end_date={$today}");
        $reportResponse->assertOk();

        $pagiVoucherPulsa = collect($reportResponse->json('shifts.pagi.voucher_pulsa'));

        $perdanaAllRow = $pagiVoucherPulsa->firstWhere('name', 'perdana ALL OPERATOR');
        $voucherTelkomselRow = $pagiVoucherPulsa->firstWhere('name', 'voucher telkomsel');

        $this->assertNotNull($perdanaAllRow);
        $this->assertEquals(1, $perdanaAllRow['qty']);
        $this->assertEquals(35000, $perdanaAllRow['total']);

        // Must NOT leak to voucher telkomsel
        $this->assertEquals(0, $voucherTelkomselRow['qty']);
        $this->assertEquals(0, $voucherTelkomselRow['total']);

        // Check in details.vouchers
        $detailedVouchers = collect($reportResponse->json('details.vouchers'));
        $item = $detailedVouchers->firstWhere('reference_number', $refNumber);
        $this->assertNotNull($item);
        $this->assertEquals('Perdana Telkomsel 14GB', $item['product_name']);
        $this->assertEquals(35000, $item['price']);
        $this->assertEquals(28000, $item['cost_price']);
        $this->assertEquals(7000, $item['profit']);
        $this->assertEquals('Menu Perdana', $item['source']);
    }

    public function test_manual_perdana_sale_on_debt_and_cancellation(): void
    {
        $this->signIn('cashier');

        // 1. Perdana sale on debt requires customer name
        $failResponse = $this->postJson('/ajax/perdana/sales', [
            'operator_name' => 'AXIS Kuota 5GB',
            'price' => 25000,
            'cost_price' => 20000,
            'shift' => 'sore',
            'payment_method' => 'utang',
            'customer_name' => '',
            'cash_paid' => 0,
        ]);
        $failResponse->assertStatus(422)
            ->assertJsonValidationErrors('customer_name');

        // 2. Successful Perdana sale on debt
        $response = $this->postJson('/ajax/perdana/sales', [
            'operator_name' => 'AXIS Kuota 5GB',
            'price' => 25000,
            'cost_price' => 20000,
            'serial_number' => 'SN-AXIS-5GB',
            'phone_number' => '083812345678',
            'customer_name' => 'Mas Doni',
            'shift' => 'sore',
            'payment_method' => 'utang',
            'cash_paid' => 10000,
            'notes' => 'Janji lunas lusa',
        ]);

        $response->assertCreated();
        $saleId = $response->json('sale.id');
        $refNumber = $response->json('sale.reference_number');

        // Verify debt is created
        $this->assertDatabaseHas('debts', [
            'pulsa_sale_id' => $saleId,
            'customer_name' => 'Mas Doni',
            'amount' => 25000,
            'paid_amount' => 10000,
            'status' => 'partial',
            'shift' => 'sore',
        ]);

        // 3. Cancel the perdana sale
        $cancelResponse = $this->postJson("/ajax/pulsa/sales/{$saleId}/cancel", [
            'notes' => 'Pelanggan salah pilih nomor',
        ]);
        $cancelResponse->assertOk();

        $this->assertDatabaseHas('pulsa_sales', [
            'id' => $saleId,
            'status' => 'cancelled',
        ]);

        // Verify linked debt is cancelled
        $this->assertDatabaseHas('debts', [
            'pulsa_sale_id' => $saleId,
            'status' => 'cancelled',
        ]);
    }

    public function test_cashier_and_admin_can_access_debts_page_and_dashboard(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $admin = User::factory()->create(['role' => 'admin']);

        // Cashier accesses debts page
        $this->actingAs($cashier);
        $pageResponse = $this->get('/utang');
        $pageResponse->assertOk()
            ->assertSee('Buku Utang')
            ->assertSee('Tingkat Pengembalian Piutang Toko')
            ->assertSee('Riwayat Pelunasan');

        // Create a debt to verify dashboard calculations
        $debt = Debt::create([
            'reference_number' => 'DEBT-' . now()->format('YmdHis') . '-TEST',
            'debt_date' => now(),
            'customer_name' => 'Pak Joko',
            'customer_phone' => '081299990000',
            'amount' => 200000,
            'paid_amount' => 50000,
            'status' => 'partial',
            'shift' => 'pagi',
            'notes' => 'Beli barang warung',
            'created_by' => $cashier->id,
        ]);

        DebtPayment::create([
            'reference_number' => 'PAY-' . now()->format('YmdHis') . '-TEST',
            'debt_id' => $debt->id,
            'customer_name' => 'Pak Joko',
            'amount' => 50000,
            'payment_method' => 'cash',
            'shift' => 'pagi',
            'created_by' => $cashier->id,
            'payment_date' => now(),
            'notes' => 'Cicilan pertama',
        ]);

        // Cashier queries dashboard
        $dashboardResponse = $this->getJson('/ajax/debts/dashboard');
        $dashboardResponse->assertOk()
            ->assertJsonStructure([
                'summary' => [
                    'total_debt',
                    'total_paid',
                    'total_remaining',
                    'progress_percent',
                    'count_total',
                    'count_paid',
                    'count_partial',
                    'count_unpaid',
                    'today_payments_amount',
                ],
                'debts',
                'payments',
            ]);

        $this->assertEquals(200000, (float) $dashboardResponse->json('summary.total_debt'));
        $this->assertEquals(50000, (float) $dashboardResponse->json('summary.total_paid'));
        $this->assertEquals(150000, (float) $dashboardResponse->json('summary.total_remaining'));
        $this->assertEquals(25.0, (float) $dashboardResponse->json('summary.progress_percent'));
        $this->assertEquals(1, $dashboardResponse->json('summary.count_partial'));
        $this->assertEquals(50000, (float) $dashboardResponse->json('summary.today_payments_amount'));

        $debtItem = collect($dashboardResponse->json('debts'))->firstWhere('id', $debt->id);
        $this->assertNotNull($debtItem);
        $this->assertEquals(25.0, (float) $debtItem['progress_percent']);
        $this->assertEquals(150000, (float) $debtItem['remaining_amount']);

        // Admin also has full access
        $this->actingAs($admin);
        $this->get('/utang')->assertOk();
        $this->getJson('/ajax/debts/dashboard')->assertOk();
    }

    public function test_debtor_search_and_streamlined_quick_payment(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $this->actingAs($cashier);

        // 1. Create debt
        $createRes = $this->postJson('/ajax/debts', [
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081234567890',
            'amount' => 100000,
            'notes' => 'Utang perdana & pulsa',
        ]);
        $createRes->assertCreated();
        $debtId = $createRes->json('debt.id');

        // 2. Verify in unpaid debts (searchable dataset)
        $unpaidRes = $this->getJson('/ajax/debts/unpaid');
        $unpaidRes->assertOk();
        $unpaidList = collect($unpaidRes->json());
        $found = $unpaidList->firstWhere('id', $debtId);
        $this->assertNotNull($found);
        $this->assertEquals('Budi Santoso', $found['customer_name']);
        $this->assertEquals('081234567890', $found['customer_phone']);
        $this->assertEquals(100000, (float) $found['amount']);
        $this->assertEquals(100000, (float) $found['remaining_amount']);
        $this->assertEquals(0, (float) $found['progress_percent']);

        // 3. Partial quick repayment (e.g., 40,000)
        $pay1 = $this->postJson('/ajax/debts/payments', [
            'debt_id' => $debtId,
            'amount' => 40000,
            'payment_method' => 'cash',
            'notes' => 'Cicilan 1',
        ]);
        $pay1->assertCreated();

        $this->assertDatabaseHas('debts', [
            'id' => $debtId,
            'paid_amount' => 40000,
            'status' => 'partial',
        ]);

        // Unpaid list now reflects 40% progress and 60,000 remaining
        $unpaidRes2 = $this->getJson('/ajax/debts/unpaid');
        $found2 = collect($unpaidRes2->json())->firstWhere('id', $debtId);
        $this->assertNotNull($found2);
        $this->assertEquals(60000, (float) $found2['remaining_amount']);
        $this->assertEquals(40.0, (float) $found2['progress_percent']);

        // 4. Quick full repayment of remaining 60,000
        $pay2 = $this->postJson('/ajax/debts/payments', [
            'debt_id' => $debtId,
            'amount' => 60000,
            'payment_method' => 'cash',
            'notes' => 'Pelunasan sisa',
        ]);
        $pay2->assertCreated();

        $this->assertDatabaseHas('debts', [
            'id' => $debtId,
            'paid_amount' => 100000,
            'status' => 'paid',
        ]);

        // Fully paid debt is removed from unpaid debts
        $unpaidRes3 = $this->getJson('/ajax/debts/unpaid');
        $this->assertNull(collect($unpaidRes3->json())->firstWhere('id', $debtId));

        // In dashboard, it appears as 100% paid
        $dashRes = $this->getJson('/ajax/debts/dashboard');
        $debtInDash = collect($dashRes->json('debts'))->firstWhere('id', $debtId);
        $this->assertNotNull($debtInDash);
        $this->assertEquals('paid', $debtInDash['status']);
        $this->assertEquals(100.0, (float) $debtInDash['progress_percent']);
        $this->assertEquals(0, (float) $debtInDash['remaining_amount']);
    }

    public function test_barcode_scanner_modal_elements_and_batch_stock_addition(): void
    {
        $cashier = $this->signIn('cashier');

        // 1. Check stock page has scan modal and buttons
        $stockPage = $this->get('/stock');
        $stockPage->assertOk()
            ->assertSee('Scan Barcode Tambah Stok')
            ->assertSee('scanStockModal')
            ->assertSee('openScanStockModal')
            ->assertSee('btn-open-scan-stock');

        // 2. Create physical product
        $product = Product::create([
            'code' => 'VCR-TSM-10G',
            'name' => 'Voucher Telkomsel 10GB',
            'product_type' => 'voucher',
            'category' => 'voucher',
            'price' => 35000,
            'cost_price' => 28000,
            'stock' => 5,
            'minimum_stock' => 2,
            'unit' => 'pcs',
            'status' => 'active',
        ]);

        // 3. Simulate stock-in payload sent from scanner popup
        $scanResponse = $this->postJson("/ajax/products/{$product->id}/stock", [
            'items' => [
                [
                    'serial_number' => '896200001001',
                    'quantity' => 1,
                    'cost_price' => 29000,
                    'expiry_date' => now()->addMonths(6)->toDateString(),
                ],
                [
                    'serial_number' => '896200001002',
                    'quantity' => 1,
                    'cost_price' => 29000,
                    'expiry_date' => now()->addMonths(6)->toDateString(),
                ],
                [
                    'serial_number' => '896200001003',
                    'quantity' => 1,
                    'cost_price' => 29000,
                    'expiry_date' => now()->addMonths(6)->toDateString(),
                ],
            ],
            'note' => 'Stok masuk via barcode scanner (3 pcs)',
        ]);

        $scanResponse->assertOk()
            ->assertJsonPath('message', 'Stok berhasil ditambahkan.')
            ->assertJsonPath('product.stock', 8);

        // Verify stock movements recorded for each scanned serial
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'serial_number' => '896200001001',
            'cost_price' => 29000,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'serial_number' => '896200001002',
            'cost_price' => 29000,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'serial_number' => '896200001003',
            'cost_price' => 29000,
        ]);

        // Product stock is 5 + 3 = 8
        $this->assertEquals(8, $product->fresh()->stock);
        // Cost price is updated to latest (29000)
        $this->assertEquals(29000, (float) $product->fresh()->cost_price);
    }
}




