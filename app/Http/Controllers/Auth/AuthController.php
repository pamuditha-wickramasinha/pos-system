<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'pass' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route('login')->with('failed', 'Please enter username & password!');
        }

        $user = $this->attemptLogin($request->string('username'), $request->string('pass'));

        if (! $user) {
            return redirect()->route('login')->with('failed', 'Invalid username & password.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Welcome '.ucfirst($user->username).' !');
    }

    protected function attemptLogin(string $username, string $password): ?User
    {
        $user = User::where('username', $username)->where('status', true)->first();

        if (! $user) {
            return null;
        }

        if (Hash::check($password, $user->password)) {
            return $user;
        }

        if ($user->legacy_password && hash_equals($user->legacy_password, md5($password))) {
            $user->forceFill([
                'password' => Hash::make($password),
                'legacy_password' => null,
            ])->save();

            return $user;
        }

        return null;
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showForgotPassword(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return redirect()->route('login.forgot')->with('failed', 'Invalid Email!');
        }

        $email = $request->string('email');
        $user = User::where('email', $email)->where('status', true)->first();

        if (! $user) {
            return redirect()->route('login.forgot')->with('failed', 'This Email ID not Exist in Our Records!');
        }

        $otp = random_int(1000, 9999);

        try {
            Mail::raw(
                "Hello User,\n\nYou are requested for Password Change,\nPlease enter {$otp} as a OTP.\n\nNote: Don't share this OTP with anyone.\nThank you",
                function ($message) use ($email, $otp) {
                    $message->to($email)->subject("OTP for Password Change | OTP: {$otp}");
                }
            );
        } catch (\Throwable) {
            return redirect()->route('login.forgot')->with('failed', 'Failed to Send Message.Try again!');
        }

        $request->session()->put('otp_email', $email);
        $request->session()->put('otp_code', $otp);

        return redirect()->route('login.otp')->with('success', 'OTP has been sent to your email ID! (Check Inbox/Spam Box)');
    }

    public function showOtp(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.otp');
    }

    public function verifyOtp(Request $request): View|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required',
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route('login.otp')->with('failed', 'Invalid OTP!');
        }

        $otp = $request->string('otp');
        $email = $request->string('email');

        if ((string) $request->session()->get('otp_email') === (string) $email
            && (string) $request->session()->get('otp_code') === (string) $otp) {
            return view('auth.change-password', ['email' => $email, 'otp' => $otp]);
        }

        return redirect()->route('login.otp')->with('failed', 'Invalid OTP!!');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required',
            'email' => 'required',
            'password' => 'required',
            'cpassword' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route('login.otp')->with('failed', 'Please Enter Correct Passwords!');
        }

        $otp = $request->string('otp');
        $email = $request->string('email');
        $password = $request->string('password');
        $cpassword = $request->string('cpassword');

        $otpMatches = (string) $request->session()->get('otp_email') === (string) $email
            && (string) $request->session()->get('otp_code') === (string) $otp;

        if ($password->exactly($cpassword) && $otpMatches) {
            $user = User::where('email', $email)->where('status', true)->first();

            if ($user) {
                $user->forceFill([
                    'password' => Hash::make((string) $password),
                    'legacy_password' => null,
                ])->save();

                $request->session()->forget(['otp_email', 'otp_code']);

                return redirect()->route('login')->with('success', 'Password Changed Successfully!');
            }
        }

        return redirect()->route('login.otp')->with('failed', 'Please Enter Correct Passwords!');
    }
}
