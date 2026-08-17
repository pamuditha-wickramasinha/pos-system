<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the 'local_agent' connection type.
 *
 * Needed when the app is hosted on a remote server but the printer hangs off the
 * USB port of the counter PC: the server cannot reach that printer at all (no
 * route to it, no spooler), so the browser on that PC relays the ESC/POS bytes
 * to a small agent listening on its own loopback. See agent/README.md.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE printers MODIFY COLUMN connection_type ENUM('network', 'windows_local', 'rawbt', 'local_agent') NOT NULL DEFAULT 'network'");
    }

    public function down(): void
    {
        DB::table('printers')->where('connection_type', 'local_agent')->update(['connection_type' => 'windows_local']);

        DB::statement("ALTER TABLE printers MODIFY COLUMN connection_type ENUM('network', 'windows_local', 'rawbt') NOT NULL DEFAULT 'network'");
    }
};
