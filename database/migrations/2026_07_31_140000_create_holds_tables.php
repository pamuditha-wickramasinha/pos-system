<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holds', function (Blueprint $table) {
            $table->id();
            $table->string('reference_id')->nullable();
            $table->string('reference_no')->nullable();
            $table->date('sales_date')->nullable();
            $table->string('sales_status', 50)->nullable();
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
            $table->text('sales_note')->nullable();
            $table->boolean('pos')->default(true);
            $table->string('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('hold_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hold_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->decimal('sales_qty', 20, 2)->default(0);
            $table->decimal('price_per_unit', 20, 2)->default(0);
            $table->string('tax_type', 50)->nullable();
            $table->foreignId('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->decimal('tax_amt', 20, 2)->nullable();
            $table->string('discount_type', 50)->nullable();
            $table->decimal('discount_input', 20, 2)->nullable();
            $table->decimal('discount_amt', 20, 2)->nullable();
            $table->decimal('unit_total_cost', 20, 2)->default(0);
            $table->decimal('total_cost', 20, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hold_items');
        Schema::dropIfExists('holds');
    }
};
