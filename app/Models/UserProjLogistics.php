<?php

namespace App\Models;

use App\Helpers\ITTMSHttp;
use App\Helpers\LogisticHTTP;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class UserProjLogistics extends Model
{
    use HasFactory;
    protected $table = 'usr_user_proj_logistics';
    protected $guarded = [];
    public $timestamps = false;

    //變更託運人員在物流專案是否開啟
    public static function modifyLogisticUser($user_token, int $user_id, array $lgt_users) {
        if (false == isset($user_token)) {
            return ['success' => 0, 'error_key' => 'lgt_user', 'error_msg' => '無權限可編輯託運人員'];
        }
        try {
            $user = User::where('id', $user_id)->get()->first();
            if ("0" == $lgt_users['user']) {
                $http = Http::withToken($user_token);
                $api_user_delete = $http->get(env('LOGISTIC_URL') . '/api/user/user/delete/' . $user->account);
                $api_user_delete = json_decode($api_user_delete->body());
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
                $http = Http::withToken($user_token);
                $api_user_create = $http->post(env('LOGISTIC_URL') . '/api/user/user/create/', [
                    'account' => $user->account
                    , 'name' => $user->name
                ]);
                $api_user_create = json_decode($api_user_create->body());
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
                    $http = Http::withToken($user_token);
                    $api_user_recover = $http->get(env('LOGISTIC_URL') . '/api/user/user/recover/' . $user->account);
                    $api_user_recover = json_decode($api_user_recover->body());
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
        } catch (\Exception $e) {
            return ['success' => 0, 'error_key' => 'lgt_user', 'error_msg' => $e->getMessage()];
        }
    }
}
