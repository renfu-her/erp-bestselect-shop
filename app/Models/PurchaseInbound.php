<?php

namespace App\Models;

use App\Enums\Purchase\InboundStatus;
use App\Enums\Purchase\LogEventFeature;
use App\Enums\Purchase\LogEvent;
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
        $can_tally = Depot::can_tally($depot_id);

        return DB::transaction(function () use (
            $purchase_id
            , $purchase_item_id
            , $product_style_id
            , $expiry_date
            , $inbound_date
            , $inbound_num
            , $depot_id
            , $depot_name
            , $inbound_user_id
            , $inbound_user_name
            , $memo
            , $can_tally
        ) {

            $sn = date("ymd") . str_pad((PurchaseInbound::whereDate('created_at', '=', date('Y-m-d'))
                        ->withTrashed()
                        ->get()
                        ->count()) + 1, 3, '0', STR_PAD_LEFT);

            $id = self::create([
                'sn' => $sn,
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
            PurchaseItem::updateArrivedNum($purchase_item_id, $inbound_num, $can_tally);
            PurchaseLog::stockChange($purchase_id, $product_style_id, LogEvent::inbound()->value, $id, LogEventFeature::inbound_add()->value, $inbound_num, null, $inbound_user_id, $inbound_user_name);
            ProductStock::stockChange($product_style_id, $inbound_num, StockEvent::inbound()->value, $id, null, true, $can_tally);

            return $id;
        });
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
                    ->where('purchase.id', '=', $inboundDataGet->purchase_id)
                    ->get()->first();
                if (null != $purchaseData && null != $purchaseData->close_date) {
                    return ['success' => 0, 'error_msg' => 'purchase already close, so cant be delete'];
                }
                //判斷是否有賣出過 有則不能刪
                //寫入ProductStock
                else if (is_numeric($inboundDataGet->sale_num) && 0 < $inboundDataGet->sale_num) {
                    return ['success' => 0, 'error_msg' => 'inbound already sell'];
                } else {
                    $can_tally = Depot::can_tally($inboundDataGet->depot_id);
                    //判斷若為理貨倉 則採購款式 已到貨 ++; 採購款式 理貨 ++; product_style in_stock ++
                    //否則採購款式 已到貨 ++
                    $qty = $inboundDataGet->inbound_num * -1;
                    PurchaseItem::updateArrivedNum($inboundDataGet->purchase_item_id, $qty, $can_tally);
                    PurchaseLog::stockChange($inboundDataGet->purchase_id, $inboundDataGet->product_style_id, LogEvent::inbound()->value, $id, LogEventFeature::inbound_del()->value, $qty, null, $inboundDataGet->inbound_user_id, $inboundDataGet->inbound_user_name);
                    ProductStock::stockChange($inboundDataGet->product_style_id, $qty, StockEvent::inbound_del()->value, $id, $inboundDataGet->inbound_user_name . LogEventFeature::inbound_del()->getDescription(LogEventFeature::inbound_del()->value), true, $can_tally);
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
                    PurchaseLog::stockChange($inboundDataGet->purchase_id, $inboundDataGet->product_style_id, LogEvent::inbound()->value, $id, LogEventFeature::inbound_shipping()->value, $sale_num, null, $inboundDataGet->inbound_user_id, $inboundDataGet->inbound_user_name);
                }
            }
        });
    }

    //歷史入庫
    public static function getInboundList($param)
    {
        $result = DB::table('pcs_purchase_inbound as inbound')
            ->leftJoin('prd_product_styles as style', 'style.id', '=', 'inbound.product_style_id')
            ->leftJoin('prd_products as product', 'product.id', '=', 'style.product_id')
            ->select('inbound.purchase_id as purchase_id' //採購ID
                , 'product.title as product_title' //商品名稱
                , 'style.title as style_title' //款式名稱
                , 'style.sku as style_sku' //款式SKU
                , 'inbound.id as inbound_id' //入庫ID
                , 'inbound.sn as inbound_sn' //入庫sn
                , 'inbound.inbound_num as inbound_num' //入庫實進數量
                , 'inbound.depot_id as depot_id'  //入庫倉庫ID
                , 'inbound.depot_name as depot_name'  //入庫倉庫名稱
                , 'inbound.inbound_user_id as inbound_user_id'  //入庫人員ID
                , 'inbound.inbound_user_name as inbound_user_name' //入庫人員名稱
                , 'inbound.close_date as inbound_close_date'
                , 'inbound.memo as inbound_memo' //入庫備註
            )
            ->selectRaw('DATE_FORMAT(inbound.expiry_date,"%Y-%m-%d") as expiry_date') //有效期限
            ->selectRaw('DATE_FORMAT(inbound.inbound_date,"%Y-%m-%d") as inbound_date') //入庫日期
            ->selectRaw('DATE_FORMAT(inbound.deleted_at,"%Y-%m-%d") as deleted_at') //刪除日期
            ->whereNotNull('inbound.id');
        if (isset($param['purchase_id'])) {
            $result->where('inbound.purchase_id', '=', $param['purchase_id']);
        }
        if (isset($param['keyword'])) {
            $keyword = $param['keyword'];
            $result->where(function ($q) use ($keyword) {
                if ($keyword) {
                    $q->where('product.title', 'like', "%$keyword%");
                    $q->orWhere('style.title', 'like', "%$keyword%");
                    $q->orWhere('style.sku', 'like', "%$keyword%");
                }
            });
        }

        if (isset($param['product_style_id'])) {
            $result->where('inbound.product_style_id', '=', $param['product_style_id']);
        }
        $result->orderByDesc('inbound.created_at');
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


    /**
     * 取得可入庫單 可出貨列表
     * @param $param [product_style_id]
     * @param false $showNegativeVal 顯示負值 若為true 則只顯示大於1的數量 預設為false 不顯示
     * @return \Illuminate\Database\Query\Builder
     */
    public static function getSelectInboundList($param, $showNegativeVal = false) {
        $receive_depotQuerySub = DB::table('dlv_receive_depot')
            ->select('dlv_receive_depot.inbound_id as inbound_id'
                , 'dlv_receive_depot.product_style_id as product_style_id'
                , 'dlv_receive_depot.product_title as product_title'
            )
            ->selectRaw('sum(dlv_receive_depot.qty) as qty')
            ->where('dlv_receive_depot.is_setup' , '=', 0)
            ->whereNull('dlv_receive_depot.deleted_at')
            ->groupBy('dlv_receive_depot.inbound_id')
            ->groupBy('dlv_receive_depot.product_style_id')
            ->groupBy('dlv_receive_depot.product_title');

        $calc_qty = '(case when tb_rd.qty is null then inbound.inbound_num - inbound.sale_num
       else inbound.inbound_num - inbound.sale_num - tb_rd.qty end)';
        $result = DB::table('pcs_purchase_inbound as inbound')
            ->leftJoin('prd_product_styles as style', 'style.id', '=', 'inbound.product_style_id')
            ->leftJoin('prd_products as product', 'product.id', '=', 'style.product_id')
            ->leftJoin(DB::raw("({$receive_depotQuerySub->toSql()}) as tb_rd"), function ($join) {
                $join->on('tb_rd.inbound_id', '=', 'inbound.id');
            })
            ->select('inbound.purchase_id as purchase_id' //採購ID
                , 'product.title as product_title' //商品名稱
                , 'style.title as style_title' //款式名稱
                , 'style.sku as style_sku' //款式SKU
                , 'inbound.id as inbound_id' //入庫ID
                , 'inbound.sn as inbound_sn' //入庫sn
                , 'inbound.product_style_id as product_style_id'
                , 'inbound.depot_id as depot_id'  //入庫倉庫ID
                , 'inbound.depot_name as depot_name'  //入庫倉庫名稱
                , 'inbound.inbound_user_id as inbound_user_id'  //入庫人員ID
                , 'inbound.inbound_user_name as inbound_user_name' //入庫人員名稱
                , 'inbound.close_date as inbound_close_date'
                , 'inbound.memo as inbound_memo' //入庫備註
                , 'inbound.inbound_num as inbound_num'
                , 'inbound.sale_num as sale_num'
                , 'tb_rd.qty as tb_rd_qty'
            )
            ->selectRaw($calc_qty.' as qty') //可出庫數量
            ->selectRaw('DATE_FORMAT(inbound.expiry_date,"%Y-%m-%d") as expiry_date') //有效期限
            ->selectRaw('DATE_FORMAT(inbound.inbound_date,"%Y-%m-%d") as inbound_date') //入庫日期
            ->selectRaw('DATE_FORMAT(inbound.deleted_at,"%Y-%m-%d") as deleted_at') //刪除日期
            ->whereNotNull('inbound.id')
            ->whereNull('inbound.deleted_at')
            ->mergeBindings($receive_depotQuerySub);

        if (isset($param['product_style_id'])) {
            $result->where('inbound.product_style_id', '=', $param['product_style_id']);
        }
        if (false == $showNegativeVal) {
            $result->where(DB::raw($calc_qty), '>', 0);
        }
        $result->orderBy('inbound.expiry_date');
        return $result;
    }
}
