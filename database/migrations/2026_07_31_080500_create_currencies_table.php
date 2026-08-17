<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('currency_name');
            $table->string('currency_code', 20)->nullable();
            $table->string('currency')->nullable();
            $table->string('symbol')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->foreign('currency_id')->references('id')->on('currencies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
        });
        Schema::dropIfExists('currencies');
    }
};
