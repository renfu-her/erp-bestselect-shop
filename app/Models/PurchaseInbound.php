<?php

namespace App\Models;

use App\Enums\InboundEvent;
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

    public static function createInbound($purchase_id, $product_style_id, $expiry_date = null, $status = 0, $inbound_date = null, $inbound_num = 0, $error_num = 0, $depot_id = null, $inbound_user_id = null, $memo = null)
    {
        $id = self::create([
            "purchase_id" => $purchase_id,
            "product_style_id" => $product_style_id,
            "expiry_date" => $expiry_date,
            "status" => $status,
            "inbound_date" => $inbound_date,
            "inbound_num" => $inbound_num,
            "error_num" => $error_num,
            "depot_id" => $depot_id,
            "inbound_user_id" => $inbound_user_id,
            "memo" => $memo
        ])->id;

        //入庫 新增入庫數量
        //寫入ProductStock
        PurchaseInboundLog::stockChange($id, $inbound_num, InboundEvent::inbound()->value);
        ProductStock::stockChange($product_style_id, $inbound_num, StockEvent::inbound()->value, $id);

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
                    ->get()->first();
                if (null != $purchaseData->close_date) {
                    return ['success' => 0, 'error_msg' => 'purchase already close, so cant be delete'];
                }
                //判斷是否有賣出過 有則不能刪
                //寫入ProductStock
                else if (is_numeric($inboundDataGet->sale_num) && 0 < $inboundDataGet->sale_num) {
                    return ['success' => 0, 'error_msg' => 'inbound already sell'];
                } else {
                    PurchaseInboundLog::stockChange($id, $inboundDataGet->inbound_num * -1, InboundEvent::delete()->value, $user_id . '刪除入庫單');
                    ProductStock::stockChange($inboundDataGet->product_style_id, $inboundDataGet->inbound_num * -1, StockEvent::inbound_del()->value, $id, $user_id . '刪除入庫單');
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
                    PurchaseInboundLog::stockChange($id, $sale_num, InboundEvent::shipping()->value);
                }
            }
        });
    }

    //使用者退回 更新資料 寫入error_num
    public static function sendBackInbound($id, $send_back_num = 0)
    {
        return DB::transaction(function () use (
            $id,
            $send_back_num
        ) {
            PurchaseInbound::where('id', $id)
                ->update(['error_num' => DB::raw("error_num + $send_back_num")]);
            PurchaseInboundLog::stockChange($id, $send_back_num, InboundEvent::send_back()->value);
        });
    }

    //歷史入庫
    public static function getInboundList($purchase_id)
    {
        $result = DB::table('pcs_purchase as purchase')
            ->leftJoin('pcs_purchase_items as items', 'items.purchase_id', '=', 'purchase.id')
            ->leftJoin('pcs_purchase_inbound as inbound', 'inbound.product_style_id', '=', 'items.product_style_id')
            ->leftJoin('usr_users as users', 'users.id', '=', 'inbound.inbound_user_id')
            //->select('*')
            ->select('purchase.id as purchase_id' //採購ID
                , 'items.title as title' //商品名稱-款式名稱
                , 'items.sku as sku' //款式SKU
                , 'items.num as item_num' //採購款式數量
                , 'items.memo as item_memo' //採購款式備註
                , 'inbound.id as inbound_id' //入庫ID
                , 'inbound.status as status' //入庫狀態
                , 'inbound.inbound_num as inbound_num' //入庫實進數量
                , 'inbound.error_num as error_num' //入庫異常數量
                , 'inbound.depot_id as depot_id'  //入庫倉庫ID
                , 'inbound.inbound_user_id as inbound_user_id'  //入庫人員ID
                , 'inbound.close_date as close_date'
                , 'inbound.memo as inbound_memo' //入庫備註
                , 'users.name as user_name' //入庫人員名稱
            )
            ->selectRaw('DATE_FORMAT(inbound.expiry_date,"%Y-%m-%d") as expiry_date') //有效期限
            ->selectRaw('DATE_FORMAT(inbound.inbound_date,"%Y-%m-%d") as inbound_date') //入庫日期
            ->whereNull('purchase.deleted_at')
            ->whereNull('items.deleted_at')
            ->whereNull('inbound.deleted_at')
            ->whereNotNull('inbound.id')
            ->where('purchase.id', '=', $purchase_id);
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
            ->selectRaw('sum(inbound.error_num) as error_num')
            ->groupBy('inbound.purchase_id')
            ->groupBy('inbound.product_style_id');

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
//                , 'products.title as product_title' //商品名稱
//                , 'styles.title as style_title' //款式名稱
//                , 'users.name as user_name' //商品負責人
            )
            ->selectRaw('min(items.sku) as sku') //款式SKU
            ->selectRaw('sum(items.num) as num') //採購數量
            ->selectRaw('(inbound.inbound_num) as inbound_num') //已到數量
            ->selectRaw('(inbound.error_num) as error_num') //異常數量
            ->selectRaw('( COALESCE(sum(items.num), 0) - COALESCE((inbound.inbound_num), 0) ) AS should_enter_num') //應進數量
            ->whereNull('purchase.deleted_at')
            ->whereNull('items.deleted_at')
            ->where('purchase.id', '=', $purchase_id)
            ->groupBy('purchase.id'
                , 'items.product_style_id'
            )
            ->orderBy('purchase.id')
            ->orderBy('items.product_style_id')
            ->mergeBindings($tempInboundSql);
        dd($result->get());
        return $result;
    }
}
