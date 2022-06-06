<?php

namespace App\Http\Controllers\Api;

use App\Enums\Customer\AccountStatus;
use App\Enums\Globals\ApiStatusMessage;
use App\Enums\Globals\ResponseParam;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $uData = $request->only('email', 'name', 'phone', 'password', 'birthday', 'sex', 'newsletter');

        $id = Customer::createCustomer(
            $uData['name']
            , $uData['email']
            , $uData['password']
            , $uData['phone'] ?? null
            , $uData['birthday'] ?? null
            , $uData['sex'] ?? null
            , AccountStatus::open()->value
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
                ResponseParam::msg()->key => $validator->errors()
            ]);
        }

        $data = $request->only('email', 'password');

        $customer = Customer::where('email', $data['email'])->get()->first();

        if (null == $customer
            || false == Hash::check($data['password'], $customer->password)
            || AccountStatus::open()->value != $customer->acount_status
        ) {
            return response()->json([
                ResponseParam::status()->key => 'E02',
                ResponseParam::msg()->key => '帳號密碼錯誤'
            ]);
        }

        $scope = []; //設定令牌能力
        $token = $customer->createToken($request->device_name ?? $customer->name, $scope);
        $customer['token'] = $token->plainTextToken;

        return response()->json([
            ResponseParam::status()->key => ApiStatusMessage::Succeed,
            ResponseParam::msg()->key => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
            ResponseParam::data()->key =>  $this->arrayConverNullValToEmpty($customer->toArray()),
        ]);
    }

    //客戶資訊
    function customerInfo(Request $request)
    {
        $user = $request->user()->toArray();
        $user = $this->arrayConverNullValToEmpty($user);
        return response()->json([
            ResponseParam::status()->key => ApiStatusMessage::Succeed,
            ResponseParam::msg()->key =>  ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
            ResponseParam::data()->key =>  $user,
        ]);
    }

    /**
     * @param  Request  $request
     * 消費者收件地址API
     * @return
     */
    function customerAddress(Request $request)
    {
        $customerId  = $request->user()->id;
        $data = DB::table('usr_customers as customer')
            ->where('customer.id', $customerId)
            ->leftJoin('usr_customers_address', 'customer.id', '=', 'usr_customers_address.usr_customers_id_fk')
            ->whereNotNull('address')
            ->select([
                'customer.id as id',
                'name',
                'phone',
                'city_id',
                'region_id',
                'addr',
                'address',
                DB::raw('(CASE WHEN usr_customers_address.is_default_addr = 1
                        THEN TRUE
                        ELSE FALSE END)
                        AS is_default')
            ])
            ->get();

        return response()->json([
            ResponseParam::status => ApiStatusMessage::Succeed,
            ResponseParam::msg    => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
            ResponseParam::data   => $data,
        ]);
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

    /**
     * @param $data
     * @return mixed
     */
    private function arrayConverNullValToEmpty($data)
    {
        foreach ($data as $key => $value) {
            if (is_null($data[$key])) {
                $data[$key] = '';
            }
        }
        return $data;
    }
}
