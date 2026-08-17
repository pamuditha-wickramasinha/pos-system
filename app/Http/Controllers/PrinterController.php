<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use App\Models\Printer;
use App\Models\Sale;
use App\Services\PrintQueue;
use App\Services\ReceiptPrinterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PrinterController extends Controller
{
    public function index(): View
    {
        $this->authorize('printers_view');

        return view('printer.list', [
            'page_title' => 'Printers',
            'printers' => Printer::orderBy('id')->get(),
        ]);
    }

    public function add(): View
    {
        $this->authorize('printers_add');

        return view('printer.form', ['page_title' => 'New Printer']);
    }

    public function edit(Printer $printer): View
    {
        $this->authorize('printers_edit');

        return view('printer.form', [
            'page_title' => 'Edit Printer',
            'q_id' => $printer->id,
            'printer' => $printer,
        ]);
    }

    public function saveOrUpdate(Request $request): Response
    {
        $validator = Validator::make($request->all(), $this->connectionRules());

        if ($validator->fails()) {
            return response($validator->errors()->first());
        }

        $id = $request->input('q_id');
        $isUpdate = $request->input('command') === 'update';
        $data = $this->connectionData($request);

        if ($data['is_default']) {
            Printer::query()->update(['is_default' => false]);
        }

        if ($isUpdate) {
            $this->authorize('printers_edit');

            Printer::whereKey($id)->update($data);

            session()->flash('success', 'Success!! Printer Updated Successfully!');

            return response('success');
        }

        $this->authorize('printers_add');

        $data['status'] = true;
        // The agent authenticates with this and nothing else, so it is generated here
        // rather than being anything a user picks, and never regenerated on update.
        $data['agent_token'] = Str::random(48);
        Printer::create($data);

        session()->flash('success', 'Success!! New Printer Added Successfully!');

        return response('success');
    }

    /**
     * Shows the agent-config.json the counter PC needs, including the printer's token.
     */
    public function agentSetup(Printer $printer): View
    {
        $this->authorize('printers_edit');

        return view('printer.agent-setup', [
            'page_title' => 'Agent Setup',
            'printer' => $printer,
            'config' => json_encode([
                'server_url' => rtrim(url('/'), '/'),
                'token' => $printer->agent_token,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        ]);
    }

    protected function connectionRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'connection_type' => 'required|in:network,local_agent',
            'paper_width' => 'required|in:58,80',
            'ip_address' => 'required_if:connection_type,network',
            'port' => 'nullable|integer|min:1|max:65535',
            // Resolved on the counter PC by the print agent, not on this server.
            'windows_printer_name' => 'required_if:connection_type,local_agent',
        ];
    }

    protected function connectionData(Request $request): array
    {
        return [
            'name' => $request->input('name'),
            'connection_type' => $request->input('connection_type'),
            'ip_address' => $request->input('connection_type') === 'network' ? $request->input('ip_address') : null,
            'port' => $request->input('connection_type') === 'network' ? ($request->input('port') ?: 9100) : null,
            'windows_printer_name' => $request->input('connection_type') === 'local_agent' ? $request->input('windows_printer_name') : null,
            'paper_width' => (int) $request->input('paper_width'),
            'cut_paper' => $request->boolean('cut_paper'),
            'open_cash_drawer' => $request->boolean('open_cash_drawer'),
            'is_default' => $request->boolean('is_default'),
        ];
    }

    /**
     * Test the connection details currently typed into the add/edit form, before the
     * printer is even saved.
     */
    public function testConnection(Request $request, ReceiptPrinterService $service): JsonResponse
    {
        if (! $request->user()->can('printers_add') && ! $request->user()->can('printers_edit')) {
            abort(403);
        }

        $validator = Validator::make($request->all(), $this->connectionRules());

        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $printer = new Printer($this->connectionData($request));

        if ($printer->connection_type === 'network') {
            return response()->json($service->printTestFromServer($printer));
        }

        // A local_agent job has to be queued against a saved printer, because the agent
        // finds its work by the printer's token - which does not exist until it is saved.
        return response()->json([
            'status' => 'failed',
            'message' => 'Save the printer first, then use Test Print from the printers list.',
        ]);
    }

    public function updateStatus(Request $request): Response
    {
        $this->authorize('printers_edit');

        Printer::whereKey($request->input('id'))->update(['status' => $request->input('status')]);

        return response('success');
    }

    public function destroy(Request $request): Response
    {
        $this->authorize('printers_delete');

        Printer::whereKey($request->input('id'))->delete();

        return response('success');
    }

    /**
     * Called right after a POS sale is saved. Either prints straight from the server
     * (network) or queues the job for the counter PC's agent to collect (local_agent).
     */
    public function printSale(Sale $sale, Printer $printer, Request $request, PrintQueue $queue, ReceiptPrinterService $service): JsonResponse
    {
        if (! $request->user()->can('sales_add') && ! $request->user()->can('sales_edit')) {
            abort(403);
        }

        if ($printer->connection_type === 'network') {
            $result = $service->printFromServer($sale, $printer);

            PrintJob::create([
                'sale_id' => $sale->id,
                'printer_id' => $printer->id,
                'device_label' => $request->input('device_label'),
                'status' => $result['status'],
                'error_message' => $result['message'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            return response()->json($result);
        }

        $job = $queue->queueSale($sale, $printer, $request->user()->id, $request->input('device_label'));

        return $this->queuedResponse($job);
    }

    public function testPrint(Printer $printer, Request $request, PrintQueue $queue, ReceiptPrinterService $service): JsonResponse
    {
        $this->authorize('printers_edit');

        if ($printer->connection_type === 'network') {
            return response()->json($service->printTestFromServer($printer));
        }

        $job = $queue->queueTest($printer, $request->user()->id, $request->input('device_label'));

        return $this->queuedResponse($job);
    }

    /**
     * Whether a queued job actually printed is only known once the agent reports back,
     * so the browser polls this until the job leaves 'pending'.
     */
    public function jobStatus(PrintJob $printJob, Request $request): JsonResponse
    {
        if (! $request->user()->can('sales_add') && ! $request->user()->can('printers_edit')) {
            abort(403);
        }

        return response()->json([
            'status' => $printJob->status,
            'message' => $printJob->error_message,
        ]);
    }

    protected function queuedResponse(PrintJob $job): JsonResponse
    {
        return response()->json([
            'status' => 'queued',
            'job_id' => $job->id,
        ]);
    }

}
