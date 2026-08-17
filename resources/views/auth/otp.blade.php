<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $SITE_TITLE }} | Verify OTP</title>
    <link rel='shortcut icon' href='{{ $theme_link }}images/favicon.ico' />
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="{{ $theme_link }}bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ $theme_link }}css/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ $theme_link }}dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="{{ $theme_link }}plugins/iCheck/square/blue.css">
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-box-body">
            <p class="login-box-msg">Enter the OTP sent to your email</p>
            <div class="text-danger tex-center">{{ session('failed') }}</div>
            <div class="text-success tex-center">{{ session('success') }}</div>

            <form action="{{ route('login.verify-otp') }}" method="post">
                @csrf
                <div class="form-group has-feedback">
                    <input type="email" class="form-control" placeholder="Email" name="email" value="{{ old('email', session('otp_email')) }}" autofocus>
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="text" class="form-control" placeholder="OTP" name="otp">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">Verify OTP</button>
                    </div>
                </div>
            </form>
            <br>
            <a href="{{ route('login') }}">Back to Sign In</a>
        </div>
    </div>
    <script src="{{ $theme_link }}plugins/jQuery/jquery-2.2.3.min.js"></script>
    <script src="{{ $theme_link }}bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
