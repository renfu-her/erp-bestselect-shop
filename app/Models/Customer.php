<?php

namespace App\Models;

use App\Enums\Customer\AccountStatus;
use App\Enums\Customer\Newsletter;
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
     * @param $phone
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
        , $phone = null, $birthday = null, $sex = null, $acount_status = 0
        , $address = null, $city_id = null, $region_id = null, $addr = null
        , $newsletter = null
        , $loginMethods = null
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
                'phone' => $phone,
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

     */

    public static function getCustomerBySearch($keyword)
    {
        $admin = new Customer();
        $admin_table = DB::table($admin->getTable());

        if ($keyword) {
            $admin_table->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%")
                    ->orWhere('email', 'like', "%$keyword%");
            });
        }

        $admin_table->whereNull('deleted_at');
        return $admin_table;

    }

    public static function attachIdentity($customer_id, $type, $no, $phone, $pass, $recommend_id = null)
    {
        DB::beginTransaction();
        if (!self::validateIdentity($type, $no, $phone, $pass)) {
            return ['success' => '0', 'message' => '驗證錯誤'];
        }

        CustomerIdentity::add($customer_id, $type);
        $updateData = ['phone' => $phone];
        if ($recommend_id) {
            $updateData['recommend_id'] = $recommend_id;
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
}
