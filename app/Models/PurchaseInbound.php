<?php

namespace App\Models;

use App\Enums\Purchase\InboundStatus;
use App\Enums\Purchase\LogFeatureEvent;
use App\Enums\Purchase\LogFeature;
use App\Enums\StockEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PurchaseInbound extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pcs_purchase_inbound';
    protected $guarded = [];

    public static function createInbound($purchase_id, $purchase_item_id, $product_style_id, $expiry_date = null, $inbound_date = null, $inbound_num = 0, $depot_id = null, $depot_name = null, $inbound_user_id = null, $inbound_user_name = null, $memo = null)
    {
        $id = self::create([
            "purchase_id" => $purchase_id,
            "purchase_item_id" => $purchase_item_id,
            "product_style_id" => $product_style_id,
            "expiry_date" => $expiry_date,
            "inbound_date" => $inbound_date,
            "inbound_num" => $inbound_num,
            "depot_id" => $depot_id,
            "depot_name" => $depot_name,
            "inbound_user_id" => $inbound_user_id,
            "inbound_user_name" => $inbound_user_name,
            "memo" => $memo
        ])->id;

        //入庫 新增入庫數量
        //寫入ProductStock
        PurchaseItem::updateArrivedNum($purchase_item_id, $inbound_num);
        PurchaseLog::stockChange($purchase_id, $product_style_id, LogFeature::inbound()->value, $id, LogFeatureEvent::inbound_add()->value, $inbound_num, null, $inbound_user_id, $inbound_user_name);
        ProductStock::stockChange($product_style_id, $inbound_num, StockEvent::inbound()->value, $id, null, true);

        return $id;
    }

    //取消入庫 刪除資料
    public static function delInbound($id, $user_id)
    {
        return DB::transaction(function () use (
            $id,
            $user_id
        ) {
            $inboundData = PurchaseInbound::where('id', '=', $id);
            $inboundDataGet = $inboundData->get()->first();
            if (null != $inboundDataGet) {
                //刪除
                //判斷是否已結單 有則不能刪
                $purchaseData = DB::table('pcs_purchase as purchase')
                    ->leftJoin('pcs_purchase_inbound as inbound', 'inbound.purchase_id', '=', 'purchase.id')
                    ->select('purchase.close_date as close_date')
                    ->where('purchase.id', '', $inboundDataGet->purchase_id)
                    ->get()->first();
                if (null != $purchaseData && null != $purchaseData->close_date) {
                    return ['success' => 0, 'error_msg' => 'purchase already close, so cant be delete'];
                }
                //判斷是否有賣出過 有則不能刪
                //寫入ProductStock
                else if (is_numeric($inboundDataGet->sale_num) && 0 < $inboundDataGet->sale_num) {
                    return ['success' => 0, 'error_msg' => 'inbound already sell'];
                } else {
                    $qty = $inboundDataGet->inbound_num * -1;
                    PurchaseItem::updateArrivedNum($inboundDataGet->purchase_item_id, $qty);
                    PurchaseLog::stockChange($inboundDataGet->purchase_id, $inboundDataGet->product_style_id, LogFeature::inbound()->value, $id, LogFeatureEvent::inbound_del()->value, $qty, null, $inboundDataGet->inbound_user_id, $inboundDataGet->inbound_user_name);
                    ProductStock::stockChange($inboundDataGet->product_style_id, $qty, StockEvent::inbound_del()->value, $id, $inboundDataGet->inbound_user_name . LogFeatureEvent::inbound_del()->getDescription(LogFeatureEvent::inbound_del()->value), true);
                    $inboundData->delete();
                }
            }
        });
    }

    //售出 更新資料
    public static function shippingInbound($id, $sale_num = 0)
    {
        return DB::transaction(function () use (
            $id,
            $sale_num
        ) {
            $inboundData = PurchaseInbound::where('id', '=', $id);
            $inboundDataGet = $inboundData->get()->first();
            if (null != $inboundDataGet) {
                if (($inboundDataGet->inbound_num - $inboundDataGet->sale_num - $sale_num) < 0) {
                    return ['success' => 0, 'error_msg' => '數量超出範圍'];
                } else {
                    PurchaseInbound::where('id', $id)
                        ->update(['sale_num' => DB::raw("sale_num + $sale_num")]);
                    PurchaseLog::stockChange($inboundDataGet->purchase_id, $inboundDataGet->product_style_id, LogFeature::inbound()->value, $id, LogFeatureEvent::inbound_shipping()->value, $sale_num, null, $inboundDataGet->inbound_user_id, $inboundDataGet->inbound_user_name);
                }
            }
        });
    }

    //歷史入庫
    public static function getInboundList($purchase_id)
    {
        $result = DB::table('pcs_purchase_inbound as inbound')
            ->leftJoin('prd_product_styles as style', 'style.id', '=', 'inbound.product_style_id')
            ->leftJoin('prd_products as product', 'product.id', '=', 'style.product_id')
            ->select('inbound.purchase_id as purchase_id' //採購ID
                , 'product.title as product_title' //商品名稱
                , 'style.title as style_title' //款式名稱
                , 'style.sku as style_sku' //款式SKU
                , 'inbound.id as inbound_id' //入庫ID
                , 'inbound.inbound_num as inbound_num' //入庫實進數量
                , 'inbound.depot_id as depot_id'  //入庫倉庫ID
                , 'inbound.depot_name as depot_name'  //入庫倉庫名稱
                , 'inbound.inbound_user_id as inbound_user_id'  //入庫人員ID
                , 'inbound.inbound_user_name as user_name' //入庫人員名稱
                , 'inbound.close_date as close_date'
                , 'inbound.memo as inbound_memo' //入庫備註
            )
            ->selectRaw('DATE_FORMAT(inbound.expiry_date,"%Y-%m-%d") as expiry_date') //有效期限
            ->selectRaw('DATE_FORMAT(inbound.inbound_date,"%Y-%m-%d") as inbound_date') //入庫日期
            ->selectRaw('DATE_FORMAT(inbound.deleted_at,"%Y-%m-%d") as deleted_at') //刪除日期
//            ->whereNull('inbound.deleted_at')
            ->whereNotNull('inbound.id')
            ->where('inbound.purchase_id', '=', $purchase_id)
            ->orderByDesc('inbound.created_at');
        return $result;
    }

    //採購單入庫總覽
    public static function getOverviewInboundList($purchase_id)
    {
        $tempInboundSql = DB::table('pcs_purchase_inbound as inbound')
            ->where('inbound.purchase_id', '=', $purchase_id)
            ->whereNull('inbound.deleted_at')
            ->select('inbound.purchase_id as purchase_id'
                , 'inbound.product_style_id as product_style_id')
            ->selectRaw('sum(inbound.inbound_num) as inbound_num')
            ->groupBy('inbound.purchase_id')
            ->groupBy('inbound.product_style_id');

        $queryTotalInboundNum = '( COALESCE(sum(items.num), 0) - COALESCE((inbound.inbound_num), 0) )'; //應進數量

        $result = DB::table('pcs_purchase as purchase')
            ->leftJoin('pcs_purchase_items as items', 'items.purchase_id', '=', 'purchase.id')
//            ->leftJoin('pcs_purchase_inbound as inbound', 'inbound.product_style_id', '=', 'items.product_style_id')
            ->leftJoin(DB::raw("({$tempInboundSql->toSql()}) as inbound"), function ($join) {
                $join->on('inbound.purchase_id', '=', 'items.purchase_id');
                $join->on('inbound.product_style_id', '=', 'items.product_style_id');
            })
            ->leftJoin('prd_product_styles as styles', 'styles.id', '=', 'items.product_style_id')
            ->leftJoin('prd_products as products', 'products.id', '=', 'styles.product_id')
            ->leftJoin('usr_users as users', 'users.id', '=', 'products.user_id')
            ->select('purchase.id as purchase_id' //採購ID
                , 'items.product_style_id as product_style_id' //商品款式ID
                , 'products.title as product_title' //商品名稱
                , 'styles.title as style_title' //款式名稱
                , 'users.name as user_name' //商品負責人
            )
            ->selectRaw('min(items.sku) as sku') //款式SKU
            ->selectRaw('sum(items.num) as num') //採購數量
            ->selectRaw('(inbound.inbound_num) as inbound_num') //已到數量
            ->selectRaw($queryTotalInboundNum.' AS should_enter_num') //應進數量

            ->selectRaw('(case
                    when '. $queryTotalInboundNum. ' = 0 and COALESCE(inbound.inbound_num, 0) <> 0 then "'.InboundStatus::getDescription(InboundStatus::normal()->value).'"
                    when COALESCE(inbound.inbound_num, 0) = 0 then "'.InboundStatus::getDescription(InboundStatus::not_yet()->value).'"
                    when COALESCE(sum(items.num), 0) < COALESCE(inbound.inbound_num) then "'.InboundStatus::getDescription(InboundStatus::overflow()->value).'"
                    when COALESCE(sum(items.num), 0) > COALESCE(inbound.inbound_num) then "'.InboundStatus::getDescription(InboundStatus::shortage()->value).'"
                end) as inbound_type') //採購狀態
            ->whereNull('purchase.deleted_at')
            ->whereNull('items.deleted_at')
            ->where('purchase.id', '=', $purchase_id)
            ->groupBy('purchase.id'
                , 'items.product_style_id'
                , 'products.title'
                , 'styles.title'
                , 'users.name'
                , 'inbound.inbound_num'
            )
            ->orderBy('purchase.id')
            ->orderBy('items.product_style_id')
            ->mergeBindings($tempInboundSql);
        return $result;
    }

    public static function inboundList($id) {
        $result = DB::table('pcs_purchase as purchase')
            ->leftJoin('pcs_purchase_inbound as inbound', 'inbound.purchase_id', '=', 'purchase.id')
            ->whereNull('purchase.deleted_at')
            ->where('purchase.id', '=', $id);
        return $result;
    }
}
