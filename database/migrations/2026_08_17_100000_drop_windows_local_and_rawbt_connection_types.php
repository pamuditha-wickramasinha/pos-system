<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Drops the 'windows_local' and 'rawbt' connection types.
 *
 * Neither survives hosting the app on a remote server: windows_local prints via the
 * *server's* Windows spooler (there isn't one on a Linux VPS, and it could not see a
 * printer in the shop anyway), and rawbt needs an Android device holding the printer.
 * A USB printer on a Windows counter PC is handled by 'local_agent' instead.
 *
 * Existing windows_local rows carry the Windows printer name that local_agent needs,
 * so they convert cleanly. rawbt rows have no such name and are left for the operator
 * to fill in, so they are flagged inactive rather than silently pointed at nothing.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('printers')
            ->where('connection_type', 'windows_local')
            ->update(['connection_type' => 'local_agent']);

        DB::table('printers')
            ->where('connection_type', 'rawbt')
            ->update(['connection_type' => 'local_agent', 'status' => false]);

        DB::statement("ALTER TABLE printers MODIFY COLUMN connection_type ENUM('network', 'local_agent') NOT NULL DEFAULT 'local_agent'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE printers MODIFY COLUMN connection_type ENUM('network', 'windows_local', 'rawbt', 'local_agent') NOT NULL DEFAULT 'network'");
    }
};
