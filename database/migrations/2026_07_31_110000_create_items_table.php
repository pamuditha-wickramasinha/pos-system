<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->nullable();
            $table->string('custom_barcode')->nullable();
            $table->string('item_sing_name')->nullable();
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->nullable();
            $table->string('hsn')->nullable();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('alert_qty')->default(0);
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('lot_number')->nullable();
            $table->date('expire_date')->nullable();
            $table->decimal('price', 20, 2)->default(0);
            $table->foreignId('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->decimal('purchase_price', 20, 2)->default(0);
            $table->string('tax_type', 50)->default('Exclusive');
            $table->decimal('profit_margin', 20, 2)->nullable();
            $table->decimal('sales_price', 20, 2)->default(0);
            $table->decimal('final_price', 20, 2)->default(0);
            $table->decimal('stock', 20, 2)->default(0);
            $table->string('item_image')->nullable();
            $table->string('discount_type', 100)->nullable();
            $table->decimal('discount', 20, 2)->default(0);
            $table->decimal('wholesale_discount', 20, 2)->default(0);
            $table->string('created_by')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('stock_entries', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date')->nullable();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->decimal('qty', 20, 2)->default(0);
            $table->text('note')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_entries');
        Schema::dropIfExists('items');
    }
};
