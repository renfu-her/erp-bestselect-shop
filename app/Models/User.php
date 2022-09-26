<?php

namespace App\Models;

use App\Http\MenuTreeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;
    use MenuTreeTrait;

    protected $table = 'usr_users';
    public $userType = 'user';

    const HAS_ROLE_PERMISSION = '1';
    const NO_ROLE_PERMISSION = '2';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'account',
        'email',
        'password',
        'uuid',
        'api_token',
        'company_code',
        'title',
        'group',
        'department',
        'company',
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

    public function menuTree(): array
    {
        return $this->getMenuTree(true, include 'userMenu.php');
    }

    public static function createUser($name, $account, $email, $password, $permission_id = [], $role_id = [], $company_code = null, $title = null, $main_company = null, $company = null, $department = null, $group = null)
    {
        //檢查是否有此消費者ID

        if (!$company_code) {
            $company_code = config('global.company_code');
        }

        //    dd($company_code);

        $id = self::create([
            'name' => $name,
            'email' => $email,
            'account' => $account,
            'password' => Hash::make($password),
            'uuid' => Str::uuid(),
            'api_token' => Str::random(80),
            'company_code' => $company_code,
            'title' => $title,
            'company' => $company,
            'department' => $department,
            'group' => $group,
            'main_company' => $main_company,
        ])->id;

        self::where('id', '=', $id)->get()->first()->givePermissionTo($permission_id);
        self::where('id', '=', $id)->get()->first()->assignRole($role_id);

        return $id;
    }

    /**
     * @param  array  $query
     * @param  int  $per_page  records in pagination
     *
     */
    public static function getUserBySearch(array $query, int $per_page = 10)
    {
        $user_table = DB::table('usr_users')
                        ->leftJoin('per_model_has_roles', 'usr_users.id', '=', 'per_model_has_roles.model_id');

        if (isset($query['roles'])) {
            if ($query['roles'] == '1') {
                $user_table->whereNotNull('model_id');
            } elseif ($query['roles'] == '0') {
                $user_table->whereNull('model_id');
            }
        }

        if (isset($query['name']) && $query['name']) {
            $user_table->where('name', 'like', "%{$query['name']}%");
        }

        if (isset($query['account']) && $query['account']) {
            $user_table->where('account', 'like', "%{$query['account']}%");
        }

        if (isset($query['roleIds']) && $query['roleIds']) {
            foreach ($query['roleIds'] as $roleId) {
                $user_table->orWhere('per_model_has_roles.role_id', $roleId);
            }
        }

        $users = $user_table
            ->select([
                'id',
                'name',
                'account',
                'api_token',
                'model_id',
            ])
            ->selectRaw('GROUP_CONCAT(DISTINCT role_id) as role_ids')
            ->groupBy('id')
            ->distinct()
            ->paginate($per_page)
            ->appends($query);

        return $users;
    }

    /**
     * @param $user_id string 員工ID
     * @param $email string 消費者信箱
     * 綁定消費者（含初次綁定、重新綁定）
     * @return void
     */
    public static function customerBinding($user_id, $email)
    {
        $customer = Customer::where('email', $email)->select('id')->get()->first();
        if ($customer) {

            DB::beginTransaction();
            //更新綁定的消費者id
            User::where('id', $user_id)->update(['customer_id' => $customer->id]);
            CustomerIdentity::add($customer->id, 'employee');

            $user = User::where('id', $user_id)->get()->first();

            Customer::where('id', $customer->id)->update([
                'name' => $user->name,
            ]);

            $saleChannel = SaleChannel::where('code', '02')->get()->first();

            if ($saleChannel) {
                UserSalechannel::create(['user_id' => $user_id, 'salechannel_id' => $saleChannel->id]);
            }

            DB::commit();
        }
    }

    public static function checkCustomerBinded($email)
    {
        $customer = Customer::where('email', $email)->get()->first();

        if (!$customer) {
            return [
                'success' => '0',
                'error_msg' => '無消費者資料',
                'code' => 'no_data',
            ];
        }

        if (self::where('customer_id', $customer->id)->get()->first()) {
            return [
                'success' => '0',
                'error_msg' => '已被綁定',
                'code' => 'binded',
            ];
        }

        return [
            'success' => '1',
        ];
    }

    //取得人員在物流專案是否開啟
    public static function getLogisticUserIsOpen(int $user_id)
    {
        $user_lgt = DB::table('usr_users as users')
            ->select(
                'users.id'
                , DB::raw('ifnull((select is_open from usr_user_proj_logistics where user_fk = users.id and type = "admin"), "") as admin')
                , DB::raw('ifnull((select is_open from usr_user_proj_logistics where user_fk = users.id and type = "user"), "") as user')
                , DB::raw('ifnull((select is_open from usr_user_proj_logistics where user_fk = users.id and type = "deliveryman"), "") as deliveryman')
            )
            ->where('users.id', '=', $user_id)
            ->get()->first();
        return $user_lgt;
    }

    //取得人員在物流專案的api_token
    public static function getLogisticApiToken($user_id)
    {
        $user_lgt_token = DB::table('usr_users as users')
            ->select(
                'users.id'
                , DB::raw('ifnull((select api_token from usr_user_proj_logistics where user_fk = users.id and type = "admin" and is_open = 1), "") as admin_token')
                , DB::raw('ifnull((select api_token from usr_user_proj_logistics where user_fk = users.id and type = "user" and is_open = 1), "") as user_token')
                , DB::raw('ifnull((select api_token from usr_user_proj_logistics where user_fk = users.id and type = "deliveryman" and is_open = 1), "") as deliveryman_token')
            )
            ->where('users.id', '=', $user_id)
            ->get()->first();
        return $user_lgt_token;
    }

    public static function getEmployeeData($role_id = [])
    {
        $url = "https://www.besttour.com.tw/api/empdep.asp?type=1";
        $re = Http::get($url)->json();
        DB::beginTransaction();
        foreach ($re as $u) {
            if (self::where('account', $u['NUMBER'])->withTrashed()->get()->first()) {
                self::where('account', $u['NUMBER'])->update([
                    'name' => $u['NAME'],
                    'password' => Hash::make($u['PASSWORD']),
                    'title' => $u['TITLE'],
                    'company' => $u['COMPANY'],
                    'department' => $u['DEPARTMENT'],
                    'group' => $u['GROUP'],
                ]);
            } else {
                self::createUser($u['NAME'], $u['NUMBER'], null, $u['PASSWORD'], [], $role_id, null, $u['TITLE'], '喜鴻購物', $u['COMPANY'], $u['DEPARTMENT'], $u['GROUP']);
            }
        }
        DB::commit();
        echo "匯入完成";
    }

    /**
     * @param $user_id int 後台使用者ID
     * 取得後台使用者綁定的消費者帳號
     * @return mixed 消費者、後台使用者的table資料
     */
    public static function getUserCustomer($user_id)
    {
        return DB::table('usr_users as user')
            ->join('usr_customers as customer', 'user.customer_id', '=', 'customer.id')
            ->where('user.id', $user_id)
            ->get()
            ->first();
    }
}
