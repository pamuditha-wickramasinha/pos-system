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
            'connection_type' => 'required|in:network,windows_local,rawbt',
            'paper_width' => 'required|in:58,80',
            'ip_address' => 'required_if:connection_type,network',
            'port' => 'nullable|integer|min:1|max:65535',
            'windows_printer_name' => 'required_if:connection_type,windows_local',
        ];
    }

    protected function connectionData(Request $request): array
    {
        return [
            'name' => $request->input('name'),
            'connection_type' => $request->input('connection_type'),
            'ip_address' => $request->input('connection_type') === 'network' ? $request->input('ip_address') : null,
            'port' => $request->input('connection_type') === 'network' ? ($request->input('port') ?: 9100) : null,
            'windows_printer_name' => $request->input('connection_type') === 'windows_local' ? $request->input('windows_printer_name') : null,
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

        if (in_array($printer->connection_type, ['network', 'windows_local'], true)) {
            return response()->json($service->printTestFromServer($printer));
        }

        $bytes = $service->buildTestRawBytes($printer);

        return response()->json([
            'status' => 'dispatch',
            'connection_type' => $printer->connection_type,
            'printer_name' => $printer->name,
            'payload' => base64_encode($bytes),
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
     * (network / windows_local) or hands raw ESC/POS bytes back to the browser to
     * dispatch itself (rawbt).
     */
    public function printSale(Sale $sale, Printer $printer, Request $request, ReceiptPrinterService $service): JsonResponse
    {
        if (! $request->user()->can('sales_add') && ! $request->user()->can('sales_edit')) {
            abort(403);
        }

        if (in_array($printer->connection_type, ['network', 'windows_local'], true)) {
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

        // rawbt: the server can't reach this printer, hand the bytes back to the browser.
        $bytes = $service->buildRawBytes($sale, $printer);

        return response()->json([
            'status' => 'dispatch',
            'connection_type' => $printer->connection_type,
            'printer_name' => $printer->name,
            'payload' => base64_encode($bytes),
        ]);
    }

    public function testPrint(Printer $printer, Request $request, ReceiptPrinterService $service): JsonResponse
    {
        $this->authorize('printers_edit');

        if (in_array($printer->connection_type, ['network', 'windows_local'], true)) {
            return response()->json($service->printTestFromServer($printer));
        }

        $bytes = $service->buildTestRawBytes($printer);

        return response()->json([
            'status' => 'dispatch',
            'connection_type' => $printer->connection_type,
            'printer_name' => $printer->name,
            'payload' => base64_encode($bytes),
        ]);
    }

    /**
     * The browser reports back here after it dispatched a job itself (rawbt),
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
