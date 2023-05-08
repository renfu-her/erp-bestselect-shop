<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class PurchaseElementReturn extends Model
{
    use HasFactory;
    protected $table = 'pcs_purchase_element_return';
    protected $guarded = [];


    public static function audited_item_list($inbound_id = null, $purchase_id = null, $purchase_item_id = null, $return_id = null, $return_item_id = null)
    {
        $result = DB::table('pcs_purchase_element_return as audited_items')
            // ->leftJoinSub(PurchaseInbound::getInboundList([]), 'inbound', function($join) {
            //     $join->on('inbound.inbound_id', 'audited_items.inbound_id');
            // })
            ->leftJoin('pcs_purchase_inbound as inbound', function($join) {
                $join->on('inbound.id', 'audited_items.inbound_id');
                $join->on('inbound.event_item_id', '=', 'audited_items.purchase_item_id');
                $join->on('inbound.event_id', '=', 'audited_items.purchase_id');
                $join->where('inbound.event', 'purchase');
            })
            ->leftJoin('pcs_purchase_return_items as r_items', function($join) {
                $join->on('r_items.id', 'audited_items.return_item_id');
                $join->where('r_items.show', 1);
                $join->where('r_items.type', 0);

            })
            ->select(
                'audited_items.purchase_id',
                'audited_items.purchase_item_id',
                'audited_items.return_id',
                'audited_items.return_item_id',
                'audited_items.qty',
                'audited_items.memo',

                'r_items.product_style_id',
                'r_items.sku',
                'r_items.product_title',
                'r_items.price',
                // 'r_items.qty',
                // 'r_items.memo',

                'inbound.id as inbound_id', //入庫ID
                'inbound.sn as inbound_sn', //入庫sn
                'inbound.inbound_num as inbound_num', //入庫實進數量
                DB::raw('(inbound.sale_num + inbound.csn_num + inbound.consume_num + inbound.back_num + inbound.scrap_num) as shipped_num'),
                'inbound.scrap_num as inbound_return_num', //採購已退出數量
                'inbound.depot_id as depot_id', //入庫倉庫ID
                'inbound.depot_name as depot_name', //入庫倉庫名稱
                'inbound.inbound_user_id as inbound_user_id', //入庫人員ID
                'inbound.inbound_user_name as inbound_user_name', //入庫人員名稱
                'inbound.close_date as inbound_close_date',
                'inbound.prd_type as inbound_prd_type',
                'inbound.memo as inbound_memo', //入庫備註
                // 'inbound.title as inbound_title', //入庫商品名稱
                // 'inbound.sku as inbound_sku', //入庫商品SKU
                DB::raw('DATE_FORMAT(inbound.expiry_date,"%Y-%m-%d") as expiry_date'), //有效期限
                DB::raw('DATE_FORMAT(inbound.inbound_date,"%Y-%m-%d") as inbound_date'), //入庫日期
                DB::raw('DATE_FORMAT(inbound.deleted_at,"%Y-%m-%d") as deleted_at') //刪除日期
            );

            if ($inbound_id) {
                $result->where('inbound.inbound_id', '=', $inbound_id);
            }

            if ($purchase_id) {
                $result->where('audited_items.purchase_id', '=', $purchase_id);
            }

            if ($purchase_item_id) {
                $result->where('audited_items.purchase_item_id', '=', $purchase_item_id);
            }

            if ($return_id) {
                $result->where('audited_items.return_id', '=', $return_id);
            }

            if ($return_item_id) {
                $result->where('audited_items.return_item_id', '=', $return_item_id);
            }

        return $result;
    }
}
