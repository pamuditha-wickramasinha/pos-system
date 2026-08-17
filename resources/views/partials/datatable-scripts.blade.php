<script src="{{ $theme_link }}plugins/DataTables-1.10.18/js/jquery.dataTables.min.js"></script>
<script src="{{ $theme_link }}plugins/DataTables-1.10.18/js/dataTables.bootstrap.min.js"></script>
<script src="{{ $theme_link }}plugins/DataTables-1.10.18/extensions/FixedHeader-3.1.4/js/dataTables.fixedHeader.min.js"></script>
<script src="{{ $theme_link }}plugins/DataTables-1.10.18/extensions/Responsive-2.2.2/js/dataTables.responsive.min.js"></script>
<script src="{{ $theme_link }}plugins/DataTables-1.10.18/extensions/Responsive-2.2.2/js/responsive.bootstrap.min.js"></script>
<script src="{{ $theme_link }}plugins/DataTables-1.10.18/extensions/JSZip-2.5.0/jszip.min.js"></script>
<script src="{{ $theme_link }}plugins/DataTables-1.10.18/extensions/pdfmake-0.1.36/pdfmake.min.js"></script>
<script src="{{ $theme_link }}plugins/DataTables-1.10.18/extensions/pdfmake-0.1.36/vfs_fonts.js"></script>
<script src="{{ $theme_link }}plugins/DataTables-1.10.18/extensions/Buttons-1.5.4/js/dataTables.buttons.min.js"></script>
<script src="{{ $theme_link }}plugins/DataTables-1.10.18/extensions/Buttons-1.5.4/js/buttons.flash.min.js"></script>
<script src="{{ $theme_link }}plugins/DataTables-1.10.18/extensions/Buttons-1.5.4/js/buttons.html5.min.js"></script>
<script src="{{ $theme_link }}plugins/DataTables-1.10.18/extensions/Buttons-1.5.4/js/buttons.print.min.js"></script>
<script src="{{ $theme_link }}plugins/DataTables-1.10.18/extensions/Buttons-1.5.4/js/buttons.colVis.min.js"></script>
<script src="{{ $theme_link }}plugins/DataTables-1.10.18/extensions/Buttons-1.5.4/js/buttons.bootstrap.min.js"></script>
<script src="{{ $theme_link }}plugins/slimScroll/jquery.slimscroll.min.js"></script>
<script type="text/javascript">
function show_delete_btn() {
    var group_check_count = $(".group_check").prop("checked") ? 1: 0;
    var check_count = $('#example2').find('input[type=checkbox]:checked').length-parseInt(group_check_count);
    if(parseInt(check_count)>0){
        $(".delete_btn").removeClass('hidden').show();
    }
    else{
        $(".delete_btn").addClass('hidden').hide();
    }
    if($('#example2 > tbody').find('.checkbox').length == check_count){
        $(".group_check").prop("checked",true).iCheck('update');
    }
    else{
        $(".group_check").prop("checked",false).iCheck('update');
    }
}
$('.group_check').on('ifChanged', function(event) {
    if(event.target.checked){
        $(".column_checkbox").prop("checked",true).iCheck('update');
    }
    else{
        $(".column_checkbox").prop("checked",false).iCheck('update');
    }
    show_delete_btn();
});
function call_code(){
    $('.column_checkbox').on('ifChanged', function(event) {
        show_delete_btn();
    });
}
</script>
