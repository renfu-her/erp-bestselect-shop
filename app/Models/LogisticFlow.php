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

    //新增出貨單物態 並更新 出貨單物態欄位
    public static function createDeliveryStatus($user, $delivery_id, $logistic_status)
    {
        if (null == $logistic_status) {
            return ['success' => 0, 'error_msg' => '無此物流狀態'];
        }

        $delivery = Delivery::where('id', $delivery_id);
        $deliveryGet = $delivery->get()->first();
        if (null == $deliveryGet) {
            return ['success' => 0, 'error_msg' => '無此出貨單'];
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

            $delivery->update([
                'logistic_status' => $logistic_status->value,
                'logistic_status_code' => $logistic_status->key,
            ]);
            return ['success' => 1, 'error_msg' => ""];
//        }
    }

    public static function deleteById($id)
    {
        $logisticFlowToDel = LogisticFlow::where('id', $id);
        $logisticFlowToDelGet = $logisticFlowToDel->get()->first();
        $delivery_id = $logisticFlowToDelGet->delivery_id;
        $logisticFlowToDel->delete();
        //取得最後一筆
        $logisticFlowLast = LogisticFlow::where('delivery_id', $delivery_id)->orderByDesc('id')->get()->first();
        //回寫回出貨單
        Delivery::where('id', $delivery_id)->update([
            'logistic_status' => $logisticFlowLast->status,
            'logistic_status_code' => $logisticFlowLast->status_code,
        ]);
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
