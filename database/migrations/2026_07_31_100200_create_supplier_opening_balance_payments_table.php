<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_opening_balance_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->date('payment_date')->nullable();
            $table->string('payment_type')->nullable();
            $table->decimal('payment', 20, 2)->default(0);
            $table->text('payment_note')->nullable();
            $table->string('created_by')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('customer_opening_balance_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('supplier_opening_balance_payments');
        Schema::dropIfExists('customer_opening_balance_payments');
    }
};
