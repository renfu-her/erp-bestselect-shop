<?php

namespace App\Http\Controllers\Cms\User;

use App\Http\Controllers\Controller;
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

    public function customerBinding(Request $request)
    {

        return view('cms.admin.maintenance.bind', [
            'method' => 'edit',
            'formAction' => Route("cms.usermnt.customer-binding"),
            'data' => $request->user(),
        ]);
    }

    public function updateCustomerBinding(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        $email = $request->input('email');
        $re = User::checkCustomerBinded($request->input('email'));

        if ($re['success'] == '1') {
            User::customerBinding($request->user()->id, $email);
            wToast('綁定完成');
            return redirect()->back();
        } else {
            return redirect()->back()->withInput(array_merge($request->input(), ['error_code' => $re['code']]))->withErrors(['email' => $re['error_msg']]);

        }

    }
}
