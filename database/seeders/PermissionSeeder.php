<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public const PERMISSIONS = [
        'users_add', 'users_edit', 'users_delete', 'users_view',
        'tax_add', 'tax_edit', 'tax_delete', 'tax_view',
        'currency_add', 'currency_edit', 'currency_delete', 'currency_view',
        'company_edit', 'site_edit',
        'units_add', 'units_edit', 'units_delete', 'units_view',
        'roles_add', 'roles_edit', 'roles_delete', 'roles_view',
        'places_add', 'places_edit', 'places_delete', 'places_view',
        'expense_add', 'expense_edit', 'expense_delete', 'expense_view',
        'items_add', 'items_edit', 'items_delete', 'items_view',
        'brand_add', 'brand_edit', 'brand_delete', 'brand_view',
        'suppliers_add', 'suppliers_edit', 'suppliers_delete', 'suppliers_view',
        'customers_add', 'customers_edit', 'customers_delete', 'customers_view',
        'purchase_add', 'purchase_edit', 'purchase_delete', 'purchase_view',
        'sales_add', 'sales_edit', 'sales_delete', 'sales_view',
        'sales_payment_view', 'sales_payment_add', 'sales_payment_delete',
        'sales_report', 'purchase_report', 'expense_report', 'profit_report',
        'stock_report', 'item_sales_report', 'purchase_payments_report',
        'sales_payments_report', 'expired_items_report',
        'items_category_add', 'items_category_edit', 'items_category_delete', 'items_category_view',
        'print_labels', 'import_items',
        'expense_category_add', 'expense_category_edit', 'expense_category_delete', 'expense_category_view',
        'dashboard_view',
        'send_sms', 'sms_template_edit', 'sms_template_view', 'sms_api_view', 'sms_api_edit',
        'purchase_return_add', 'purchase_return_edit', 'purchase_return_delete', 'purchase_return_view', 'purchase_return_report',
        'sales_return_add', 'sales_return_edit', 'sales_return_delete', 'sales_return_view', 'sales_return_report',
        'sales_return_payment_view', 'sales_return_payment_add', 'sales_return_payment_delete',
        'purchase_return_payment_view', 'purchase_return_payment_add', 'purchase_return_payment_delete',
        'purchase_payment_view', 'purchase_payment_add', 'purchase_payment_delete',
        'payment_types_add', 'payment_types_edit', 'payment_types_delete', 'payment_types_view',
        'import_customers', 'import_suppliers', 'item_purchase_report',
        'pos', 'help',
        'printers_add', 'printers_edit', 'printers_delete', 'printers_view',
        'view_all_users_sales_invoices', 'view_all_users_sales_return_invoices',
        'view_all_users_purchase_invoices', 'view_all_users_purchase_return_invoices',
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web'], [
            'description' => 'Full system access',
            'status' => true,
        ]);
        $admin->syncPermissions(self::PERMISSIONS);
    }
}
