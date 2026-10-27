<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pulsa_sales', function (Blueprint $table): void {
            $table->foreignId('product_id')->nullable()->change();
            $table->string('manual_product_name', 120)->nullable()->after('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('pulsa_sales', function (Blueprint $table): void {
            $table->dropColumn('manual_product_name');
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
