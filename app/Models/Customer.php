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

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;
    use MenuTreeTrait;

    protected $table = 'usr_customers';
    public $userType = 'customer';

    const HAS_ROLE_PERMISSION = '1';
    const NO_ROLE_PERMISSION = '2';

    protected $guarded = ['email'];

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

    const CUSTOMER_TREE_MENU = [];

    public function menuTree(): array
    {
        return $this->getMenuTree(true, self::CUSTOMER_TREE_MENU);
    }

    public static function createCustomer($name, $email, $password
        , $phone = null, $address = null, $birthday = null, $acount_status = 0, $bind_customer_id = null
        , $permission_id = [], $role_id = []
    )
    {
        $id = self::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'birthday' => $birthday,
            'acount_status' => $acount_status,
            'password' => Hash::make($password),
            'bind_customer_id' => $bind_customer_id,
            'api_token' => Str::random(80),
        ])->id;

//        self::where('id', '=', $id)->get()->first()->givePermissionTo($permission_id);
//        self::where('id', '=', $id)->get()->first()->assignRole($role_id);

        return $id;
    }

    /**
     * @param array $query
     * @param int $per_page pagination
     * @return array [LengthAwarePaginator|array]
     */
    #[ArrayShape(['dataList' => "array", 'account' => "\Illuminate\Contracts\Pagination\LengthAwarePaginator"])]
    public static function getCustomerBySearch(array $query, int $per_page = 10): array
    {
        $admin = new Customer();
        $admin_table = DB::table($admin->getTable());
        if (isset($query['roles']) && $query['roles']) {
            if ($query['roles'] == self::HAS_ROLE_PERMISSION) {
                $admin_table->join('per_model_has_roles', 'id', '=', 'model_id')
                    ->where('model_type', '=', get_class($admin));
            } elseif ($query['roles'] == self::NO_ROLE_PERMISSION) {
                $data = DB::table('per_model_has_roles')->where('model_type', '=', get_class($admin))
                    ->select('model_id')
                    ->get()
                    ->toArray();
                $assigned_roles = array();
                foreach ($data as $key => $datum) {
                    $assigned_roles[$key] = $datum->model_id;
                }
                $admin_table->whereNotIn('id', $assigned_roles);
            }
        }

        if (isset($query['name']) && $query['name']) {
            $admin_table->where('name', 'like', "%{$query['name']}%");
        }

        if (isset($query['email']) && $query['email']) {
            $admin_table->where('email', 'like', "%{$query['email']}%");
        }

        $admins = $admin_table->paginate($per_page)->appends($query);

        $admin_data = array();
        foreach ($admins as $x) {
            $admin_data[] = $x;
        }
        $total_data = array();
        foreach ($admin_data as $admin) {
            array_push($total_data, [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'api_token' => $admin->api_token,
                'is_master' => (isset($admin->is_master) && $admin->is_master) ? 1 : 0,
                'role' => Role::getUserRoles($admin->id, 'admin'),
            ]);
        }

        return [
            'dataList' => $total_data,
            'account' => $admins,
        ];
    }
}
