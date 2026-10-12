<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table): void {
            $table->id();
            $table->string('reference_number', 40)->unique();
            $table->dateTime('debt_date');
            $table->string('customer_name', 120);
            $table->string('customer_phone', 50)->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->string('status', 20)->default('unpaid');
            $table->string('shift', 10)->default('pagi');
            $table->string('description', 255)->nullable();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->foreignId('pulsa_sale_id')->nullable()->constrained('pulsa_sales')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('debt_payments', function (Blueprint $table): void {
            $table->id();
            $table->string('reference_number', 40)->unique();
            $table->dateTime('payment_date');
            $table->foreignId('debt_id')->nullable()->constrained('debts')->nullOnDelete();
            $table->string('customer_name', 120);
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('payment_method', 30)->default('cash');
            $table->string('shift', 10)->default('pagi');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_payments');
        Schema::dropIfExists('debts');
    }
};
