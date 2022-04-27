<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class CustomerResetCtrl extends Controller
{
    protected static $customers = 'customers';

    //消費者重設密碼
    public function forgotPassword(Request $request)
    {
        return view('auth.forgot-password');
    }

    //發送郵件
    public function sendResetPwMail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $broker = Password::broker(self::$customers);;
        $status = $broker->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    //重設密碼頁
    public function resetPassword(Request $request, $token = 'token=')
    {
        $arr = explode("=", $token);
        return view('auth.reset-password', ['request' => $request, 'token' => $arr[0]]);
    }

    public function resetPasswordStore(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:4|confirmed',
        ]);

        $broker = Password::broker(self::$customers);;
        $status = $broker->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();

                $user->setRememberToken(Str::random(60));

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('customer.login-reset-status')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    //重設結果頁
    public function loginResetStatus(Request $request)
    {
        return view('cms.customerLoginResetStatus', [
            'status' => session('status'),
            'formAction' => env('FRONTEND_URL'),
        ]);
    }
}
