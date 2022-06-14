<?php

namespace App\Models;

use App\Enums\ProjLogisticLog\Feature;
use App\Helpers\ITTMSHttp;
use App\Helpers\LogisticHTTP;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
            return ['success' => 0, 'error_msg' => '無權限可編輯託運人員'];
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
                    return ['success' => 0, 'error_msg' => $api_user_delete->message];
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
                            return ['success' => 0, 'error_msg' => '物流專案已有此人員 請工程師手動同步新增到本專案'];
                        }
                    } else {
                        return ['success' => 0, 'error_msg' => $api_user_recover->message];
                    }
                } else {
                    return ['success' => 0, 'error_msg' => $api_user_create->message];
                }
            }
        } catch (\Exception $e) {
            return ['success' => 0, 'error_msg' => $e->getMessage()];
        }
    }

    //2.3.4. 取得倉庫列表 GET
    public static function getDepot($user_token) {
        if (false == isset($user_token)) {
            return ['success' => 0, 'error_msg' => '無權限 api_token'];
        }
        try {
            $http = Http::withToken($user_token);
            $request = $http->get(env('LOGISTIC_URL') . '/api/user/depot/get');
            $response = json_decode($request->body());
            if ("0" == $response->status) {
                return ['success' => 1, 'error_msg' => "", 'data' => $response->data];
            } else {
                return ['success' => 0, 'error_msg' => $response->message];
            }
        } catch (\Exception $e) {
            return ['success' => 0, 'error_msg' => $e->getMessage()];
        }
    }

    //2.3.5. 取得溫層列表 GET
    public static function getTemp($user_token) {
        if (false == isset($user_token)) {
            return ['success' => 0, 'error_msg' => '無權限 api_token'];
        }
        try {
            $http = Http::withToken($user_token);
            $request = $http->get(env('LOGISTIC_URL') . '/api/user/temp/get');
            $response = json_decode($request->body());
            if ("0" == $response->status) {
                return ['success' => 1, 'error_msg' => "", 'data' => $response->data];
            } else {
                return ['success' => 0,'error_msg' => $response->message];
            }
        } catch (\Exception $e) {
            return ['success' => 0, 'error_msg' => $e->getMessage()];
        }
    }

    //2.3.6. 取得積材列表 GET
    public static function getDim($user_token, $temp_id) {
        if (false == isset($user_token)) {
            return ['success' => 0, 'error_msg' => '無權限 api_token'];
        }
        if (false == isset($temp_id)) {
            return ['success' => 0, 'error_msg' => '無temp_id'];
        }
        try {
            $http = Http::withToken($user_token);
            $request = $http->get(env('LOGISTIC_URL') . '/api/user/temp/'. $temp_id.'/dim/get');
            $response = json_decode($request->body());
            if ("0" == $response->status) {
                return ['success' => 1, 'error_msg' => "", 'data' => $response->data];
            } else {
                return ['success' => 0,'error_msg' => $response->message];
            }
        } catch (\Exception $e) {
            return ['success' => 0, 'error_msg' => $e->getMessage()];
        }
    }

    //2.3.7. 建立托運單 POST
    public static function createOrder($modify_user, $logistic_id, $user_token
        , $depot_id, $temp_id, $dim_id
        , $rcv_name, $rcv_tel, $rcv_addr
        , $memo, $order_no, $pickup_date
        , $items = null
        , $send_name, $send_tel, $send_addr
    ) {
        if (false == isset($user_token)) {
            return ['success' => 0, 'error_msg' => '無權限 api_token'];
        }
        try {
            $http = Http::withToken($user_token);
            $req_arr = [
                'depot_id' => $depot_id
                , 'temp_id' => $temp_id
                , 'dim_id' => $dim_id
                , 'name' => $rcv_name
                , 'tel' => $rcv_tel
                , 'addr' => $rcv_addr
                , 'order_no' => $order_no
                , 'pickup_date' => $pickup_date

                , 'send_name' => $send_name
                , 'send_tel' => $send_tel
                , 'send_addr' => $send_addr
                , 'order_memo' => $memo
            ];
            if (null != $items && true == is_array($items)) {
                $req_arr['items'] = json_encode($items);
            }
            $request = $http->post(env('LOGISTIC_URL') . '/api/user/order/create', $req_arr);
            $response = json_decode($request->body());
            LogisticProjLogisticLog::createData(Feature::create()->value, $logistic_id, $response->sn ?? null, $response->status, $req_arr, $items, $response, $modify_user);
            if ("0" == $response->status) {
                return ['success' => 1, 'error_msg' => "", 'sn' => $response->sn];
            } else {
                return ['success' => 0,'error_msg' => $response->message];
            }
        } catch (\Exception $e) {
            return ['success' => 0, 'error_msg' => $e->getMessage()];
        }
    }

    //2.3.8. 託運單可否刪除 POST
    public static function isEnableDel($modify_user, $logistic_id, $user_token, $sn) {
        if (false == isset($user_token)) {
            return ['success' => 0, 'error_msg' => '無權限 api_token'];
        }
        if (false == isset($sn)) {
            return ['success' => 0, 'error_msg' => '無sn'];
        }
        try {
            $http = Http::withToken($user_token);
            $req_arr = [
                'sn' => $sn
            ];
            $request = $http->post(env('LOGISTIC_URL') . '/api/user/order/is-enable-del', $req_arr);
            $response = json_decode($request->body());
            LogisticProjLogisticLog::createData(Feature::is_enable_del()->value, $logistic_id, $sn, $response->status, $req_arr, null, $response, $modify_user);
            if ("0" == $response->status) {
                if ("true" == $response->data) {
                    return ['success' => 1, 'error_msg' => ""];
                } else {
                    return ['success' => 0,'error_msg' => '無法刪除託運單'];
                }
            } else {
                return ['success' => 0,'error_msg' => $response->message];
            }
        } catch (\Exception $e) {
            return ['success' => 0, 'error_msg' => $e->getMessage()];
        }
    }

    //2.3.9. 刪除託運單 POST
    public static function delSn($modify_user, $logistic_id, $user_token, $sn) {
        $isEnable = self::isEnableDel($modify_user, $logistic_id, $user_token, $sn);
        if ($isEnable['success'] == 0) {
            return $isEnable;
        } else {
            try {
                $http = Http::withToken($user_token);
                $req_arr = [
                    'sn' => $sn
                ];
                $request = $http->post(env('LOGISTIC_URL') . '/api/user/order/del', $req_arr);
                $response = json_decode($request->body());
                LogisticProjLogisticLog::createData(Feature::del_order()->value, $logistic_id, $sn, $response->status, $req_arr, null, $response, $modify_user);
                if ("0" == $response->status) {
                    return ['success' => 1, 'error_msg' => ""];
                } else {
                    return ['success' => 0,'error_msg' => $response->message];
                }
            } catch (\Exception $e) {
                return ['success' => 0, 'error_msg' => $e->getMessage()];
            }
        }
    }
}
