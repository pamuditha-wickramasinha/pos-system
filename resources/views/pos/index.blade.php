<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $page_title }}</title>
    <link rel='shortcut icon' href='{{ $theme_link }}images/favicon.ico' />
    <meta content="width=device-width, initial-scale=1, viewport-fit=cover" name="viewport">
    <link rel="stylesheet" href="{{ $theme_link }}bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ $theme_link }}css/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ $theme_link }}css/ionicons-2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="{{ $theme_link }}plugins/select2/select2.min.css">
    <link rel="stylesheet" href="{{ $theme_link }}dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="{{ $theme_link }}dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" href="{{ $theme_link }}plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="{{ $theme_link }}plugins/datepicker/datepicker3.css">
    <link rel="stylesheet" href="{{ $theme_link }}toastr/toastr.css">
    <link rel="stylesheet" href="{{ $theme_link }}dist/css/custom.css">
    <link rel="stylesheet" href="{{ $theme_link }}plugins/autocomplete/autocomplete.css">
    <link rel="stylesheet" href="{{ $theme_link }}plugins/pace/pace.min.css">
    <link rel="stylesheet" href="{{ $theme_link }}plugins/iCheck/square/blue.css">
    <link rel="stylesheet" href="{{ $theme_link }}css/pos-ui.css">
    <style type="text/css">
    .select2-container--default .select2-selection--single {
        border-radius: 0px;
    }

    .table-striped>tbody>tr:nth-of-type(2n+1) {
        background-color: #ede3e3;
    }

    .table-striped>tbody>tr {
        background-color: #ddc8c8;
    }

    .tot_qty,
    .tot_amt,
    .tot_disc,
    .tot_grand {
        font-size: 19px;
        color: #023763;
    }

    .pointer {
        cursor: pointer;
    }

    .navbar-nav>.user-menu>.dropdown-width-lg {
        width: 350px;
    }

    .header-custom {
        background-image: -webkit-gradient(linear, left top, right top, from(#20b9ae), to(#006fd6));
        color: white;
    }

    .border-custom-bottom {
        border-bottom: 1px solid;
        padding-top: 10px;
        padding-bottom: 5px;
    }

    .custom-font-size {
        font-size: 22px;
    }

    .min_width {
        min-width: 70px;
    }
    </style>
    <script type="text/javascript">
    var theme_skin = (typeof(Storage) !== "undefined") ? localStorage.getItem('skin') : 'skin-blue';
    theme_skin = (theme_skin == '' || theme_skin == null) ? 'skin-blue' : theme_skin;
    var sidebar_collapse = (typeof(Storage) !== "undefined") ? localStorage.getItem('collapse') : 'skin-blue';
    </script>
    <script src="{{ $theme_link }}plugins/jQuery/jquery-2.2.3.min.js"></script>
</head>

<body class="hold-transition skin-blue layout-top-nav pos-page">
    <script type="text/javascript">
    if (theme_skin != 'skin-blue') {
        $("body").addClass(theme_skin);
        $("body").removeClass('skin-blue');
    }
    if (sidebar_collapse == 'true') {
        $("body").addClass('sidebar-collapse');
    }
    </script>
    <div class="wrapper">
        <header class="main-header">
            <nav class="navbar navbar-static-top">
                <div class="container">
                    <div class="navbar-header">
                        <a href="{{ url('dashboard') }}" class="navbar-brand" title="Go to Dashboard!"><b
                                class="hidden-xs">{{ $SITE_TITLE }}</b><b class="hidden-lg">POS</b></a>
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                            data-target="#navbar-collapse"><i class="fa fa-bars"></i></button>
                    </div>
                    <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
                        <ul class="nav navbar-nav">
                            @can('sales_view')
                            <li><a href="{{ route('sales.index') }}" title="View Sales List!"><i
                                        class="fa fa-list text-yellow"></i> <span>Sales List</span></a></li>
                            @endcan
                            @can('sales_add')
                            <li><a href="{{ route('pos.index') }}" title="Create New POS Invoice"><i
                                        class="fa fa-calculator text-yellow"></i> <span>New Invoice</span></a></li>
                            @endcan
                            @can('items_view')
                            <li><a href="{{ route('items.index') }}" title="View Items List"><i
                                        class="fa fa-cubes text-yellow"></i> <span>Items List</span></a></li>
                            @endcan
                        </ul>
                    </div>
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown"
                                    title="Click To View Hold Invoices">
                                    <span>Hold List</span>
                                    <span class="label label-danger hold_invoice_list_count">{{ $holdCount }}</span>
                                </a>
                                <ul class="dropdown-menu dropdown-width-lg">
                                    <li class="user-body">
                                        <div class="row">
                                            <div class="col-xs-12 text-center"
                                                style="max-height:300px;overflow-y: scroll;">
                                                <table class="table table-bordered" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Date</th>
                                                            <th>Ref.ID</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="hold_invoice_list">{!! $holdListHtml !!}</tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            <li class="hidden-xs" id="fullscreen"><a title="Fullscreen On/Off"><i
                                        class="fa fa-tv text-white"></i></a></li>
                            <li class="text-center"><a title="Dashboard" href="{{ url('dashboard') }}"><i
                                        class="fa fa-dashboard text-yellow"></i><b class="hidden-xs">Dashboard</b></a>
                            </li>
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <img src="{{ $theme_link }}dist/img/avatar5.png" class="user-image"
                                        alt="User Image">
                                    <span class="hidden-xs">{{ ucfirst(auth()->user()->username) }}</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="user-header">
                                        <img src="{{ $theme_link }}dist/img/avatar5.png" class="img-circle"
                                            alt="User Image">
                                        <p>{{ ucfirst(auth()->user()->username) }}<small>Year {{ date('Y') }}</small>
                                        </p>
                                    </li>
                                    <li class="user-footer">
                                        <div class="pull-left"><a href="{{ route('users.edit', auth()->id()) }}"
                                                class="btn btn-default btn-flat">Profile</a></div>
                                        <div class="pull-right">
                                            <form id="logout-form" method="POST" action="{{ route('logout') }}">@csrf
                                            </form>
                                            <a href="#"
                                                onclick="event.preventDefault();document.getElementById('logout-form').submit();"
                                                class="btn btn-default btn-flat">Sign out</a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>

        <div class="content-wrapper">
            @include('partials.flashdata')
            @include('modals.customer')
            @include('modals.sales-item')
            @include('modals.quick-add-item')

            <section class="content pos-shell">
                <form class="form-horizontal" id="pos-form">
                    <div class="pos-top">
                        <div class="pos-left">
                            <div class="box box-primary">
                                <div class="box-header with-border" style="padding-bottom: 0px;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="col-md-4">
                                                <h3 class="box-title text-primary"><i
                                                        class="fa fa-shopping-cart text-aqua"></i> Sales Invoice</h3>
                                            </div>
                                            @if($salesId)
                                            @can('sales_add')
                                            <div class="col-md-4 pull-right">
                                                <a href="{{ route('pos.index') }}"
                                                    class="btn btn-primary pull-right">New Invoice</a>
                                            </div>
                                            @endcan
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @csrf
                                <input type="hidden" value="0" id="hidden_rowcount" name="hidden_rowcount">
                                <input type="hidden" value="" id="hidden_invoice_id" name="hidden_invoice_id">
                                <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                                <input type="hidden" value="" id="temp_customer_id" name="temp_customer_id">

                                @include('pos.payments-multi-modal')

                                <div class="modal fade" id="discount-modal">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title">Set Discount</h4>
                                            </div>
                                            <div class="modal-body">
                                                @php($discountDefault =
                                                \App\Models\SiteSetting::query()->value('sales_discount'))
                                                @php($discountDefault = $discountDefault == 0 ? '' : $discountDefault)
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="box-body">
                                                            <div class="form-group">
                                                                <label for="discount_input">Discount</label>
                                                                <input type="text" class="form-control"
                                                                    id="discount_input" name="discount_input"
                                                                    value="{{ $discountDefault }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="box-body">
                                                            <div class="form-group">
                                                                <label for="discount_type">Discount Type</label>
                                                                <select class="form-control" id="discount_type"
                                                                    name="discount_type">
                                                                    <option value="in_percentage">Per%</option>
                                                                    <option value="in_fixed">Fixed</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-warning"
                                                    data-dismiss="modal">Close</button>
                                                <button type="button"
                                                    class="btn btn-primary discount_update">Update</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="box-body">
                                    @if($salesId)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="input-group date">
                                                <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                                <input type="text" class="form-control pull-right datepicker"
                                                    id="sales_date" name="sales_date" readonly value="">
                                            </div>
                                            <span id="sales_date_msg" style="display:none" class="text-danger"></span>
                                        </div>
                                    </div>
                                    <br>
                                    @endif

                                    <div class="pos-search-row">
                                        <div class="pos-field">
                                            <div class="input-group">
                                                <span class="input-group-addon" title="Customer"><i
                                                        class="fa fa-user"></i></span>
                                                <select class="form-control select2" id="customer_id" name="customer_id"
                                                    style="width: 100%;"></select>
                                                <span class="input-group-addon pointer" data-toggle="modal"
                                                    data-target="#customer-modal" title="New Customer?"><i
                                                        class="fa fa-user-plus text-primary fa-lg"></i></span>
                                            </div>
                                            <span class="customer_points text-success" style="display: none;"></span>
                                        </div>
                                        <div class="pos-field">
                                            <div class="input-group">
                                                <span class="input-group-addon" title="Select Items"><i
                                                        class="fa fa-barcode"></i></span>
                                                <input type="text" class="form-control"
                                                    placeholder="Item name/Barcode/Itemcode [Ctrl+Shift+S]"
                                                    id="item_search">
                                                <span class="input-group-addon pointer" data-toggle="modal"
                                                    data-target="#quick_add_item" title="Quick Item?"><i
                                                        class="fa fa-plus text-primary fa-lg"></i></span>
                                            </div>
                                        </div>
                                        <div class="pos-field">
                                            <div class="input-group">
                                                <span class="input-group-addon" title="Sell Type"><i
                                                        class="fa fa-money"></i></span>
                                                <select class="form-control select2" id="sell_type" name="sell_type"
                                                    style="width: 100%;">
                                                    <option value="retail">Retail</option>
                                                    <option value="wholesale">Wholesale</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pos-items-wrap">
                                                    <table
                                                        class="table table-condensed table-bordered table-striped table-responsive items_table">
                                                        <thead class="bg-primary">
                                                            <th width="30%">Item Name</th>
                                                            <th width="10%">Stock</th>
                                                            <th width="25%">Quantity</th>
                                                            <th width="15%">Price</th>
                                                            <th width="10%">Discount</th>
                                                            <th style="display: none" width="10%"
                                                                class="{{ tax_disable_class() }}">Tax</th>
                                                            <th width="15%">Subtotal</th>
                                                            <th width="5%"><i class="fa fa-close"></i></th>
                                                        </thead>
                                                        <tbody id="pos-form-tbody"
                                                            style="font-size: 16px;font-weight: bold;overflow: scroll;">
                                                        </tbody>
                                                        <tfoot></tfoot>
                                                    </table>
                                    </div>

                                    <div class="pos-charges">
                                        <label for="other_charges">Other Charges<label
                                                class="text-danger">*</label></label>
                                        <div class="pos-charges-field">
                                            <input type="text" class="form-control text-right" id="other_charges"
                                                name="other_charges" placeholder="0.00" inputmode="decimal" value="">
                                            <span id="other_charges_msg" style="display:none" class="text-danger"></span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="pos-right">
                            <div class="box box-primary pos-actions-box">
                                <div class="box-footer bg-gray">
                                    <div class="pos-totals">
                                        <div><label>Quantity:</label><span class="text-bold tot_qty"></span></div>
                                        <div><label>Total Amount:</label>{!!
                                            currency('<span class="tot_amt text-bold"></span>') !!}</div>
                                        <div><label>Total Discount:<a
                                                    class="fa fa-pencil-square-o cursor-pointer" data-toggle="modal"
                                                    data-target="#discount-modal"></a></label>{!!
                                            currency('<span class="tot_disc text-bold"></span>') !!}</div>
                                        <div><label>Grand Total:</label>{!!
                                            currency('<span class="tot_grand text-bold"></span>') !!}</div>
                                    </div>

                                    @if($salesId)
                                    <input type="hidden" name="sales_id" id="sales_id" value="{{ $salesId }}">
                                    @endif

                                    <div class="pos-actions">
                                        <button type="button" id="hold_invoice" class="btn bg-maroon btn-flat"
                                            title="Hold Invoice [Ctrl+Shift+H]">
                                            <span class="pos-act-ic"><i class="fa fa-hand-paper-o"></i></span>
                                            <span class="pos-act-lb">Hold</span>
                                            <span class="pos-act-sc">Ctrl+Shift+H</span>
                                        </button>
                                        <button type="button" class="btn btn-primary btn-flat show_payments_modal"
                                            title="Multiple Payments [Ctrl+Shift+M]">
                                            <span class="pos-act-ic"><i class="fa fa-credit-card"></i></span>
                                            <span class="pos-act-lb">Multiple</span>
                                            <span class="pos-act-sc">Ctrl+Shift+M</span>
                                        </button>
                                        <button type="button" id="show_cash_modal" class="btn btn-success btn-flat shift_c"
                                            title="By Cash & Save [Ctrl+Shift+C]">
                                            <span class="pos-act-ic"><i class="fa fa-money"></i></span>
                                            <span class="pos-act-lb">Cash</span>
                                            <span class="pos-act-sc">Ctrl+Shift+C</span>
                                        </button>
                                        <button type="button" id="pay_all" class="btn bg-purple btn-flat shift_a"
                                            title="By Cash & Save [Ctrl+Shift+A]">
                                            <span class="pos-act-ic"><i class="fa fa-money"></i></span>
                                            <span class="pos-act-lb">Pay All</span>
                                            <span class="pos-act-sc">Ctrl+Shift+A</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            </section>
        </div>

        @include('partials.footer')
        @include('pos.keypad')
        <div class="control-sidebar-bg"></div>
    </div>

    <audio id="failed">
        <source src="{{ $theme_link }}sound/failed.mp3" type="audio/mpeg">
        <source src="{{ $theme_link }}sound/failed.ogg" type="audio/ogg">
    </audio>
    <audio id="success">
        <source src="{{ $theme_link }}sound/success.mp3" type="audio/mpeg">
        <source src="{{ $theme_link }}sound/success.ogg" type="audio/ogg">
    </audio>

    <script src="{{ $theme_link }}bootstrap/js/bootstrap.min.js"></script>
    <script>
    var AdminLTEOptions = {
        sidebarExpandOnHover: true,
        navbarMenuHeight: "200px",
        animationSpeed: 250
    };
    </script>
    <script src="{{ $theme_link }}dist/js/app.js"></script>
    <script src="{{ $theme_link }}plugins/fastclick/fastclick.js"></script>
    <script src="{{ $theme_link }}plugins/select2/select2.full.min.js"></script>
    <script src="{{ $theme_link }}dist/js/demo.js"></script>
    <script src="{{ $theme_link }}toastr/toastr.js"></script>
    <script src="{{ $theme_link }}toastr/toastr_custom.js"></script>
    <script src="{{ $theme_link }}plugins/daterangepicker/moment.min.js"></script>
    <script src="{{ $theme_link }}plugins/daterangepicker/daterangepicker.js"></script>
    <script src="{{ $theme_link }}plugins/datepicker/bootstrap-datepicker.js"></script>
    <script src="{{ $theme_link }}js/sweetalert.min.js"></script>
    <script src="{{ $theme_link }}plugins/shortcuts/shortcuts.js"></script>
    <script src="{{ $theme_link }}js/special_char_check.js"></script>
    <script src="{{ $theme_link }}js/custom.js"></script>
    <script src="{{ $theme_link }}plugins/autocomplete/autocomplete.js"></script>
    <script src="{{ $theme_link }}plugins/pace/pace.min.js"></script>
    <script src="{{ $theme_link }}plugins/iCheck/icheck.min.js"></script>
    <script src="{{ $theme_link }}js/fullscreen.js"></script>
    <script src="{{ $theme_link }}js/modals.js"></script>
    <script src="{{ $theme_link }}js/printer-bridge.js"></script>
    <script src="{{ $theme_link }}js/pos.js"></script>
    <script src="{{ $theme_link }}js/pos-keypad.js"></script>
    <script src="{{ $theme_link }}js/ajaxselect/customer_select_ajax.js"></script>
    <script src="{{ $theme_link }}dist/js/bootstrap3-typeahead.min.js"></script>
    <script>
    $(function() {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%'
        });
    });
    $(function($) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
    });

    function round_off(input = 0) {
        @if(is_enabled_round_off())
        return Math.round(input);
        @else
        return input;
        @endif
    }

    function tax_disabled() {
        @if(is_tax_disabled())
        return true;
        @else
        return false;
        @endif
    }
    $(document).ready(function() {
        setTimeout(function() {
            $(".alert-dismissable").fadeOut(1000, function() {});
        }, 10000);
    });
    $(document).ajaxStart(function() {
        Pace.restart();
    });
    </script>

    <script>
    function getCustomerSelectionId() {
        return '#customer_id';
    }

    $(document).ready(function() {
        var customer_id = "{{ $customerId ?? '' }}";
        autoLoadFirstCustomer(customer_id);
    });

    $("#other_charges").keyup(function(event) {
        final_total();
    });

    function addrow(id = '', item_obj = '') {
        var sell_type = $("#sell_type").val()
        var item_id = (item_obj == '') ? $('#div_' + id).attr('data-item-id') : item_obj.item_id;
        var item_check = check_same_item(item_id);
        if (!item_check) {
            return false;
        }
        var rowcount = $("#hidden_rowcount").val();

        var item_name = (item_obj == '') ? $('#div_' + id).attr('data-item-name') : item_obj.item_name;
        var stock = (item_obj == '') ? $('#div_' + id).attr('data-item-available-qty') : item_obj.stock;
        stock = (parseFloat(stock)).toFixed(2);

        var tax_type = (item_obj == '') ? $('#div_' + id).attr('data-item-tax-type') : item_obj.tax_type;
        var tax_id = (item_obj == '') ? $('#div_' + id).attr('data-item-tax-id') : item_obj.tax_id;
        var tax_value = (item_obj == '') ? $('#div_' + id).attr('data-item-tax-value') : item_obj.tax;
        var tax_name = (item_obj == '') ? $('#div_' + id).attr('data-item-tax-name') : item_obj.tax_name;
        var tax_amt = (item_obj == '') ? $('#div_' + id).attr('data-item-tax-amt') : item_obj.item_tax_amt;
        var purchase_price = (item_obj == '') ? $('#div_' + id).attr('data-purchase_price') : item_obj.purchase_price;
        var discount_type = (item_obj == '') ? $('#div_' + id).attr('data-discount_type') : item_obj.discount_type;
        var discount = (item_obj == '') ? $('#div_' + id).attr('data-discount') : item_obj.discount;
        var wholesale_discount = (item_obj == '') ? $('#div_' + id).attr('data-wholesale_discount') : item_obj
            .wholesale_discount;

        var item_cost = (item_obj == '') ? $('#div_' + id).attr('data-item-cost') : item_obj.purchase_price;
        var sales_price = (item_obj == '') ? $('#div_' + id).attr('data-item-sales-price') : item_obj.sales_price;
        var sales_price_temp = sales_price;
        sales_price = (parseFloat(sales_price)).toFixed(2);

        var real_discount = discount;
        if (sell_type == 'wholesale') {
            real_discount = wholesale_discount
        }

        if (stock > 1 || stock < 0) {
            qty = 1;
        } else {
            qty = stock;
        }

        var quantity =
            '<div class="input-group input-group-sm"><span class="input-group-btn"><button onclick="decrement_qty(' +
            item_id + ',' + rowcount +
            ')" type="button" class="btn btn-default btn-flat"><i class="fa fa-minus text-danger"></i></button></span>';
        quantity += '<input type="text" inputmode="decimal" value="' + qty +
            '" class="form-control no-padding text-center min_width" onchange="item_qty_input(' + item_id + ',' +
            rowcount + ')" id="item_qty_' + item_id + '" name="item_qty_' + item_id + '">';
        quantity += '<span class="input-group-btn"><button onclick="increment_qty(' + item_id + ',' + rowcount +
            ')" type="button" class="btn btn-default btn-flat"><i class="fa fa-plus text-success"></i></button></span></div>';
        var sub_total = (parseFloat(1) * parseFloat(sales_price)).toFixed(2);
        var remove_btn =
            '<a class="fa fa-fw fa-trash-o text-red" style="cursor: pointer;font-size: 20px;" onclick="removerow(' +
            rowcount + ')" title="Delete Item?"></a>';

        var str = ' <tr id="row_' + rowcount + '" data-row="0" data-item-id=' + item_id + '>';
        str += '<td id="td_' + rowcount + '_0"><a class="pointer" id="td_data_' + rowcount + '_0">' + item_name +
            '</a></td>';
        str += '<td id="td_' + rowcount + '_1">' + stock + '</td>';
        str += '<td id="td_' + rowcount + '_2">' + quantity + '</td>';

        info = '<input id="sales_price_' + rowcount + '" onblur="set_to_original(' + rowcount + ',' + item_cost +
            ')" onkeyup="update_price(' + rowcount + ',' + item_cost + ')" name="sales_price_' + rowcount +
            '" type="text" inputmode="decimal" class="form-control no-padding min_width" value="' + sales_price + '">';
        str += '<td id="td_' + rowcount + '_3" class="text-right">' + info + '</td>';

        info = '<input data-toggle="tooltip" title="Click to Change" onclick="show_sales_item_modal(' + rowcount +
            ')" id="item_discount_' + rowcount + '" readonly name="item_discount_' + rowcount +
            '" type="text" class="form-control no-padding min_width pointer" value="0">';
        str += '<td id="td_' + rowcount + '_6" class="text-right">' + info + '</td>';

        str += '<td style="display: none" id="td_' + rowcount +
            '_11" class="{{ tax_disable_class() }}"><input data-toggle="tooltip" title="Click to Change" id="td_data_' +
            rowcount + '_11" onclick="show_sales_item_modal(' + rowcount + ')" name="td_data_' + rowcount +
            '_11" type="text" class="form-control no-padding pointer min_width" readonly value="' + tax_amt + '"></td>';

        str += '<td id="td_' + rowcount +
            '_4" class="text-right"><input data-toggle="tooltip" title="Total" id="td_data_' + rowcount +
            '_4" name="td_data_' + rowcount +
            '_4" type="text" class="form-control no-padding pointer" readonly value="' + sub_total + '"></td>';
        str += '<td id="td_' + rowcount + '_5">' + remove_btn + '</td>';

        str += '<input type="hidden" name="tr_item_id_' + rowcount + '" id="tr_item_id_' + rowcount + '" value="' +
            item_id + '">';
        str += '<input type="hidden" id="tr_sales_price_temp_' + rowcount + '" name="tr_sales_price_temp_' + rowcount +
            '" value="' + sales_price_temp + '">';
        str += '<input type="hidden" id="tr_tax_type_' + rowcount + '" name="tr_tax_type_' + rowcount + '" value="' +
            tax_type + '">';
        str += '<input type="hidden" id="tr_tax_id_' + rowcount + '" name="tr_tax_id_' + rowcount + '" value="' +
            tax_id + '">';
        str += '<input type="hidden" id="tr_tax_value_' + rowcount + '" name="tr_tax_value_' + rowcount + '" value="' +
            tax_value + '">';
        str += '<input type="hidden" id="description_' + rowcount + '" name="description_' + rowcount + '" value="">';
        str += '<input id="item_discount_type_' + rowcount + '" name="item_discount_type_' + rowcount +
            '" type="hidden" value="' + discount_type + '">';
        str += '<input id="item_discount_input_' + rowcount + '" name="item_discount_input_' + rowcount +
            '" type="hidden" value="' + real_discount + '">';
        str += '<input type="hidden" id="purchase_price_' + rowcount + '" name="purchase_price_' + rowcount +
            '" value="' + purchase_price + '">';
        str += '</tr>';

        $('#pos-form-tbody').append(str);
        $("#hidden_rowcount").val(parseFloat($("#hidden_rowcount").val()) + 1);
        failed.currentTime = 0;
        failed.play();
        make_subtotal(item_id, rowcount);
    }

    function update_price(row_id, item_cost) {
        make_subtotal($("#tr_item_id_" + row_id).val(), row_id);
    }

    function set_to_original(row_id, item_cost) {
        return true;
    }

    function increment_qty(item_id, rowcount) {
        var item_qty = $("#item_qty_" + item_id).val();
        new_item_qty = parseFloat(item_qty) + 1;
        $("#item_qty_" + item_id).val(parseFloat(new_item_qty).toFixed(2));
        make_subtotal(item_id, rowcount);
    }

    function decrement_qty(item_id, rowcount) {
        var item_qty = parseFloat($("#item_qty_" + item_id).val());
        item_qty = isNaN(item_qty) ? 0 : item_qty;
        if (item_qty < 1) {
            $("#item_qty_" + item_id).val((item_qty).toFixed(2));
            toastr["warning"]("Minimum Stock!");
            return;
        }
        if (item_qty <= 1) {
            $("#item_qty_" + item_id).val((1).toFixed(2));
            toastr["warning"]("Minimum Stock!");
            return;
        }
        $("#item_qty_" + item_id).val((parseFloat(item_qty) - 1).toFixed(2));
        make_subtotal(item_id, rowcount);
    }

    function item_qty_input(item_id, rowcount) {
        var item_qty = $("#item_qty_" + item_id).val();
        var stock = $("#td_" + rowcount + "_1").html();
        if (parseFloat(item_qty) > parseFloat(stock)) {
            $("#item_qty_" + item_id).val(stock);
            toastr["warning"]("Oops! You have only " + stock + " items in Stock");
        }
        if (item_qty == 0) {
            $("#item_qty_" + item_id).val(1);
            toastr["warning"]("You must have atlease one Quantity");
        }
        make_subtotal(item_id, rowcount);
    }

    function removerow(id) {
        $("#row_" + id).remove();
        failed.currentTime = 0;
        failed.play();
        final_total();
    }

    function make_subtotal(item_id, rowcount) {
        set_tax_value(rowcount);
        var tax_type = $("#tr_tax_type_" + rowcount).val();
        var tax_amount = $("#td_data_" + rowcount + "_11").val();
        var sales_price = $("#sales_price_" + rowcount).val();
        var item_qty = $("#item_qty_" + item_id).val();
        var tot_sales_price = parseFloat(item_qty) * parseFloat(sales_price);
        var subtotal = parseFloat(tot_sales_price);
        var discount_amt = $("#item_discount_" + rowcount).val();
        subtotal = (tax_type == 'Inclusive') ? subtotal : parseFloat(subtotal) + parseFloat(tax_amount);
        subtotal -= parseFloat(discount_amt);
        $("#td_data_" + rowcount + "_4").val(parseFloat(subtotal).toFixed(2));
        final_total();
    }

    function calulate_discount(discount_input, discount_type, total) {
        if (discount_input == "" || discount_input == null) {
            return 0;
        }
        if (discount_type == 'in_percentage') {
            return parseFloat((total * discount_input) / 100);
        } else {
            return parseFloat(discount_input);
        }
    }

    function final_total() {
        var total = 0;
        var item_qty = 0;
        var rowcount = $("#hidden_rowcount").val();
        var discount_input = $("#discount_input").val();
        var discount_type = $("#discount_type").val();
        var other_charges = parseFloat($("#other_charges").val());
        other_charges = (isNaN(other_charges)) ? parseFloat(0) : other_charges;

        if ($(".items_table tr").length > 1) {
            for (i = 0; i < rowcount; i++) {
                if (document.getElementById('tr_item_id_' + i)) {
                    item_id = $("#tr_item_id_" + i).val();
                    total = parseFloat(total) + +parseFloat($("#td_data_" + i + "_4").val()).toFixed(2);
                    item_qty = parseFloat(item_qty) + +parseFloat($("#item_qty_" + item_id).val()).toFixed(2);
                }
            }
        }
        total += other_charges;
        total = round_off(total);
        var discount_amt = 0;
        if (total > 0) {
            discount_amt = calulate_discount(discount_input, discount_type, total);
        }
        set_total(item_qty, total, discount_amt, total - discount_amt);
    }

    function set_total(tot_qty = 0, tot_amt = 0, tot_disc = 0, tot_grand = 0) {
        $(".tot_qty").html(tot_qty);
        $(".tot_amt").html((round_off(tot_amt).toFixed(2)));
        $(".tot_disc").html((round_off(tot_disc).toFixed(2)));
        $(".tot_grand").html((round_off(tot_grand)).toFixed(2));
    }

    function adjust_payments() {
        var total = 0;
        var item_qty = 0;
        var rowcount = $("#hidden_rowcount").val();
        var discount_input = $("#discount_input").val();
        var discount_type = $("#discount_type").val();
        var other_charges = parseFloat($("#other_charges").val());
        other_charges = (isNaN(other_charges)) ? parseFloat(0) : other_charges;

        if ($(".items_table tr").length > 1) {
            for (i = 0; i < rowcount; i++) {
                if (document.getElementById('tr_item_id_' + i)) {
                    total = parseFloat(total) + +parseFloat($("#td_data_" + i + "_4").val()).toFixed(2);
                    item_id = $("#tr_item_id_" + i).val();
                    item_qty = parseFloat(item_qty) + +parseFloat($("#item_qty_" + item_id).val()).toFixed(2);
                }
            }
        }
        total += other_charges;
        total = round_off(total);

        var payments_row = get_id_value("payment_row_count");
        var paid_amount = parseFloat(0);
        for (var i = 1; i <= payments_row; i++) {
            if (document.getElementById("amount_" + i)) {
                var amount = parseFloat(get_id_value("amount_" + i));
                amount = isNaN(amount) ? 0 : amount;
                paid_amount += amount;
            }
        }

        var discount_amt = calulate_discount(discount_input, discount_type, total);

        var change_return = 0;
        var balance = total - discount_amt - paid_amount;
        if (balance < 0) {
            change_return = Math.abs(parseFloat(balance));
            balance = 0;
        }
        balance = round_off(balance);

        $(".sales_div_tot_qty").html(item_qty);
        $(".sales_div_tot_amt").html((round_off(total)).toFixed(2));
        $(".sales_div_tot_discount").html((parseFloat(round_off(discount_amt))).toFixed(2));
        $(".sales_div_tot_payble").html((parseFloat(round_off(total - discount_amt))).toFixed(2));
        $(".sales_div_tot_paid").html((round_off(paid_amount)).toFixed(2));
        $(".sales_div_tot_balance").html((parseFloat(round_off(balance))).toFixed(2));
        $(".sales_div_change_return").html((change_return).toFixed(2));
    }

    function check_same_item(item_id) {
        if ($(".items_table tr").length > 1) {
            var rowcount = $("#hidden_rowcount").val();
            for (i = 0; i <= rowcount; i++) {
                if ($("#tr_item_id_" + i).val() == item_id) {
                    increment_qty(item_id, i);
                    failed.currentTime = 0;
                    failed.play();
                    return false;
                }
            }
        }
        return true;
    }

    function reset_tooltip() {
        $('[data-toggle="tooltip"]').tooltip("destroy");
        $('[data-toggle="tooltip"]').tooltip();
    }

    // The product tile list was removed from this screen - items are added by
    // scanning or searching instead. pos.js and quick_model.js still call this
    // after every save, hold-recall and quick-add, so it stays as a no-op.
    function get_details(last_id = '', show_only_searched = false) {
        return;
    }

    function get_item_details(item_id) {
        $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
        $.post("{{ route('pos.get_item_details') }}", {
            item_id: item_id
        }, function(result) {
            var item = parse_ajax_json(result);
            var obj = {};
            obj['item_id'] = item['id'];
            obj['item_name'] = item['item_name'];
            obj['stock'] = item['stock'];
            obj['sales_price'] = item['sales_price'];
            obj['purchase_price'] = item['purchase_price'];
            obj['tax_id'] = item['tax_id'];
            obj['tax_type'] = item['tax_type'];
            obj['tax'] = item['tax'];
            obj['tax_name'] = item['tax_name'];
            obj['item_tax_amt'] = item['item_tax_amt'];
            obj['discount_type'] = item['discount_type'];
            obj['discount'] = item['discount'];
            addrow(null, obj);
            $(".overlay").remove();
        });
    }

    function show_sales_item_modal(row_id) {
        $('#sales_item').modal('toggle');
        var item_name = $("#td_data_" + row_id + "_0").html();
        var tax_type = $("#tr_tax_type_" + row_id).val();
        var tax_id = $("#tr_tax_id_" + row_id).val();
        var description = $("#description_" + row_id).val();
        var item_discount_input = $("#item_discount_input_" + row_id).val();
        var item_discount_type = $("#item_discount_type_" + row_id).val();
        $("#item_discount_input").val(item_discount_input);
        $("#item_discount_type").val(item_discount_type).select2();
        $("#popup_item_name").html(item_name);
        $("#popup_tax_type").val(tax_type).select2();
        $("#popup_tax_id").val(tax_id).select2();
        $("#popup_row_id").val(row_id);
        $("#popup_description").val(description);
    }

    function set_info() {
        var row_id = $("#popup_row_id").val();
        var tax_type = $("#popup_tax_type").val();
        var tax_id = $("#popup_tax_id").val();
        var description = $("#popup_description").val();
        var tax = parseFloat($('option:selected', "#popup_tax_id").attr('data-tax'));
        var item_discount_input = $("#item_discount_input").val();
        var item_discount_type = $("#item_discount_type").val();

        $("#item_discount_input_" + row_id).val(item_discount_input);
        $("#item_discount_type_" + row_id).val(item_discount_type);
        $("#tr_tax_type_" + row_id).val(tax_type);
        $("#tr_tax_id_" + row_id).val(tax_id);
        $("#description_" + row_id).val(description);
        $("#tr_tax_value_" + row_id).val(tax);

        var item_id = $("#tr_item_id_" + row_id).val();
        make_subtotal(item_id, row_id);
        $('#sales_item').modal('toggle');
    }

    function set_tax_value(row_id) {
        var tax_type = $("#tr_tax_type_" + row_id).val();
        var tax = $("#tr_tax_value_" + row_id).val();
        var item_id = $("#tr_item_id_" + row_id).val();
        var qty = ($("#item_qty_" + item_id).val());
        qty = (isNaN(qty)) ? 0 : qty;
        var sales_price = parseFloat($("#sales_price_" + row_id).val());
        sales_price = (isNaN(sales_price)) ? 0 : sales_price;
        sales_price = sales_price * qty;

        var item_discount_type = $("#item_discount_type_" + row_id).val();
        var item_discount_input = parseFloat($("#item_discount_input_" + row_id).val());
        item_discount_input = (isNaN(item_discount_input)) ? 0 : item_discount_input;
        var discount_amt = (item_discount_type == 'Percentage') ? ((sales_price) * item_discount_input) / 100 : (
            item_discount_input * qty);
        sales_price -= parseFloat(discount_amt);

        var tax_amount = (tax_type == 'Inclusive') ? calculate_inclusive(sales_price, tax) : calculate_exclusive(
            sales_price, tax);
        $("#item_discount_" + row_id).val(discount_amt);
        $("#td_data_" + row_id + "_11").val(tax_amount);
    }

    $(document).ready(function() {
        // The invoice table is sized by pos-ui.css against the viewport, so it
        // keeps the totals and payment buttons on screen at every width.

        set_total();

        $(".discount_update").on("click", function() {
            final_total();
            $('#discount-modal').modal('toggle');
        });

        @if($salesId)
        $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
        $.get("{{ route('pos.fetch_sales', $salesId) }}", {}, function(result) {
            result = result.split("<<<###>>>");
            $('#pos-form-tbody').append(result[0]);
            $('#discount_input').val(result[1]);
            $('#discount_type').val(result[2]);
            $('#temp_customer_id').val(result[3]);
            $('#other_charges').val(result[4]);
            $('#sales_date').val(result[5]);
            $("#hidden_rowcount").val(parseFloat($(".items_table tr").length) - 1);
            $(".overlay").remove();
            final_total();
            adjust_payments();
        });
        $("#hold_invoice,#show_cash_modal").attr('disabled', true).removeAttr('id');
        @endif

        $("#item_search").focus();
    });

    // When a search finds nothing, Quick Add opens on its own - no Enter needed.
    // It waits for a short pause in typing first, so that a partially typed name
    // (still mid-word, and legitimately matching nothing yet) does not pop the
    // modal open and steal the cursor. Enter skips the wait.
    var QUICK_ADD_IDLE_MS = 800;
    var quick_add_timer = null;
    var open_quick_add_now = false;

    function cancel_quick_add() {
        clearTimeout(quick_add_timer);
        quick_add_timer = null;
    }

    // Clearing the box with .val('') is not enough. jQuery UI remembers the last
    // term it searched for and skips the next search while the box still "holds"
    // that text, so scanning the same barcode a second time did nothing at all
    // until the page was reloaded. Wipe the remembered term with the value, and
    // put the cursor back in the box so the next scan lands there.
    function reset_item_search() {
        var $search = $("#item_search");
        var widget = $search.data('ui-autocomplete');

        cancel_quick_add();
        open_quick_add_now = false;

        $search.val('');

        if (widget) {
            widget.term = '';
            widget.selectedItem = null;
            $search.autocomplete('close');
        }

        $search.focus();
    }

    $('#item_search').keypress(function(e) {
        if (e.which == 13) {
            cancel_quick_add();
            open_quick_add_now = true;
            $("#item_search").autocomplete('search');
        }
    });
    $("#item_search").bind("paste", function(e) {
        $("#item_search").autocomplete('search');
    });

    // Still typing means any pending "not found" decision is stale.
    $('#item_search').on('input', cancel_quick_add);

    // Open Quick Add New Item pre-filled with whatever the cashier searched for.
    function open_quick_add(term) {
        if ($('#quick_add_item').hasClass('in')) {
            return;
        }

        // term = $.trim(term || '');
        $('#item_search').autocomplete('close');

        $('#custom_barcode').val('');
        $('#item_name').val('වෙනත්');
        $('#price').val(0);

        // Digits only is almost certainly a scanned barcode; anything else is a name.
        var is_barcode = term !== '' && /^\d+$/.test(term);

        // if (is_barcode) {
        //     $('#custom_barcode').val(term);
        // } else if (term !== '') {
        //     $('#item_name').val(term);
        // }

        $('#quick_add_item').modal('show');

        // Land the cursor on whichever field still needs filling in.
        $('#quick_add_item').one('shown.bs.modal', function() {
            $(is_barcode ? '#item_name' : '#price').focus().select();
        });
    }

    $("#item_search").autocomplete({
        source: function(data, cb) {
            $.ajax({
                autoFocus: true,
                url: $("#base_url").val() + 'items/get_json_items_details',
                method: 'GET',
                dataType: 'json',
                showHintOnFocus: true,
                autoSelect: true,
                selectInitial: true,
                data: {
                    name: data.term
                },
                success: function(res) {
                    var immediate = open_quick_add_now;
                    open_quick_add_now = false;
                    cancel_quick_add();

                    if (res.length) {
                        cb($.map(res, function(el) {
                            return {
                                label: el.item_code + '--[Qty:' + el.stock +
                                    '] --' + el.label,
                                value: '',
                                id: el.id,
                                item_name: el.value,
                                stock: el.stock
                            };
                        }));
                        return;
                    }

                    // Nothing matched. Show that straight away, then offer Quick Add.
                    cb([{
                        label: 'No Records Found ',
                        value: ''
                    }]);

                    if (immediate) {
                        open_quick_add(data.term);
                        return;
                    }

                    quick_add_timer = setTimeout(function() {
                        // Only act if the cashier stopped typing and the box still
                        // holds the same text this search was run for.
                        if ($.trim($('#item_search').val()) === $.trim(data.term) && $
                            .trim(data.term) !== '') {
                            open_quick_add(data.term);
                        }
                    }, QUICK_ADD_IDLE_MS);
                }
            });
        },
        response: function(e, ui) {
            if (ui.content.length == 1) {
                $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                $(this).autocomplete("close");
            }
        },
        search: function(e, ui) {},
        select: function(e, ui) {
            if (typeof ui.content != 'undefined') {
                if (isNaN(ui.content[0].id)) {
                    return;
                }
                var item_id = ui.content[0].id;
            } else {
                // The "No Records Found" row carries no id - treat picking it as
                // "add this item now" rather than a dead end.
                if (!ui.item || isNaN(ui.item.id)) {
                    open_quick_add($("#item_search").val());
                    return false;
                }
                var item_id = ui.item.id;
            }
            get_item_details(item_id);
            reset_item_search();
        },
    });

    $('.datepicker').datepicker({
        autoclose: true,
        format: '{{ $VIEW_DATE }}',
        todayHighlight: true
    });

    shortcut.add("Ctrl+Shift+m", function(e) {
        e.preventDefault();
        $(".show_payments_modal").trigger('click');
    }, {
        type: 'keydown',
        propagate: true,
        target: document
    });
    shortcut.add("F12", function(e) {
        e.preventDefault();
        $(".shift_c").trigger('click');
    }, {
        type: 'keydown',
        propagate: true,
        target: document
    });
    shortcut.add("F4", function(e) {
        e.preventDefault();
        $(".save-print").trigger('click');
    }, {
        type: 'keydown',
        propagate: true,
        target: document
    });
    shortcut.add("Ctrl+Shift+h", function(e) {
        e.preventDefault();
        $("#hold_invoice").trigger('click');
    }, {
        type: 'keydown',
        propagate: true,
        target: document
    });
    shortcut.add("Ctrl+Shift+c", function(e) {
        e.preventDefault();
        $(".shift_c").trigger('click');
    }, {
        type: 'keydown',
        propagate: true,
        target: document
    });
    shortcut.add("Ctrl+Shift+a", function(e) {
        e.preventDefault();
        $(".shift_a").trigger('click');
    }, {
        type: 'keydown',
        propagate: true,
        target: document
    });
    shortcut.add("Ctrl+Shift+s", function(e) {
        e.preventDefault();
        $("#item_search").focus();
    }, {
        type: 'keydown',
        propagate: true,
        target: document
    });
    </script>
</body>

</html>