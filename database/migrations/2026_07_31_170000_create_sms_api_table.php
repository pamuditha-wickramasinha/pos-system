<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_apis', function (Blueprint $table) {
            $table->id();
            $table->string('info', 150)->nullable();
            $table->string('key', 600)->nullable();
            $table->string('key_value', 600)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_apis');
    }
};
