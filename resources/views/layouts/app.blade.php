<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $page_title ?? $SITE_TITLE }}</title>
    <link rel='shortcut icon' href='{{ $theme_link }}images/favicon.ico' />
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
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
    <link rel="stylesheet" href="{{ $theme_link }}plugins/iCheck/square/orange.css">
    @stack('styles')
    <script type="text/javascript">
    var theme_skin = (typeof (Storage) !== "undefined") ? localStorage.getItem('skin') : 'skin-blue';
    theme_skin = (theme_skin=='' || theme_skin==null) ? 'skin-blue' : theme_skin;
    var sidebar_collapse = (typeof (Storage) !== "undefined") ? localStorage.getItem('collapse') : 'skin-blue';
    </script>
    <script src="{{ $theme_link }}plugins/jQuery/jquery-2.2.3.min.js"></script>
</head>
<body class="hold-transition skin-blue sidebar-mini fixed">
    <div class="wrapper">
        @include('partials.sidebar')

        <div class="content-wrapper">
            @yield('content')
        </div>

        @include('partials.footer')
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
        animationSpeed: 250,
    };
    </script>
    <script src="{{ $theme_link }}plugins/slimScroll/jquery.slimscroll.min.js"></script>
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
    <script>
    $(function () {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-orange',
            radioClass: 'iradio_square-orange',
            increaseArea: '10%'
        });
    });
    </script>
    <script type="text/javascript"> $(".select2").select2(); </script>
    <script type="text/javascript">
    $('.datepicker').datepicker({
        autoclose: true,
        format: '{{ $VIEW_DATE }}',
        todayHighlight: true
    });
    </script>
    <script>
    $(function () {
        $('.daterange-btn').daterangepicker(
            {
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: moment().subtract(29, 'days'),
                endDate: moment()
            },
            function (start, end) {
                $('.daterange-btn span').html(start.format('{{ strtoupper($VIEW_DATE) }}') + ' - ' + end.format('{{ strtoupper($VIEW_DATE) }}'))
            }
        );
    });
    function get_start_date(input_id){
        return $('#'+input_id).data('daterangepicker').startDate.format('{{ strtoupper($VIEW_DATE) }}');
    }
    function get_end_date(input_id){
        return $('#'+input_id).data('daterangepicker').endDate.format('{{ strtoupper($VIEW_DATE) }}');
    }
    </script>
    <script type="text/javascript">
    $(document).ready(function(){ $('[data-toggle="popover"]').popover(); });
    </script>
    <script type="text/javascript">
    $(document).ajaxStart(function() { Pace.restart(); });
    </script>
    <script type="text/javascript">
    $(document).ready(function () { setTimeout(function() {$( ".alert-dismissable" ).fadeOut( 1000, function() {});}, 10000); });
    </script>
    <script type="text/javascript">
    function round_off(input=0){
        @if(is_enabled_round_off())
            return Math.round(input);
        @else
            return input;
        @endif
    }
    </script>
    <script>
    function tax_disabled(){
        @if(is_tax_disabled())
            return true;
        @else
            return false;
        @endif
    }
    </script>
    <script type="text/javascript">
    $(function($) {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
    });
    </script>
    @stack('scripts')
    @isset($activeMenu)
    <script>
    $(".{{ $activeMenu }}-active-li").addClass("active");
    </script>
    @endisset
</body>
</html>
