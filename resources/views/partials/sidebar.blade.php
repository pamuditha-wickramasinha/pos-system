<script type="text/javascript">
if (theme_skin != 'skin-blue') {
    $("body").addClass(theme_skin);
    $("body").removeClass('skin-blue');
}
if (sidebar_collapse == 'true') {
    $("body").addClass('sidebar-collapse');
}
</script>

<header class="main-header">
    <a href="{{ url('dashboard') }}" class="logo">
        <span class="logo-mini"><b>POS</b></span>
        <span class="logo-lg"><b>{{ $SITE_TITLE }}</b></span>
    </a>

    <nav class="navbar navbar-static-top">
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        <div class="btn-group hidden-xs"></div>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                @can('pos')
                    <li class="text-center" id="">
                        <a title="POS [Shift+P]" href="{{ url('pos') }}"><i class="fa fa-plus-square "></i> POS</a>
                    </li>
                @endcan
                <li class="text-center hidden-xs" id="">
                    <a title="Dashboard" href="{{ url('dashboard') }}"><i class="fa fa-dashboard "></i> Dashboard</a>
                </li>
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="{{ auth()->user()?->profile_picture ? asset('storage/'.auth()->user()->profile_picture) : asset('theme/dist/img/avatar5.png') }}" class="user-image" alt="User Image">
                        <span class="hidden-xs">{{ ucfirst(auth()->user()->username ?? '') }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header">
                            <img src="{{ auth()->user()?->profile_picture ? asset('storage/'.auth()->user()->profile_picture) : asset('theme/dist/img/avatar5.png') }}" class="img-circle" alt="User Image">
                            <p>
                                {{ ucfirst(auth()->user()->username ?? '') }}
                                <small>Year {{ date('Y') }}</small>
                            </p>
                        </li>
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="{{ url('users/edit/'.auth()->id()) }}" class="btn btn-default btn-flat">Profile</a>
                            </div>
                            <div class="pull-right">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-default btn-flat">Sign out</button>
                                </form>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>

<aside class="main-sidebar">
    <section class="sidebar">
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ auth()->user()?->profile_picture ? asset('storage/'.auth()->user()->profile_picture) : asset('theme/dist/img/avatar5.png') }}" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                <p>{{ ucfirst(auth()->user()->username ?? '') }}<i class="fa fa-fw fa-check-circle text-aqua"></i></p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li class="dashboard-active-li"><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard text-aqua"></i> <span>Dashboard</span></a></li>

            @canany(['sales_add', 'pos', 'sales_view', 'sales_return_view', 'sales_return_add'])
            <li class="pos-active-li sales-list-active-li sales-active-li sales-return-active-li sales-return-list-active-li treeview">
                <a href="#">
                    <i class=" fa fa-shopping-cart text-aqua"></i> <span>Sales</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    @can('pos')
                    <li class="pos-active-li"><a href="{{ url('pos') }}"><i class="fa fa-calculator "></i> <span>POS</span></a></li>
                    @endcan
                    @can('sales_add')
                    <li class="sales-active-li"><a href="{{ url('sales/add') }}"><i class="fa fa-plus-square-o "></i> <span>New Sales</span></a></li>
                    @endcan
                    @can('sales_view')
                    <li class="sales-list-active-li"><a href="{{ url('sales') }}"><i class="fa fa-list "></i> <span>Sales List</span></a></li>
                    @endcan
                    @can('sales_return_add')
                    <li class="sales-return-active-li"><a href="{{ url('sales_return/create') }}"><i class="fa fa-plus-square-o "></i> <span>New Sales Return</span></a></li>
                    @endcan
                    @can('sales_return_view')
                    <li class="sales-return-list-active-li"><a href="{{ url('sales_return') }}"><i class="fa fa-list "></i> <span>Sales Returns List</span></a></li>
                    @endcan
                </ul>
            </li>
            @endcanany

            @canany(['customers_add', 'customers_view', 'import_customers'])
            <li class="customers-view-active-li customers-active-li import_customers-active-li treeview">
                <a href="#">
                    <i class="fa fa-group text-aqua"></i> <span>Customers</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    @can('customers_add')
                    <li class="customers-active-li"><a href="{{ url('customers/add') }}"><i class="fa fa-plus-square-o "></i> <span>New Customer</span></a></li>
                    @endcan
                    @can('customers_view')
                    <li class="customers-view-active-li"><a href="{{ url('customers') }}"><i class="fa fa-list "></i> <span>Customers List</span></a></li>
                    @endcan
                    @can('import_customers')
                    <li class="import_customers-active-li"><a href="{{ url('import/customers') }}"><i class="fa fa-arrow-circle-o-left "></i> <span>Import Customers</span></a></li>
                    @endcan
                </ul>
            </li>
            @endcanany

            @canany(['purchase_add', 'purchase_view', 'purchase_return_view'])
            <li class="purchase-list-active-li purchase-active-li purchase-returns-list-active-li purchase-returns-active-li treeview">
                <a href="#">
                    <i class="fa fa-th-large text-aqua"></i> <span>Purchase</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    @can('purchase_add')
                    <li class="purchase-active-li"><a href="{{ url('purchase/add') }}"><i class="fa fa-plus-square-o "></i> <span>New Purchase</span></a></li>
                    @endcan
                    @can('purchase_view')
                    <li class="purchase-list-active-li"><a href="{{ url('purchase') }}"><i class="fa fa-list "></i> <span>Purchase List</span></a></li>
                    @endcan
                    @can('purchase_return_view')
                    <li class="purchase-returns-active-li"><a href="{{ url('purchase_return/create') }}"><i class="fa fa-plus-square-o "></i> <span>New Purchase Return</span></a></li>
                    <li class="purchase-returns-list-active-li"><a href="{{ url('purchase_return') }}"><i class="fa fa-list "></i> <span>Purchase Returns List</span></a></li>
                    @endcan
                </ul>
            </li>
            @endcanany

            @canany(['suppliers_add', 'suppliers_view', 'import_suppliers'])
            <li class="suppliers-list-active-li suppliers-active-li import_suppliers-active-li treeview">
                <a href="#">
                    <i class="fa fa-user-plus text-aqua"></i> <span>Suppliers</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    @can('suppliers_add')
                    <li class="suppliers-active-li"><a href="{{ url('suppliers/add') }}"><i class="fa fa-plus-square-o "></i> <span>New Supplier</span></a></li>
                    @endcan
                    @can('suppliers_view')
                    <li class="suppliers-list-active-li"><a href="{{ url('suppliers') }}"><i class="fa fa-list "></i> <span>Suppliers List</span></a></li>
                    @endcan
                    @can('import_suppliers')
                    <li class="import_suppliers-active-li"><a href="{{ url('import/suppliers') }}"><i class="fa fa-arrow-circle-o-left "></i> <span>Import Suppliers</span></a></li>
                    @endcan
                </ul>
            </li>
            @endcanany

            @canany(['items_add', 'items_view', 'items_category_add', 'items_category_view', 'brand_add', 'brand_view', 'print_labels'])
            <li class="items-list-active-li items-active-li category-view-active-li category-active-li brand-active-li brand-view-active-li labels-active-li import_items-active-li treeview">
                <a href="#">
                    <i class="fa fa-cubes text-aqua"></i> <span>Items</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    @can('items_add')
                    <li class="items-active-li"><a href="{{ url('items/add') }}"><i class="fa fa-plus-square-o "></i> <span>New Item</span></a></li>
                    @endcan
                    @can('items_view')
                    <li class="items-list-active-li"><a href="{{ url('items') }}"><i class="fa fa-list "></i> <span>Items List</span></a></li>
                    @endcan
                    @can('items_category_add')
                    <li class="category-active-li"><a href="{{ url('category/add') }}"><i class="fa fa-plus-square-o "></i> <span>New Category</span></a></li>
                    @endcan
                    @can('items_category_view')
                    <li class="category-view-active-li"><a href="{{ url('category/view') }}"><i class="fa fa-list "></i> <span>Categories List</span></a></li>
                    @endcan
                    @can('brand_add')
                    <li class="brand-active-li"><a href="{{ url('brands/add') }}"><i class="fa fa-plus-square-o "></i> <span>New Brand</span></a></li>
                    @endcan
                    @can('brand_view')
                    <li class="brand-view-active-li"><a href="{{ url('brands/view') }}"><i class="fa fa-list "></i> <span>Brands List</span></a></li>
                    @endcan
                    @can('print_labels')
                    <li class="labels-active-li"><a href="{{ url('items/labels') }}"><i class="fa fa-barcode "></i> <span>Print Labels</span></a></li>
                    @endcan
                    @can('import_items')
                    <li class="import_items-active-li"><a href="{{ url('import/items') }}"><i class="fa fa-arrow-circle-o-left "></i> <span>Import Items</span></a></li>
                    @endcan
                </ul>
            </li>
            @endcanany

            @canany(['expense_add', 'expense_view', 'expense_category_add', 'expense_category_view'])
            <li class="expense-list-active-li expense-active-li expense-category-active-li expense-category-list-active-li treeview">
                <a href="#">
                    <i class="fa fa-minus-circle text-aqua"></i> <span>Expenses</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    @can('expense_add')
                    <li class="expense-active-li"><a href="{{ url('expense/add') }}"><i class="fa fa-plus-square-o "></i> <span>New Expense</span></a></li>
                    @endcan
                    @can('expense_view')
                    <li class="expense-list-active-li"><a href="{{ url('expense') }}"><i class="fa fa-list "></i> <span>Expenses List</span></a></li>
                    @endcan
                    @can('expense_category_add')
                    <li class="expense-category-active-li"><a href="{{ url('expense/category_add') }}"><i class="fa fa-plus-square-o "></i> <span>New Category</span></a></li>
                    @endcan
                    @can('expense_category_view')
                    <li class="expense-category-list-active-li "><a href="{{ url('expense/category') }}"><i class="fa fa-list "></i> <span>Categories List</span></a></li>
                    @endcan
                </ul>
            </li>
            @endcanany

            @canany(['places_add', 'places_view'])
            <li class="country-active-li city-list-active-li country-list-active-li state-active-li state-list-active-li city-active-li treeview">
                <a href="#">
                    <i class="fa fa-paper-plane-o text-aqua"></i> <span>Places</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    @can('places_add')
                    <li class="country-active-li"><a href="{{ url('country/add') }}"><i class="fa fa-plus-square-o "></i> <span>New Country</span></a></li>
                    @endcan
                    @can('places_view')
                    <li class="country-list-active-li "><a href="{{ url('country') }}"><i class="fa fa-list "></i> <span>Countries List</span></a></li>
                    @endcan
                    @can('places_add')
                    <li class="state-active-li"><a href="{{ url('state/add') }}"><i class="fa fa-plus-square-o "></i> <span>New State</span></a></li>
                    @endcan
                    @can('places_view')
                    <li class="state-list-active-li "><a href="{{ url('state') }}"><i class="fa fa-list "></i> <span>States List</span></a></li>
                    @endcan
                </ul>
            </li>
            @endcanany

            @canany(['item_purchase_report', 'sales_report', 'item_sales_report', 'purchase_report', 'purchase_return_report', 'expense_report', 'profit_report', 'stock_report', 'purchase_payments_report', 'sales_payments_report', 'expired_items_report'])
            <li class="report-sales-active-li report-sales-return-active-li report-purchase-active-li report-purchase-return-active-li report-expense-active-li report-profit-loss-active-li report-stock-active-li report-purchase-payments-active-li report-sales-item-active-li report-sales-payments-active-li report-expired-items-active-li report-purchase-item-active-li treeview">
                <a href="#">
                    <i class="fa fa-bar-chart text-aqua"></i> <span>Reports</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    @can('profit_report')
                    <li class="report-profit-loss-active-li"><a href="{{ url('reports/profit_loss') }}"><i class="fa fa-files-o "></i> <span>Profit & Loss Report</span></a></li>
                    @endcan
                    @can('purchase_report')
                    <li class="report-purchase-active-li"><a href="{{ url('reports/purchase') }}"><i class="fa fa-files-o "></i> <span>Purchase Report</span></a></li>
                    @endcan
                    @can('purchase_return_report')
                    <li class="report-purchase-return-active-li"><a href="{{ url('reports/purchase_return') }}"><i class="fa fa-files-o "></i> <span>Purchase Return Report</span></a></li>
                    @endcan
                    @can('purchase_payments_report')
                    <li class="report-purchase-payments-active-li"><a href="{{ url('reports/purchase_payments') }}"><i class="fa fa-files-o "></i> <span>Purchase Payments Report</span></a></li>
                    @endcan
                    @can('item_sales_report')
                    <li class="report-sales-item-active-li"><a href="{{ url('reports/item_sales') }}"><i class="fa fa-files-o "></i> <span>Item Sales Report</span></a></li>
                    @endcan
                    @can('item_purchase_report')
                    <li class="report-purchase-item-active-li"><a href="{{ url('reports/item_purchase') }}"><i class="fa fa-files-o "></i> <span>Item Purchase Report</span></a></li>
                    @endcan
                    @can('sales_report')
                    <li class="report-sales-active-li"><a href="{{ url('reports/sales') }}"><i class="fa fa-files-o "></i> <span>Sales Report</span></a></li>
                    @endcan
                    @can('sales_return_report')
                    <li class="report-sales-return-active-li"><a href="{{ url('reports/sales_return') }}"><i class="fa fa-files-o "></i> <span>Sales Return Report</span></a></li>
                    @endcan
                    @can('sales_payments_report')
                    <li class="report-sales-payments-active-li"><a href="{{ url('reports/sales_payments') }}"><i class="fa fa-files-o "></i> <span>Sales Payments Report</span></a></li>
                    @endcan
                    @can('stock_report')
                    <li class="report-stock-active-li"><a href="{{ url('reports/stock') }}"><i class="fa fa-files-o "></i> <span>Stock Report</span></a></li>
                    @endcan
                    @can('expense_report')
                    <li class="report-expense-active-li"><a href="{{ url('reports/expense') }}"><i class="fa fa-files-o "></i> <span>Expense Report</span></a></li>
                    @endcan
                    @can('expired_items_report')
                    <li class="report-expired-items-active-li"><a href="{{ url('reports/expired_items') }}"><i class="fa fa-files-o "></i> <span>Expired Items Report</span></a></li>
                    @endcan
                </ul>
            </li>
            @endcanany

            @canany(['users_add', 'users_view', 'roles_view'])
            <li class="users-view-active-li users-active-li roles-list-active-li role-active-li treeview">
                <a href="#">
                    <i class="fa fa-users text-aqua"></i> <span>Users</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    @can('users_add')
                    <li class="users-active-li"><a href="{{ url('users/') }}"><i class="fa fa-plus-square-o "></i> <span>New User</span></a></li>
                    @endcan
                    @can('users_view')
                    <li class="users-view-active-li"><a href="{{ url('users/view') }}"><i class="fa fa-list "></i> <span>Users List</span></a></li>
                    @endcan
                    @can('roles_view')
                    <li class="roles-list-active-li role-active-li"><a href="{{ url('roles/view') }}"><i class="fa fa-list "></i> <span>Roles List</span></a></li>
                    @endcan
                </ul>
            </li>
            @endcanany

            @canany(['send_sms', 'sms_template_view', 'sms_api_view'])
            <li class="sms-active-li sms-api-active-li sms-template-active-li sms-templates-list-active-li treeview">
                <a href="#">
                    <i class="fa fa-envelope text-aqua"></i> <span>SMS</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    @can('send_sms')
                    <li class="sms-active-li"><a href="{{ url('sms') }}"><i class="fa fa-envelope-o "></i> <span>Send SMS</span></a></li>
                    @endcan
                    @can('sms_template_view')
                    <li class="sms-templates-list-active-li sms-template-active-li"><a href="{{ url('templates/sms') }}"><i class="fa fa-list "></i> <span>SMS Templates</span></a></li>
                    @endcan
                    @can('sms_api_view')
                    <li class="sms-api-active-li"><a href="{{ url('sms/api') }}"><i class="fa fa-cube "></i> <span>SMS API</span></a></li>
                    @endcan
                </ul>
            </li>
            @endcanany

            <li class=" company-profile-active-li site-settings-active-li change-pass-active-li dbbackup-active-li warehouse-active-li warehouse-list-active-li tax-active-li currency-view-active-li currency-active-li database_updater-active-li tax-list-active-li units-list-active-li unit-active-li payment_types_list-active-li payment_types-active-li printers-list-active-li printers-active-li treeview">
                <a href="#">
                    <i class="fa fa-gears text-aqua"></i> <span>Settings</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    @can('company_edit')
                    <li class="company-profile-active-li"><a href="{{ url('company') }}"><i class="fa fa-suitcase "></i> <span>Company Profile</span></a></li>
                    @endcan
                    @can('site_edit')
                    <li class="site-settings-active-li"><a href="{{ url('site') }}"><i class="fa fa-shield  "></i> <span>Site Settings</span></a></li>
                    @endcan
                    @can('printers_view')
                    <li class="printers-list-active-li printers-active-li"><a href="{{ url('printers') }}"><i class="fa fa-print "></i> <span>Printers</span></a></li>
                    @endcan
                    @can('tax_view')
                    <li class="tax-active-li  tax-list-active-li"><a href="{{ url('tax') }}"><i class="fa fa-percent  "></i> <span>Tax List</span></a></li>
                    @endcan
                    @can('units_view')
                    <li class="units-list-active-li unit-active-li"><a href="{{ url('units/') }}"><i class="fa fa-list "></i> <span>Units List</span></a></li>
                    @endcan
                    @can('payment_types_view')
                    <li class="payment_types_list-active-li payment_types-active-li"><a href="{{ url('payment_types/') }}"><i class="fa fa-list "></i> <span>Payment Types List</span></a></li>
                    @endcan
                    @can('currency_view')
                    <li class="currency-view-active-li currency-active-li"><a href="{{ url('currency/view') }}"><i class="fa fa-gg "></i> <span>Currency List</span></a></li>
                    @endcan
                    <li class="change-pass-active-li"><a href="{{ url('users/password_reset') }}"><i class="fa fa-lock "></i> <span>Change Password</span></a></li>
                    @can('database_backup')
                    <li class="dbbackup-active-li"><a href="{{ url('users/dbbackup') }}"><i class="fa fa-database "></i> <span>Database Backup</span></a></li>
                    @endcan
                </ul>
            </li>
        </ul>
    </section>
</aside>
