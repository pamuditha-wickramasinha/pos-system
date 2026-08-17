<?php

namespace App\Services;

use App\Models\PrintJob;
use App\Models\Printer;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Work queue between the server and the counter PC's print agent.
 *
 * The agent pulls from here rather than the browser pushing to the agent, because a
 * browser on a plain-HTTP public origin is not allowed to open a connection to
 * 127.0.0.1 at all (Private Network Access). Outbound polling has no such restriction,
 * needs no open port on the shop's network, and keeps working over HTTPS.
 */
class PrintQueue
{
    /** A job claimed but not reported on within this many seconds is offered again. */
    protected const STALE_CLAIM_SECONDS = 90;

    /** Cap on how many jobs one poll may take, so a backlog cannot flood a till. */
    protected const CLAIM_LIMIT = 5;

    public function __construct(protected ReceiptPrinterService $printer) {}

    public function queueSale(Sale $sale, Printer $printer, ?int $userId = null, ?string $deviceLabel = null): PrintJob
    {
        return $this->queue(
            $printer,
            $this->printer->buildRawBytes($sale, $printer),
            ['sale_id' => $sale->id, 'created_by' => $userId, 'device_label' => $deviceLabel],
        );
    }

    public function queueTest(Printer $printer, ?int $userId = null, ?string $deviceLabel = null): PrintJob
    {
        return $this->queue(
            $printer,
            $this->printer->buildTestRawBytes($printer),
            ['is_test' => true, 'created_by' => $userId, 'device_label' => $deviceLabel],
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function queue(Printer $printer, string $bytes, array $attributes = []): PrintJob
    {
        return PrintJob::create([
            'printer_id' => $printer->id,
            'payload' => base64_encode($bytes),
            'status' => 'pending',
            ...$attributes,
        ]);
    }

    /**
     * Hand this printer's waiting jobs to the agent that just polled.
     *
     * Claiming happens inside a locked transaction so two agents polling the same
     * printer at the same moment cannot both take the same job and print it twice.
     *
     * @return Collection<int, PrintJob>
     */
    public function claimFor(Printer $printer): Collection
    {
        return DB::transaction(function () use ($printer) {
            $jobs = PrintJob::query()
                ->where('printer_id', $printer->id)
                ->where('status', 'pending')
                ->where(function ($query) {
                    // Unclaimed, or claimed by an agent that then died before reporting.
                    $query->whereNull('claimed_at')
                        ->orWhere('claimed_at', '<', now()->subSeconds(self::STALE_CLAIM_SECONDS));
                })
                ->orderBy('id')
                ->limit(self::CLAIM_LIMIT)
                ->lockForUpdate()
                ->get();

            if ($jobs->isNotEmpty()) {
                PrintJob::whereIn('id', $jobs->pluck('id'))->update(['claimed_at' => now()]);
            }

            return $jobs;
        });
    }

    /**
     * Record what the agent reported. The payload is dropped once a job is done so the
     * table does not grow into a store of every receipt ever printed.
     */
    public function report(PrintJob $job, bool $success, ?string $message = null): void
    {
        $job->update([
            'status' => $success ? 'success' : 'failed',
            'error_message' => $message,
            'payload' => null,
        ]);
    }
}
