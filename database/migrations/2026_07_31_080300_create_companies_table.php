<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_code')->nullable();
            $table->string('company_name')->nullable();
            $table->string('mobile')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('upi_id')->nullable();
            $table->string('upi_code')->nullable();
            $table->string('signature')->nullable();
            $table->boolean('show_signature')->default(false);
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('postcode')->nullable();
            $table->string('gst_no')->nullable();
            $table->string('vat_no')->nullable();
            $table->string('pan_no')->nullable();
            $table->text('bank_details')->nullable();
            $table->string('category_init', 5)->default('CT');
            $table->string('item_init', 5)->default('IT');
            $table->string('supplier_init', 5)->default('SU');
            $table->string('purchase_init', 5)->default('PU');
            $table->string('purchase_return_init', 5)->default('PR');
            $table->string('customer_init', 5)->default('CU');
            $table->string('sales_init', 5)->default('SL');
            $table->string('sales_return_init', 5)->default('SR');
            $table->string('expense_init', 5)->default('EX');
            $table->unsignedTinyInteger('invoice_view')->default(1)->comment('1=Standard,2=Indian GST');
            $table->boolean('status')->default(true);
            $table->boolean('sms_status')->default(false);
            $table->text('sales_terms_and_conditions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
