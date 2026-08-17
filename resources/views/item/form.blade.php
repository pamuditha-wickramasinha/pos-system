@extends('layouts.app')
@php($activeMenu = 'items')

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>Add/Update Items</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i>Home</a></li>
        <li><a href="{{ route('items.index') }}">Items List</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-md-12">
            <div class="box box-info">
                <form class="form" id="items-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <input type="hidden" id="initial_item_code" value="{{ $item_code }}">
                    <input type="hidden" name="q_id" id="q_id" value="{{ $q_id ?? '' }}">
                    <div class="box-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="item_code">Item Code<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="item_code" name="item_code" value="{{ $item_code }}">
                                <span id="item_code_msg" style="display:none" class="text-danger"></span>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="custom_barcode">Barcode</label>
                                <input type="text" class="form-control" id="custom_barcode" name="custom_barcode" value="{{ $custom_barcode ?? '' }}">
                                <span id="custom_barcode_msg" style="display:none" class="text-danger"></span>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="item_sing_name">Item Name(Siglish)<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="item_sing_name" name="item_sing_name" value="{{ $item_sing_name ?? '' }}">
                                <span id="item_sing_name_msg" style="display:none" class="text-danger"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="item_name">Item Name<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="item_name" name="item_name" value="{{ $item_name ?? '' }}">
                                <span id="item_name_msg" style="display:none" class="text-danger"></span>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="brand_id">Brand</label>
                                <div class="input-group">
                                    <select class="form-control select2" id="brand_id" name="brand_id" style="width: 100%;">
                                        <option value="">-Select-</option>
                                        @foreach (\App\Models\Brand::where('status', true)->get() as $b)
                                            <option @selected(($brand_id ?? null) == $b->id) value="{{ $b->id }}">{{ $b->brand_name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-addon pointer" data-toggle="modal" data-target="#brand_modal" title="Add Brand"><i class="fa fa-plus-square-o text-primary fa-lg"></i></span>
                                </div>
                                <span id="brand_id_msg" style="display:none" class="text-danger"></span>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="category_id">Category <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-control select2" id="category_id" name="category_id" style="width: 100%;">
                                        <option value="">-Select-</option>
                                        @foreach (\App\Models\Category::where('status', true)->get() as $c)
                                            <option @selected(($category_id ?? null) == $c->id) value="{{ $c->id }}">{{ $c->category_name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-addon pointer" data-toggle="modal" data-target="#category_modal" title="Add Category"><i class="fa fa-plus-square-o text-primary fa-lg"></i></span>
                                </div>
                                <span id="category_id_msg" style="display:none" class="text-danger"></span>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="unit_id">Unit<span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-control select2" id="unit_id" name="unit_id" style="width: 100%;">
                                        <option value="">-Select-</option>
                                        @foreach (\App\Models\Unit::where('status', true)->get() as $u)
                                            <option @selected(($unit_id ?? null) == $u->id) value="{{ $u->id }}">{{ $u->unit_name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-addon pointer" data-toggle="modal" data-target="#unit_modal" title="Add Unit"><i class="fa fa-plus-square-o text-primary fa-lg"></i></span>
                                </div>
                                <span id="unit_id_msg" style="display:none" class="text-danger"></span>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="sku">SKU (Stock Keeping Unit)</label>
                                <input type="text" class="form-control" id="sku" name="sku" value="{{ $sku ?? '' }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="hsn">HSN (Harmonized System of Nomenclature)</label>
                                <input type="text" class="form-control" id="hsn" name="hsn" value="{{ $hsn ?? '' }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="alert_qty">Minimum Qty</label>
                                <input type="number" class="form-control" id="alert_qty" name="alert_qty" min="0" value="{{ $alert_qty ?? 1 }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="expire_date">Expire Date</label>
                                <div class="input-group date">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    <input type="text" class="form-control pull-right datepicker" id="expire_date" name="expire_date" value="{{ $expire_date ?? '' }}">
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description">{{ $description ?? '' }}</textarea>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="price">Price<span class="text-danger">*</span></label>
                                <input type="text" class="form-control only_currency" id="price" name="price" placeholder="Price of Item without Tax" value="{{ $price ?? '' }}">
                            </div>
                            <div class="form-group col-md-4 {{ tax_disable_class() }}">
                                <label for="tax_id">Tax<span class="text-danger">*</span></label>
                                <select class="form-control" id="tax_id" name="tax_id" style="width: 100%;">
                                    @foreach (\App\Models\Tax::where('status', true)->orderByDesc('undelete_bit')->get() as $t)
                                        <option @selected(($tax_id ?? null) == $t->id) data-tax="{{ $t->tax }}" value="{{ $t->id }}">{{ $t->tax_name }}({{ $t->tax }}%)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="purchase_price">Purchase Price<span class="text-danger">*</span></label>
                                <input type="text" class="form-control only_currency" id="purchase_price" name="purchase_price" placeholder="Total Price with Tax Amount" value="{{ $purchase_price ?? '' }}" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4 {{ tax_disable_class() }}">
                                <label for="tax_type">Tax Type<span class="text-danger">*</span></label>
                                <select class="form-control" id="tax_type" name="tax_type" style="width: 100%;">
                                    <option value="Exclusive">Exclusive</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="profit_margin">Profit Margin(%)</label>
                                <input type="text" class="form-control only_currency" id="profit_margin" name="profit_margin" placeholder="Profit in %" value="{{ $profit_margin ?? '' }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="sales_price">Sales Price<span class="text-danger">*</span></label>
                                <input type="text" class="form-control only_currency" id="sales_price" name="sales_price" placeholder="Sales Price" value="{{ $sales_price ?? '' }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="final_price">Final Price<span class="text-danger">*</span></label>
                                <input type="text" class="form-control only_currency" id="final_price" name="final_price" placeholder="Final Price" value="{{ $final_price ?? '' }}" readonly>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="discount_type">Discount Type</label>
                                <select class="form-control" id="discount_type" name="discount_type" style="width: 100%;">
                                    <option value="Fixed">Fixed({{ currency() }})</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="discount">Discount</label>
                                <input type="text" class="form-control only_currency" id="discount" name="discount" value="{{ $discount ?? '' }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="wholesale_discount">Wholesale Discount</label>
                                <input type="text" class="form-control only_currency" id="wholesale_discount" name="wholesale_discount" value="{{ $wholesale_discount ?? '' }}">
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="current_opening_stock">Current Opening Stock</label>
                                <input type="text" class="form-control only_currency" id="current_opening_stock" name="current_opening_stock" readonly value="{{ $stock ?? 0 }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="new_opening_stock">Adjust Stock</label>
                                <input type="text" class="form-control" id="new_opening_stock" name="new_opening_stock" placeholder="-/+">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="adjustment_note">Adjustment Note</label>
                                <textarea class="form-control" id="adjustment_note" name="adjustment_note"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="col-sm-8 col-sm-offset-2 text-center">
                            @if (! empty($item_name))
                                @php($btn_id = 'update') @php($btn_name = 'Update')
                            @else
                                @php($btn_id = 'save') @php($btn_name = 'Save')
                            @endif
                            <div class="col-md-3 col-md-offset-3">
                                <button type="button" id="{{ $btn_id }}" class="btn btn-block btn-success">{{ $btn_name }}</button>
                            </div>
                            <div class="col-sm-3">
                                <a href="{{ url('dashboard') }}"><button type="button" class="col-sm-3 btn btn-block btn-warning close_btn">Close</button></a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="box">
                <div class="box-header"><h3 class="box-title text-blue">Opening Stock Adjustment Records</h3></div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr class="bg-gray"><th>#</th><th>Entry Date</th><th>Stock</th><th>Note</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                        @php($entries = isset($q_id) ? \App\Models\StockEntry::where('item_id', $q_id)->get() : collect())
                        @forelse ($entries as $i => $e)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ show_date($e->entry_date) }}</td>
                                <td>{{ $e->qty }}</td>
                                <td>{{ $e->note }}</td>
                                <td><i class="fa fa-trash text-red pointer" onclick="delete_stock_entry({{ $e->id }})"> Delete</i></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-bold">No Previous Stock Entry Found!!</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('modals.brand')
    @include('modals.category')
    @include('modals.unit')
</section>
@endsection

@push('scripts')
<script src="{{ $theme_link }}js/items.js"></script>
<script src="{{ $theme_link }}js/modals.js"></script>
<script>
$("#discount_type").val('{{ $discount_type ?? 'Fixed' }}');
</script>
@endpush
