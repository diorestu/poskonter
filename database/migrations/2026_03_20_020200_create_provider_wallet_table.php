<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_wallet', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->unique(['provider_id', 'wallet_id']);
        });

        if (! Schema::hasTable('product_wallet')) {
            return;
        }

        $rows = DB::table('product_wallet')
            ->join('products', 'products.id', '=', 'product_wallet.product_id')
            ->whereNotNull('products.provider_id')
            ->select('products.provider_id', 'product_wallet.wallet_id')
            ->distinct()
            ->get()
            ->map(fn (object $row) => [
                'provider_id' => $row->provider_id,
                'wallet_id' => $row->wallet_id,
            ])
            ->all();

        if ($rows !== []) {
            DB::table('provider_wallet')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_wallet');
    }
};
