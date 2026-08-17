<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Support\DatatableHtml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public function index(): View
    {
        $this->authorize('roles_view');

        return view('role.list', ['page_title' => 'Roles List']);
    }

    public function add(): View
    {
        $this->authorize('roles_add');

        return view('role.form', [
            'page_title' => 'New Role',
            'permissionGroups' => $this->permissionGroups(),
            'assignedPermissions' => [],
        ]);
    }

    public function edit(Role $role): View|RedirectResponse
    {
        if ($role->id === 1) {
            return redirect()->route('roles.index')->with('failed', "Restricted!! Admin Role Can't Be Updated!");
        }

        $this->authorize('roles_edit');

        return view('role.form', [
            'page_title' => 'Edit Role',
            'q_id' => $role->id,
            'role_name' => $role->name,
            'description' => $role->description,
            'permissionGroups' => $this->permissionGroups(),
            'assignedPermissions' => $role->permissions->pluck('name')->all(),
        ]);
    }

    public function store(Request $request): Response
    {
        $validator = Validator::make($request->all(), ['role_name' => 'required']);

        if ($validator->fails()) {
            return response('Please Enter Role Name.');
        }

        if (Role::whereRaw('upper(name) = upper(?)', [$request->input('role_name')])->exists()) {
            return response('This Role Name Already Exist.');
        }

        $role = Role::create([
            'name' => $request->input('role_name'),
            'guard_name' => 'web',
            'description' => $request->input('description'),
            'status' => true,
        ]);

        $role->syncPermissions($this->selectedPermissions($request));

        session()->flash('success', 'Success!! New Role Added Successfully!');

        return response('success');
    }

    public function update(Request $request): Response
    {
        $validator = Validator::make($request->all(), ['role_name' => 'required', 'q_id' => 'required']);

        if ($validator->fails()) {
            return response('Please Enter Role Name.');
        }

        $id = (int) $request->input('q_id');

        if ($id === 1) {
            return response("Restricted!! Admin Role Can't Be Updated!");
        }

        if (Role::whereRaw('upper(name) = upper(?)', [$request->input('role_name')])->where('id', '!=', $id)->exists()) {
            return response('This Role Name Already Exist.');
        }

        $role = Role::findOrFail($id);
        $role->update([
            'name' => $request->input('role_name'),
            'description' => $request->input('description'),
        ]);

        $role->syncPermissions($this->selectedPermissions($request));

        session()->flash('success', 'Success!! Role Updated Successfully!');

        return response('success');
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('roles_view');

        return DataTables::of(Role::query())
            ->addIndexColumn()
            ->addColumn('status_badge', fn (Role $r) => $r->id === 1
                ? "<span class='label label-warning'> Restricted </span>"
                : DatatableHtml::statusBadge($r->id, (bool) $r->status))
            ->addColumn('actions', function (Role $r) use ($request) {
                if ($r->id === 1) {
                    return '--';
                }

                return DatatableHtml::actionMenu([
                    ['label' => 'Edit', 'icon' => 'fa-edit text-blue', 'url' => route('roles.edit', $r), 'can' => $request->user()->can('roles_edit')],
                    ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_roles({$r->id})", 'can' => $request->user()->can('roles_delete')],
                ]);
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function updateStatus(Request $request): Response
    {
        $this->authorize('roles_edit');

        $id = (int) $request->input('id');

        if ($id === 1) {
            return response("Restricted! Can't Update this Role Status!");
        }

        Role::whereKey($id)->update(['status' => $request->input('status')]);

        return response('success');
    }

    public function destroy(Request $request): Response
    {
        $this->authorize('roles_delete');

        return $this->deleteIds((string) $request->input('q_id'));
    }

    public function multiDestroy(Request $request): Response
    {
        $this->authorize('roles_delete');

        return $this->deleteIds(implode(',', $request->input('checkbox', [])));
    }

    protected function deleteIds(string $ids): Response
    {
        $idArray = array_map('intval', array_filter(explode(',', $ids)));

        if (in_array(1, $idArray, true)) {
            return response("Restricted! Can't Delete this Role!");
        }

        Role::whereIn('id', $idArray)->delete();

        return response('success');
    }

    protected function selectedPermissions(Request $request): array
    {
        $posted = array_keys((array) $request->input('permission', []));

        return Permission::whereIn('name', $posted)->pluck('name')->all();
    }

    public function permissionGroups(): array
    {
        return [
            'users' => ['label' => 'Users', 'permissions' => [
                'users_add' => 'Add', 'users_edit' => 'Edit', 'users_delete' => 'Delete', 'users_view' => 'View',
            ]],
            'roles' => ['label' => 'Roles', 'permissions' => [
                'roles_add' => 'Add', 'roles_edit' => 'Edit', 'roles_delete' => 'Delete', 'roles_view' => 'View',
            ]],
            'tax' => ['label' => 'Tax', 'permissions' => [
                'tax_add' => 'Add', 'tax_edit' => 'Edit', 'tax_delete' => 'Delete', 'tax_view' => 'View',
            ]],
            'currency' => ['label' => 'Currency', 'permissions' => [
                'currency_add' => 'Add', 'currency_edit' => 'Edit', 'currency_delete' => 'Delete', 'currency_view' => 'View',
            ]],
            'units' => ['label' => 'Units', 'permissions' => [
                'units_add' => 'Add', 'units_edit' => 'Edit', 'units_delete' => 'Delete', 'units_view' => 'View',
            ]],
            'payment_types' => ['label' => 'Payment Types', 'permissions' => [
                'payment_types_add' => 'Add', 'payment_types_edit' => 'Edit', 'payment_types_delete' => 'Delete', 'payment_types_view' => 'View',
            ]],
            'site' => ['label' => 'Site Settings', 'permissions' => ['site_edit' => 'Edit']],
            'printers' => ['label' => 'Printers', 'permissions' => [
                'printers_add' => 'Add', 'printers_edit' => 'Edit', 'printers_delete' => 'Delete', 'printers_view' => 'View',
            ]],
            'company' => ['label' => 'Company Profile', 'permissions' => ['company_edit' => 'Edit']],
            'dashboard' => ['label' => 'Dashboard', 'permissions' => ['dashboard_view' => 'View Dashboard Data']],
            'places' => ['label' => 'Places', 'permissions' => [
                'places_add' => 'Add', 'places_edit' => 'Edit', 'places_delete' => 'Delete', 'places_view' => 'View',
            ]],
            'expense' => ['label' => 'Expense', 'permissions' => [
                'expense_add' => 'Add', 'expense_edit' => 'Edit', 'expense_delete' => 'Delete', 'expense_view' => 'View',
                'expense_category_add' => 'Category Add', 'expense_category_edit' => 'Category Edit',
                'expense_category_delete' => 'Category Delete', 'expense_category_view' => 'Category View',
            ]],
            'items' => ['label' => 'Items', 'permissions' => [
                'items_add' => 'Add', 'items_edit' => 'Edit', 'items_delete' => 'Delete', 'items_view' => 'View',
                'items_category_add' => 'Category Add', 'items_category_edit' => 'Category Edit',
                'items_category_delete' => 'Category Delete', 'items_category_view' => 'Category View',
                'print_labels' => 'Print Labels', 'import_items' => 'Import Items',
            ]],
            'brand' => ['label' => 'Brand', 'permissions' => [
                'brand_add' => 'Add', 'brand_edit' => 'Edit', 'brand_delete' => 'Delete', 'brand_view' => 'View',
            ]],
            'suppliers' => ['label' => 'Suppliers', 'permissions' => [
                'suppliers_add' => 'Add', 'suppliers_edit' => 'Edit', 'suppliers_delete' => 'Delete', 'suppliers_view' => 'View',
                'import_suppliers' => 'Import Suppliers',
            ]],
            'customers' => ['label' => 'Customers', 'permissions' => [
                'customers_add' => 'Add', 'customers_edit' => 'Edit', 'customers_delete' => 'Delete', 'customers_view' => 'View',
                'import_customers' => 'Import Customers',
            ]],
            'purchase' => ['label' => 'Purchase', 'permissions' => [
                'purchase_add' => 'Add', 'purchase_edit' => 'Edit', 'purchase_delete' => 'Delete', 'purchase_view' => 'View',
                'view_all_users_purchase_invoices' => 'View All Users Purchase Invoices',
                'purchase_payment_view' => 'Purchase Payments View', 'purchase_payment_add' => 'Purchase Payments Add',
                'purchase_payment_delete' => 'Purchase Payments Delete',
            ]],
            'purchase_return' => ['label' => 'Purchase Return', 'permissions' => [
                'purchase_return_add' => 'Add', 'purchase_return_edit' => 'Edit', 'purchase_return_delete' => 'Delete',
                'purchase_return_view' => 'View',
                'view_all_users_purchase_return_invoices' => 'View All Users Purchase Return Invoices',
                'purchase_return_payment_view' => 'Purchase Return Payments View',
                'purchase_return_payment_add' => 'Purchase Return Payments Add',
                'purchase_return_payment_delete' => 'Purchase Return Payments Delete',
            ]],
            'sales' => ['label' => 'Sales (Including POS)', 'permissions' => [
                'pos' => 'POS', 'sales_add' => 'Add', 'sales_edit' => 'Edit', 'sales_delete' => 'Delete', 'sales_view' => 'View',
                'view_all_users_sales_invoices' => 'View All Users Sales Invoices',
                'sales_payment_view' => 'Sales Payments View', 'sales_payment_add' => 'Sales Payments Add',
                'sales_payment_delete' => 'Sales Payments Delete',
            ]],
            'sales_return' => ['label' => 'Sales Return', 'permissions' => [
                'sales_return_add' => 'Add', 'sales_return_edit' => 'Edit', 'sales_return_delete' => 'Delete',
                'sales_return_view' => 'View',
                'view_all_users_sales_return_invoices' => 'View All Users Sales Return Invoices',
                'sales_return_payment_view' => 'Sales Return Payments View',
                'sales_return_payment_add' => 'Sales Return Payments Add',
                'sales_return_payment_delete' => 'Sales Return Payments Delete',
            ]],
            'sms' => ['label' => 'SMS', 'permissions' => [
                'send_sms' => 'Send SMS', 'sms_template_edit' => 'SMS Template Edit', 'sms_template_view' => 'SMS Template View',
                'sms_api_view' => 'SMS API View', 'sms_api_edit' => 'SMS API Edit',
            ]],
            'reports' => ['label' => 'Reports', 'permissions' => [
                'sales_report' => 'Sales Report', 'sales_return_report' => 'Sales Return Report',
                'purchase_report' => 'Purchase Report', 'purchase_return_report' => 'Purchase Return Report',
                'expense_report' => 'Expense Report', 'profit_report' => 'Profit Report', 'stock_report' => 'Stock Report',
                'item_sales_report' => 'Item Sales Report', 'item_purchase_report' => 'Item Purchase Report',
                'purchase_payments_report' => 'Purchase Payments Report', 'sales_payments_report' => 'Sales Payments Report',
                'expired_items_report' => 'Expired Items Report',
            ]],
            'help' => ['label' => 'Help / Documentation', 'permissions' => ['help' => 'Help / Documentation']],
        ];
    }
}
