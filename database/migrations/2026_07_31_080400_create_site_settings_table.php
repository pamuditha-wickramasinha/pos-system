<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->nullable();
            $table->string('logo')->nullable();
            $table->string('language', 20)->default('English');
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('currency_placement', 20)->default('Left');
            $table->string('timezone')->default('UTC');
            $table->string('date_format', 30)->default('dd-mm-yyyy');
            $table->unsignedTinyInteger('time_format')->default(12);
            $table->string('site_title')->nullable();
            $table->boolean('change_return')->default(true)->comment('show change-return in pos');
            $table->unsignedTinyInteger('sales_invoice_format_id')->default(1);
            $table->text('sales_invoice_footer_text')->nullable();
            $table->boolean('round_off')->default(false);
            $table->boolean('show_upi_code')->default(false);
            $table->boolean('disable_tax')->default(false);
            $table->string('number_to_words', 100)->default('Default');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
