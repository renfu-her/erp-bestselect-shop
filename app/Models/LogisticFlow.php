<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LogisticFlow extends Model
{
    use HasFactory;
    protected $table = 'dlv_logistic_flow';
    protected $guarded = [];

    //更新出貨單物態
    public static function createDeliveryStatus($user, $delivery_id, $logistic_status)
    {
        if (null == $logistic_status) {
            return ['success' => 0, 'error_msg' => '無此物流狀態'];
        }

        //判斷最新一筆物態是否相同 不同才做
//        $logisticFlow = LogisticFlow::getListByDeliveryId($delivery_id);
//        $logisticFlow_get = $logisticFlow->get()->first();
//        if (null == $logisticFlow_get || $logisticFlow_get->status_code != $logistic_status->key)
//        {
            LogisticFlow::create([
                'delivery_id' => $delivery_id,
                'status' => $logistic_status->value,
                'status_code' => $logistic_status->key,
                'user_id' => $user->id ?? null,
                'user_name' => $user->name ?? null,
            ]);
            return ['success' => 1, 'error_msg' => ""];
//        }
    }

    /**
     * 取得出貨單目前最新物態
     * @param $delivery_id 出貨單ID
     */
    public static function getListByDeliveryId($delivery_id) {
        $query = LogisticFlow::where('delivery_id', $delivery_id)
            ->orderByDesc('dlv_logistic_flow.created_at');
        return $query;
    }
}
