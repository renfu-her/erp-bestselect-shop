<?php

namespace App\Http\Controllers\Api;

use App\Enums\Globals\ApiStatusMessage;
use App\Enums\Globals\ResponseParam;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class CustomerCtrl extends Controller
{
    //註冊
    function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email:rfc,dns', 'unique:App\Models\Customer']
            , 'name' => 'required|string'
            , 'phone' => ['nullable', 'regex:/^09[0-9]{8}/', 'unique:App\Models\Customer']
            , 'password' => 'required|confirmed|min:4'
            , 'birthday' => 'nullable|date_format:"Y-m-d"'
            , 'newsletter' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            $re = [];
            $re[ResponseParam::status()->key] = 'E01';
            $re[ResponseParam::msg()->key] = $validator->errors();

            return response()->json($re);
        }

        $uData = $request->only('email', 'name', 'phone', 'password', 'birthday', 'newsletter');

        $id = Customer::createCustomer(
            $uData['name']
            , $uData['email']
            , $uData['password']
            , $uData['phone'] ?? null
            , $uData['birthday'] ?? null
            , 0
            , null
            , null
            , null
            , null
            , $uData['newsletter'] ?? null
        );

        return response()->json([
            ResponseParam::status()->key => ApiStatusMessage::Succeed,
            ResponseParam::msg()->key => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
            ResponseParam::data()->key =>  $id,
        ]);
    }

    //登入
    function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                ResponseParam::status()->key => 'E01',
                ResponseParam::msg()->key => $validator->messages()
            ]);
        }

        $data = $request->only('email', 'password');

        $customer = Customer::where('email', $data['email'])->get()->first();

        if (! $customer || ! Hash::check($data['password'], $customer->password)) {
            return response()->json([
                ResponseParam::status() => 'E02',
                ResponseParam::msg()->key => '帳號密碼錯誤'
            ]);
        }

        $token = $customer->createToken($request->device_name ?? $customer->name);
        $customer['token'] = $token->plainTextToken;

        return response()->json([
            ResponseParam::status()->key => ApiStatusMessage::Succeed,
            ResponseParam::msg()->key => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
            ResponseParam::data()->key =>  $customer,
        ]);
    }

    //客戶資訊
    function customerInfo(Request $request)
    {
        return $request->user();
    }

    //撤销所有令牌
    function tokensDeleteAll(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            ResponseParam::status()->key => ApiStatusMessage::Succeed,
            ResponseParam::msg()->key =>  ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
        ]);
    }

    //撤销當前令牌
    function tokensDeleteCurrent(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            ResponseParam::status()->key => ApiStatusMessage::Succeed,
            ResponseParam::msg()->key =>  ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
        ]);
    }
}
