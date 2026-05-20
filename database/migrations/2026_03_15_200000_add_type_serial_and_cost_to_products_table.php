<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('product_type', 20)->default('regular')->after('name');
            $table->string('serial_number', 120)->nullable()->after('variant');
            $table->decimal('cost_price', 14, 2)->default(0)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['product_type', 'serial_number', 'cost_price']);
        });
    }
};
