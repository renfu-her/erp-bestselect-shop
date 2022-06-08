<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UserProjLogistics extends Model
{
    use HasFactory;
    protected $table = 'usr_user_proj_logistics';
    protected $guarded = [];
    public $timestamps = false;

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
