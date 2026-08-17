<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PaymentTypeController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PrinterController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\SiteSettingsController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\TaxGroupController;
use App\Http\Controllers\TemplatesController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login/verify', [AuthController::class, 'login'])->name('login.verify');
    Route::get('/login/forgot_password', [AuthController::class, 'showForgotPassword'])->name('login.forgot');
    Route::post('/login/send_otp', [AuthController::class, 'sendOtp'])->name('login.send-otp');
    Route::get('/login/otp', [AuthController::class, 'showOtp'])->name('login.otp');
    Route::post('/login/verify_otp', [AuthController::class, 'verifyOtp'])->name('login.verify-otp');
    Route::post('/login/change_password', [AuthController::class, 'changePassword'])->name('login.change-password');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/', fn () => redirect()->route('dashboard'))->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/barcode/{code}/{rand?}', [BarcodeController::class, 'show'])->name('barcode');

    Route::prefix('category')->name('category.')->group(function () {
        Route::get('/view', [CategoryController::class, 'view'])->name('view');
        Route::get('/add', [CategoryController::class, 'add'])->name('add');
        Route::get('/update/{category}', [CategoryController::class, 'edit'])->name('edit');
        Route::post('/newcategory', [CategoryController::class, 'store'])->name('store');
        Route::post('/add_category_modal', [CategoryController::class, 'addModal'])->name('add_modal');
        Route::post('/update_category', [CategoryController::class, 'update'])->name('update');
        Route::post('/ajax_list', [CategoryController::class, 'ajaxList'])->name('ajax_list');
        Route::post('/update_status', [CategoryController::class, 'updateStatus'])->name('update_status');
        Route::post('/delete_category', [CategoryController::class, 'destroy'])->name('destroy');
        Route::post('/multi_delete', [CategoryController::class, 'multiDestroy'])->name('multi_destroy');
    });

    Route::prefix('brands')->name('brands.')->group(function () {
        Route::get('/view', [BrandController::class, 'view'])->name('view');
        Route::get('/add', [BrandController::class, 'add'])->name('add');
        Route::get('/update/{brand}', [BrandController::class, 'edit'])->name('edit');
        Route::post('/newbrand', [BrandController::class, 'store'])->name('store');
        Route::post('/add_brand_modal', [BrandController::class, 'addModal'])->name('add_modal');
        Route::post('/update_brand', [BrandController::class, 'update'])->name('update');
        Route::post('/ajax_list', [BrandController::class, 'ajaxList'])->name('ajax_list');
        Route::post('/update_status', [BrandController::class, 'updateStatus'])->name('update_status');
        Route::post('/delete_brand', [BrandController::class, 'destroy'])->name('destroy');
        Route::post('/multi_delete', [BrandController::class, 'multiDestroy'])->name('multi_destroy');
    });

    Route::prefix('units')->name('units.')->group(function () {
        Route::get('/', [UnitController::class, 'index'])->name('index');
        Route::get('/add', [UnitController::class, 'add'])->name('add');
        Route::get('/update/{unit}', [UnitController::class, 'edit'])->name('edit');
        Route::post('/new_unit', [UnitController::class, 'store'])->name('store');
        Route::post('/add_unit_modal', [UnitController::class, 'addModal'])->name('add_modal');
        Route::post('/update_unit', [UnitController::class, 'update'])->name('update');
        Route::post('/ajax_list', [UnitController::class, 'ajaxList'])->name('ajax_list');
        Route::post('/update_status', [UnitController::class, 'updateStatus'])->name('update_status');
        Route::post('/delete_unit', [UnitController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('payment_types')->name('payment_types.')->group(function () {
        Route::get('/', [PaymentTypeController::class, 'index'])->name('index');
        Route::get('/add', [PaymentTypeController::class, 'add'])->name('add');
        Route::get('/update/{paymentType}', [PaymentTypeController::class, 'edit'])->name('edit');
        Route::post('/new_payment_type', [PaymentTypeController::class, 'store'])->name('store');
        Route::post('/update_payment_type', [PaymentTypeController::class, 'update'])->name('update');
        Route::post('/ajax_list', [PaymentTypeController::class, 'ajaxList'])->name('ajax_list');
        Route::post('/update_status', [PaymentTypeController::class, 'updateStatus'])->name('update_status');
        Route::post('/delete_payment_type', [PaymentTypeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('country')->name('country.')->group(function () {
        Route::get('/', [CountryController::class, 'index'])->name('index');
        Route::get('/add', [CountryController::class, 'add'])->name('add');
        Route::get('/update/{country}', [CountryController::class, 'edit'])->name('edit');
        Route::post('/newcountry', [CountryController::class, 'store'])->name('store');
        Route::post('/update_country', [CountryController::class, 'update'])->name('update');
        Route::post('/ajax_list', [CountryController::class, 'ajaxList'])->name('ajax_list');
        Route::post('/update_status', [CountryController::class, 'updateStatus'])->name('update_status');
        Route::post('/delete_country', [CountryController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('state')->name('state.')->group(function () {
        Route::get('/', [StateController::class, 'index'])->name('index');
        Route::get('/add', [StateController::class, 'add'])->name('add');
        Route::get('/update/{state}', [StateController::class, 'edit'])->name('edit');
        Route::post('/newstate', [StateController::class, 'store'])->name('store');
        Route::post('/update_state', [StateController::class, 'update'])->name('update');
        Route::post('/ajax_list', [StateController::class, 'ajaxList'])->name('ajax_list');
        Route::post('/update_status', [StateController::class, 'updateStatus'])->name('update_status');
        Route::post('/delete_state', [StateController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('currency')->name('currency.')->group(function () {
        Route::get('/view', [CurrencyController::class, 'view'])->name('view');
        Route::get('/add', [CurrencyController::class, 'add'])->name('add');
        Route::get('/update/{currency}', [CurrencyController::class, 'edit'])->name('edit');
        Route::post('/newcurrency', [CurrencyController::class, 'store'])->name('store');
        Route::post('/update_currency', [CurrencyController::class, 'update'])->name('update');
        Route::post('/ajax_list', [CurrencyController::class, 'ajaxList'])->name('ajax_list');
        Route::post('/update_status', [CurrencyController::class, 'updateStatus'])->name('update_status');
        Route::post('/delete_currency', [CurrencyController::class, 'destroy'])->name('destroy');
        Route::post('/multi_delete', [CurrencyController::class, 'multiDestroy'])->name('multi_destroy');
    });

    Route::prefix('tax')->name('tax.')->group(function () {
        Route::get('/', [TaxController::class, 'index'])->name('index');
        Route::get('/add', [TaxController::class, 'add'])->name('add');
        Route::get('/update/{tax}', [TaxController::class, 'edit'])->name('edit');
        Route::post('/newtax', [TaxController::class, 'store'])->name('store');
        Route::post('/update_tax', [TaxController::class, 'update'])->name('update');
        Route::post('/ajax_list', [TaxController::class, 'ajaxList'])->name('ajax_list');
        Route::post('/update_status', [TaxController::class, 'updateStatus'])->name('update_status');
        Route::post('/delete_tax', [TaxController::class, 'destroy'])->name('destroy');
        Route::post('/multi_delete', [TaxController::class, 'multiDestroy'])->name('multi_destroy');
    });

    Route::prefix('tax_group')->name('tax_group.')->group(function () {
        Route::get('/', [TaxGroupController::class, 'index'])->name('index');
        Route::get('/add', [TaxGroupController::class, 'add'])->name('add');
        Route::get('/update/{tax}', [TaxGroupController::class, 'edit'])->name('edit');
        Route::post('/newtax', [TaxGroupController::class, 'store'])->name('store');
        Route::post('/update_tax', [TaxGroupController::class, 'update'])->name('update');
        Route::post('/ajax_list', [TaxGroupController::class, 'ajaxList'])->name('ajax_list');
    });

    Route::prefix('warehouse')->name('warehouse.')->group(function () {
        Route::get('/', [WarehouseController::class, 'index'])->name('index');
        Route::get('/add', [WarehouseController::class, 'add'])->name('add');
        Route::get('/edit/{warehouse}', [WarehouseController::class, 'edit'])->name('edit');
        Route::post('/save_or_update', [WarehouseController::class, 'saveOrUpdate'])->name('save_or_update');
        Route::post('/status_update', [WarehouseController::class, 'statusUpdate'])->name('status_update');
        Route::post('/delete_warehouse', [WarehouseController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('company')->name('company.')->group(function () {
        Route::get('/', [CompanyController::class, 'index'])->name('index');
        Route::post('/update_company', [CompanyController::class, 'update'])->name('update');
    });

    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::get('/add', [SupplierController::class, 'add'])->name('add');
        Route::get('/update/{supplier}', [SupplierController::class, 'edit'])->name('edit');
        Route::post('/newsuppliers', [SupplierController::class, 'store'])->name('store');
        Route::post('/update_suppliers', [SupplierController::class, 'update'])->name('update');
        Route::post('/ajax_list', [SupplierController::class, 'ajaxList'])->name('ajax_list');
        Route::post('/update_status', [SupplierController::class, 'updateStatus'])->name('update_status');
        Route::post('/delete_suppliers', [SupplierController::class, 'destroy'])->name('destroy');
        Route::post('/multi_delete', [SupplierController::class, 'multiDestroy'])->name('multi_destroy');
        Route::post('/delete_opening_balance_entry', [SupplierController::class, 'deleteOpeningBalanceEntry'])->name('delete_opening_balance_entry');
        Route::post('/getsuppliers/{id?}', [SupplierController::class, 'search'])->name('get');
        Route::post('/show_pay_now_modal', [SupplierController::class, 'showPayNowModal'])->name('show_pay_now_modal');
        Route::post('/save_payment', [SupplierController::class, 'savePayment'])->name('save_payment');
        Route::post('/show_pay_return_due_modal', [SupplierController::class, 'showPayReturnDueModal'])->name('show_pay_return_due_modal');
        Route::post('/save_return_due_payment', [SupplierController::class, 'saveReturnDuePayment'])->name('save_return_due_payment');
    });

    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/add', [CustomerController::class, 'add'])->name('add');
        Route::get('/update/{customer}', [CustomerController::class, 'edit'])->name('edit');
        Route::post('/newcustomers', [CustomerController::class, 'store'])->name('store');
        Route::post('/update_customers', [CustomerController::class, 'update'])->name('update');
        Route::post('/ajax_list', [CustomerController::class, 'ajaxList'])->name('ajax_list');
        Route::post('/update_status', [CustomerController::class, 'updateStatus'])->name('update_status');
        Route::post('/delete_customers', [CustomerController::class, 'destroy'])->name('destroy');
        Route::post('/multi_delete', [CustomerController::class, 'multiDestroy'])->name('multi_destroy');
        Route::post('/delete_opening_balance_entry', [CustomerController::class, 'deleteOpeningBalanceEntry'])->name('delete_opening_balance_entry');
        Route::match(['get', 'post'], '/getCustomers/{id?}', [CustomerController::class, 'search'])->name('get');
        Route::post('/show_pay_now_modal', [CustomerController::class, 'showPayNowModal'])->name('show_pay_now_modal');
        Route::post('/save_payment', [CustomerController::class, 'savePayment'])->name('save_payment');
        Route::post('/show_pay_return_due_modal', [CustomerController::class, 'showPayReturnDueModal'])->name('show_pay_return_due_modal');
        Route::post('/save_return_due_payment', [CustomerController::class, 'saveReturnDuePayment'])->name('save_return_due_payment');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'create'])->name('create');
        Route::get('/view', [UserController::class, 'view'])->name('view');
        Route::get('/edit/{user}', [UserController::class, 'edit'])->name('edit');
        Route::post('/save_or_update', [UserController::class, 'saveOrUpdate'])->name('save_or_update');
        Route::post('/status_update', [UserController::class, 'statusUpdate'])->name('status_update');
        Route::post('/delete_user', [UserController::class, 'destroy'])->name('destroy');
        Route::get('/password_reset', [UserController::class, 'passwordReset'])->name('password_reset');
        Route::post('/password_update', [UserController::class, 'passwordUpdate'])->name('password_update');
        Route::get('/dbbackup', [UserController::class, 'dbBackup'])->name('dbbackup');
    });

    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/view', [RoleController::class, 'index'])->name('index');
        Route::get('/add', [RoleController::class, 'add'])->name('add');
        Route::get('/update/{role}', [RoleController::class, 'edit'])->name('edit');
        Route::post('/newrole', [RoleController::class, 'store'])->name('store');
        Route::post('/update_role', [RoleController::class, 'update'])->name('update');
        Route::post('/ajax_list', [RoleController::class, 'ajaxList'])->name('ajax_list');
        Route::post('/update_status', [RoleController::class, 'updateStatus'])->name('update_status');
        Route::post('/delete_roles', [RoleController::class, 'destroy'])->name('destroy');
        Route::post('/multi_delete', [RoleController::class, 'multiDestroy'])->name('multi_destroy');
    });

    Route::prefix('items')->name('items.')->group(function () {
        Route::get('/', [ItemController::class, 'index'])->name('index');
        Route::get('/add', [ItemController::class, 'add'])->name('add');
        Route::get('/update/{item}', [ItemController::class, 'edit'])->name('edit');
        Route::post('/newitems', [ItemController::class, 'store'])->name('store');
        Route::post('/update_items', [ItemController::class, 'update'])->name('update');
        Route::post('/ajax_list', [ItemController::class, 'ajaxList'])->name('ajax_list');
        Route::post('/update_status', [ItemController::class, 'updateStatus'])->name('update_status');
        Route::post('/delete_items', [ItemController::class, 'destroy'])->name('destroy');
        Route::post('/multi_delete', [ItemController::class, 'multiDestroy'])->name('multi_destroy');
        Route::post('/delete_stock_entry', [ItemController::class, 'deleteStockEntry'])->name('delete_stock_entry');
        Route::post('/get_item_details_by_barcode', [ItemController::class, 'getByBarcode'])->name('get_by_barcode');
        Route::match(['get', 'post'], '/get_json_items_details', [ItemController::class, 'jsonDetails'])->name('json_details');
        Route::get('/getItems/{id?}', [ItemController::class, 'search'])->name('get');
        Route::get('/labels', [ItemController::class, 'labels'])->name('labels');
        Route::post('/preview_labels', [ItemController::class, 'previewLabels'])->name('preview_labels');
        Route::post('/return_row_with_data/{rowcount}/{item}', [ItemController::class, 'returnRowWithData'])->name('return_row_with_data');
        Route::post('/show_labels/{purchaseId?}', [ItemController::class, 'showLabels'])->name('show_labels');
    });

    Route::prefix('purchase')->name('purchase.')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::get('/add', [PurchaseController::class, 'add'])->name('add');
        Route::get('/update/{id}', [PurchaseController::class, 'update'])->name('update');
        Route::post('/purchase_save_and_update', [PurchaseController::class, 'saveAndUpdate'])->name('save_and_update');
        Route::post('/newsupplier', [PurchaseController::class, 'newSupplier'])->name('new_supplier');
        Route::post('/ajax_list', [PurchaseController::class, 'ajaxList'])->name('ajax_list');
        Route::post('/delete_purchase', [PurchaseController::class, 'destroy'])->name('destroy');
        Route::post('/multi_delete', [PurchaseController::class, 'multiDestroy'])->name('multi_destroy');
        Route::get('/search_item', [PurchaseController::class, 'searchItem'])->name('search_item');
        Route::post('/find_item_details', [PurchaseController::class, 'findItemDetails'])->name('find_item_details');
        Route::post('/return_row_with_data/{rowcount}/{item}', [PurchaseController::class, 'returnRowWithData'])->name('return_row_with_data');
        Route::post('/return_purchase_list/{purchase}', [PurchaseController::class, 'returnPurchaseList'])->name('return_purchase_list');
        Route::post('/delete_payment', [PurchaseController::class, 'deletePayment'])->name('delete_payment');
        Route::post('/show_pay_now_modal', [PurchaseController::class, 'showPayNowModal'])->name('show_pay_now_modal');
        Route::post('/save_payment', [PurchaseController::class, 'savePayment'])->name('save_payment');
        Route::post('/view_payments_modal', [PurchaseController::class, 'viewPaymentsModal'])->name('view_payments_modal');
        Route::get('/invoice/{purchase}', [PurchaseController::class, 'invoice'])->name('invoice');
        Route::get('/print_invoice/{purchase}', [PurchaseController::class, 'printInvoice'])->name('print_invoice');
        Route::get('/pdf/{purchase}', [PurchaseController::class, 'pdf'])->name('pdf');
    });

    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SalesController::class, 'index'])->name('index');
        Route::get('/add', [SalesController::class, 'add'])->name('add');
        Route::get('/update/{id}', [SalesController::class, 'update'])->name('update');
        Route::post('/sales_save_and_update', [SalesController::class, 'saveAndUpdate'])->name('save_and_update');
        Route::post('/ajax_list', [SalesController::class, 'ajaxList'])->name('ajax_list');
        Route::post('/delete_sales', [SalesController::class, 'destroy'])->name('destroy');
        Route::post('/multi_delete', [SalesController::class, 'multiDestroy'])->name('multi_destroy');
        Route::get('/search_item', [SalesController::class, 'searchItem'])->name('search_item');
        Route::post('/find_item_details', [SalesController::class, 'findItemDetails'])->name('find_item_details');
        Route::post('/return_row_with_data/{rowcount}/{item}', [SalesController::class, 'returnRowWithData'])->name('return_row_with_data');
        Route::post('/return_sales_list/{sale}', [SalesController::class, 'returnSalesList'])->name('return_sales_list');
        Route::post('/delete_payment', [SalesController::class, 'deletePayment'])->name('delete_payment');
        Route::post('/show_pay_now_modal', [SalesController::class, 'showPayNowModal'])->name('show_pay_now_modal');
        Route::post('/save_payment', [SalesController::class, 'savePayment'])->name('save_payment');
        Route::post('/view_payments_modal', [SalesController::class, 'viewPaymentsModal'])->name('view_payments_modal');
        Route::get('/invoice/{sale}', [SalesController::class, 'invoice'])->name('invoice');
        Route::get('/print_invoice/{sale}', [SalesController::class, 'printInvoice'])->name('print_invoice');
        Route::get('/print_invoice_pos/{sale}', [SalesController::class, 'printInvoicePos'])->name('print_invoice_pos');
        Route::get('/pdf/{sale}', [SalesController::class, 'pdf'])->name('pdf');
    });

    Route::prefix('sales_return')->name('sales_return.')->group(function () {
        Route::get('/', [SalesReturnController::class, 'index'])->name('index');
        Route::get('/create', [SalesReturnController::class, 'create'])->name('create');
        Route::get('/add/{salesId}', [SalesReturnController::class, 'add'])->name('add');
        Route::get('/edit/{salesReturn}', [SalesReturnController::class, 'edit'])->name('edit');
        Route::post('/sales_save_and_update', [SalesReturnController::class, 'salesSaveAndUpdate'])->name('save_and_update');
        Route::post('/ajax_list', [SalesReturnController::class, 'ajaxList'])->name('ajax_list');
        Route::post('/delete_return', [SalesReturnController::class, 'destroy'])->name('destroy');
        Route::post('/multi_delete', [SalesReturnController::class, 'multiDestroy'])->name('multi_destroy');
        Route::get('/search_item', [SalesReturnController::class, 'searchItem'])->name('search_item');
        Route::post('/return_row_with_data/{rowcount}/{item}', [SalesReturnController::class, 'returnRowWithData'])->name('return_row_with_data');
        Route::post('/return_sales_list/{salesReturn}', [SalesReturnController::class, 'returnSalesList'])->name('return_sales_list');
        Route::post('/sales_list/{salesId}', [SalesReturnController::class, 'salesList'])->name('sales_list');
        Route::post('/delete_payment', [SalesReturnController::class, 'deletePayment'])->name('delete_payment');
        Route::post('/show_pay_now_modal', [SalesReturnController::class, 'showPayNowModal'])->name('show_pay_now_modal');
        Route::post('/save_payment', [SalesReturnController::class, 'savePayment'])->name('save_payment');
        Route::post('/view_payments_modal', [SalesReturnController::class, 'viewPaymentsModal'])->name('view_payments_modal');
        Route::get('/invoice/{salesReturn}', [SalesReturnController::class, 'invoice'])->name('invoice');
        Route::get('/print_invoice/{salesReturn}', [SalesReturnController::class, 'printInvoice'])->name('print_invoice');
        Route::get('/pdf/{salesReturn}', [SalesReturnController::class, 'pdf'])->name('pdf');
    });

    Route::prefix('purchase_return')->name('purchase_return.')->group(function () {
        Route::get('/', [PurchaseReturnController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseReturnController::class, 'create'])->name('create');
        Route::get('/add/{purchaseId}', [PurchaseReturnController::class, 'add'])->name('add');
        Route::get('/edit/{purchaseReturn}', [PurchaseReturnController::class, 'edit'])->name('edit');
        Route::post('/purchase_save_and_update', [PurchaseReturnController::class, 'purchaseSaveAndUpdate'])->name('save_and_update');
        Route::post('/ajax_list', [PurchaseReturnController::class, 'ajaxList'])->name('ajax_list');
        Route::post('/delete_return', [PurchaseReturnController::class, 'destroy'])->name('destroy');
        Route::post('/multi_delete', [PurchaseReturnController::class, 'multiDestroy'])->name('multi_destroy');
        Route::get('/search_item', [PurchaseReturnController::class, 'searchItem'])->name('search_item');
        Route::post('/return_row_with_data/{rowcount}/{item}', [PurchaseReturnController::class, 'returnRowWithData'])->name('return_row_with_data');
        Route::post('/return_purchase_list/{purchaseReturn}', [PurchaseReturnController::class, 'returnPurchaseList'])->name('return_purchase_list');
        Route::post('/purchase_list/{purchaseId}', [PurchaseReturnController::class, 'purchaseList'])->name('purchase_list');
        Route::post('/delete_payment', [PurchaseReturnController::class, 'deletePayment'])->name('delete_payment');
        Route::post('/show_pay_now_modal', [PurchaseReturnController::class, 'showPayNowModal'])->name('show_pay_now_modal');
        Route::post('/save_payment', [PurchaseReturnController::class, 'savePayment'])->name('save_payment');
        Route::post('/view_payments_modal', [PurchaseReturnController::class, 'viewPaymentsModal'])->name('view_payments_modal');
        Route::get('/invoice/{purchaseReturn}', [PurchaseReturnController::class, 'invoice'])->name('invoice');
        Route::get('/print_invoice/{purchaseReturn}', [PurchaseReturnController::class, 'printInvoice'])->name('print_invoice');
        Route::get('/pdf/{purchaseReturn}', [PurchaseReturnController::class, 'pdf'])->name('pdf');
    });

    Route::prefix('expense')->name('expense.')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::get('/add', [ExpenseController::class, 'add'])->name('add');
        Route::post('/newexpense', [ExpenseController::class, 'store'])->name('store');
        Route::get('/update/{expense}', [ExpenseController::class, 'edit'])->name('edit');
        Route::post('/update_expense', [ExpenseController::class, 'update'])->name('update');
        Route::post('/ajax_list', [ExpenseController::class, 'ajaxList'])->name('ajax_list');
        Route::post('/update_status', [ExpenseController::class, 'updateStatus'])->name('update_status');
        Route::post('/delete_expense', [ExpenseController::class, 'destroy'])->name('destroy');
        Route::post('/multi_delete_expense', [ExpenseController::class, 'multiDestroy'])->name('multi_destroy');

        Route::get('/category', [ExpenseCategoryController::class, 'index'])->name('category');
        Route::get('/category_add', [ExpenseCategoryController::class, 'add'])->name('category_add');
        Route::post('/newcategory', [ExpenseCategoryController::class, 'store'])->name('category_store');
        Route::get('/expense_update/{expenseCategory}', [ExpenseCategoryController::class, 'edit'])->name('category_edit');
        Route::post('/update_category', [ExpenseCategoryController::class, 'update'])->name('category_update');
        Route::post('/ajax_list_expense', [ExpenseCategoryController::class, 'ajaxList'])->name('category_ajax_list');
        Route::post('/expense_update_status', [ExpenseCategoryController::class, 'updateStatus'])->name('category_update_status');
        Route::post('/delete_category', [ExpenseCategoryController::class, 'destroy'])->name('category_destroy');
        Route::post('/multi_delete', [ExpenseCategoryController::class, 'multiDestroy'])->name('category_multi_destroy');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [ReportsController::class, 'sales'])->name('sales');
        Route::post('/show_sales_report', [ReportsController::class, 'showSalesReport'])->name('show_sales_report');

        Route::get('/sales_return', [ReportsController::class, 'salesReturn'])->name('sales_return');
        Route::post('/show_sales_return_report', [ReportsController::class, 'showSalesReturnReport'])->name('show_sales_return_report');

        Route::get('/purchase', [ReportsController::class, 'purchase'])->name('purchase');
        Route::post('/show_purchase_report', [ReportsController::class, 'showPurchaseReport'])->name('show_purchase_report');

        Route::get('/purchase_return', [ReportsController::class, 'purchaseReturn'])->name('purchase_return');
        Route::post('/show_purchase_return_report', [ReportsController::class, 'showPurchaseReturnReport'])->name('show_purchase_return_report');

        Route::get('/expense', [ReportsController::class, 'expense'])->name('expense');
        Route::post('/show_expense_report', [ReportsController::class, 'showExpenseReport'])->name('show_expense_report');

        Route::get('/profit_loss', [ReportsController::class, 'profitLoss'])->name('profit_loss');
        Route::post('/get_profit_loss_report', [ReportsController::class, 'getProfitLossReport'])->name('get_profit_loss_report');
        Route::post('/get_profit_by_item', [ReportsController::class, 'getProfitByItem'])->name('get_profit_by_item');
        Route::post('/get_profit_by_invoice', [ReportsController::class, 'getProfitByInvoice'])->name('get_profit_by_invoice');

        Route::get('/stock', [ReportsController::class, 'stock'])->name('stock');
        Route::post('/get_stock_report', [ReportsController::class, 'getStockReport'])->name('get_stock_report');

        Route::get('/item_sales', [ReportsController::class, 'itemSales'])->name('item_sales');
        Route::post('/show_item_sales_report', [ReportsController::class, 'showItemSalesReport'])->name('show_item_sales_report');

        Route::get('/item_purchase', [ReportsController::class, 'itemPurchase'])->name('item_purchase');
        Route::post('/show_item_purchase_report', [ReportsController::class, 'showItemPurchaseReport'])->name('show_item_purchase_report');

        Route::get('/purchase_payments', [ReportsController::class, 'purchasePayments'])->name('purchase_payments');
        Route::post('/show_purchase_payments_report', [ReportsController::class, 'showPurchasePaymentsReport'])->name('show_purchase_payments_report');
        Route::post('/supplier_payments_report', [ReportsController::class, 'supplierPaymentsReport'])->name('supplier_payments_report');

        Route::get('/sales_payments', [ReportsController::class, 'salesPayments'])->name('sales_payments');
        Route::post('/show_sales_payments_report', [ReportsController::class, 'showSalesPaymentsReport'])->name('show_sales_payments_report');
        Route::post('/customer_payments_report', [ReportsController::class, 'customerPaymentsReport'])->name('customer_payments_report');

        Route::get('/expired_items', [ReportsController::class, 'expiredItems'])->name('expired_items');
        Route::post('/show_expired_items_report', [ReportsController::class, 'showExpiredItemsReport'])->name('show_expired_items_report');
    });

    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/edit/{sale}', [PosController::class, 'edit'])->name('edit');
        Route::post('/newcustomer', [SalesController::class, 'newCustomer'])->name('new_customer');
        Route::post('/get_details', [PosController::class, 'getDetails'])->name('get_details');
        Route::post('/get_item_details', [PosController::class, 'getItemDetails'])->name('get_item_details');
        Route::get('/fetch_sales/{salesId}', [PosController::class, 'fetchSales'])->name('fetch_sales');
        Route::post('/pos_save_update', [PosController::class, 'posSaveUpdate'])->name('pos_save_update');
        Route::post('/hold_invoice', [PosController::class, 'holdInvoice'])->name('hold_invoice');
        Route::post('/hold_invoice_list', [PosController::class, 'holdInvoiceList'])->name('hold_invoice_list');
        Route::post('/hold_invoice_delete/{id}', [PosController::class, 'holdInvoiceDelete'])->name('hold_invoice_delete');
        Route::post('/hold_invoice_edit', [PosController::class, 'holdInvoiceEdit'])->name('hold_invoice_edit');
        Route::post('/add_payment_row', [PosController::class, 'addPaymentRow'])->name('add_payment_row');
        Route::get('/print_invoice_pos/{sale}', [SalesController::class, 'printInvoicePos'])->name('print_invoice_pos');
    });

    Route::prefix('sms')->name('sms.')->group(function () {
        Route::get('/', [SmsController::class, 'index'])->name('index');
        Route::post('/send_message', [SmsController::class, 'sendMessage'])->name('send_message');
        Route::get('/api', [SmsController::class, 'api'])->name('api');
        Route::post('/api_update', [SmsController::class, 'apiUpdate'])->name('api_update');
    });

    Route::prefix('templates')->name('templates.')->group(function () {
        Route::get('/sms_new', [TemplatesController::class, 'smsNew'])->name('sms_new');
        Route::post('/newtemplate', [TemplatesController::class, 'store'])->name('store');
        Route::get('/update/{template}', [TemplatesController::class, 'edit'])->name('edit');
        Route::post('/update_template', [TemplatesController::class, 'update'])->name('update');
        Route::get('/sms', [TemplatesController::class, 'sms'])->name('sms');
        Route::post('/ajax_list', [TemplatesController::class, 'ajaxList'])->name('ajax_list');
        Route::post('/update_status', [TemplatesController::class, 'updateStatus'])->name('update_status');
        Route::post('/delete_template', [TemplatesController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/customers', [ImportController::class, 'customers'])->name('customers');
        Route::post('/import_customers_csv', [ImportController::class, 'importCustomersCsv'])->name('import_customers_csv');
        Route::get('/suppliers', [ImportController::class, 'suppliers'])->name('suppliers');
        Route::post('/import_suppliers_csv', [ImportController::class, 'importSuppliersCsv'])->name('import_suppliers_csv');
        Route::get('/items', [ImportController::class, 'items'])->name('items');
        Route::post('/import_items_csv', [ImportController::class, 'importItemsCsv'])->name('import_items_csv');
    });

    Route::prefix('site')->name('site.')->group(function () {
        Route::get('/', [SiteSettingsController::class, 'index'])->name('index');
        Route::post('/update_site', [SiteSettingsController::class, 'update'])->name('update_site');
    });

    Route::prefix('printers')->name('printers.')->group(function () {
        Route::get('/', [PrinterController::class, 'index'])->name('index');
        Route::get('/add', [PrinterController::class, 'add'])->name('add');
        Route::get('/edit/{printer}', [PrinterController::class, 'edit'])->name('edit');
        Route::post('/save_or_update', [PrinterController::class, 'saveOrUpdate'])->name('save_or_update');
        Route::post('/update_status', [PrinterController::class, 'updateStatus'])->name('update_status');
        Route::post('/delete_printer', [PrinterController::class, 'destroy'])->name('destroy');
        Route::post('/print_sale/{sale}/{printer}', [PrinterController::class, 'printSale'])->name('print_sale');
        Route::post('/test_print/{printer}', [PrinterController::class, 'testPrint'])->name('test_print');
        Route::post('/test_connection', [PrinterController::class, 'testConnection'])->name('test_connection');
        Route::post('/log_result', [PrinterController::class, 'logResult'])->name('log_result');
    });
});
