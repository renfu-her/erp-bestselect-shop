<?php

namespace App\Http\Controllers\Api;

use App\Enums\Customer\AccountStatus;
use App\Enums\Globals\ApiStatusMessage;
use App\Enums\Globals\ResponseParam;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;


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
     * 更新客戶資訊
     * @return
     */
    function updateCustomerInfo(Request $request)
    {
        $customerId = $request->user()->id;
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                Rule::unique('usr_customers', 'email')->ignore($customerId),
            ],
            'name' => ['required', 'string'],
            'phone' => ['nullable', 'string'],
            'birthday' => ['nullable', 'date_format:"Y-m-d"'],
            'sex' => ['nullable', 'integer', 'min:0', 'max:1'],
            'newsletter' => ['required', 'integer', 'min:0', 'max:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                ResponseParam::status => ApiStatusMessage::Fail,
                ResponseParam::msg => $validator->errors(),
                ResponseParam::data => [],
            ]);
        }

        $data = $request->only(
            'email',
            'name',
            'phone',
            'birthday',
            'sex',
            'newsletter',
        );

        DB::table('usr_customers')
            ->where('id', '=', $customerId)
            ->update([
                'email' => $data['email'],
                'name' => $data['name'],
                'phone' => $data['phone'] ?? '',
                'birthday' => $data['birthday'] ?? null,
                'sex' => $data['sex'] ?? null,
                'newsletter' => $data['newsletter'],
            ]);

        return response()->json([
            ResponseParam::status => ApiStatusMessage::Succeed,
            ResponseParam::msg    => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
            ResponseParam::data   => [],
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
                'usr_customers_address.id as id',
                'usr_customers_address.name',
                'usr_customers_address.phone',
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

    /**
     * @param  Request  $request
     * 刪除收件地址
     * @return
     */
    function deleteAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'exists:usr_customers_address,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                ResponseParam::status => ApiStatusMessage::Fail,
                ResponseParam::msg => $validator->errors(),
                ResponseParam::data => [],
            ]);
        }

        $data = $request->only('id');
        $customerId  = $request->user()->id;
        $queryExist = CustomerAddress::where('usr_customers_id_fk', '=', $customerId)
                                       ->where('id', '=', $data['id'])
                                        ->get()
                                        ->first();
        if ($queryExist){
            CustomerAddress::where('id', '=', $data['id'])->delete();
            return response()->json([
                ResponseParam::status => ApiStatusMessage::Succeed,
                ResponseParam::msg    => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
                ResponseParam::data   => [],
            ]);
        } else {
            return response()->json([
                ResponseParam::status => ApiStatusMessage::Fail,
                ResponseParam::msg => '找不到該使用者的收件地址',
                ResponseParam::data => [],
            ]);
        }
    }

    /**
     * @param  Request  $request
     * 設定預設地址
     * @return
     */
    function setDefaultAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'exists:usr_customers_address,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                ResponseParam::status => ApiStatusMessage::Fail,
                ResponseParam::msg => $validator->errors(),
                ResponseParam::data => [],
            ]);
        }

        $data = $request->only('id');
        $customerId  = $request->user()->id;

        $queryExist = CustomerAddress::where('usr_customers_id_fk', '=', $customerId)
            ->where('id', '=', $data['id'])
            ->get()
            ->first();
        if ($queryExist) {
            CustomerAddress::where('usr_customers_id_fk', '=', $customerId)
                ->where('id', '<>', $data['id'])
                ->update([
                    'is_default_addr' => 0,
                ]);
            CustomerAddress::where('usr_customers_id_fk', '=', $customerId)
                ->where('id', '=', $data['id'])
                ->update([
                    'is_default_addr' => 1,
                ]);
            return response()->json([
                ResponseParam::status => ApiStatusMessage::Succeed,
                ResponseParam::msg    => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
                ResponseParam::data   => [],
            ]);
        } else {
            return response()->json([
                ResponseParam::status => ApiStatusMessage::Fail,
                ResponseParam::msg => '找不到該使用者的收件地址',
                ResponseParam::data => [],
            ]);
        }
    }

    /**
     * @param  Request  $request
     * 編輯/建立 收件地址
     * @return
     */
    function editAddress (Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['nullable', 'exists:usr_customers_address,id'],
            'name' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'city_id' => ['required', Rule::exists('loc_addr', 'id')->where(function ($query){
                $query->whereNull('zipcode')
                        ->whereNull('parent_id');
            })],
            'region_id' => ['required', Rule::exists('loc_addr', 'id')->where(function ($query){
                $query->whereNotNull('zipcode')
                    ->whereNotNull('parent_id');
            })],
            'addr' => ['required', 'string'],
            'is_default' => ['required', 'numeric', 'min:0', 'max:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                ResponseParam::status => ApiStatusMessage::Fail,
                ResponseParam::msg => $validator->errors(),
                ResponseParam::data => [],
            ]);
        }

        $data = $request->only(
            'id',
            'name',
            'phone',
            'city_id',
            'region_id',
            'addr',
            'is_default',
        );

        $customerId  = $request->user()->id;
        $cityQuery = DB::table('loc_addr')
            ->where('id', '=', $data['city_id'])
            ->select('title')
            ->get()
            ->first();
        $regionQuery = DB::table('loc_addr')
            ->where('id', '=', $data['region_id'])
            ->select('title', 'zipcode')
            ->get()
            ->first();
        $address = $regionQuery->zipcode .
                                ' ' .
                                $cityQuery->title .
                                $regionQuery->title .
                                $data['addr'];

        // update address
        if ($request->exists('id')) {
            $queryExist = CustomerAddress::where('usr_customers_id_fk', '=', $customerId)
                                        ->where('id', '=', $data['id'])
                                        ->get()
                                        ->first();
            if ($queryExist) {
                DB::table('usr_customers_address')
                    ->where('id', '=', $data['id'])
                    ->update([
                        'usr_customers_id_fk' => $customerId,
                        'name'                => $data['name'],
                        'phone'               => $data['phone'],
                        'address'             => $address,
                        'city_id'             => $data['city_id'],
                        'region_id'           => $data['region_id'],
                        'addr'                => $data['addr'],
                    ]);

                return response()->json([
                    ResponseParam::status => ApiStatusMessage::Succeed,
                    ResponseParam::msg    => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
                    ResponseParam::data   => [],
                ]);
            } else {
                return response()->json([
                    ResponseParam::status => ApiStatusMessage::Fail,
                    ResponseParam::msg => '找不到該使用者的收件地址',
                    ResponseParam::data => [],
                ]);
            }
        } else {
            //only one default
            if (intval($data['is_default']) === 1) {
                DB::table('usr_customers_address')
                    ->where('usr_customers_id_fk', '=', $customerId)
                    ->update([
                        'is_default_addr' => 0,
                    ]);
            }

            //create address
            CustomerAddress::create([
                'usr_customers_id_fk' => $customerId,
                'name'                => $data['name'],
                'phone'               => $data['phone'],
                'address'             => $address,
                'city_id'             => $data['city_id'],
                'region_id'           => $data['region_id'],
                'addr'                => $data['addr'],
                'is_default_addr'     => intval($data['is_default']),
            ]);

            return response()->json([
                ResponseParam::status => ApiStatusMessage::Succeed,
                ResponseParam::msg    => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
                ResponseParam::data   => [],
            ]);
        }
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

    function validateIdentity(Request $request)
    {
        $url1 = "https://www.besttour.com.tw/api/Check_emp.asp";
        $url2 = "https://www.besttour.com.tw/api/Check_agt.asp";

        $response = Http::get($url1, [
            'no' => '08073',
            'phone' => '0955587777',
            'pass' => '123456',
        ]);

        dd($response->json());
    }
}
