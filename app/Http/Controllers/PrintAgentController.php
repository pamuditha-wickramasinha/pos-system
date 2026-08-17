<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use App\Models\Printer;
use App\Services\PrintQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints the counter PC's print agent polls. Authenticated by the printer's
 * agent_token rather than a user session, since no human is driving these calls.
 *
 * Deliberately outside the 'auth' middleware group and exempt from CSRF - the agent is
 * a background process on the shop's PC, not a browser, and holds no session or token
 * cookie. The token in the request body is the whole of its authority, so it is
 * compared in constant time and scoped to exactly one printer's queue.
 */
class PrintAgentController extends Controller
{
    /**
     * Agent asks: any receipts for me? Returns the claimed jobs with their payloads.
     */
    public function claim(Request $request, PrintQueue $queue): JsonResponse
    {
        $printer = $this->authenticate($request);

        if (! $printer) {
            return response()->json(['status' => 'failed', 'message' => 'Unknown agent token.'], 401);
        }

        if (! $printer->status) {
            return response()->json(['status' => 'ok', 'printer' => $printer->name, 'jobs' => []]);
        }

        $jobs = $queue->claimFor($printer)->map(fn (PrintJob $job) => [
            'id' => $job->id,
            'printer' => $printer->windows_printer_name,
            'payload' => $job->payload,
            'is_test' => (bool) $job->is_test,
        ]);

        return response()->json([
            'status' => 'ok',
            'printer' => $printer->name,
            'jobs' => $jobs->values(),
        ]);
    }

    /**
     * Agent reports back whether a job actually reached the printer.
     */
    public function result(Request $request, PrintJob $printJob, PrintQueue $queue): JsonResponse
    {
        $printer = $this->authenticate($request);

        // Scoped check: a token may only report on jobs belonging to its own printer.
        if (! $printer || $printJob->printer_id !== $printer->id) {
            return response()->json(['status' => 'failed', 'message' => 'Unknown agent token.'], 401);
        }

        $queue->report(
            $printJob,
            $request->input('status') === 'success',
            $request->input('message'),
        );

        return response()->json(['status' => 'ok']);
    }

    protected function authenticate(Request $request): ?Printer
    {
        $token = (string) ($request->input('token') ?: $request->header('X-Agent-Token'));

        if (strlen($token) < 20) {
            return null;
        }

        // Compare every candidate in constant time so a wrong token cannot be recovered
        // by timing the response.
        foreach (Printer::whereNotNull('agent_token')->get() as $printer) {
            if (hash_equals((string) $printer->agent_token, $token)) {
                return $printer;
            }
        }

        return null;
    }
}
