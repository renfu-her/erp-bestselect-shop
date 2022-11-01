<?php

namespace App\Http\Controllers\Api;

use App\Enums\Customer\AccountStatus;
use App\Enums\Customer\Login;
use App\Enums\Globals\ApiStatusMessage;
use App\Enums\Globals\ResponseParam;
use App\Http\Controllers\Controller;
use App\Models\CouponEvent;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerDividend;
use App\Models\CustomerIdentity;
use App\Models\CustomerLoginMethod;
use App\Models\CustomerProfit;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CustomerCtrl extends Controller
{
    //註冊
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email:rfc,dns', 'unique:App\Models\Customer']
            , 'name' => 'required|string'
            , 'phone' => ['nullable', 'regex:/^09[0-9]{8}/', 'unique:App\Models\Customer']
            , 'password' => 'required|confirmed|min:4'
            , 'birthday' => 'nullable|date_format:"Y-m-d"'
            , 'newsletter' => 'nullable|boolean',
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
            ResponseParam::data()->key => $id,
        ]);
    }

    //登入
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                ResponseParam::status()->key => 'E01',
                ResponseParam::msg()->key => $validator->errors(),
            ]);
        }

        $data = $request->only('email', 'password');

        $customer = Customer::where('email', $data['email'])->get()->first();
        $customer = $this->setProfit($customer);

        if (null == $customer
            || false == Hash::check($data['password'], $customer->password)
            || AccountStatus::open()->value != $customer->acount_status
        ) {
            return response()->json([
                ResponseParam::status()->key => 'E02',
                ResponseParam::msg()->key => '帳號密碼錯誤',
            ]);
        }

        $scope = []; //設定令牌能力
        $token = $customer->createToken($request->device_name ?? $customer->name, $scope);
        $customer['token'] = $token->plainTextToken;

        return response()->json([
            ResponseParam::status()->key => ApiStatusMessage::Succeed,
            ResponseParam::msg()->key => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
            ResponseParam::data()->key => $this->arrayConverNullValToEmpty($customer->toArray()),
        ]);
    }

    private function setProfit($customer)
    {
        if (isset($customer)) {
            $customerProfit = CustomerProfit::getProfitData($customer->id);
            $customer->profit = $customerProfit;
        }
        return $customer;
    }

    //第三方登入
    public function login_third_party(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => ['required', 'email:rfc,dns'],
            'method' => 'required|numeric',
            'uid' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                ResponseParam::status()->key => 'E01',
                ResponseParam::msg()->key => $validator->errors(),
            ]);
        }

        $data = $request->only('name', 'email', 'method', 'uid');

        if (!Login::hasValue((int) $data['method'])) {
            return response()->json([
                ResponseParam::status()->key => 'E01',
                ResponseParam::msg()->key => '無此登入方式',
            ]);
        }
        $customer = null;
        $customer_method = CustomerLoginMethod::where('method', $data['method'])->where('uid', $data['uid'])->get()->first();
        //有則代表已註冊 否則直接註冊會員後幫登入
        if (false == isset($customer_method)) {
            //判斷email是否已註冊過 有則使用該customer
            $customer = Customer::where('email', $data['email'])->first();
            if (false == isset($customer)) {
                $id = Customer::createCustomer(
                    $data['name']
                    , $data['email']
                    , Hash::make(Str::random(10))
                    , null
                    , null
                    , null
                    , AccountStatus::open()->value
                );
                $customer = Customer::where('id', $id)->first();
            }
            //判斷email是否已與其他第三方帳號綁定
            $customer_with_method = DB::table(app(CustomerLoginMethod::class)->getTable() . ' as log_method')
                ->where('log_method.usr_customer_id_fk', '=', $customer->id)
                ->where('log_method.method', '=', $data['method'])
                ->first();
            if (isset($customer_with_method)) {
                return response()->json([
                    ResponseParam::status()->key => 'E03',
                    ResponseParam::msg()->key => 'email已與其他第三方帳號綁定',
                ]);
            }
            CustomerLoginMethod::createData($customer->id, $data['method'], $data['uid']);
        } else {
            $customer = Customer::where('id', $customer_method->usr_customer_id_fk)->first();
        }

        $customer = $this->setProfit($customer);

        if (null == $customer) {
            return response()->json([
                ResponseParam::status()->key => 'E02',
                ResponseParam::msg()->key => '註冊有誤 請回報工程師 ' . $data['uid'],
            ]);
        }

        $scope = []; //設定令牌能力
        $token = $customer->createToken($request->device_name ?? $customer->name, $scope);
        $customer['token'] = $token->plainTextToken;

        return response()->json([
            ResponseParam::status()->key => ApiStatusMessage::Succeed,
            ResponseParam::msg()->key => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
            ResponseParam::data()->key => $this->arrayConverNullValToEmpty($customer->toArray()),
        ]);
    }

    //客戶資訊
    public function customerInfo(Request $request)
    {
        $user = $request->user()->toArray();
        if (isset($user)) {
            $customerProfit = CustomerProfit::getProfitData($user['id']);
            $user['profit'] = $customerProfit;
        }
        $identity = CustomerIdentity::where('customer_id', $user['id'])->where('identity_code', '<>', 'customer')->get()->first();
        $user['identity_title'] = '';
        $user['identity_code'] = '';
        if ($identity) {
            $user['identity_title'] = $identity->identity_title;
            $user['identity_code'] = $identity->identity_code;
        }

        $user = $this->arrayConverNullValToEmpty($user);

        return response()->json([
            ResponseParam::status()->key => ApiStatusMessage::Succeed,
            ResponseParam::msg()->key => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
            ResponseParam::data()->key => $user,
        ]);
    }

    /**
     * @param  Request  $request
     * 更新客戶資訊
     * @return
     */
    public function updateCustomerInfo(Request $request)
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
            ResponseParam::msg => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
            ResponseParam::data => [],
        ]);
    }
    /**
     * @param  Request  $request
     * 消費者收件地址API
     * @return
     */
    public function customerAddress(Request $request)
    {
        $customerId = $request->user()->id;
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
                        AS is_default'),
            ])
            ->get();

        return response()->json([
            ResponseParam::status => ApiStatusMessage::Succeed,
            ResponseParam::msg => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
            ResponseParam::data => $data,
        ]);
    }

    /**
     * @param  Request  $request
     * 刪除收件地址
     * @return
     */
    public function deleteAddress(Request $request)
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
        $customerId = $request->user()->id;
        $queryExist = CustomerAddress::where('usr_customers_id_fk', '=', $customerId)
            ->where('id', '=', $data['id'])
            ->get()
            ->first();
        if ($queryExist) {
            CustomerAddress::where('id', '=', $data['id'])->delete();
            return response()->json([
                ResponseParam::status => ApiStatusMessage::Succeed,
                ResponseParam::msg => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
                ResponseParam::data => [],
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
    public function setDefaultAddress(Request $request)
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
        $customerId = $request->user()->id;

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
                ResponseParam::msg => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
                ResponseParam::data => [],
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
    public function editAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['nullable', 'exists:usr_customers_address,id'],
            'name' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'city_id' => ['required', Rule::exists('loc_addr', 'id')->where(function ($query) {
                $query->whereNull('zipcode')
                    ->whereNull('parent_id');
            })],
            'region_id' => ['required', Rule::exists('loc_addr', 'id')->where(function ($query) {
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

        $customerId = $request->user()->id;
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
                        'name' => $data['name'],
                        'phone' => $data['phone'],
                        'address' => $address,
                        'city_id' => $data['city_id'],
                        'region_id' => $data['region_id'],
                        'addr' => $data['addr'],
                    ]);

                return response()->json([
                    ResponseParam::status => ApiStatusMessage::Succeed,
                    ResponseParam::msg => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
                    ResponseParam::data => [],
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
                'name' => $data['name'],
                'phone' => $data['phone'],
                'address' => $address,
                'city_id' => $data['city_id'],
                'region_id' => $data['region_id'],
                'addr' => $data['addr'],
                'is_default_addr' => intval($data['is_default']),
            ]);

            return response()->json([
                ResponseParam::status => ApiStatusMessage::Succeed,
                ResponseParam::msg => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
                ResponseParam::data => [],
            ]);
        }
    }

    //撤销所有令牌
    public function tokensDeleteAll(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            ResponseParam::status()->key => ApiStatusMessage::Succeed,
            ResponseParam::msg()->key => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
        ]);
    }

    //撤销當前令牌
    public function tokensDeleteCurrent(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            ResponseParam::status()->key => ApiStatusMessage::Succeed,
            ResponseParam::msg()->key => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
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

    public function attachIdentity(Request $request)
    {
        $user = $request->user();
        $vali1 = ['no' => ['required'],
            'phone' => ['required'],
            'pass' => ['required']];

        $vali2 = ['no' => ['required']];

        $validator = Validator::make($request->all(), [
            'type' => ['required', 'in:customer,employee,company,leader,agent,buyer'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                ResponseParam::status => ApiStatusMessage::Fail,
                ResponseParam::msg => $validator->errors(),
                ResponseParam::data => [],
            ]);
        }

        $d = $request->all();
        if ($d['type'] == 'buyer') {
            $vali = $vali2;
        } else {
            $vali = $vali1;
        }

        $validator = Validator::make($request->all(), $vali);

        if ($validator->fails()) {
            return response()->json([
                ResponseParam::status => ApiStatusMessage::Fail,
                ResponseParam::msg => $validator->errors(),
                ResponseParam::data => [],
            ]);
        }

        $d = $request->all();

        $recommend_sn = Arr::get($d, 'recommend_sn', null);
        $phone = Arr::get($d, 'phone', null);
        $pass = Arr::get($d, 'pass', null);

        $re = Customer::attachIdentity($user->id, $d['type'], $d['no'], $phone, $pass, $recommend_sn);

        if ($re['success'] == '1') {
            return [
                'status' => '0',
            ];
        }

        return [
            'status' => 'E03',
            'message' => $re['message'],
        ];

    }
    public function createProfit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_id' => ['required'],
            'bank_account' => ['required'],
            'bank_account_name' => ['required'],
            'identity_sn' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                ResponseParam::status => ApiStatusMessage::Fail,
                ResponseParam::msg => $validator->errors(),
                ResponseParam::data => [],
            ]);
        }

        $user = $request->user();
        $d = $request->all();

        $img1 = '';
        if ($request->hasfile('img1_file')) {
            $img1 = $request->file('img1_file')->store('profit_data/' . date("Ymd"));
        }
        $img2 = '';
        if ($request->hasfile('img2_file')) {
            $img2 = $request->file('img2_file')->store('profit_data/' . date("Ymd"));
        }
        $img3 = '';
        if ($request->hasfile('img3_file')) {
            $img3 = $request->file('img3_file')->store('profit_data/' . date("Ymd"));
        }

        /*
        $img1 = Arr::get($d, 'img1', '');
        $img2 = Arr::get($d, 'img2', '');
        $img3 = Arr::get($d, 'img3', '');
         */
        $re = CustomerProfit::createProfit($user->id, $d['bank_id'], $d['bank_account'], $d['bank_account_name'], $d['identity_sn'], $img1, $img2, $img3);
        if ($re['success'] == '1') {
            return [
                'status' => '0',
                'id' => $re['id'],
            ];
        }

        return [
            'status' => 'E04',
            'message' => $re['message'],
        ];

    }

    public function profitStatus(Request $request)
    {

        $profit = CustomerProfit::where('customer_id', $request->user()->id)->get()->first();
        if ($profit) {
            return [
                'status' => '0',
                'data' => $profit,
            ];
        }

        return [
            'status' => 'E02',
            'message' => "尚未申請",
        ];
    }

    public function checkRecommender(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'sn' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                ResponseParam::status => ApiStatusMessage::Fail,
                ResponseParam::msg => $validator->errors(),
                ResponseParam::data => [],
            ]);
        }

        $d = $request->all();

        if (Auth::guard('sanctum')->check()) {
            $re = Customer::checkRecommender($d['sn'], '1', $request->user()->id);
        } else {
            $re = Customer::checkRecommender($d['sn']);
        }

        if ($re) {
            return [
                'status' => '0',
                'data' => $re->name,
            ];
        }

        return [
            'status' => 'E03',
            'message' => '無推薦權限',
        ];
    }

    public function checkDividendFromErp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required'],
            'account' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                ResponseParam::status => ApiStatusMessage::Fail,
                ResponseParam::msg => $validator->errors(),
                ResponseParam::data => [],
            ]);
        }

        $re = CustomerDividend::checkDividendFromErp($request->input('account'), $request->input('password'));

        if ($re['status'] != '0') {
            return response()->json([
                'status' => $re['status'],
                'msg' => $re['error_log'],
            ]);
        }

        return response()->json($re);

    }

    public function getDividendFromErp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'edword' => ['required'],
            'points' => ['required'],
            'type' => ['required'],
            'requestid' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                ResponseParam::status => ApiStatusMessage::Fail,
                ResponseParam::msg => $validator->errors(),
                ResponseParam::data => [],
            ]);
        }
        $d = $request->all();
        $re = CustomerDividend::getDividendFromErp($request->user()->id, $d['edword'], $d['points'], $d['type'], $d['requestid']);

        if ($re['status'] != '0') {
            return response()->json([
                'status' => $re['status'],
                'msg' => $re['error_log'],
            ]);
        }

        return response()->json($re);

    }

    public function getEventCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sn' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                ResponseParam::status => ApiStatusMessage::Fail,
                ResponseParam::msg => $validator->errors(),
                ResponseParam::data => [],
            ]);
        }
        $d = $request->all();
        $re = CouponEvent::getCoupon($request->user()->id, $d['sn']);

        if ($re['success'] != '1') {
            return response()->json([
                'status' => 'E01',
                'msg' => $re['msg'],
            ]);
        }

        return response()->json([
            'status' => '0',
        ]);

    }

}
