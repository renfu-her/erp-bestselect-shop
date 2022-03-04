<?php

namespace App\Models;

use App\Http\MenuTreeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

    public static function createUser($name, $account, $email, $password, $permission_id = [], $role_id = [], $company_code = null)
    {
        //檢查是否有此消費者ID

        if (!$company_code) {
            $company_code = config('global.company_code');
        }

        $id = self::create([
            'name' => $name,
            'email' => $email,
            'account' => $account,
            'password' => Hash::make($password),
            'uuid' => Str::uuid(),
            'api_token' => Str::random(80),
            'company_code' => $company_code,
        ])->id;

        self::where('id', '=', $id)->get()->first()->givePermissionTo($permission_id);
        self::where('id', '=', $id)->get()->first()->assignRole($role_id);

        return $id;
    }

    /**
     * @param  array  $query
     * @param $company_id
     * @param  int  $per_page  records in pagination
     *
     * @return array [LengthAwarePaginator|array]
     */
    public static function getUserBySearch(array $query, $company_id = null, int $per_page = 10)
    {
        $user_model = new User();
        $user_table = DB::table($user_model->getTable());
        if (isset($query['roles']) && $query['roles']) {
            if ($query['roles'] == self::HAS_ROLE_PERMISSION) {
                $user_table->join('per_model_has_roles', 'id', '=', 'model_id')
                    ->where('model_type', '=', get_class($user_model));
            } elseif ($query['roles'] == self::NO_ROLE_PERMISSION) {
                $data = DB::table('per_model_has_roles')
                    ->where('model_type', '=', get_class($user_model))
                    ->select('model_id')->get()->toArray();
                $assigned_roles = array();
                foreach ($data as $key => $datum) {
                    $assigned_roles[$key] = $datum->model_id;
                }
                $user_table->whereNotIn('id', $assigned_roles);
            }
        }

        if (isset($query['name']) && $query['name']) {
            $user_table->where('name', 'like', "%{$query['name']}%");
        }

        if (isset($query['account']) && $query['account']) {
            $user_table->where('account', 'like', "%{$query['account']}%");
        }

        $users = $user_table->paginate($per_page)->appends($query);

        $users_data = array();
        foreach ($users as $x) {
            $users_data[] = $x;
        }
        $total_data = array();
        foreach ($users_data as $user) {
            $total_data[] = [
                'id' => $user->id, 'name' => $user->name,
                'account' => $user->account, 'api_token' => $user->api_token,
                'is_master' => (isset($user->is_master) && $user->is_master) ? 1
                : 0, 'role' => Role::getUserRoles($user->id, 'user'),
            ];
        }

        return [
            'dataList' => $total_data, 'account' => $users,
        ];
    }

    public static function customerBinding($user_id, $email)
    {
        $customer = Customer::where('email', $email)->select('id')->get()->first();
        if ($customer) {
            User::where('id', $user_id)->update(['customer_id' => $customer->id]);
            CustomerIdentity::add($customer->id, 'employee');

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
}
