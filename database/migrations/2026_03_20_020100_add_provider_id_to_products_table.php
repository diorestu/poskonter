<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('provider_id')->nullable()->after('product_type')->constrained()->nullOnDelete();
        });

        $providers = DB::table('providers')->pluck('id', 'name');

        DB::table('products')
            ->where('product_type', 'pulsa')
            ->orderBy('id')
            ->get(['id', 'category'])
            ->each(function (object $product) use ($providers): void {
                $providerId = $providers[trim((string) $product->category)] ?? null;
                if ($providerId) {
                    DB::table('products')
                        ->where('id', $product->id)
                        ->update(['provider_id' => $providerId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('provider_id');
        });
    }
};
