<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class CustomerCtrl extends Controller
{
    function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->messages()
            ]);
        }

        $data = $request->only('account', 'password');

        $customer = Customer::where('email', $data['account'])->get()->first();

        if (! $customer || ! Hash::check($data['password'], $customer->password)) {
            return response()->json([
                'status' => 'E02',
                'message' => '帳號密碼錯誤'
            ]);
        }

        $token = $customer->createToken($customer->name);
        $customer['token'] = $token->plainTextToken;

        return response()->json([
            'status' => '0',
            'data' =>  $customer,
        ]);
    }
}
