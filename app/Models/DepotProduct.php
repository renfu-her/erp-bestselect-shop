<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

class DepotProduct extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'prd_product_depot_select';

    protected $grard = [];


    public static function product_list($depot_id = null, $keyword = null, $type = null)
    {
        $re = DB::table('prd_product_depot_select as select_list')
            ->leftJoin('depot', 'select_list.depot_id', '=', 'depot.id')
            ->leftJoin('prd_products as product', 'select_list.product_id', '=', 'product.id')
            ->leftJoin('prd_product_styles as style', 'select_list.product_style_id', '=', 'style.id')
            ->leftJoin('usr_users as user', 'select_list.updated_users_id', '=', 'user.id')

            ->leftJoin('prd_salechannel_style_price as p', 'style.id', '=', 'p.style_id')

            ->where(function ($q) use ($keyword) {
                if ($keyword) {
                    $q->where('product.title', 'like', "%$keyword%");
                    $q->orWhere('style.title', 'like', "%$keyword%");
                    $q->orWhere('style.sku', 'like', "%$keyword%");
                }
            })
            ->whereNotNull('style.sku')
            ->whereNull('style.deleted_at')
            ->whereNull('select_list.deleted_at')
            ->where('p.sale_channel_id', 1)

            ->select(
                'select_list.id as select_id',
                'select_list.depot_id',
                'select_list.depot_product_no',
                'select_list.ost_price',
                'select_list.depot_price',
                'style.id',
                'style.sku',
                'product.title as product_title',
                'product.id as product_id',
                'style.title as spec',
                'style.safety_stock',
                'style.in_stock',
                'style.total_inbound',
                'p.sale_channel_id',
                'p.dealer_price',
                'p.origin_price',
                'p.price',
                'p.bonus',
                'p.dividend',
                DB::raw('CASE product.type WHEN "p" THEN "一般商品" WHEN "c" THEN "組合包商品" END as type_title'),
            );

        if ($depot_id) {
            $re->where('select_list.depot_id', $depot_id);
        }

        if ($type && $type != 'all') {
            $re->where('style.type', $type);
        }

        return $re;
    }

    /** 寄倉 出貨倉可入到入庫倉的商品款式庫存列表
     * @param null $send_depot_id 出貨倉
     * @param null $receive_depot_id 入庫倉
     * @param null $type 'all' => '不限', 'p' => '一般', 'c' => '組合包',
     */
    public static function productExistInboundList($send_depot_id = null, $receive_depot_id = null, $type = null)
    {
        $extPrdStyleList_send = PurchaseInbound::getExistInboundProductStyleList([$send_depot_id]);

        $re = DB::table('prd_product_depot_select as select_list')
            ->leftJoin('prd_salechannel_style_price as p', 'select_list.product_style_id', '=', 'p.style_id')
            ->leftJoinSub($extPrdStyleList_send, 'inbound', function($join) use($send_depot_id) {
                //對應到入庫倉可入到進貨倉 相同的product_style_id
                $join->on('inbound.product_style_id', '=', 'select_list.product_style_id');
                if ($send_depot_id) {
                    $join->where('inbound.depot_id', $send_depot_id);
                }
            })
            ->select(
                'select_list.id as select_id',
                'select_list.depot_id',
                'select_list.depot_product_no',
                'select_list.ost_price',
                'select_list.depot_price',
                'inbound.product_id as product_id',
                'inbound.depot_id as inbound_depot_id',
                'inbound.product_style_id as id',
                'inbound.product_title as product_title',
                'inbound.title as spec',
                'inbound.sku',
                'inbound.total_in_stock_num',
                DB::raw('CASE inbound.product_type WHEN "p" THEN "一般商品" WHEN "c" THEN "組合包商品" END as type_title'),
            )
            ->whereNull('select_list.deleted_at')
            ->where('p.sale_channel_id', 1)

            ->orderBy('inbound.product_id', 'ASC')
            ->orderBy('inbound.product_style_id', 'ASC');

        if ($receive_depot_id) {
            $re->where('select_list.depot_id', $receive_depot_id);
        }

        if ($type && $type != 'all') {
            $re->where('inbound.product_type', $type);
        }
        //修正未知錯誤 底層做mergebinding時，多一個值
        unset($re->bindings['join'][6]);
        return $re;
    }
}
