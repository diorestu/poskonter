<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => 'admin@zeni.local',
        ], [
            'name' => 'Admin Zeni',
            'role' => 'admin',
            'password' => Hash::make('admin12345'),
        ]);

        User::query()->updateOrCreate([
            'email' => 'cashier@zeni.local',
        ], [
            'name' => 'Cashier Zeni',
            'role' => 'cashier',
            'password' => Hash::make('cashier12345'),
        ]);

        Wallet::query()->updateOrCreate([
            'name' => 'Dompet Utama',
        ], [
            'balance' => 0,
            'is_active' => true,
        ]);
    }
}
