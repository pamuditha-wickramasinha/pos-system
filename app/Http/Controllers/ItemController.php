<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockEntry;
use App\Models\Tax;
use App\Services\StockService;
use App\Support\DatatableHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ItemController extends Controller
{
    public function __construct(protected StockService $stock) {}

    public function index(): View
    {
        $this->authorize('items_view');

        return view('item.list', ['page_title' => 'Items List']);
    }

    public function add(): View
    {
        $this->authorize('items_add');

        $nextId = (Item::max('id') ?? 0) + 1;
        $itemInit = \App\Models\Company::query()->value('item_init') ?? 'IT';

        return view('item.form', [
            'page_title' => 'Items',
            'item_code' => $itemInit.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
        ]);
    }

    public function edit(Item $item): View
    {
        $this->authorize('items_edit');

        return view('item.form', array_merge(
            ['page_title' => 'Items', 'q_id' => $item->id],
            $item->only([
                'item_code', 'custom_barcode', 'item_sing_name', 'item_name', 'description',
                'brand_id', 'category_id', 'sku', 'hsn', 'unit_id', 'alert_qty', 'lot_number',
                'price', 'tax_id', 'purchase_price', 'tax_type', 'profit_margin', 'sales_price',
                'final_price', 'stock', 'custom_barcode', 'discount', 'wholesale_discount', 'discount_type',
            ]),
            ['expire_date' => $item->expire_date ? show_date($item->expire_date) : '']
        ));
    }

    protected function validationRules(): array
    {
        return [
            'item_name' => 'required',
            'category_id' => 'required',
            'unit_id' => 'required',
            'price' => 'required',
            'tax_id' => 'required',
            'purchase_price' => 'required',
            'sales_price' => 'required',
        ];
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validationRules());

        if ($validator->fails()) {
            return response($validator->errors()->first());
        }

        $item = Item::create([
            'item_code' => $request->input('item_code'),
            'custom_barcode' => $request->input('custom_barcode'),
            'item_sing_name' => $request->input('item_sing_name'),
            'item_name' => $request->input('item_name'),
            'description' => $request->input('description'),
            'brand_id' => $request->input('brand_id') ?: null,
            'category_id' => $request->input('category_id') ?: null,
            'sku' => $request->input('sku'),
            'hsn' => $request->input('hsn'),
            'unit_id' => $request->input('unit_id') ?: null,
            'alert_qty' => $request->input('alert_qty') ?: 0,
            'lot_number' => $request->input('lot_number'),
            'expire_date' => $request->filled('expire_date') ? \Illuminate\Support\Carbon::parse($request->input('expire_date')) : null,
            'price' => $request->input('price'),
            'tax_id' => $request->input('tax_id'),
            'purchase_price' => $request->input('purchase_price'),
            'tax_type' => $request->input('tax_type') ?: 'Exclusive',
            'profit_margin' => $request->input('profit_margin') ?: null,
            'sales_price' => $request->input('sales_price'),
            'final_price' => $request->input('final_price') ?: 0,
            'discount_type' => $request->input('discount_type'),
            'discount' => $request->input('discount') ?: 0,
            'wholesale_discount' => $request->input('wholesale_discount') ?: 0,
            'created_by' => $request->user()->username,
            'status' => true,
        ]);

        if ($request->hasFile('item_image')) {
            $item->update(['item_image' => $this->storeItemImage($request)]);
        }

        $newStock = (float) $request->input('new_opening_stock', 0);
        if ($newStock != 0) {
            StockEntry::create([
                'entry_date' => now(),
                'item_id' => $item->id,
                'qty' => $newStock,
                'note' => $request->input('adjustment_note'),
                'status' => true,
            ]);
        }

        $this->stock->recalculate($item->id);

        if ($request->boolean('quick_add')) {
            app(\App\Services\TelegramNotifier::class)->notifyNewItem($item);
        }

        session()->flash('success', 'Success!! New Item Added Successfully!');

        return response('success');
    }

    protected function storeItemImage(Request $request): string
    {
        $path = $request->file('item_image')->store('items', 'public');

        try {
            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $full = storage_path('app/public/'.$path);
            $thumbPath = str_replace('.', '_thumb.', $full);
            $manager->read($full)->scaleDown(width: 75, height: 50)->save($thumbPath);
        } catch (\Throwable) {
        }

        return $path;
    }

    public function update(Request $request)
    {
        $rules = $this->validationRules();
        $rules['q_id'] = 'required';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response($validator->errors()->first());
        }

        $item = Item::findOrFail($request->input('q_id'));

        $item->update([
            'item_code' => $request->input('item_code'),
            'custom_barcode' => $request->input('custom_barcode'),
            'item_sing_name' => $request->input('item_sing_name'),
            'item_name' => $request->input('item_name'),
            'description' => $request->input('description'),
            'brand_id' => $request->input('brand_id') ?: null,
            'category_id' => $request->input('category_id') ?: null,
            'sku' => $request->input('sku'),
            'hsn' => $request->input('hsn'),
            'unit_id' => $request->input('unit_id') ?: null,
            'alert_qty' => $request->input('alert_qty') ?: 0,
            'lot_number' => $request->input('lot_number'),
            'expire_date' => $request->filled('expire_date') ? \Illuminate\Support\Carbon::parse($request->input('expire_date')) : null,
            'price' => $request->input('price'),
            'tax_id' => $request->input('tax_id'),
            'purchase_price' => $request->input('purchase_price'),
            'tax_type' => $request->input('tax_type') ?: 'Exclusive',
            'profit_margin' => $request->input('profit_margin') ?: null,
            'sales_price' => $request->input('sales_price'),
            'final_price' => $request->input('final_price') ?: 0,
            'discount_type' => $request->input('discount_type'),
            'discount' => $request->input('discount') ?: 0,
            'wholesale_discount' => $request->input('wholesale_discount') ?: 0,
        ]);

        if ($request->hasFile('item_image')) {
            $item->update(['item_image' => $this->storeItemImage($request)]);
        }

        $newStock = (float) $request->input('new_opening_stock', 0);
        if ($newStock != 0) {
            StockEntry::create([
                'entry_date' => now(),
                'item_id' => $item->id,
                'qty' => $newStock,
                'note' => $request->input('adjustment_note'),
                'status' => true,
            ]);
        }

        $this->stock->recalculate($item->id);

        session()->flash('success', 'Success!! Item Updated Successfully!');

        return response('success');
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('items_view');

        $query = Item::query()->with(['brand', 'category', 'unit', 'tax']);

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->input('brand_id'));
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        return DataTables::of($query)
            ->addColumn('checkbox', fn (Item $i) => DatatableHtml::checkbox($i->id))
            ->addColumn('barcode', fn (Item $i) => $i->custom_barcode ?: $i->item_code)
            ->addColumn('brand_category', fn (Item $i) => trim(($i->brand?->brand_name ?? '').' ('.($i->category?->category_name ?? '').')'))
            ->addColumn('unit_name', fn (Item $i) => $i->unit?->unit_name)
            ->addColumn('stock_qty', fn (Item $i) => $i->stock.' ('.$i->alert_qty.')')
            ->editColumn('purchase_price', fn (Item $i) => app_number_format($i->purchase_price))
            ->editColumn('final_price', fn (Item $i) => app_number_format($i->final_price))
            ->filterColumn('barcode', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('custom_barcode', 'like', "%{$keyword}%")
                        ->orWhere('item_code', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('status_badge', fn (Item $i) => DatatableHtml::statusBadge($i->id, $i->status))
            ->addColumn('actions', fn (Item $i) => DatatableHtml::actionMenu([
                ['label' => 'Edit', 'icon' => 'fa-edit text-blue', 'url' => route('items.edit', $i), 'can' => $request->user()->can('items_edit')],
                ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_items({$i->id})", 'can' => $request->user()->can('items_delete')],
            ]))
            ->rawColumns(['checkbox', 'status_badge', 'actions'])
            ->make(true);
    }

    public function updateStatus(Request $request)
    {
        $this->authorize('items_edit');

        Item::whereKey($request->input('id'))->update(['status' => $request->input('status')]);

        return response('success');
    }

    public function destroy(Request $request)
    {
        $this->authorize('items_delete');

        return $this->deleteIds($request->input('q_id'));
    }

    public function multiDestroy(Request $request)
    {
        $this->authorize('items_delete');

        return $this->deleteIds(implode(',', $request->input('checkbox', [])));
    }

    protected function deleteIds(string $ids): \Illuminate\Http\Response
    {
        $idArray = explode(',', $ids);
        Item::whereIn('id', $idArray)->delete();
        StockEntry::whereIn('item_id', $idArray)->delete();

        return response('success');
    }

    public function deleteStockEntry(Request $request)
    {
        $entry = StockEntry::findOrFail($request->input('entry_id'));
        $itemId = $entry->item_id;
        $entry->delete();

        $this->stock->recalculate($itemId);

        return redirect()->back()->with('success', 'Success!! Item Opening Stock Entry Deleted!');
    }

    public function getByBarcode(Request $request)
    {
        $item = Item::where('custom_barcode', $request->input('barcode'))->first();

        return response()->json($item);
    }

    public function search(Request $request)
    {
        $term = strtoupper((string) $request->input('searchTerm'));

        $items = Item::query()
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->input('category_id')))
            ->when($term, fn ($q) => $q->whereRaw('(upper(item_name) like ? or upper(item_code) like ? or upper(custom_barcode) like ?)', ["%{$term}%", "%{$term}%", "%{$term}%"]))
            ->limit(10)
            ->get(['id', 'item_name', 'item_code'])
            ->map(fn ($i) => ['id' => $i->id, 'text' => $i->item_name, 'item_code' => $i->item_code]);

        return response()->json($items);
    }

    public function jsonDetails(Request $request)
    {
        $term = strtoupper(trim((string) ($request->input('name')
            ?? $request->input('term')
            ?? $request->input('q'))));

        $items = Item::query()
            ->when($term !== '', fn ($query) => $query
                ->whereRaw('(upper(item_name) like ? or upper(item_code) like ? or upper(custom_barcode) like ?)', ["%{$term}%", "%{$term}%", "%{$term}%"]))
            ->limit(10)
            ->get()
            ->map(fn (Item $i) => [
                'id' => $i->id,
                'value' => $i->item_name,
                'label' => $i->item_name,
                'item_code' => $i->item_code,
                'stock' => $i->stock,
            ]);

        return response()->json($items);
    }

    public function returnRowWithData(int $rowcount, Item $item)
    {
        return view('item.label-row', ['rowcount' => $rowcount, 'item' => $item]);
    }

    public function showLabels(Request $request, $purchaseId = '')
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('purchase_items') || $purchaseId === '') {
            return response('');
        }

        $rows = DB::table('purchase_items')->where('purchase_id', $purchaseId)->get(['item_id', 'purchase_qty']);
        $html = '';
        $i = 1;
        foreach ($rows as $row) {
            $item = Item::find($row->item_id);
            if ($item) {
                $html .= view('item.label-row', ['rowcount' => $i++, 'item' => $item, 'qty' => $row->purchase_qty])->render();
            }
        }

        return response($html);
    }

    public function labels(): View
    {
        $this->authorize('print_labels');

        return view('item.labels', [
            'page_title' => 'Print Labels',
            'items' => Item::orderBy('item_name')->get(),
        ]);
    }

    public function previewLabels(Request $request): View
    {
        $rowCount = (int) $request->input('hidden_rowcount', 0);
        $rows = [];

        for ($i = 1; $i <= $rowCount; $i++) {
            $itemId = $request->input("tr_item_id_{$i}");
            if (empty($itemId)) {
                continue;
            }
            $count = (int) $request->input("td_data_{$i}_3", 0);
            $item = Item::find($itemId);
            if ($item) {
                $rows[] = ['item' => $item, 'count' => $count];
            }
        }

        return view('item.labels-preview', [
            'rows' => $rows,
            'company_name' => \App\Models\Company::query()->value('company_name'),
            'doubleColumn' => $request->boolean('double_column'),
        ]);
    }
}