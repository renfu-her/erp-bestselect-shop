<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PurchaseReturnItem extends Model
{
    use HasFactory;
    protected $table = 'pcs_purchase_return_items';
    protected $guarded = [];


    public static function return_item_list($return_id = null, $show = null, $type = null)
    {
        $result = DB::table('pcs_purchase_return_items as r_items')
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'l_grade', function($join) {
                $join->on('l_grade.primary_id', 'r_items.grade_id');
            })
            ->leftJoin('pcs_purchase_items as p_items', function($join) {
                $join->on('p_items.id', 'r_items.purchase_item_id');
            })
            // ->leftJoin('pcs_purchase_inbound as inbound', function($join) {
            //     $join->on('inbound.event_item_id', '=', 'r_items.purchase_item_id');
            //     $join->on('inbound.event_id', '=', 'p_items.purchase_id');
            //     $join->where('inbound.event', 'purchase');
            // })
            ->leftJoin('prd_product_styles as p_style', function($join) {
                $join->on('p_style.id', 'r_items.product_style_id');
            })
            ->leftJoin('prd_products as product', function($join) {
                $join->on('product.id', 'p_style.product_id');
            })
            ->leftJoin('usr_users as user', function($join) {
                $join->on('user.id', 'product.user_id');
            })
            ->select(
                'r_items.*',
                DB::raw('(r_items.price * r_items.qty) as sub_total'),

                'r_items.qty as num',
                'p_items.num as p_num',
                'l_grade.primary_id as grade_id',
                'l_grade.code as grade_code',
                'l_grade.name as grade_name',
                'product.id as product_id',
                'product.has_tax as product_taxation',
                'user.name as product_user_name',

                // 'inbound.id as inbound_id', //入庫ID
                // 'inbound.sn as inbound_sn', //入庫sn
                // 'inbound.inbound_num as inbound_num', //入庫實進數量
                // DB::raw('(inbound.sale_num + inbound.csn_num + inbound.consume_num + inbound.back_num + inbound.scrap_num) as shipped_num'),
                // 'inbound.scrap_num as inbound_return_num', //採購已退出數量
                // 'inbound.depot_id as depot_id', //入庫倉庫ID
                // 'inbound.depot_name as depot_name', //入庫倉庫名稱
                // 'inbound.inbound_user_id as inbound_user_id', //入庫人員ID
                // 'inbound.inbound_user_name as inbound_user_name', //入庫人員名稱
                // 'inbound.close_date as inbound_close_date',
                // 'inbound.prd_type as inbound_prd_type',
                // 'inbound.memo as inbound_memo' //入庫備註
            )
            // ->selectRaw('DATE_FORMAT(inbound.expiry_date,"%Y-%m-%d") as expiry_date') //有效期限
            // ->selectRaw('DATE_FORMAT(inbound.inbound_date,"%Y-%m-%d") as inbound_date') //入庫日期
            // ->selectRaw('DATE_FORMAT(inbound.deleted_at,"%Y-%m-%d") as deleted_at') //刪除日期
            ;

            if ($return_id) {
                $result->where('r_items.return_id', '=', $return_id);
            }

            if (! is_null($show)) {
                $result->where('r_items.show', '=', $show);
            }

            if (! is_null($type)) {
                $result->where('r_items.type', '=', $type);
            }

        return $result;
    }


    public static function update_return_item($parm)
    {
        $update = [];
        if(Arr::exists($parm, 'grade_id')){
            $update['grade_id'] = $parm['grade_id'];
        }
        if(Arr::exists($parm, 'memo')){
            $update['memo'] = $parm['memo'];
        }
        if(Arr::exists($parm, 'ro_note')){
            $update['ro_note'] = $parm['ro_note'];
        }
        if(Arr::exists($parm, 'po_note')){
            $update['po_note'] = $parm['po_note'];
        }

        self::where('id', $parm['return_item_id'])->update($update);
    }
}
