<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Item;
use App\Models\State;
use App\Models\StockEntry;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\Unit;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function __construct(protected StockService $stock) {}

    public function customers(): View
    {
        $this->authorize('import_customers');

        return view('import.customers', ['page_title' => 'Import Customers']);
    }

    public function importCustomersCsv(Request $request)
    {
        $this->authorize('import_customers');

        return $this->importPeopleCsv($request, Customer::class, 'customer', Company::query()->value('customer_init') ?? 'CU');
    }

    public function suppliers(): View
    {
        $this->authorize('import_suppliers');

        return view('import.suppliers', ['page_title' => 'Import Suppliers']);
    }

    public function importSuppliersCsv(Request $request)
    {
        $this->authorize('import_suppliers');

        return $this->importPeopleCsv($request, Supplier::class, 'supplier', Company::query()->value('supplier_init') ?? 'SU');
    }

    protected function importPeopleCsv(Request $request, string $model, string $prefix, string $codeInit)
    {
        if (! $request->hasFile('import_file')) {
            return response('Please select a CSV file to upload.');
        }

        $rows = array_map('str_getcsv', file($request->file('import_file')->getRealPath()));
        array_shift($rows);

        DB::beginTransaction();
        try {
            foreach ($rows as $data) {
                $name = trim((string) ($data[0] ?? ''));
                if ($name === '') {
                    continue;
                }

                $mobile = trim((string) ($data[1] ?? ''));
                if ($mobile !== '' && $model::where('mobile', $mobile)->exists()) {
                    DB::rollBack();

                    return response("Import Failed!<br>'{$mobile}' Mobile Number already Exist.");
                }

                $countryName = trim((string) ($data[6] ?? ''));
                $stateName = trim((string) ($data[7] ?? ''));
                $countryId = $countryName !== '' ? $this->getCountryId($countryName) : null;
                $stateId = $stateName !== '' ? $this->getStateId($stateName, $countryId) : null;

                $nextId = ($model::max('id') ?? 0) + 1;

                $model::create([
                    "{$prefix}_code" => $codeInit.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
                    "{$prefix}_name" => $name,
                    'mobile' => $mobile,
                    'email' => trim((string) ($data[2] ?? '')),
                    'phone' => trim((string) ($data[3] ?? '')),
                    'gstin' => trim((string) ($data[4] ?? '')),
                    'tax_number' => trim((string) ($data[5] ?? '')),
                    'country_id' => $countryId,
                    'state_id' => $stateId,
                    'city' => trim((string) ($data[8] ?? '')),
                    'postcode' => trim((string) ($data[9] ?? '')),
                    'address' => trim((string) ($data[10] ?? '')),
                    'opening_balance' => is_numeric($data[11] ?? null) ? $data[11] : 0,
                    'created_by' => $request->user()->username,
                    'status' => true,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response('failed');
        }

        session()->flash('success', 'Success!! Data Imported Successfully!');

        return response('success');
    }

    protected function getCountryId(string $name): int
    {
        return Country::firstOrCreate(
            ['country' => $name],
            ['status' => true]
        )->id;
    }

    protected function getStateId(string $name, ?int $countryId): int
    {
        return State::firstOrCreate(
            ['state' => $name],
            ['country_id' => $countryId, 'status' => true]
        )->id;
    }

    public function items(): View
    {
        $this->authorize('import_items');

        return view('import.items', ['page_title' => 'Import Items']);
    }

    public function importItemsCsv(Request $request)
    {
        $this->authorize('import_items');

        if (! $request->hasFile('import_file')) {
            return response('Please select a CSV file to upload.');
        }

        $rows = array_map('str_getcsv', file($request->file('import_file')->getRealPath()));
        array_shift($rows);

        $itemInit = Company::query()->value('item_init') ?? 'IT';
        $touchedItemIds = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $data) {
                $itemName = trim((string) ($data[0] ?? ''));
                if ($itemName === '') {
                    continue;
                }

                $categoryName = trim((string) ($data[1] ?? ''));
                $unitName = trim((string) ($data[4] ?? ''));
                $brandName = trim((string) ($data[6] ?? ''));
                $taxName = trim((string) ($data[9] ?? ''));
                $taxPer = (float) ($data[10] ?? 0);

                $categoryId = $categoryName !== '' ? $this->getCategoryId($categoryName) : null;
                $unitId = $unitName !== '' ? $this->getUnitId($unitName) : null;
                $brandId = $brandName !== '' ? $this->getBrandId($brandName) : null;
                $taxId = $taxName !== '' ? $this->getTaxId($taxName, $taxPer) : null;

                $taxType = trim((string) ($data[11] ?? '')) ?: 'Exclusive';
                $price = (float) ($data[8] ?? 0);
                $salesPrice = (float) ($data[12] ?? 0);
                $expireDate = trim((string) ($data[7] ?? ''));
                $stockQty = (float) ($data[13] ?? 0);

                $purchasePrice = $price;
                if ($taxType === 'Exclusive') {
                    $purchasePrice += ($purchasePrice * $taxPer) / 100;
                }

                $finalPrice = strtoupper($taxType) === 'INCLUSIVE' ? 0 : (float) calculate_exclusive($salesPrice, $taxPer);
                $finalPrice += $salesPrice;

                $profitMargin = $price > 0 ? (($salesPrice - $price) / $price) * 100 : ($salesPrice - $price);

                $nextId = (Item::max('id') ?? 0) + 1;

                $item = Item::create([
                    'item_code' => $itemInit.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
                    'item_name' => $itemName,
                    'category_id' => $categoryId,
                    'sku' => trim((string) ($data[2] ?? '')),
                    'hsn' => trim((string) ($data[3] ?? '')),
                    'unit_id' => $unitId,
                    'alert_qty' => (int) ($data[5] ?? 0),
                    'brand_id' => $brandId,
                    'expire_date' => $expireDate !== '' ? date('Y-m-d', strtotime($expireDate)) : null,
                    'price' => $price,
                    'tax_id' => $taxId,
                    'purchase_price' => $purchasePrice,
                    'tax_type' => $taxType,
                    'sales_price' => $salesPrice,
                    'profit_margin' => $profitMargin,
                    'stock' => 0,
                    'custom_barcode' => trim((string) ($data[14] ?? '')) ?: null,
                    'discount_type' => trim((string) ($data[15] ?? '')) ?: null,
                    'discount' => (float) ($data[16] ?? 0),
                    'final_price' => $finalPrice,
                    'created_by' => $request->user()->username,
                    'status' => true,
                ]);

                if ($stockQty > 0) {
                    StockEntry::create([
                        'entry_date' => now()->toDateString(),
                        'item_id' => $item->id,
                        'qty' => $stockQty,
                        'status' => true,
                    ]);
                }

                $touchedItemIds[] = $item->id;
            }

            foreach ($touchedItemIds as $itemId) {
                $this->stock->recalculate($itemId);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response('failed');
        }

        session()->flash('success', 'Success!! Items Data Imported Successfully!');

        return response('success');
    }

    protected function getCategoryId(string $name): int
    {
        $category = Category::whereRaw('upper(category_name) = upper(?)', [$name])->first();
        if ($category) {
            return $category->id;
        }

        $nextId = (Category::max('id') ?? 0) + 1;

        return Category::create([
            'category_code' => 'CT'.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
            'category_name' => $name,
            'status' => true,
        ])->id;
    }

    protected function getUnitId(string $name): int
    {
        $unit = Unit::whereRaw('upper(unit_name) = upper(?)', [$name])->first();

        return $unit?->id ?? Unit::create(['unit_name' => $name, 'status' => true])->id;
    }

    protected function getBrandId(string $name): int
    {
        $brand = Brand::whereRaw('upper(brand_name) = upper(?)', [$name])->first();

        return $brand?->id ?? Brand::create(['brand_name' => $name, 'status' => true])->id;
    }

    protected function getTaxId(string $name, float $taxPer): int
    {
        $tax = Tax::whereRaw('upper(tax_name) = upper(?)', [$name])->first();

        return $tax?->id ?? Tax::create(['tax_name' => $name, 'tax' => $taxPer, 'status' => true])->id;
    }
}
