@extends('layouts.app')
@php($activeMenu = 'labels')

@push('styles')
<link rel="stylesheet" href="{{ $theme_link }}plugins/autocomplete/autocomplete.css">
@endpush

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>Print Barcode Labels</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <form id="labels-form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
            <input type="hidden" value="1" id="hidden_rowcount" name="hidden_rowcount">
            <input type="hidden" value="0" id="hidden_update_rowid" name="hidden_update_rowid">

            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-info">
                        <div class="box-body">
                            <div class="col-md-8 col-md-offset-2">
                                <div class="input-group">
                                    <span class="input-group-addon" title="Select Items"><i class="fa fa-barcode"></i></span>
                                    <input type="text" class="form-control" placeholder="Item name/Barcode/Itemcode" id="item_search">
                                </div>
                            </div>
                            <br><br>
                            <table class="table table-hover table-bordered col-md-8 col-md-offset-2" style="width:67%" id="sales_table">
                                <thead class="custom_thead">
                                <tr class="bg-primary">
                                    <th rowspan="2" style="width:45%">Item Name</th>
                                    <th rowspan="2" style="width:45%">Quantity</th>
                                    <th rowspan="2" style="width:10%">Action</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="col-sm-4 control-label">Total Labels</label>
                                            <div class="col-sm-4"><label class="control-label total_quantity text-success">0</label></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer col-sm-12">
                            <center>
                                <div class="col-md-3 col-md-offset-3">
                                    <button type="button" id="preview" class="btn bg-maroon btn-block btn-flat btn-lg payments_modal" title="Preview Labels">Preview</button>
                                </div>
                                <div class="col-sm-3">
                                    <a href="{{ url('dashboard') }}"><button type="button" class="btn bg-gray btn-block btn-flat btn-lg" title="Go Dashboard">Close</button></a>
                                </div>
                            </center>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-info">
                        <div class="box-footer col-sm-12">
                            <span id="preview_data" class="col-md-offset-1 col-sm-10" style="overflow: auto;"></span>
                            <div class="col-sm-1">
                                <input type="button" class="btn btn-primary btn-flat" id="print" value="Print">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ $theme_link }}plugins/autocomplete/autocomplete.js"></script>
<script src="{{ $theme_link }}js/labels.js"></script>
<script>
$('#item_search').keypress(function (e) {
    if (e.which == 13) { $("#item_search").autocomplete('search'); }
});
function final_total(){
    var rowcount=$("#hidden_rowcount").val();
    var total_quantity=0;
    for(i=1;i<=rowcount;i++){
        if(document.getElementById("td_data_"+i+"_1")){
            if($("#td_data_"+i+"_1").val()!=null && $("#td_data_"+i+"_1").val()!=''){
                total_quantity +=parseInt($("#td_data_"+i+"_3").val().trim());
            }
        }
    }
    $(".total_quantity").html(total_quantity);
}
function removerow(id){
    $("#row_"+id).remove();
    final_total();
}
$("#print").on("click",function(event) { PrintMe("preview_data"); });
function PrintMe(DivID) {
    var disp_setting="toolbar=yes,location=no,directories=yes,menubar=yes,scrollbars=yes,width=800, height=600, left=100, top=25";
    var content_vlue = document.getElementById(DivID).innerHTML;
    var docprint=window.open("","",disp_setting);
    docprint.document.open();
    docprint.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
    docprint.document.write('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">');
    docprint.document.write('<head><title>Labels</title>');
    docprint.document.write('<style type="text/css">body{ margin:0px;font-family:verdana,Arial;color:#000;font-family:Verdana, Geneva, sans-serif; font-size:12px;} a{color:#000;text-decoration:none;} </style>');
    docprint.document.write('</head><body onLoad="self.print()">');
    docprint.document.write(content_vlue);
    docprint.document.write('</body></html>');
    docprint.document.close();
    docprint.focus();
}
</script>
@endpush
