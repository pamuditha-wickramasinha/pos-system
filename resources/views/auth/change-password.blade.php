<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $SITE_TITLE }} | Change Password</title>
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
            <p class="login-box-msg">Set a new password</p>
            <div class="text-danger tex-center">{{ session('failed') }}</div>

            <form action="{{ route('login.change-password') }}" method="post">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <input type="hidden" name="otp" value="{{ $otp }}">
                <div class="form-group has-feedback">
                    <input type="password" class="form-control" placeholder="New Password" name="password" autofocus>
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="password" class="form-control" placeholder="Confirm Password" name="cpassword">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">Change Password</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script src="{{ $theme_link }}plugins/jQuery/jquery-2.2.3.min.js"></script>
    <script src="{{ $theme_link }}bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
