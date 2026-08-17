<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Turns print_jobs from an after-the-fact log into a work queue the print agent pulls
 * from, and gives each printer a token the agent authenticates with.
 *
 * The browser used to carry the receipt bytes to the agent on 127.0.0.1, which browsers
 * block from a plain-HTTP public origin (Private Network Access). Reversing the
 * direction - the agent asks the server for work - avoids the browser entirely, so no
 * CORS or private-network rule applies, and a sale rung up on any device can print at
 * the counter.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('printers', function (Blueprint $table) {
            $table->string('agent_token', 64)->nullable()->unique()->after('windows_printer_name');
        });

        // Existing rows need a token too, or their agent could never authenticate.
        foreach (DB::table('printers')->whereNull('agent_token')->pluck('id') as $id) {
            DB::table('printers')->where('id', $id)->update(['agent_token' => Str::random(48)]);
        }

        Schema::table('print_jobs', function (Blueprint $table) {
            // base64 ESC/POS. longText because an 80mm receipt runs to tens of KB and
            // grows with the number of lines; TEXT would silently truncate it.
            $table->longText('payload')->nullable()->after('device_label');
            $table->boolean('is_test')->default(false)->after('payload');
            $table->timestamp('claimed_at')->nullable()->after('is_test');

            $table->index(['printer_id', 'status', 'claimed_at'], 'print_jobs_queue_index');
        });
    }

    public function down(): void
    {
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->dropIndex('print_jobs_queue_index');
            $table->dropColumn(['payload', 'is_test', 'claimed_at']);
        });

        Schema::table('printers', function (Blueprint $table) {
            $table->dropColumn('agent_token');
        });
    }
};
