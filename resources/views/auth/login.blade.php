<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $SITE_TITLE }} | Log in</title>
    <link rel='shortcut icon' href='{{ $theme_link }}images/favicon.ico' />
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="{{ $theme_link }}bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ $theme_link }}css/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ $theme_link }}css/ionicons-2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="{{ $theme_link }}dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="{{ $theme_link }}plugins/iCheck/square/blue.css">
</head>

<body class="hold-transition login-page"
    style="height:0;background-repeat: no-repeat;background: url('{{ asset('theme/images/pos-background.jpeg') }}') no-repeat center center fixed">
    <div class="login-box">
        <div class="login-logo">
            <a href="#"><b>
                    <img src="{{ site()->settings()->logo ? asset('storage/'.site()->settings()->logo) : asset('theme/images/noimage.png') }}"
                        width="60%" height="70px">
                </b></a>
        </div>
        <div class="login-box-body">
            <p class="login-box-msg">Sign in to start your session</p>
            <div class="text-danger tex-center">{{ session('failed') }}</div>
            <div class="text-success tex-center">{{ session('success') }}</div>

            <form action="{{ route('login.verify') }}" method="post" id="login">
                @csrf
                <div class="form-group has-feedback">
                    <input type="text" class="form-control" placeholder="Username" id="username" name="username"
                        autofocus>
                    <span class="glyphicon glyphicon-user form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="password" class="form-control" placeholder="Password" id="pass" name="pass">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <div class="row">
                    <div class="col-xs-8">
                        <div class="checkbox icheck">
                            <label><input type="checkbox"> Remember Me</label>
                        </div>
                    </div>
                    <div class="col-xs-4">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button>
                    </div>
                </div>
            </form>
            <a href="{{ route('login.forgot') }}">I forgot my password</a><br>
            <div class="row">
                <div class="col-md-12 text-right">
                    <!-- <p style='font-style: italic;'>Version {{ app_version() }}</p> -->
                </div>
            </div>
        </div>
    </div>

    <script src="{{ $theme_link }}plugins/jQuery/jquery-2.2.3.min.js"></script>
    <script src="{{ $theme_link }}bootstrap/js/bootstrap.min.js"></script>
    <script src="{{ $theme_link }}plugins/iCheck/icheck.min.js"></script>
    <script>
    $(function() {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%'
        });
    });
    </script>
</body>

</html>