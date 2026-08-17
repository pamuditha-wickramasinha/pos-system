<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code')->nullable();
            $table->string('supplier_name');
            $table->string('mobile')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('gstin')->nullable();
            $table->string('tax_number')->nullable();
            $table->decimal('opening_balance', 20, 2)->default(0);
            $table->decimal('purchase_due', 20, 2)->default(0);
            $table->decimal('purchase_return_due', 20, 2)->default(0);
            $table->string('country_id')->nullable();
            $table->string('state_id')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->string('address')->nullable();
            $table->string('created_by')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
