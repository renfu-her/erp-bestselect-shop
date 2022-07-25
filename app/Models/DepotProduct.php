<?php

namespace App\Models;

use App\Enums\Delivery\Event;
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
    public static function productExistInboundList($send_depot_id = null, $receive_depot_id = null, $type = null, $keyword = null)
    {
        $extPrdStyleList_send = PurchaseInbound::getExistInboundProductStyleList([$send_depot_id]);

        $querySelectList = DB::table('prd_product_depot_select as select_list')
            ->leftJoin('prd_salechannel_style_price as p', 'select_list.product_style_id', '=', 'p.style_id')
            ->leftJoin('prd_products as product', 'select_list.product_id', '=', 'product.id')
            ->leftJoin('prd_product_styles as style', 'select_list.product_style_id', '=', 'style.id')
            ->select(
                'select_list.id as select_id',
                'select_list.depot_id',
                'select_list.depot_product_no',
                'select_list.ost_price',
                'select_list.depot_price',
                'select_list.product_id as product_id',
                'select_list.product_style_id as product_style_id',
                'product.title as title',
                'product.type as type',
                'style.title as spec',
                'style.sku as sku',
                'p.sale_channel_id',
            )
            ->whereNull('select_list.deleted_at')
            ->where('p.sale_channel_id', 1)
        ;
        if ($receive_depot_id) {
            $querySelectList->where('select_list.depot_id', $receive_depot_id);
        }

        if ($type && $type != 'all') {
            $querySelectList->where('product.type', $type);
        }

        if ($keyword) {
            $querySelectList->where(function ($query) use ($keyword) {
                $query->Where('product.title', 'like', "%{$keyword}%")
                    ->orWhere('style.title', 'like', "%{$keyword}%")
                    ->orWhere('style.sku', 'like', "%{$keyword}%");
            });
        }


        $re = DB::query()->fromSub($querySelectList, 'select_list')
            ->leftJoinSub($extPrdStyleList_send, 'inbound', function($join) use($send_depot_id) {
                //對應到入庫倉可入到進貨倉 相同的product_style_id
                $join->on('inbound.product_style_id', '=', 'select_list.product_style_id');
            })
            ->select(
                'select_list.select_id as select_id',
                'select_list.depot_id',
                'select_list.depot_product_no',
                'select_list.ost_price',
                'select_list.depot_price',
                'select_list.product_id as product_id',
                'select_list.product_style_id as style_id',
                'inbound.depot_id as inbound_depot_id',
                'select_list.title as product_title',
                'select_list.type as prd_type',
                'select_list.spec as spec',
                'select_list.sku as sku',
                DB::raw('CASE select_list.type
                    WHEN "p" THEN IFNULL(inbound.total_in_stock_num,"")
                   ELSE "" END as total_in_stock_num'),
                DB::raw('CASE select_list.type WHEN "p" THEN "一般商品" WHEN "c" THEN "組合包商品" END as type_title'),
            )
//            ->whereNotNull('inbound.depot_id')
            ->orderBy('select_list.product_id', 'ASC')
            ->orderBy('select_list.product_style_id', 'ASC');

//        dd($re->bindings, $re->getBindings(), $re->get(), IttmsUtils::getEloquentSqlWithBindings($re));
        return $re;
    }

    public static function ProductCsnExistInboundList($depot_id, $type = null, $keyword = null) {
        $queryInbound = PurchaseInbound::getCsnExistInboundProductStyleList(Event::consignment()->value);

        $queryDepotProduct = DB::query()->fromSub(DepotProduct::product_list(), 'prd_list')
            ->leftJoinSub($queryInbound, 'inbound', function($join) {
                $join->on('inbound.product_style_id', 'prd_list.id')
                    ->on('inbound.depot_id', 'prd_list.depot_id');
            })
            ->select(
                'prd_list.product_id as product_id'
                ,'prd_list.depot_id as depot_id'
                ,'prd_list.sku as sku'
                ,'prd_list.id as product_style_id'
                ,'prd_list.product_title as product_title'
                ,'prd_list.spec as spec'
                ,'prd_list.depot_price as depot_price'

                , DB::raw('ifnull(inbound.depot_name, "") as depot_name')  //入庫倉庫名稱
                , DB::raw('ifnull(inbound.inbound_num, 0) as inbound_num')
                , DB::raw('ifnull(inbound.sale_num, 0) as sale_num')
                , DB::raw('ifnull(inbound.csn_num, 0) as csn_num')
                , DB::raw('ifnull(inbound.consume_num, 0) as consume_num')
                , DB::raw('ifnull(inbound.back_num, 0) as back_num')
                , DB::raw('ifnull(inbound.scrap_num, 0) as scrap_num')
                , DB::raw('ifnull(inbound.available_num, 0) as available_num')
                , DB::raw('ifnull(inbound.prd_type, "") as prd_type')
            )
            //->where('inbound.available_num', '<>', 0)
        ;

        if ($depot_id) {
            $queryDepotProduct->where('prd_list.depot_id', $depot_id);
        }
        if ($keyword) {
            $queryDepotProduct->where(function ($query) use ($keyword) {
                $query->Where('prd_list.product_title', 'like', "%{$keyword}%")
                    ->orWhere('prd_list.spec', 'like', "%{$keyword}%")
                    ->orWhere('prd_list.sku', 'like', "%{$keyword}%");
            });
        }

        if ($type && $type != 'all') {
            $queryDepotProduct->where('inbound.prd_type', $type);
        }
        return $queryDepotProduct;
    }
}
