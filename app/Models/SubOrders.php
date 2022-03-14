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
}
