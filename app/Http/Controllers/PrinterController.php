<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use App\Models\Printer;
use App\Models\Sale;
use App\Services\ReceiptPrinterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
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
        Printer::create($data);

        session()->flash('success', 'Success!! New Printer Added Successfully!');

        return response('success');
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
     * printer is even saved. Same dispatch shape as testPrint() below, just against an
     * unpersisted Printer instance built from the posted fields.
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

        return response()->json($this->dispatchToBrowser($printer, $service->buildTestRawBytes($printer)));
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
     * (network) or hands the raw ESC/POS bytes back to the browser to dispatch
     * itself (local_agent).
     */
    public function printSale(Sale $sale, Printer $printer, Request $request, ReceiptPrinterService $service): JsonResponse
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

        // local_agent: the server can't reach this printer, hand the bytes back
        // to the browser on the device that can.
        return response()->json($this->dispatchToBrowser($printer, $service->buildRawBytes($sale, $printer)));
    }

    public function testPrint(Printer $printer, Request $request, ReceiptPrinterService $service): JsonResponse
    {
        $this->authorize('printers_edit');

        if ($printer->connection_type === 'network') {
            return response()->json($service->printTestFromServer($printer));
        }

        return response()->json($this->dispatchToBrowser($printer, $service->buildTestRawBytes($printer)));
    }

    /**
     * Response telling the browser to deliver these bytes itself, because the server
     * has no path to the printer. 'printer_target' is only meaningful for local_agent,
     * where the agent on the counter PC needs to know which Windows printer to spool to.
     *
     * @return array<string, mixed>
     */
    protected function dispatchToBrowser(Printer $printer, string $bytes): array
    {
        return [
            'status' => 'dispatch',
            'connection_type' => $printer->connection_type,
            'printer_name' => $printer->name,
            'printer_target' => $printer->windows_printer_name,
            'agent_port' => (int) config('printing.agent_port', 9110),
            'payload' => base64_encode($bytes),
        ];
    }

    /**
     * The browser reports back here after it dispatched a job itself (local_agent),
     * since the server has no way to know whether it actually printed.
     */
    public function logResult(Request $request): Response
    {
        if (! $request->user()->can('sales_add') && ! $request->user()->can('sales_edit')) {
            abort(403);
        }

        PrintJob::create([
            'sale_id' => $request->input('sale_id'),
            'printer_id' => $request->input('printer_id'),
            'device_label' => $request->input('device_label'),
            'status' => $request->input('status') === 'success' ? 'success' : 'failed',
            'error_message' => $request->input('message'),
            'created_by' => $request->user()->id,
        ]);

        return response('success');
    }
}
