<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // network       = printer has its own IP, server sends ESC/POS bytes over TCP (works from any device)
            // windows_local = USB printer shared on the Windows PC that runs this server; server prints
            //                 directly via the Windows print spooler (works from any device, since the
            //                 server does the printing, not the browser)
            // rawbt         = USB-OTG / Bluetooth / WiFi printer attached to a mobile device itself; the
            //                 browser on that device hands the raw bytes to the RawBT Android app
            $table->enum('connection_type', ['network', 'windows_local', 'rawbt'])->default('network');
            $table->string('ip_address')->nullable();
            $table->unsignedInteger('port')->nullable();
            $table->string('windows_printer_name')->nullable();
            $table->unsignedTinyInteger('paper_width')->default(80); // mm, 58 or 80
            $table->boolean('cut_paper')->default(true);
            $table->boolean('open_cash_drawer')->default(false);
            $table->boolean('is_default')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('printers');
    }
};
