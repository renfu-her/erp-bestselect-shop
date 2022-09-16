<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        $credentials['company_code'] = config('global.company_code');
        $remember_me = (!empty($request->remember_me)) ? true : false;

        if (Auth::guard('user')->attempt($credentials, $remember_me)) {
            return redirect(Route('cms.dashboard'));
        } else {
            return redirect(Route('cms.login'))
                ->withErrors([
                    'login-error' => __('auth.failed'),
                ]);
        }
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        return redirect()->route('cms.login');
    }

    public function erpLogin(Request $request)
    {

        $query = $request->all();
        // dd($query);
        if (!isset($query['account'])) {
            return redirect(route('cms.login'));
        }

        $user = User::where('account', $query['account'])->get()->first();

        if (!$user) {
            return redirect(route('cms.login'))->withErrors([
                'login-error' => __('auth.failed'),
            ]);
        }

        Auth::guard('user')->login($user);
        return redirect(Route('cms.dashboard'));


    }

}
