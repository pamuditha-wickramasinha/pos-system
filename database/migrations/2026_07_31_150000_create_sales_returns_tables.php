<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->string('return_code')->nullable();
            $table->string('reference_no')->nullable();
            $table->date('return_date')->nullable();
            $table->string('return_status', 50)->nullable();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('other_charges_input', 20, 2)->nullable();
            $table->foreignId('other_charges_tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->decimal('other_charges_amt', 20, 2)->nullable();
            $table->decimal('discount_to_all_input', 20, 2)->nullable();
            $table->string('discount_to_all_type', 50)->nullable();
            $table->decimal('tot_discount_to_all_amt', 20, 2)->nullable();
            $table->decimal('subtotal', 20, 2)->nullable();
            $table->decimal('round_off', 20, 2)->nullable();
            $table->decimal('grand_total', 20, 2)->default(0);
            $table->text('return_note')->nullable();
            $table->string('payment_status', 50)->nullable();
            $table->decimal('paid_amount', 20, 2)->default(0);
            $table->string('created_by')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('sales_items_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->foreignId('return_id')->constrained('sales_returns')->cascadeOnDelete();
            $table->string('return_status', 50)->nullable();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->decimal('return_qty', 20, 2)->default(0);
            $table->decimal('price_per_unit', 20, 2)->default(0);
            $table->foreignId('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->decimal('tax_amt', 20, 2)->nullable();
            $table->string('tax_type', 50)->nullable();
            $table->string('discount_type', 50)->nullable();
            $table->decimal('discount_input', 20, 2)->nullable();
            $table->decimal('discount_amt', 20, 2)->nullable();
            $table->decimal('unit_total_cost', 20, 2)->default(0);
            $table->decimal('total_cost', 20, 2)->default(0);
            $table->decimal('purchase_price', 20, 2)->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('sales_payments_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->foreignId('return_id')->constrained('sales_returns')->cascadeOnDelete();
            $table->date('payment_date')->nullable();
            $table->string('payment_type')->nullable();
            $table->decimal('payment', 20, 2)->default(0);
            $table->text('payment_note')->nullable();
            $table->string('created_by')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_payments_returns');
        Schema::dropIfExists('sales_items_returns');
        Schema::dropIfExists('sales_returns');
    }
};
