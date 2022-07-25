<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SubOrders extends Model
{
    use HasFactory;
    protected $table = 'ord_sub_orders';
    protected $guarded = [];
    public $timestamps = false;

    public static function getListWithShiGroupById($sub_order_id) {
        $sub_order = DB::table('ord_sub_orders')->where('ord_sub_orders.id', $sub_order_id)
            ->leftJoin('shi_group', function($join) {
                $join->on('shi_group.id', '=', 'ord_sub_orders.ship_event_id');
                $join->where('ord_sub_orders.ship_category', '=', 'deliver');
            })
            ->select('ord_sub_orders.id as id'
                , 'ord_sub_orders.order_id as order_id'
                , 'ord_sub_orders.sn as sn'
                , 'ord_sub_orders.ship_sn as ship_sn'
                , 'ord_sub_orders.ship_category as ship_category'
                , 'ord_sub_orders.ship_category_name as ship_category_name'
                , 'ord_sub_orders.ship_event as ship_event'
                , 'ord_sub_orders.ship_event_id as ship_event_id'
                , 'ord_sub_orders.ship_temp as ship_temp'
                , 'ord_sub_orders.ship_temp_id as ship_temp_id'
                , 'ord_sub_orders.ship_rule_id as ship_rule_id'
                , 'ord_sub_orders.dlv_fee as dlv_fee'
                , 'ord_sub_orders.status as status'
                , 'ord_sub_orders.total_price as total_price'
                , 'ord_sub_orders.statu as status'
                , 'ord_sub_orders.statu_code as statu_code'
                , 'shi_group.id as ship_group_id' );
        return $sub_order;
    }


    //更新物流單資料
    public static function updateLogisticData($id, $package_sn, $actual_ship_group_id, $cost, $memo) {
        $data = DB::table('ord_sub_orders')->where('id', $id);
        $dataGet = null;
        if (null != $data) {
            $dataGet = $data->first();
        }

        if (null != $dataGet) {
            $updateData = [
                'package_sn' => $package_sn
                , 'actual_ship_group_id' => $actual_ship_group_id
//                , 'cost' => $cost
//                , 'memo' => $memo
            ];

            $data->update($updateData);

            return ['success' => 1, 'error_msg' => "", 'id' => $id];
        } else {
            return ['success' => 0, 'error_msg' => "查無資料"];
        }
    }
}
