<?php

namespace App\Models;

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
        'email_verified_at',
        'name',
        'phone',
        'address',
        'city_id',
        'region_id',
        'addr',
        'birthday',
        'acount_status',
        'bind_customer_id',
        'password',
        'api_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function createCustomer($name, $email, $password
        , $phone = null, $birthday = null, $acount_status = 0, $bind_customer_id = null
        , $address = null, $city_id = null, $region_id = null, $addr = null
    ) {
        $arr = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'birthday' => $birthday,
//            'acount_status' => $acount_status,
            'password' => Hash::make($password),
            'address' => $address,
            'city_id' => $city_id,
            'region_id' => $region_id,
            'addr' => $addr,
            'bind_customer_id' => $bind_customer_id,
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
                , 'acount_status'
                , 'bind_customer_id'
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
