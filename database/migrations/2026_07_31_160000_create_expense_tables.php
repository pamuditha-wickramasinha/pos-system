<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_code')->nullable();
            $table->string('category_name');
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->string('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_code')->nullable();
            $table->foreignId('category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->date('expense_date')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('expense_for')->nullable();
            $table->decimal('expense_amt', 20, 2)->default(0);
            $table->text('note')->nullable();
            $table->string('created_by')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
