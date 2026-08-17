<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Services\StockService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class LegacyImportCommand extends Command
{
    protected $signature = 'legacy:import {--force : Skip the confirmation prompt}';

    protected $description = 'Import data from the legacy CodeIgniter "pos" database into the new schema';

    protected array $roleNameByLegacyId = [];

    public function handle(StockService $stockService): int
    {
        if (! $this->option('force') && ! $this->confirm('This will TRUNCATE all data in the current database and re-import it from the legacy database. Continue?')) {
            $this->info('Aborted.');

            return self::SUCCESS;
        }

        $legacyTables = DB::connection('legacy')->select('SHOW TABLES');
        if (empty($legacyTables)) {
            $this->error('Legacy database is empty or unreachable.');

            return self::FAILURE;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $this->truncateAll();

        $this->call(PermissionSeeder::class);

        $this->importRolesAndPermissions();
        $this->importUsers();
        $this->importLookups();
        $this->importCompanyAndSettings();
        $this->importSuppliers();
        $this->importCustomers();
        $this->importItems();
        $this->importPurchases();
        $this->importSales();
        $this->importPurchaseReturns();
        $this->importSalesReturns();
        $this->importExpenses();
        $this->importHolds();
        $this->importSms();

        $this->info('Recalculating stock for all items...');
        $itemIds = DB::table('items')->pluck('id');
        foreach ($itemIds as $itemId) {
            $stockService->recalculate($itemId);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('Legacy import complete.');

        return self::SUCCESS;
    }

    protected function truncateAll(): void
    {
        $tables = [
            'model_has_roles', 'model_has_permissions', 'role_has_permissions', 'roles', 'permissions',
            'users', 'countries', 'states', 'currencies', 'taxes', 'units', 'categories', 'brands',
            'payment_types', 'warehouses', 'companies', 'site_settings',
            'suppliers', 'supplier_opening_balance_payments', 'customers', 'customer_opening_balance_payments',
            'items', 'stock_entries',
            'purchases', 'purchase_items', 'purchase_payments',
            'sales', 'sales_items', 'sales_payments',
            'purchase_returns', 'purchase_items_returns', 'purchase_payments_returns',
            'sales_returns', 'sales_items_returns', 'sales_payments_returns',
            'expense_categories', 'expenses',
            'holds', 'hold_items',
            'sms_apis', 'sms_templates',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
    }

    protected function legacy(string $table)
    {
        return DB::connection('legacy')->table($table);
    }

    protected function nz(mixed $value): ?int
    {
        return empty($value) ? null : (int) $value;
    }

    protected function nd(mixed $value): float
    {
        return $value ?? 0;
    }

    protected function combineDateTime(?string $date, ?string $time): Carbon
    {
        if (empty($date)) {
            return now();
        }

        try {
            return Carbon::parse($date.' '.($time ?: '00:00:00'));
        } catch (\Throwable) {
            return now();
        }
    }

    protected function importRolesAndPermissions(): void
    {
        $this->info('Importing roles and permissions...');

        $validPermissions = Permission::pluck('name')->all();

        foreach ($this->legacy('db_roles')->get() as $legacyRole) {
            $role = Role::firstOrCreate(
                ['name' => $legacyRole->role_name, 'guard_name' => 'web'],
                ['description' => $legacyRole->description, 'status' => (bool) $legacyRole->status]
            );

            $this->roleNameByLegacyId[$legacyRole->id] = $role->name;

            $permissionNames = $this->legacy('db_permissions')
                ->where('role_id', $legacyRole->id)
                ->pluck('permissions')
                ->intersect($validPermissions)
                ->all();

            if (empty($permissionNames) && strcasecmp($legacyRole->role_name, 'Admin') === 0) {
                $permissionNames = $validPermissions;
            }

            $role->syncPermissions($permissionNames);
        }
    }

    protected function importUsers(): void
    {
        $this->info('Importing users...');

        foreach ($this->legacy('db_users')->orderBy('id')->get() as $u) {
            DB::table('users')->insert([
                'id' => $u->id,
                'username' => $u->username,
                'password' => Hash::make(bin2hex(random_bytes(16))),
                'legacy_password' => $u->password,
                'firstname' => $u->firstname,
                'lastname' => $u->lastname,
                'mobile' => $u->mobile,
                'email' => $u->email,
                'gender' => $u->gender,
                'dob' => $u->dob,
                'country' => $u->country,
                'state' => $u->state,
                'city' => $u->city,
                'address' => $u->address,
                'postcode' => $u->postcode,
                'profile_picture' => $u->profile_picture,
                'company_id' => $this->nz($u->company_id),
                'status' => (bool) $u->status,
                'created_by' => $u->created_by,
                'system_ip' => $u->system_ip,
                'system_name' => $u->system_name,
                'created_at' => $this->combineDateTime($u->created_date, $u->created_time),
                'updated_at' => $this->combineDateTime($u->created_date, $u->created_time),
            ]);

            $roleName = $this->roleNameByLegacyId[$u->role_id] ?? null;
            $role = $roleName ? Role::where('name', $roleName)->where('guard_name', 'web')->first() : null;

            if ($role) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $role->id,
                    'model_type' => \App\Models\User::class,
                    'model_id' => $u->id,
                ]);
            }
        }

        DB::statement('ALTER TABLE users AUTO_INCREMENT = '.((DB::table('users')->max('id') ?? 0) + 1));
    }

    protected function importLookups(): void
    {
        $this->info('Importing lookup data...');

        foreach ($this->legacy('db_country')->get() as $r) {
            DB::table('countries')->insert([
                'id' => $r->id, 'country_code' => $r->country_code, 'country' => $r->country,
                'added_on' => $r->added_on, 'status' => (bool) $r->status,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        foreach ($this->legacy('db_states')->get() as $r) {
            $countryId = $this->nz($r->country_id);
            if (! $countryId && ! empty($r->country)) {
                $countryId = DB::table('countries')->whereRaw('upper(country) = upper(?)', [$r->country])->value('id');
            }

            DB::table('states')->insert([
                'id' => $r->id, 'state_code' => $r->state_code, 'state' => $r->state,
                'country_id' => $countryId, 'added_on' => $r->added_on, 'status' => (bool) $r->status,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        foreach ($this->legacy('db_currency')->get() as $r) {
            DB::table('currencies')->insert([
                'id' => $r->id, 'currency_name' => $r->currency_name, 'currency_code' => $r->currency_code,
                'currency' => $r->currency, 'symbol' => $r->symbol, 'status' => (bool) $r->status,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        foreach ($this->legacy('db_tax')->get() as $r) {
            DB::table('taxes')->insert([
                'id' => $r->id, 'tax_name' => $r->tax_name, 'tax' => $this->nd($r->tax),
                'group_bit' => (bool) $r->group_bit, 'subtax_ids' => $r->subtax_ids,
                'status' => (bool) $r->status, 'undelete_bit' => (bool) $r->undelete_bit,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        foreach ($this->legacy('db_units')->get() as $r) {
            DB::table('units')->insert([
                'id' => $r->id, 'unit_name' => $r->unit_name, 'description' => $r->description,
                'status' => (bool) $r->status, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        foreach ($this->legacy('db_category')->get() as $r) {
            DB::table('categories')->insert([
                'id' => $r->id, 'category_code' => $r->category_code, 'category_name' => $r->category_name,
                'description' => $r->description, 'status' => (bool) $r->status,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        foreach ($this->legacy('db_brands')->get() as $r) {
            DB::table('brands')->insert([
                'id' => $r->id, 'brand_code' => $r->brand_code, 'brand_name' => $r->brand_name,
                'description' => $r->description, 'status' => (bool) $r->status,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        foreach ($this->legacy('db_paymenttypes')->get() as $r) {
            DB::table('payment_types')->insert([
                'id' => $r->id, 'payment_type' => $r->payment_type, 'status' => (bool) $r->status,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        foreach ($this->legacy('db_warehouse')->get() as $r) {
            DB::table('warehouses')->insert([
                'id' => $r->id, 'warehouse_name' => $r->warehouse_name, 'mobile' => $r->mobile,
                'email' => $r->email, 'status' => (bool) $r->status,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        foreach (['countries', 'states', 'currencies', 'taxes', 'units', 'categories', 'brands', 'payment_types', 'warehouses'] as $table) {
            $max = DB::table($table)->max('id');
            if ($max) {
                DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = ".($max + 1));
            }
        }
    }

    protected function importCompanyAndSettings(): void
    {
        $this->info('Importing company and site settings...');

        $c = $this->legacy('db_company')->first();
        if ($c) {
            DB::table('companies')->insert([
                'id' => $c->id, 'company_code' => $c->company_code, 'company_name' => $c->company_name,
                'mobile' => $c->mobile, 'phone' => $c->phone, 'email' => $c->email, 'website' => $c->website,
                'logo' => $c->logo ?: $c->company_logo, 'upi_id' => $c->upi_id, 'upi_code' => $c->upi_code,
                'signature' => $c->signature, 'show_signature' => (bool) $c->show_signature,
                'country' => $c->country, 'state' => $c->state, 'city' => $c->city, 'address' => $c->address,
                'postcode' => $c->postcode, 'gst_no' => $c->gst_no, 'vat_no' => $c->vat_no, 'pan_no' => $c->pan_no,
                'bank_details' => $c->bank_details, 'category_init' => $c->category_init, 'item_init' => $c->item_init,
                'supplier_init' => $c->supplier_init, 'purchase_init' => $c->purchase_init,
                'purchase_return_init' => $c->purchase_return_init, 'customer_init' => $c->customer_init,
                'sales_init' => $c->sales_init, 'sales_return_init' => $c->sales_return_init,
                'expense_init' => $c->expense_init, 'invoice_view' => $c->invoice_view ?: 1,
                'status' => (bool) $c->status, 'sms_status' => (bool) $c->sms_status,
                'sales_terms_and_conditions' => $c->sales_terms_and_conditions,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::statement('ALTER TABLE companies AUTO_INCREMENT = '.($c->id + 1));
        }

        $s = $this->legacy('db_sitesettings')->first();
        if ($s) {
            DB::table('site_settings')->insert([
                'id' => $s->id, 'site_name' => $s->site_name, 'logo' => $s->logo,
                'currency_id' => $this->nz($s->currency_id), 'currency_placement' => $s->currency_placement ?: 'Left',
                'timezone' => $s->timezone ?: 'UTC', 'date_format' => $s->date_format ?: 'dd-mm-yyyy',
                'time_format' => $s->time_format ?: 12,
                'site_title' => $s->site_title, 'sales_discount' => $s->sales_discount ?? 0,
                'change_return' => (bool) $s->change_return, 'sales_invoice_format_id' => $s->sales_invoice_format_id ?: 1,
                'sales_invoice_footer_text' => $s->sales_invoice_footer_text, 'round_off' => (bool) $s->round_off,
                'show_upi_code' => (bool) $s->show_upi_code, 'disable_tax' => (bool) $s->disable_tax,
                'number_to_words' => $s->number_to_words ?: 'Default',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::statement('ALTER TABLE site_settings AUTO_INCREMENT = '.($s->id + 1));
        }
    }

    protected function importSuppliers(): void
    {
        $this->info('Importing suppliers...');

        foreach ($this->legacy('db_suppliers')->orderBy('id')->get() as $r) {
            DB::table('suppliers')->insert([
                'id' => $r->id, 'supplier_code' => $r->supplier_code, 'supplier_name' => $r->supplier_name,
                'mobile' => $r->mobile, 'phone' => $r->phone, 'email' => $r->email, 'gstin' => $r->gstin,
                'tax_number' => $r->tax_number, 'opening_balance' => $this->nd($r->opening_balance),
                'purchase_due' => $this->nd($r->purchase_due), 'purchase_return_due' => $this->nd($r->purchase_return_due),
                'country_id' => $r->country_id, 'state_id' => $r->state_id, 'city' => $r->city,
                'postcode' => $r->postcode, 'address' => $r->address, 'created_by' => $r->created_by,
                'status' => (bool) $r->status,
                'created_at' => $this->combineDateTime($r->created_date, $r->created_time),
                'updated_at' => $this->combineDateTime($r->created_date, $r->created_time),
            ]);
        }

        foreach ($this->legacy('db_sobpayments')->orderBy('id')->get() as $r) {
            DB::table('supplier_opening_balance_payments')->insert([
                'id' => $r->id, 'supplier_id' => $r->supplier_id, 'payment_date' => $r->payment_date,
                'payment_type' => $r->payment_type, 'payment' => $this->nd($r->payment), 'payment_note' => $r->payment_note,
                'created_by' => $r->created_by, 'status' => (bool) $r->status,
                'created_at' => $this->combineDateTime($r->created_date, $r->created_time),
                'updated_at' => $this->combineDateTime($r->created_date, $r->created_time),
            ]);
        }

        $this->resetAutoIncrement('suppliers');
        $this->resetAutoIncrement('supplier_opening_balance_payments');
    }

    protected function importCustomers(): void
    {
        $this->info('Importing customers...');

        foreach ($this->legacy('db_customers')->orderBy('id')->get() as $r) {
            DB::table('customers')->insert([
                'id' => $r->id, 'customer_code' => $r->customer_code, 'customer_name' => $r->customer_name,
                'mobile' => $r->mobile, 'phone' => $r->phone, 'email' => $r->email, 'gstin' => $r->gstin,
                'tax_number' => $r->tax_number, 'opening_balance' => $this->nd($r->opening_balance),
                'sales_due' => $this->nd($r->sales_due), 'sales_return_due' => $this->nd($r->sales_return_due),
                'country_id' => $r->country_id, 'state_id' => $r->state_id, 'city' => $r->city,
                'postcode' => $r->postcode, 'address' => $r->address, 'created_by' => $r->created_by,
                'status' => (bool) $r->status,
                'created_at' => $this->combineDateTime($r->created_date, $r->created_time),
                'updated_at' => $this->combineDateTime($r->created_date, $r->created_time),
            ]);
        }

        foreach ($this->legacy('db_cobpayments')->orderBy('id')->get() as $r) {
            DB::table('customer_opening_balance_payments')->insert([
                'id' => $r->id, 'customer_id' => $r->customer_id, 'payment_date' => $r->payment_date,
                'payment_type' => $r->payment_type, 'payment' => $this->nd($r->payment), 'payment_note' => $r->payment_note,
                'created_by' => $r->created_by, 'status' => (bool) $r->status,
                'created_at' => $this->combineDateTime($r->created_date, $r->created_time),
                'updated_at' => $this->combineDateTime($r->created_date, $r->created_time),
            ]);
        }

        $this->resetAutoIncrement('customers');
        $this->resetAutoIncrement('customer_opening_balance_payments');
    }

    protected function importItems(): void
    {
        $this->info('Importing items...');

        foreach ($this->legacy('db_items')->orderBy('id')->get() as $r) {
            DB::table('items')->insert([
                'id' => $r->id, 'item_code' => $r->item_code, 'custom_barcode' => $r->custom_barcode,
                'item_sing_name' => $r->item_sing_name, 'item_name' => $r->item_name, 'description' => $r->description,
                'category_id' => $this->nz($r->category_id), 'sku' => $r->sku, 'hsn' => $r->hsn,
                'unit_id' => $this->nz($r->unit_id), 'alert_qty' => $this->nd($r->alert_qty), 'brand_id' => $this->nz($r->brand_id),
                'lot_number' => $r->lot_number, 'expire_date' => $r->expire_date, 'price' => $this->nd($r->price),
                'tax_id' => $this->nz($r->tax_id), 'purchase_price' => $this->nd($r->purchase_price), 'tax_type' => $r->tax_type,
                'profit_margin' => $r->profit_margin, 'sales_price' => $this->nd($r->sales_price), 'final_price' => $this->nd($r->final_price),
                'stock' => $this->nd($r->stock), 'item_image' => $r->item_image, 'discount_type' => $r->discount_type,
                'discount' => $this->nd($r->discount), 'wholesale_discount' => $this->nd($r->wholesale_discount),
                'created_by' => $r->created_by, 'status' => (bool) $r->status,
                'created_at' => $this->combineDateTime($r->created_date, $r->created_time),
                'updated_at' => $this->combineDateTime($r->created_date, $r->created_time),
            ]);
        }
        $this->resetAutoIncrement('items');

        foreach ($this->legacy('db_stockentry')->orderBy('id')->get() as $r) {
            DB::table('stock_entries')->insert([
                'id' => $r->id, 'entry_date' => $r->entry_date, 'item_id' => $r->item_id, 'qty' => $r->qty,
                'note' => $r->note, 'status' => (bool) $r->status,
                'created_at' => $r->entry_date ?: now(), 'updated_at' => $r->entry_date ?: now(),
            ]);
        }
        $this->resetAutoIncrement('stock_entries');
    }

    protected function importPurchases(): void
    {
        $this->info('Importing purchases...');

        foreach ($this->legacy('db_purchase')->orderBy('id')->get() as $r) {
            DB::table('purchases')->insert([
                'id' => $r->id, 'purchase_code' => $r->purchase_code, 'reference_no' => $r->reference_no,
                'purchase_date' => $r->purchase_date, 'purchase_status' => $r->purchase_status,
                'supplier_id' => $this->nz($r->supplier_id), 'other_charges_input' => $r->other_charges_input,
                'other_charges_tax_id' => $this->nz($r->other_charges_tax_id), 'other_charges_amt' => $r->other_charges_amt,
                'discount_to_all_input' => $r->discount_to_all_input, 'discount_to_all_type' => $r->discount_to_all_type,
                'tot_discount_to_all_amt' => $r->tot_discount_to_all_amt, 'subtotal' => $r->subtotal,
                'round_off' => $r->round_off, 'grand_total' => $this->nd($r->grand_total), 'purchase_note' => $r->purchase_note,
                'payment_status' => $r->payment_status, 'paid_amount' => $this->nd($r->paid_amount),
                'created_by' => $r->created_by, 'status' => (bool) $r->status, 'return_bit' => (bool) $r->return_bit,
                'created_at' => $this->combineDateTime($r->created_date, $r->created_time),
                'updated_at' => $this->combineDateTime($r->created_date, $r->created_time),
            ]);
        }
        $this->resetAutoIncrement('purchases');

        foreach ($this->legacy('db_purchaseitems')->orderBy('id')->get() as $r) {
            DB::table('purchase_items')->insert([
                'id' => $r->id, 'purchase_id' => $r->purchase_id, 'purchase_status' => $r->purchase_status,
                'item_id' => $r->item_id, 'purchase_qty' => $this->nd($r->purchase_qty), 'price_per_unit' => $this->nd($r->price_per_unit),
                'tax_id' => $this->nz($r->tax_id), 'tax_amt' => $r->tax_amt, 'tax_type' => $r->tax_type,
                'unit_discount_per' => $r->unit_discount_per, 'discount_amt' => $r->discount_amt,
                'unit_total_cost' => $this->nd($r->unit_total_cost), 'total_cost' => $this->nd($r->total_cost),
                'profit_margin_per' => $r->profit_margin_per, 'unit_sales_price' => $r->unit_sales_price,
                'status' => (bool) $r->status, 'description' => $r->description, 'discount_type' => $r->discount_type,
                'discount_input' => $r->discount_input, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $this->resetAutoIncrement('purchase_items');

        foreach ($this->legacy('db_purchasepayments')->orderBy('id')->get() as $r) {
            DB::table('purchase_payments')->insert([
                'id' => $r->id, 'purchase_id' => $r->purchase_id, 'payment_date' => $r->payment_date,
                'payment_type' => $r->payment_type, 'payment' => $this->nd($r->payment), 'payment_note' => $r->payment_note,
                'created_by' => $r->created_by, 'status' => (bool) $r->status,
                'created_at' => $this->combineDateTime($r->created_date, $r->created_time),
                'updated_at' => $this->combineDateTime($r->created_date, $r->created_time),
            ]);
        }
        $this->resetAutoIncrement('purchase_payments');
    }

    protected function importSales(): void
    {
        $this->info('Importing sales...');

        foreach ($this->legacy('db_sales')->orderBy('id')->get() as $r) {
            DB::table('sales')->insert([
                'id' => $r->id, 'sales_code' => $r->sales_code, 'reference_no' => $r->reference_no,
                'sales_date' => $r->sales_date, 'sales_status' => $r->sales_status,
                'customer_id' => $this->nz($r->customer_id), 'other_charges_input' => $r->other_charges_input,
                'other_charges_tax_id' => $this->nz($r->other_charges_tax_id), 'other_charges_amt' => $r->other_charges_amt,
                'discount_to_all_input' => $r->discount_to_all_input, 'discount_to_all_type' => $r->discount_to_all_type,
                'tot_discount_to_all_amt' => $r->tot_discount_to_all_amt, 'subtotal' => $r->subtotal,
                'round_off' => $r->round_off, 'grand_total' => $this->nd($r->grand_total), 'sales_note' => $r->sales_note,
                'payment_status' => $r->payment_status, 'paid_amount' => $this->nd($r->paid_amount),
                'created_by' => $r->created_by, 'pos' => (bool) $r->pos, 'status' => (bool) $r->status,
                'return_bit' => (bool) $r->return_bit,
                'created_at' => $this->combineDateTime($r->created_date, $r->created_time),
                'updated_at' => $this->combineDateTime($r->created_date, $r->created_time),
            ]);
        }
        $this->resetAutoIncrement('sales');

        foreach ($this->legacy('db_salesitems')->orderBy('id')->get() as $r) {
            DB::table('sales_items')->insert([
                'id' => $r->id, 'sales_id' => $r->sales_id, 'sales_status' => $r->sales_status,
                'item_id' => $r->item_id, 'description' => $r->description, 'sales_qty' => $this->nd($r->sales_qty),
                'price_per_unit' => $this->nd($r->price_per_unit), 'tax_type' => $r->tax_type, 'tax_id' => $this->nz($r->tax_id),
                'tax_amt' => $r->tax_amt, 'discount_type' => $r->discount_type, 'discount_input' => $r->discount_input,
                'discount_amt' => $r->discount_amt, 'unit_total_cost' => $this->nd($r->unit_total_cost),
                'total_cost' => $this->nd($r->total_cost), 'status' => (bool) $r->status, 'purchase_price' => $this->nd($r->purchase_price),
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $this->resetAutoIncrement('sales_items');

        foreach ($this->legacy('db_salespayments')->orderBy('id')->get() as $r) {
            DB::table('sales_payments')->insert([
                'id' => $r->id, 'sales_id' => $r->sales_id, 'payment_date' => $r->payment_date,
                'payment_type' => $r->payment_type, 'payment' => $this->nd($r->payment), 'payment_note' => $r->payment_note,
                'change_return' => $r->change_return, 'created_by' => $r->created_by, 'status' => (bool) $r->status,
                'created_at' => $this->combineDateTime($r->created_date, $r->created_time),
                'updated_at' => $this->combineDateTime($r->created_date, $r->created_time),
            ]);
        }
        $this->resetAutoIncrement('sales_payments');
    }

    protected function importPurchaseReturns(): void
    {
        $this->info('Importing purchase returns...');

        foreach ($this->legacy('db_purchasereturn')->orderBy('id')->get() as $r) {
            DB::table('purchase_returns')->insert([
                'id' => $r->id, 'purchase_id' => $this->nz($r->purchase_id), 'return_code' => $r->return_code,
                'reference_no' => $r->reference_no, 'return_date' => $r->return_date, 'return_status' => $r->return_status,
                'supplier_id' => $this->nz($r->supplier_id), 'other_charges_input' => $r->other_charges_input,
                'other_charges_tax_id' => $this->nz($r->other_charges_tax_id), 'other_charges_amt' => $r->other_charges_amt,
                'discount_to_all_input' => $r->discount_to_all_input, 'discount_to_all_type' => $r->discount_to_all_type,
                'tot_discount_to_all_amt' => $r->tot_discount_to_all_amt, 'subtotal' => $r->subtotal,
                'round_off' => $r->round_off, 'grand_total' => $this->nd($r->grand_total), 'return_note' => $r->return_note,
                'payment_status' => $r->payment_status, 'paid_amount' => $this->nd($r->paid_amount),
                'created_by' => $r->created_by, 'status' => (bool) $r->status,
                'created_at' => $this->combineDateTime($r->created_date, $r->created_time),
                'updated_at' => $this->combineDateTime($r->created_date, $r->created_time),
            ]);
        }
        $this->resetAutoIncrement('purchase_returns');

        foreach ($this->legacy('db_purchaseitemsreturn')->orderBy('id')->get() as $r) {
            DB::table('purchase_items_returns')->insert([
                'id' => $r->id, 'purchase_id' => $this->nz($r->purchase_id), 'return_id' => $r->return_id,
                'return_status' => $r->return_status, 'item_id' => $r->item_id, 'return_qty' => $this->nd($r->return_qty),
                'price_per_unit' => $this->nd($r->price_per_unit), 'tax_id' => $this->nz($r->tax_id), 'tax_amt' => $r->tax_amt,
                'tax_type' => $r->tax_type, 'discount_type' => $r->discount_type, 'discount_input' => $r->discount_input,
                'discount_amt' => $r->discount_amt, 'unit_total_cost' => $this->nd($r->unit_total_cost),
                'total_cost' => $this->nd($r->total_cost), 'status' => (bool) $r->status, 'description' => $r->description,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $this->resetAutoIncrement('purchase_items_returns');

        foreach ($this->legacy('db_purchasepaymentsreturn')->orderBy('id')->get() as $r) {
            DB::table('purchase_payments_returns')->insert([
                'id' => $r->id, 'purchase_id' => $this->nz($r->purchase_id), 'return_id' => $r->return_id,
                'payment_date' => $r->payment_date, 'payment_type' => $r->payment_type, 'payment' => $this->nd($r->payment),
                'payment_note' => $r->payment_note, 'created_by' => $r->created_by, 'status' => (bool) $r->status,
                'created_at' => $this->combineDateTime($r->created_date, $r->created_time),
                'updated_at' => $this->combineDateTime($r->created_date, $r->created_time),
            ]);
        }
        $this->resetAutoIncrement('purchase_payments_returns');
    }

    protected function importSalesReturns(): void
    {
        $this->info('Importing sales returns...');

        foreach ($this->legacy('db_salesreturn')->orderBy('id')->get() as $r) {
            DB::table('sales_returns')->insert([
                'id' => $r->id, 'sales_id' => $this->nz($r->sales_id), 'return_code' => $r->return_code,
                'reference_no' => $r->reference_no, 'return_date' => $r->return_date, 'return_status' => $r->return_status,
                'customer_id' => $this->nz($r->customer_id), 'other_charges_input' => $r->other_charges_input,
                'other_charges_tax_id' => $this->nz($r->other_charges_tax_id), 'other_charges_amt' => $r->other_charges_amt,
                'discount_to_all_input' => $r->discount_to_all_input, 'discount_to_all_type' => $r->discount_to_all_type,
                'tot_discount_to_all_amt' => $r->tot_discount_to_all_amt, 'subtotal' => $r->subtotal,
                'round_off' => $r->round_off, 'grand_total' => $this->nd($r->grand_total), 'return_note' => $r->return_note,
                'payment_status' => $r->payment_status, 'paid_amount' => $this->nd($r->paid_amount),
                'created_by' => $r->created_by, 'status' => (bool) $r->status,
                'created_at' => $this->combineDateTime($r->created_date, $r->created_time),
                'updated_at' => $this->combineDateTime($r->created_date, $r->created_time),
            ]);
        }
        $this->resetAutoIncrement('sales_returns');

        foreach ($this->legacy('db_salesitemsreturn')->orderBy('id')->get() as $r) {
            DB::table('sales_items_returns')->insert([
                'id' => $r->id, 'sales_id' => $this->nz($r->sales_id), 'return_id' => $r->return_id,
                'return_status' => $r->return_status, 'item_id' => $r->item_id, 'description' => $r->description,
                'return_qty' => $this->nd($r->return_qty), 'price_per_unit' => $this->nd($r->price_per_unit),
                'tax_id' => $this->nz($r->tax_id), 'tax_amt' => $r->tax_amt, 'tax_type' => $r->tax_type,
                'discount_type' => $r->discount_type, 'discount_input' => $r->discount_input,
                'discount_amt' => $r->discount_amt, 'unit_total_cost' => $this->nd($r->unit_total_cost),
                'total_cost' => $this->nd($r->total_cost), 'purchase_price' => $this->nd($r->purchase_price), 'status' => (bool) $r->status,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $this->resetAutoIncrement('sales_items_returns');

        foreach ($this->legacy('db_salespaymentsreturn')->orderBy('id')->get() as $r) {
            DB::table('sales_payments_returns')->insert([
                'id' => $r->id, 'sales_id' => $this->nz($r->sales_id), 'return_id' => $r->return_id,
                'payment_date' => $r->payment_date, 'payment_type' => $r->payment_type, 'payment' => $this->nd($r->payment),
                'payment_note' => $r->payment_note, 'created_by' => $r->created_by, 'status' => (bool) $r->status,
                'created_at' => $this->combineDateTime($r->created_date, $r->created_time),
                'updated_at' => $this->combineDateTime($r->created_date, $r->created_time),
            ]);
        }
        $this->resetAutoIncrement('sales_payments_returns');
    }

    protected function importExpenses(): void
    {
        $this->info('Importing expenses...');

        foreach ($this->legacy('db_expense_category')->orderBy('id')->get() as $r) {
            DB::table('expense_categories')->insert([
                'id' => $r->id, 'category_code' => $r->category_code, 'category_name' => $r->category_name,
                'description' => $r->description, 'status' => (bool) $r->status, 'created_by' => $r->created_by,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $this->resetAutoIncrement('expense_categories');

        foreach ($this->legacy('db_expense')->orderBy('id')->get() as $r) {
            DB::table('expenses')->insert([
                'id' => $r->id, 'expense_code' => $r->expense_code, 'category_id' => $r->category_id,
                'expense_date' => $r->expense_date, 'reference_no' => $r->reference_no, 'expense_for' => $r->expense_for,
                'expense_amt' => $this->nd($r->expense_amt), 'note' => $r->note, 'created_by' => $r->created_by,
                'status' => (bool) $r->status,
                'created_at' => $this->combineDateTime($r->created_date, $r->created_time),
                'updated_at' => $this->combineDateTime($r->created_date, $r->created_time),
            ]);
        }
        $this->resetAutoIncrement('expenses');
    }

    protected function importHolds(): void
    {
        $this->info('Importing held sales...');

        foreach ($this->legacy('db_hold')->orderBy('id')->get() as $r) {
            DB::table('holds')->insert([
                'id' => $r->id, 'reference_id' => $r->reference_id, 'reference_no' => $r->reference_no,
                'sales_date' => $r->sales_date, 'sales_status' => $r->sales_status,
                'customer_id' => $this->nz($r->customer_id), 'other_charges_input' => $r->other_charges_input,
                'other_charges_tax_id' => $this->nz($r->other_charges_tax_id), 'other_charges_amt' => $r->other_charges_amt,
                'discount_to_all_input' => $r->discount_to_all_input, 'discount_to_all_type' => $r->discount_to_all_type,
                'tot_discount_to_all_amt' => $r->tot_discount_to_all_amt, 'subtotal' => $r->subtotal,
                'round_off' => $r->round_off, 'grand_total' => $this->nd($r->grand_total), 'sales_note' => $r->sales_note,
                'pos' => (bool) $r->pos, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $this->resetAutoIncrement('holds');

        foreach ($this->legacy('db_holditems')->orderBy('id')->get() as $r) {
            DB::table('hold_items')->insert([
                'id' => $r->id, 'hold_id' => $r->hold_id, 'item_id' => $r->item_id, 'description' => $r->description,
                'sales_qty' => $this->nd($r->sales_qty), 'price_per_unit' => $this->nd($r->price_per_unit), 'tax_type' => $r->tax_type,
                'tax_id' => $this->nz($r->tax_id), 'tax_amt' => $r->tax_amt, 'discount_type' => $r->discount_type,
                'discount_input' => $r->discount_input, 'discount_amt' => $r->discount_amt,
                'unit_total_cost' => $this->nd($r->unit_total_cost), 'total_cost' => $this->nd($r->total_cost),
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $this->resetAutoIncrement('hold_items');
    }

    protected function importSms(): void
    {
        $this->info('Importing SMS configuration...');

        foreach ($this->legacy('db_smsapi')->orderBy('id')->get() as $r) {
            DB::table('sms_apis')->insert([
                'id' => $r->id, 'info' => $r->info, 'key' => $r->key, 'key_value' => $r->key_value,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $this->resetAutoIncrement('sms_apis');

        foreach ($this->legacy('db_smstemplates')->orderBy('id')->get() as $r) {
            DB::table('sms_templates')->insert([
                'id' => $r->id, 'template_name' => $r->template_name, 'content' => $r->content,
                'status' => (bool) $r->status, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $this->resetAutoIncrement('sms_templates');
    }

    protected function resetAutoIncrement(string $table): void
    {
        $max = DB::table($table)->max('id');
        if ($max) {
            DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = ".($max + 1));
        }
    }
}
