<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthCtrl extends Controller
{
    //
    public function login(Request $request)
    {
        return view('cms.auth.login', [
            'title' => '',
            'action' => Route('cms.login'),
            'otherLogins' => [
                [
                    'title' => '管理人員',
                    'url' => '',
                ],
                [
                    'title' => '物流人員',
                    'url' => '',
                ],

            ],
        ]);
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'account' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('account', 'password');
        $remember_me = (!empty($request->remember_me)) ? true : false;

        if (Auth::guard('user')->attempt($credentials, $remember_me)) {
            return redirect(Route('cms.dashboard'));
        } else {
            return redirect(Route('cms.login'))
                ->withErrors([
                    'login-error' => __('auth.failed')
                ]);
        }
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        return redirect()->route('cms.login');
    }

    public function adminLogout(Request $request)
    {
        $request->session()->invalidate();
        return redirect()->route('cms.admin.login');
    }

    public function Deliverymanlogout(Request $request)
    {
        $request->session()->invalidate();
        return redirect()->route('cms.deliveryman.login');
    }
}
