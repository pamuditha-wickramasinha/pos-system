<script type="text/javascript" src="{{ $theme_link }}plugins/tableExporter/libs/js-xlsx/xlsx.core.min.js"></script>
<script type="text/javascript" src="{{ $theme_link }}plugins/tableExporter/libs/jsPDF/jspdf.min.js"></script>
<script type="text/javascript" src="{{ $theme_link }}plugins/tableExporter/libs/jsPDF-AutoTable/jspdf.plugin.autotable.js"></script>
<script type="text/javascript" src="{{ $theme_link }}plugins/tableExporter/tableExport.min.js"></script>
<script>
function downloadPdf(tableId){ $('#'+tableId).tableExport({type:'pdf',escape:'false'}); }
function downloadExcel(tableId){ $('#'+tableId).tableExport({type:'xlsx',escape:'false'}); }
$(".downloadPdf").on("click",function(){ downloadPdf($(this).attr("data-table-id")); });
$(".downloadExcel").on("click",function(){ downloadExcel($(this).attr("data-table-id")); });
</script>
