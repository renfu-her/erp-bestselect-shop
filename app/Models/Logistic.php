<?php

namespace App\Models;

use App\Enums\Delivery\Event;
use App\Helpers\IttmsDBB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Logistic extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dlv_logistic';
    protected $guarded = [];

    public static function getData($id) {
        $data = Logistic::where('id', $id);
        return $data;
    }

    //新增資料
    public static function createData($delivery_id)
    {
        $data = Logistic::where('delivery_id', $delivery_id);
        $dataGet = null;
        if (null != $data) {
            $dataGet = $data->first();
        }

        $result = null;
        if (null == $dataGet) {
            $delivery = Delivery::where('id', $delivery_id)->withTrashed()->get()->first();
            $sn = Sn::createSn('logistic', 'LG', 'ymd', 5);

            $result = Logistic::create([
                'sn' => $sn,
                'delivery_id' => $delivery->id,
//                'ship_group_id' => $delivery->ship_group_id,
            ])->id;
            return ['success' => 1, 'error_msg' => "", 'id' => $result];
        } else {
            $result = $dataGet->id;
            return ['success' => 1, 'error_msg' => "", 'id' => $result];
        }
    }

    //更新物流單資料
    public static function updateData($id, $package_sn, $ship_group_id, $qty, $cost, $memo) {
        $data = Logistic::where('id', $id);
        $dataGet = null;
        if (null != $data) {
            $dataGet = $data->first();
        }

        if (null != $dataGet) {
            $updateData = [
                'package_sn' => $package_sn
                , 'ship_group_id' => $ship_group_id
                , 'qty' => $qty
                , 'cost' => $cost
                , 'memo' => $memo
            ];

            $data->update($updateData);

            return ['success' => 1, 'error_msg' => "", 'id' => $id];
        } else {
            return ['success' => 0, 'error_msg' => "查無資料"];
        }
    }

    public static function deleteById($id)
    {
        return IttmsDBB::transaction(function () use ($id
        ) {
            $logistic = Logistic::where('id', $id)->withTrashed();
            $logistic_get = $logistic->first();
            if (null == $logistic_get) {
                return ['success' => 0, 'error_msg' => "無此物流單"];
            } else if ($logistic_get->audit_date != null) {
                //若已送出審核 則代表已扣除相應入庫單數量 則不給刪除
                return ['success' => 0, 'error_msg' => "已送出審核，無法刪除"];
            } else {
                $logistic->delete();
                return ['success' => 1, 'error_msg' => ""];
            }
        });
    }

    public static function updateProjlgtOrderSn($logistic_id, $order_sn, $dlv_event, $dvl_event_id) {
        $updateData = ['projlgt_order_sn' => $order_sn];

        Logistic::where('id', $logistic_id)->update($updateData);

        $event_table = null;
        if (Event::order()->value == $dlv_event) {
            $event_table = app(SubOrders::class)->getTable();
        } else if (Event::consignment()->value == $dlv_event) {
            $event_table = app(Consignment::class)->getTable();
        } else if (Event::csn_order()->value == $dlv_event) {
            $event_table = app(CsnOrder::class)->getTable();
        }
        DB::table($event_table. ' as event_tb')->where('event_tb.id', '=', $dvl_event_id)->update($updateData);
    }


    public static function update_logistic($parm)
    {
        $update = [];
        if(Arr::exists($parm, 'note')){
            $update['memo'] = $parm['note'];
        }
        if(Arr::exists($parm, 'po_note')){
            $update['po_note'] = $parm['po_note'];
        }

        self::where('id', $parm['logistic_id'])->update($update);
    }
}
