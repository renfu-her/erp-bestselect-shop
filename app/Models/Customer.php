<?php

namespace App\Models;

use App\Enums\Customer\Newsletter;
use App\Enums\Customer\Sex;
use App\Notifications\CustomerPasswordReset;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        'birthday'  => 'datetime:Y-m-d',
    ];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomerPasswordReset($token));
    }

    public static function createCustomer($name, $email, $password
        , $phone = null, $birthday = null, $acount_status = 0
        , $address = null, $city_id = null, $region_id = null, $addr = null
    ) {
        $arr = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'birthday' => $birthday,
            'acount_status' => $acount_status,
            'password' => Hash::make($password),
            'address' => $address,
            'city_id' => $city_id,
            'region_id' => $region_id,
            'addr' => $addr,
            'api_token' => Str::random(80),
        ];
        if (0 == $acount_status || 1 == $acount_status) {
            $arr['acount_status'] = $acount_status;
        }
//        dd($arr);
        $customer = Customer::create($arr);
        $id = $customer->id;

        //創建消費者時，直接給一消費者身分
       // $identity = DB::table('usr_identity')->where('code', 'customer')->get()->first();
        CustomerIdentity::add($id, 'customer');

//        self::where('id', '=', $id)->get()->first()->givePermissionTo($permission_id);
//        self::where('id', '=', $id)->get()->first()->assignRole($role_id);

        return $id;
    }

    //綁定消費者
    public static function bindCustomer($user_id, $customer_id)
    {
        $user = User::where('id', $user_id)->get()->first();
        $customer = Customer::where('id', $customer_id)->get()->first();
        if (null != $user && null != $customer) {
            return DB::transaction(function () use ($user_id, $customer_id
            ) {
                User::where('id', $user_id)->update([
                    'customer_id' => $customer_id,
                ]);
                return $user_id;
            });
        }
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
                , 'address'
                , 'city_id'
                , 'region_id'
                , 'addr'
                , 'sex'
                , DB::raw('(case
                        when sex = '. Sex::female()->value .' then "'. Sex::getDescription(Sex::female()->value) .'"
                        when sex = '. Sex::male()->value .' then "'. Sex::getDescription(Sex::male()->value) .'"
                        else "'. '' .'"
                    end) as sex_title') //性別
                , 'newsletter'
                , DB::raw('(case
                        when newsletter = '. Newsletter::un_subscribe()->value .' then "'. Newsletter::getDescription(Newsletter::un_subscribe()->value) .'"
                        when newsletter = '. Newsletter::subscribe()->value .' then "'. Newsletter::getDescription(Newsletter::subscribe()->value) .'"
                        else "'. '' .'"
                    end) as newsletter_title') //訂閱電子報
                , 'acount_status'
                , 'password'
                , 'api_token'
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
}
