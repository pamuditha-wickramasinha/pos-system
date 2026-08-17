<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('tax_name');
            $table->decimal('tax', 20, 2)->default(0);
            $table->boolean('group_bit')->default(false);
            $table->string('subtax_ids', 50)->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('undelete_bit')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
