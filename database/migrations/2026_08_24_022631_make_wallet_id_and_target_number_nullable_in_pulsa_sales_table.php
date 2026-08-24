<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pulsa_sales', function (Blueprint $table): void {
            $table->foreignId('wallet_id')->nullable()->change();
            $table->string('target_number', 30)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pulsa_sales', function (Blueprint $table): void {
            $table->foreignId('wallet_id')->nullable(false)->change();
            $table->string('target_number', 30)->nullable(false)->change();
        });
    }
};
