<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->string('serial_number', 120)->nullable()->after('expiry_date');
            $table->decimal('cost_price', 14, 2)->default(0)->after('serial_number');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->dropColumn(['serial_number', 'cost_price']);
        });
    }
};
