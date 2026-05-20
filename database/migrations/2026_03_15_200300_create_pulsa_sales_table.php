<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pulsa_sales', function (Blueprint $table): void {
            $table->id();
            $table->string('reference_number', 40)->unique();
            $table->dateTime('sale_date');
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('wallet_id')->constrained()->restrictOnDelete();
            $table->string('customer_name', 120)->nullable();
            $table->string('target_number', 30);
            $table->string('payment_method', 20);
            $table->decimal('price', 14, 2)->default(0);
            $table->decimal('cost_amount', 14, 2)->default(0);
            $table->decimal('cash_paid', 14, 2)->default(0);
            $table->decimal('change_amount', 14, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pulsa_sales');
    }
};
