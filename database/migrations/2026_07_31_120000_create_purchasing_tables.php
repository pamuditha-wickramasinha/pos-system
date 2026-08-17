<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_code')->nullable();
            $table->string('reference_no')->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('purchase_status', 50)->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('other_charges_input', 20, 2)->nullable();
            $table->foreignId('other_charges_tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->decimal('other_charges_amt', 20, 2)->nullable();
            $table->decimal('discount_to_all_input', 20, 2)->nullable();
            $table->string('discount_to_all_type', 50)->nullable();
            $table->decimal('tot_discount_to_all_amt', 20, 2)->nullable();
            $table->decimal('subtotal', 20, 2)->nullable();
            $table->decimal('round_off', 20, 2)->nullable();
            $table->decimal('grand_total', 20, 2)->default(0);
            $table->text('purchase_note')->nullable();
            $table->string('payment_status', 50)->nullable();
            $table->decimal('paid_amount', 20, 2)->default(0);
            $table->string('created_by')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('return_bit')->default(false);
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->string('purchase_status', 50)->nullable();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->decimal('purchase_qty', 20, 2)->default(0);
            $table->decimal('price_per_unit', 20, 2)->default(0);
            $table->foreignId('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->decimal('tax_amt', 20, 2)->nullable();
            $table->string('tax_type', 50)->nullable();
            $table->decimal('unit_discount_per', 20, 2)->nullable();
            $table->decimal('discount_amt', 20, 2)->nullable();
            $table->decimal('unit_total_cost', 20, 2)->default(0);
            $table->decimal('total_cost', 20, 2)->default(0);
            $table->decimal('profit_margin_per', 20, 2)->nullable();
            $table->decimal('unit_sales_price', 20, 2)->nullable();
            $table->boolean('status')->default(true);
            $table->text('description')->nullable();
            $table->string('discount_type', 100)->nullable();
            $table->decimal('discount_input', 20, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('purchase_payments');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
