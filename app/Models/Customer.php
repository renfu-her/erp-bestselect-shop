<?php

namespace App\Models;

use App\Enums\Customer\AccountStatus;
use App\Enums\Customer\Newsletter;
use App\Enums\Customer\ProfitStatus;
use App\Enums\Customer\Sex;
use App\Notifications\CustomerPasswordReset;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usr_customers';
    public $userType = 'customer';

    const HAS_ROLE_PERMISSION = '1';
    const NO_ROLE_PERMISSION = '2';

    protected $guarded = ['email'];
    protected $fillable = [
        'email',
        'name',
        'sex',
        'phone',
        'address',
        'city_id',
        'region_id',
        'addr',
        'birthday',
        'acount_status',
        'newsletter',
        'password',
        'sn',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'email_verified_at',
        'password',
        'remember_token',
        'api_token',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthday' => 'datetime:Y-m-d',
    ];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomerPasswordReset($token));
    }

    /**
     * @param $name
     * @param $email
     * @param $password
     * @param $phone string 會員電話
     * @param $recipient_phone string 收件人電話
     * @param $birthday
     * @param $sex
     * @param $acount_status
     * @param $address
     * @param $city_id
     * @param $region_id
     * @param $addr
     * @param $newsletter
     * @param  array|string|null  $loginMethods 消費者註冊登入方式, 然後用array傳送登入方式，例如[1, 2], 請參考Enums:Customer:Login
     *
     * @return mixed
     */
    public static function createCustomer($name, $email, $password
        , $phone = null, $birthday = null, $sex = null, $acount_status = 1
        , $address = null, $city_id = null, $region_id = null, $addr = null
        , $newsletter = null
        , $loginMethods = null
        , $recipient_phone = ""
    ) {
        DB::beginTransaction();
        $sn = "M" . str_pad((self::get()
                ->count()) + 1, 9, '0', STR_PAD_LEFT);

        // S000000187
        $arr = [
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'birthday' => $birthday,
            'sex' => $sex,
            'sn' => $sn,
            //'acount_status' => $acount_status,
            'password' => Hash::make($password),
            'api_token' => Str::random(80),
            'newsletter' => $newsletter ?? Newsletter::subscribe()->value,
        ];
        if (AccountStatus::close()->value == $acount_status || AccountStatus::open()->value == $acount_status) {
            $arr['acount_status'] = $acount_status;
        }
//        dd($arr);
        $customer = Customer::create($arr);
        $id = $customer->id;

        //創建消費者時，直接給一消費者身分
        // $identity = DB::table('usr_identity')->where('code', 'customer')->get()->first();
        CustomerIdentity::add($id, 'customer');

        CustomerLogin::addLoginMethod($id, $loginMethods);

        if (!is_null($address) &&
            !is_null($city_id) &&
            !is_null($region_id) &&
            !is_null($addr)) {
            CustomerAddress::create([
                'usr_customers_id_fk' => $id,
                'name' => $name,
                'phone' => $recipient_phone,
                'address' => $address,
                'city_id' => $city_id,
                'region_id' => $region_id,
                'addr' => $addr,
                'is_default_addr' => 1,
            ]);
        }

//        self::where('id', '=', $id)->get()->first()->givePermissionTo($permission_id);
        //        self::where('id', '=', $id)->get()->first()->assignRole($role_id);
        DB::commit();
        return $id;
    }

    public static function deleteById(int $id)
    {
        Customer::where('id', $id)->delete();
    }

    public static function getCustomer(int $id)
    {
        $customer = Customer::where('id', '=', $id)
            ->select('id'
                , 'email'
                , 'email_verified_at'
                , 'name'
                , 'phone'
                , 'sex'
                , DB::raw('(case
                        when sex = ' . Sex::female()->value . ' then "' . Sex::getDescription(Sex::female()->value) . '"
                        when sex = ' . Sex::male()->value . ' then "' . Sex::getDescription(Sex::male()->value) . '"
                        else "' . '' . '"
                    end) as sex_title') //性別
                , 'newsletter'
                , DB::raw('(case
                        when newsletter = ' . Newsletter::un_subscribe()->value . ' then "' . Newsletter::getDescription(Newsletter::un_subscribe()->value) . '"
                        when newsletter = ' . Newsletter::subscribe()->value . ' then "' . Newsletter::getDescription(Newsletter::subscribe()->value) . '"
                        else "' . '' . '"
                    end) as newsletter_title') //訂閱電子報
                , 'acount_status'
                , 'password'
                , 'api_token'
                , 'order_counts'
                , 'total_spending'
                , 'remember_token')
            ->selectRaw('DATE_FORMAT(birthday,"%Y-%m-%d") as birthday')
            ->selectRaw('DATE_FORMAT(created_at,"%Y-%m-%d") as created_at')
            ->selectRaw('DATE_FORMAT(updated_at,"%Y-%m-%d") as updated_at')
            ->selectRaw('DATE_FORMAT(deleted_at,"%Y-%m-%d") as deleted_at');

        return $customer;
    }

    /**
     * @param array $query
     * @param int $per_page pagination
     * @param int $profit 1: 篩出有分潤資格的消費者
     * @param string $employer 篩選出消費者的帳號是否已綁定員工帳號, 1已經綁定、0尚未綁定
     */

    public static function getCustomerBySearch($keyword = null, $profit = null, $employer = null)
    {
        $admin = new Customer();
        $admin_table = DB::table($admin->getTable() . " as customer");

        if ($keyword) {
            $admin_table->where(function ($q) use ($keyword) {
                $q->where('customer.name', 'like', "%$keyword%")
                    ->orWhere('customer.email', 'like', "%$keyword%");
            });
        }

        if ($profit) {
            $admin_table->join('usr_customer_profit as profit', 'customer.id', '=', 'profit.customer_id')
                ->where('profit.status', ProfitStatus::Success());
        }

        if (!is_null($employer)) {
            $admin_table->leftJoin('usr_users', 'customer.id', '=', 'usr_users.customer_id');
        }
        if ($employer === '1') {
            $admin_table->whereNotNull('usr_users.customer_id');
        } elseif ($employer === '0') {
            $admin_table->whereNull('usr_users.customer_id');
        }

        $admin_table->whereNull('customer.deleted_at');
        return $admin_table;

    }

    public static function attachIdentity($customer_id, $type, $no, $phone = null, $pass = null, $recommend_sn = null)
    {
        DB::beginTransaction();
        $groupbyCompany_id = null;
        if ($type == 'buyer') {
            $groupbyCompany = GroupbyCompany::where('code', $no)->where('is_active', '1')->get()->first();
            if (!$groupbyCompany) {
                return ['success' => '0', 'message' => '無效碼'];
            }
            $groupbyCompany_id = $groupbyCompany->id;
        } else {
            if (!self::validateIdentity($type, $no, $phone, $pass)) {
                return ['success' => '0', 'message' => '與喜鴻ERP驗證時發生錯誤'];
            }
        }

        CustomerIdentity::add($customer_id, $type, null, null, null, $groupbyCompany_id);
        $updateData = ['phone' => $phone];
        if ($recommend_sn) {
            $customer = self::checkRecommender($recommend_sn, '1', $customer_id);
            if ($customer) {
                $updateData['recommend_id'] = $customer->id;
            }

        }

        Customer::where('id', $customer_id)->update($updateData);

        DB::commit();

        return ['success' => '1'];

    }

    public static function validateIdentity($type, $no, $phone, $pass)
    {
        $url_emp = "https://www.besttour.com.tw/api/Check_emp.asp";
        $url_agt = "https://www.besttour.com.tw/api/Check_agt.asp";

        switch ($type) {
            case "employee":
            case "leader":
                $url = $url_emp;
                break;

            case "agent":
                $url = $url_agt;
                break;
            default:
                return false;
        }

        $response = Http::withoutVerifying()->get($url, [
            'no' => $no,
            'phone' => $phone,
            'pass' => $pass,
        ]);

        if ($response->successful()) {
            $response = $response->json();
            if ($response['check'] == 'Pass') {
                return true;
            }
        }

        return false;
    }

    public static function detail($id)
    {

        $sub = DB::table("usr_customers as cus2")->select("name")
            ->whereColumn("customer.recommend_id", "cus2.id");

        return DB::table("usr_customers as customer")
            ->select('*')
            ->selectRaw(DB::raw("({$sub->toSql()}) as recommend_name"))
            ->where('id', $id);

    }

    public static function checkRecommender($sn, $check_child = '1', $current_customer_id = null)
    {
        $re = DB::table('usr_customer_profit as profit')
            ->leftJoin('usr_customers as customer', 'profit.customer_id', '=', 'customer.id')
            ->select('customer.*')
            ->where('customer.sn', $sn)
            ->where('profit.status', ProfitStatus::Success());

        if (!$check_child) {
            $re->where('profit.has_child', '1');
        }

        if ($current_customer_id) {
            $re->where('customer.id', "<>", $current_customer_id);
        }

        return $re->get()->first();
    }

    public static function batchCreateMcode()
    {
        DB::beginTransaction();

        $customers = self::whereNull('sn')->get();
        $c = 0;
        foreach ($customers as $customer) {

            $sn = "M" . str_pad($customer->id, 9, '0', STR_PAD_LEFT);
            self::where('id', $customer->id)->update([
                'sn' => $sn,
            ]);
            $c++;
        }
        DB::commit();
        echo "更新{$c}筆mcode";
    }

    /**
     * 更新總花費
     */
    public static function updateOrderSpends($customer_id, $total_spend) {
        if (isset($customer_id) && isset($total_spend)) {
            $count = 1;
            if (0 > $total_spend) {
                $count = -1;
            }
            self::where('id', $customer_id)->update([
                'order_counts' => DB::raw("order_counts + ". $count)
                , 'total_spending' => DB::raw("total_spending + $total_spend")
            ]);
        }
    }

    /**
     * 更新最近消費時間
     */
    public static function updateLatestOrderTime($customer_id, $latest_order_time) {
        if (isset($customer_id) && isset($latest_order_time)) {
            self::where('id', $customer_id)->update([
                'latest_order' => $latest_order_time
            ]);
        }
    }

    /**
     * 用mcode取得user 
     */
    public static function getUserByMcode($mcode) {
       return  DB::table('usr_customers as customer')
        ->join('usr_users as user','user.customer_id','=','customer.id')
        ->select('user.*')
        ->where('customer.sn',$mcode)
        ->get()->first();
    }
}
