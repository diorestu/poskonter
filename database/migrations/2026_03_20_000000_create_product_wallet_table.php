<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_wallet', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->unique(['product_id', 'wallet_id']);
        });

        $pulsaProductIds = DB::table('products')
            ->where('product_type', 'pulsa')
            ->pluck('id');
        $walletIds = DB::table('wallets')->pluck('id');

        if ($pulsaProductIds->isEmpty() || $walletIds->isEmpty()) {
            return;
        }

        $rows = [];

        foreach ($pulsaProductIds as $productId) {
            foreach ($walletIds as $walletId) {
                $rows[] = [
                    'product_id' => $productId,
                    'wallet_id' => $walletId,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('product_wallet')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_wallet');
    }
};
