<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PayingOrders extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'pcs_paying_orders';
    protected $guarded = [];

    public static function getPayingOrdersWithPurchaseID($purchase_id) {
        $result = DB::table('pcs_paying_orders as paying_order')
            ->select('paying_order.id as id'
                , 'paying_order.type as type'
                , 'paying_order.sn as sn'
                , 'paying_order.price as price'
            )
            ->selectRaw('DATE_FORMAT(paying_order.pay_date,"%Y-%m-%d") as pay_date')

            ->where('paying_order.purchase_id', '=', $purchase_id)
            ->whereNull('paying_order.deleted_at');
        return $result;
    }
}
