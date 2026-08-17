<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FoundationSeeder extends Seeder
{
    public function run(): void
    {
        $currency = Currency::firstOrCreate(
            ['currency_code' => 'LKR'],
            ['currency_name' => 'Sri Lankan Rupee', 'currency' => 'LKR', 'symbol' => 'රු', 'status' => true]
        );

        SiteSetting::firstOrCreate(['id' => 1], [
            'site_name' => 'POS System',
            'site_title' => 'POS System',
            'currency_id' => $currency->id,
            'currency_placement' => 'Left',
            'date_format' => 'dd-mm-yyyy',
            'time_format' => 12,
            'change_return' => true,
            'sales_invoice_format_id' => 1,
            'round_off' => false,
            'disable_tax' => false,
        ]);

        Company::firstOrCreate(['id' => 1], [
            'company_name' => 'My Company',
            'category_init' => 'CT',
            'item_init' => 'IT',
            'supplier_init' => 'SU',
            'purchase_init' => 'PU',
            'purchase_return_init' => 'PR',
            'customer_init' => 'CU',
            'sales_init' => 'SL',
            'sales_return_init' => 'SR',
            'expense_init' => 'EX',
            'invoice_view' => 1,
            'status' => true,
        ]);

        $adminRole = Role::where('name', 'Admin')->first();

        $admin = User::firstOrCreate(['username' => 'admin'], [
            'password' => Hash::make('admin@1234'),
            'firstname' => 'Admin',
            'email' => 'admin@example.com',
            'status' => true,
        ]);

        if ($adminRole && ! $admin->hasRole('Admin')) {
            $admin->assignRole($adminRole);
        }

        Customer::firstOrCreate(['id' => 1], [
            'customer_code' => 'CU0001',
            'customer_name' => 'Walk-in Customer',
            'opening_balance' => 0,
            'status' => true,
        ]);
    }
}