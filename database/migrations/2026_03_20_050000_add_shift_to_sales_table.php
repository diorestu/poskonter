<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            $table->string('shift', 10)->default('pagi')->after('payment_method');
        });

        Schema::table('pulsa_sales', function (Blueprint $table): void {
            $table->string('shift', 10)->default('pagi')->after('serial_number');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            $table->dropColumn('shift');
        });

        Schema::table('pulsa_sales', function (Blueprint $table): void {
            $table->dropColumn('shift');
        });
    }
};
