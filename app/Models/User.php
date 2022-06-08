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

        //    dd($company_code);

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

            $saleChannel = SaleChannel::where('code', '02')->get()->first();

            if ($saleChannel) {
                UserSalechannel::create(['user_id' => $user_id, 'salechannel_id' => $saleChannel->id]);
            }
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
    public static function getLogisticUserIsOpen(int $user_id) {
        $user_lgt = DB::table('usr_users as users')
            ->select(
                'users.id'
                , DB::raw('ifnull((select is_open from usr_user_proj_logistics where user_fk = users.id and type = "admin"), "") as admin')
                , DB::raw('ifnull((select is_open from usr_user_proj_logistics where user_fk = users.id and type = "user"), "") as user')
                , DB::raw('ifnull((select is_open from usr_user_proj_logistics where user_fk = users.id and type = "deliveryman"), "") as deliveryman')
            )
            ->where('users.id', '=', $user_id);
        return $user_lgt;
    }

    //變更託運人員在物流專案是否開啟
    public static function modifyLogisticUser(int $curr_user_id, int $user_id, array $lgt_users) {
        try {
            //找目前使用者儲存在本專案的物流專案的API_TOKEN
            $user_lgt_token = DB::table('usr_users as users')
                ->select(
                    'users.id'
                    , DB::raw('ifnull((select api_token from usr_user_proj_logistics where user_fk = users.id and type = "admin" and is_open = 1), "") as admin_token')
                    , DB::raw('ifnull((select api_token from usr_user_proj_logistics where user_fk = users.id and type = "user" and is_open = 1), "") as user_token')
                    , DB::raw('ifnull((select api_token from usr_user_proj_logistics where user_fk = users.id and type = "deliveryman" and is_open = 1), "") as deliveryman_token')
                )
                ->where('users.id', '=', $curr_user_id)
                ->get()->first();

            $user = User::where('id', $user_id)->get()->first();
            if (isset($lgt_users['user'])) {
                if (isset($user_lgt_token->user_token)) {
                    if ("0" == $lgt_users['user']) {
                        $api_user_delete = Http::withToken($user_lgt_token->user_token)
                            ->get(env('LOGISTIC_URL') . '/api/user/user/delete/' . $user->account)
                            ->body();
                        $api_user_delete = json_decode($api_user_delete);
                        if ("0" == $api_user_delete->status) {
                            UserProjLogistics::where('user_fk', '=', $user_id)->where('type', '=', "user")->update(["is_open" => 0]);
                            return ['success' => 1, 'error_msg' => ""];
                        } else {
                            return ['success' => 0, 'error_key' => 'lgt_user', 'error_msg' => $api_user_delete->message];
                        }
                    } else if ("1" == $lgt_users['user']) {
                        //判斷是否在自己資料表有資料
                        //  有:更新 無:新增一筆
                        //打API創建該人員後 若有回傳API_TOKEN則存入 回傳後做恢復
                        $api_token = "";
                        $api_user_create = Http::withToken($user_lgt_token->user_token)
                            ->post(env('LOGISTIC_URL') . '/api/user/user/create/', [
                                'account' => $user->account
                                , 'name' => $user->name
                            ])
                            ->body();
                        $api_user_create = json_decode($api_user_create);
                        if ("0" == $api_user_create->status) {
                            //新增
                            $api_token = $api_user_create->data->api_token;
                            UserProjLogistics::create([
                                'user_fk' => $user_id
                                , 'type' => 'user'
                                , 'account' => $user->account
                                , 'name' => $user->name
                                , 'api_token' => $api_token
                                , 'is_open' => 1
                            ]);
                            return ['success' => 1, 'error_msg' => ""];
                        } else if ("E01" == $api_user_create->status && "The account has already been taken." == $api_user_create->message->account[0]) {
                            //已建立過
                            //打API 確認開啟
                            $api_user_recover = Http::withToken($user_lgt_token->user_token)
                                ->get(env('LOGISTIC_URL') . '/api/user/user/recover/' . $user->account)
                                ->body();
                            $api_user_recover = json_decode($api_user_recover);
                            if ("0" == $api_user_recover->status) {
                                $user_proj_lgt_user = UserProjLogistics::where('user_fk', '=', $user_id)->where('type', '=', "user")->get()->first();
                                if (null != $user_proj_lgt_user) {
                                    UserProjLogistics::where('user_fk', '=', $user_id)->where('type', '=', "user")->update(["is_open" => 1]);
                                    return ['success' => 1, 'error_msg' => ""];
                                } else {
                                    return ['success' => 0, 'error_key' => 'lgt_user', 'error_msg' => '物流專案已有此人員 請工程師手動同步新增到本專案'];
                                }
                            } else {
                                return ['success' => 0, 'error_key' => 'lgt_user', 'error_msg' => $api_user_recover->message];
                            }
                        } else {
                            return ['success' => 0, 'error_key' => 'lgt_user', 'error_msg' => $api_user_create->message];
                        }
                    }
                } else {
                    return ['success' => 0, 'error_key' => 'lgt_user', 'error_msg' => '無權限可編輯託運人員'];
                }
            }
        } catch (\Exception $e) {
            return ['success' => 0, 'error_key' => 'lgt_user', 'error_msg' => $e->getMessage()];
        }
    }
}
