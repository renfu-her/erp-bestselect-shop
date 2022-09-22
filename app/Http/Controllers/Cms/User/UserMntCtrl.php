<?php

namespace App\Http\Controllers\Cms\User;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserMntCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        //  dd('aa');
        return view('cms.admin.maintenance.edit', [
            'method' => 'edit',
            'formAction' => Route("cms.usermnt.edit"),
            'data' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => 'confirmed|min:4|nullable',
        ]);

        if ($request->exists('password')) {
            $userData['password'] = Hash::make($request->input('password'));

            $model = '\App\Models\User';

            $model::where('id', $request->user()->id)
                ->update($userData);
        }

        wToast('檔案更新完成');
        return redirect()->back();
    }

    /**
     * @param  Request  $request
     * 處理消費者會員綁定
     */
    public function customerBinding(Request $request)
    {

        $customer = Customer::where('id', $request->user()->customer_id)->get()->first();

        return view('cms.admin.maintenance.bind', [
            'method' => 'edit',
            'formAction' => Route("cms.usermnt.customer-binding"),
            'data' => $request->user(),
            'customer' => $customer,
        ]);
    }

    /**
     * @param  Request  $request
     * 用Email檢查此會員帳號是否綁定過，若此Email未曾綁定過，便執行綁定動作
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCustomerBinding(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'rebinding' => [
                'required',
                'regex:/^[0|1]$/',
            ]
        ]);

        $email = $request->input('email');
        $rebinding = $request->input('rebinding');

        $re = User::checkCustomerBinded($request->input('email'));

        if ($re['success'] == '1') {
            User::customerBinding($request->user()->id, $email, $rebinding);
            wToast('綁定完成');
            return redirect()->back();
        } else {
            return redirect()->back()->withInput(array_merge($request->input(), ['error_code' => $re['code']]))->withErrors(['email' => $re['error_msg']]);

        }

    }
}
