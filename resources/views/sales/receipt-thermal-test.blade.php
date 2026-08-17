{{-- Test page for the "Test Print" button. See sales/receipt-thermal.blade.php. --}}
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @font-face {
        font-family: 'ReceiptSinhala';
        src: url('font.ttf') format('truetype');
        font-weight: normal;
    }
    html, body { margin: 0; padding: 0; background: #fff; }
    body {
        width: {{ $widthPx }}px;
        font-family: 'ReceiptSinhala', arial, sans-serif;
        font-size: {{ $base }}px;
        line-height: 1.5;
        color: #000;
        text-align: center;
        -webkit-font-smoothing: none;
    }
    .sheet { padding: {{ $pad }}px; }
    .title { font-size: {{ $base + 8 }}px; font-weight: bold; margin-bottom: 6px; }
    .sample { font-size: {{ $base + 2 }}px; font-weight: bold; margin: 8px 0; }
</style>
</head>
<body>
<div class="sheet">
    <div class="title">Test Print</div>
    {{ $printer->name }}<br>
    {{ now()->format('Y-m-d H:i:s') }}<br>
    <div class="sample">රණතුංග ස්ටෝස් — සීනි විස්කිරිඤ්ඤා</div>
    If the Sinhala line above is shaped<br>
    correctly, printing is set up right.
</div>
</body>
</html>
