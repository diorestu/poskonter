<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Provider;
use App\Models\Wallet;
use App\Models\User;
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
        $this->get('/master-data/dompet-saldo')->assertOk()->assertSee('Dompet Saldo');
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
}
