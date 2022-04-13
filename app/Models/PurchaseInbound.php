<?php

namespace App\Models;

use App\Enums\Delivery\Event;
use App\Enums\Purchase\InboundStatus;
use App\Enums\Purchase\LogEventFeature;
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

    public static function createInbound($event, $event_id, $event_item_id, $product_style_id, $expiry_date = null, $inbound_date = null, $inbound_num = 0, $depot_id = null, $depot_name = null, $inbound_user_id = null, $inbound_user_name = null, $memo = null, $origin_inbound_id = null)
    {
        $can_tally = Depot::can_tally($depot_id);

        return DB::transaction(function () use (
            $event
            , $event_id
            , $event_item_id
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
            , $origin_inbound_id
        ) {

            $sn = "IB" . date("ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))
                        ->withTrashed()
                        ->get()
                        ->count()) + 1, 4, '0', STR_PAD_LEFT);

            $insert_data = [
                'sn' => $sn,
                "event" => $event,
                "event_id" => $event_id,
                "event_item_id" => $event_item_id,
                "product_style_id" => $product_style_id,
                "expiry_date" => $expiry_date,
                "inbound_date" => $inbound_date,
                "inbound_num" => $inbound_num,
                "depot_id" => $depot_id,
                "depot_name" => $depot_name,
                "inbound_user_id" => $inbound_user_id,
                "inbound_user_name" => $inbound_user_name,
                "memo" => $memo,
                "origin_inbound_id" => $origin_inbound_id
            ];

            $id = self::create($insert_data)->id;

            $is_pcs_inbound = false;
            //入庫 新增入庫數量
            $rePcsItemUAN = ['success' => 0, 'error_msg' => "未執行入庫"];
            if ($event == Event::purchase()->value) {
                $is_pcs_inbound = true;
                $rePcsItemUAN = PurchaseItem::updateArrivedNum($event_item_id, $inbound_num, $can_tally);
            } else if ($event == Event::consignment()->value) {
                // 個別紀錄入庫單到達數
                $rePcsItemUAN = ReceiveDepot::updateCSNArrivedNum($event_item_id, $inbound_num);
            }
            if ($rePcsItemUAN['success'] == 0) {
                DB::rollBack();
                return $rePcsItemUAN;
            }
            $rePcsLSC = PurchaseLog::stockChange($event_id, $product_style_id, $event, $id, LogEventFeature::inbound_add()->value, $inbound_num, null, $inbound_user_id, $inbound_user_name);
            if ($rePcsLSC['success'] == 0) {
                DB::rollBack();
                return $rePcsLSC;
            }
            //寫入ProductStock
            $rePSSC = ProductStock::stockChange($product_style_id, $inbound_num, StockEvent::inbound()->value, $id, null, $is_pcs_inbound, $can_tally);
            if ($rePSSC['success'] == 0) {
                DB::rollBack();
                return $rePSSC;
            }
            return ['success' => 1, 'error_msg' => "", 'id' => $id];
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
                $event = $inboundDataGet->event;

                //刪除
                //判斷是否已結單 有則不能刪
                $purchaseData = null;
                if ($event == Event::purchase()->value) {
                    $purchaseData = DB::table('pcs_purchase as purchase')
                        ->leftJoin('pcs_purchase_inbound as inbound', 'inbound.event_id', '=', 'purchase.id')
                        ->select('purchase.close_date as close_date')
                        ->where('purchase.id', '=', $inboundDataGet->event_id)
                        ->where('inbound.event', '=', $event)
                        ->get()->first();
                    if (null != $purchaseData && null != $purchaseData->close_date) {
                        return ['success' => 0, 'error_msg' => 'purchase already close, so cant be delete'];
                    }
                } else if ($event == Event::consignment()->value) {
                    $purchaseData = DB::table('csn_consignment as consignment')
                        ->leftJoin('pcs_purchase_inbound as inbound', 'inbound.event_id', '=', 'consignment.id')
                        ->select('consignment.close_date as close_date')
                        ->where('consignment.id', '=', $inboundDataGet->event_id)
                        ->where('inbound.event', '=', $event)
                        ->get()->first();
                    if (null != $purchaseData && null != $purchaseData->close_date) {
                        return ['success' => 0, 'error_msg' => 'consignment already close, so cant be delete'];
                    }
                }
                //判斷是否有賣出過 有則不能刪
                //寫入ProductStock
                if (is_numeric($inboundDataGet->sale_num) && 0 < $inboundDataGet->sale_num) {
                    return ['success' => 0, 'error_msg' => 'inbound already sell'];
                } else {
                    $can_tally = Depot::can_tally($inboundDataGet->depot_id);
                    //判斷若為理貨倉 則採購款式 已到貨 ++; 採購款式 理貨 ++; product_style in_stock ++
                    //否則採購款式 已到貨 ++
                    $qty = $inboundDataGet->inbound_num * -1;
                    $rePcsItemUAN = ['success' => 0, 'error_msg' => "未執行入庫"];
                    $is_pcs_inbound = false;
                    if ($event == Event::purchase()->value) {
                        $is_pcs_inbound = true;
                        $rePcsItemUAN = PurchaseItem::updateArrivedNum($inboundDataGet->event_item_id, $qty, $can_tally);
                    } else if ($event == Event::consignment()->value) {
                        // 個別紀錄入庫單到達數
                        $rePcsItemUAN = ReceiveDepot::updateCSNArrivedNum($inboundDataGet->event_item_id, $qty);
                    }

                    if ($rePcsItemUAN['success'] == 0) {
                        DB::rollBack();
                        return $rePcsItemUAN;
                    }
                    $rePcsLSC = PurchaseLog::stockChange($inboundDataGet->event_id, $inboundDataGet->product_style_id, $event, $id, LogEventFeature::inbound_del()->value, $qty, null, $inboundDataGet->inbound_user_id, $inboundDataGet->inbound_user_name);
                    if ($rePcsLSC['success'] == 0) {
                        DB::rollBack();
                        return $rePcsLSC;
                    }
                    $rePSSC = ProductStock::stockChange($inboundDataGet->product_style_id, $qty, StockEvent::inbound_del()->value, $id, $inboundDataGet->inbound_user_name . LogEventFeature::inbound_del()->getDescription(LogEventFeature::inbound_del()->value), $is_pcs_inbound, $can_tally);
                    if ($rePSSC['success'] == 0) {
                        DB::rollBack();
                        return $rePSSC;
                    }
                    $inboundData->delete();
                    return ['success' => 1, 'error_msg' => ""];
                }
            } else {
                return ['success' => 0, 'error_msg' => "找不到資料"];
            }
        });
    }

    //售出 更新資料
    public static function shippingInbound($event, $event_id, $feature, $id, $sale_num = 0)
    {
        return DB::transaction(function () use (
            $event,
            $event_id,
            $feature,
            $id,
            $sale_num
        ) {
            $inboundData = DB::table('pcs_purchase_inbound as inbound')
                ->leftJoin('depot', 'depot.id', 'inbound.depot_id')
                ->where('inbound.id', '=', $id)
                ->whereNull('inbound.deleted_at');
            $inboundDataGet = $inboundData->get()->first();
            if (null != $inboundDataGet) {
                if (($inboundDataGet->inbound_num - $inboundDataGet->sale_num - $inboundDataGet->csn_num - $inboundDataGet->consume_num - $sale_num) < 0) {
                    return ['success' => 0, 'error_msg' => '入庫單出貨數量超出範圍'];
                } else {
                    $update_arr = [];
                    $stock_event = '';
                    $stock_note = '';

                    //除訂單以外
                    //訂單耗材、寄倉、寄倉耗材 皆須另外修改通路庫存
                    if (Event::order()->value == $event) {
                        if (LogEventFeature::order_shipping()->value == $feature) {
                            $update_arr['sale_num'] = DB::raw("sale_num + $sale_num");
                        } elseif (LogEventFeature::consume_shipping()->value == $feature) {
                            $update_arr['consume_num'] = DB::raw("consume_num + $sale_num");
                            $stock_event = StockEvent::consume()->value;
                            $stock_note = LogEventFeature::getDescription(LogEventFeature::consume_shipping()->value);
                        }
                    } else if (Event::consignment()->value == $event) {
                        if (LogEventFeature::order_shipping()->value == $feature) {
                            $update_arr['csn_num'] = DB::raw("csn_num + $sale_num");
                            $stock_event = StockEvent::consignment()->value;
                            $stock_note = LogEventFeature::getDescription(LogEventFeature::consignment_shipping()->value);
                        } elseif (LogEventFeature::consume_shipping()->value == $feature) {
                            $update_arr['consume_num'] = DB::raw("consume_num + $sale_num");
                            $stock_event = StockEvent::consume()->value;
                            $stock_note = LogEventFeature::getDescription(LogEventFeature::consume_shipping()->value);
                        }
                    }
                    PurchaseInbound::where('id', $id)
                        ->update($update_arr);
                    $reStockChange =PurchaseLog::stockChange($event_id, $inboundDataGet->product_style_id, $event, $id, $feature, $sale_num, null, $inboundDataGet->inbound_user_id, $inboundDataGet->inbound_user_name);
                    if ($reStockChange['success'] == 0) {
                        DB::rollBack();
                        return $reStockChange;
                    }

                    //若為理貨倉can_tally 需修改通路庫存
                    if ('' != $stock_event && $inboundDataGet->can_tally) {
                        $rePSSC = ProductStock::stockChange($inboundDataGet->product_style_id, $sale_num * -1
                            , $stock_event, $id
                            , $inboundDataGet->inbound_user_name . $stock_note
                            , false, $inboundDataGet->can_tally);
                        if ($rePSSC['success'] == 0) {
                            DB::rollBack();
                            return $rePSSC;
                        }
                    }
                }
            }
            return ['success' => 1, 'error_msg' => ""];
        });
    }

    //歷史入庫
    public static function getInboundList($param)
    {
        $result = DB::table('pcs_purchase_inbound as inbound')
            ->leftJoin('prd_product_styles as style', 'style.id', '=', 'inbound.product_style_id')
            ->leftJoin('prd_products as product', 'product.id', '=', 'style.product_id')
            ->select('inbound.event_id as event_id' //採購ID
                , 'inbound.event_item_id as event_item_id'
                , 'product.title as product_title' //商品名稱
                , 'product.user_id as user_id' //負責人
                , 'style.title as style_title' //款式名稱
                , 'style.id as product_style_id' //款式id
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
            ->whereNotNull('inbound.id')
            ->whereNull('inbound.deleted_at');
        if (isset($param['event'])) {
            $result->where('inbound.event', '=', $param['event']);
        }
        if (isset($param['purchase_id'])) {
            $result->where('inbound.event_id', '=', $param['purchase_id']);
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
        if (isset($param['inbound_id'])) {
            $result->where('inbound.id', '=', $param['inbound_id']);
        }
//        $result->orderByDesc('inbound.created_at');
        return $result;
    }

    //採購單入庫總覽
    public static function getOverviewInboundList($event, $event_id)
    {
        $tempInboundSql = DB::table('pcs_purchase_inbound as inbound')

            ->select('inbound.event_id as event_id'
                , 'inbound.product_style_id as product_style_id')
            ->selectRaw('sum(inbound.inbound_num) as inbound_num')
            ->selectRaw('GROUP_CONCAT(DISTINCT inbound.inbound_user_name) as inbound_user_name'); //入庫人員

        if ($event_id) {
            $tempInboundSql->where('inbound.event_id', '=', (int)$event_id);
        }
        $tempInboundSql->whereNull('inbound.deleted_at');
        if (isset($event)) {
            $tempInboundSql->where('inbound.event', '=', $event);
        }

        $tempInboundSql->groupBy('inbound.event_id');
        $tempInboundSql->groupBy('inbound.product_style_id');

        $queryTotalInboundNum = '( COALESCE(sum(items.num), 0) - COALESCE((inbound.inbound_num), 0) )'; //應進數量

        $result = null;
        if (Event::purchase()->value == $event) {
            $result = DB::table('pcs_purchase as purchase')
                ->leftJoin('pcs_purchase_items as items', 'items.purchase_id', '=', 'purchase.id')
                ->leftJoinSub($tempInboundSql, 'inbound', function($join) {
                    $join->on('inbound.event_id', '=', 'items.purchase_id');
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
                    , 'inbound.inbound_user_name as inbound_user_name' //入庫人員
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
//                ->where('purchase.id', '=', $event_id)
                ->groupBy('purchase.id'
                    , 'items.product_style_id'
                    , 'products.title'
                    , 'styles.title'
                    , 'users.name'
                    , 'inbound.inbound_num'
                    , 'inbound.inbound_user_name'
                )
                ->orderBy('purchase.id')
                ->orderBy('items.product_style_id');
            if ($event_id) {
                $result->where('purchase.id', $event_id);
            }
        } else if (Event::consignment()->value == $event) {
            $result = DB::table('csn_consignment as consignment')
                ->leftJoin('csn_consignment_items as items', 'items.consignment_id', '=', 'consignment.id')
                ->leftJoinSub($tempInboundSql, 'inbound', function($join) {
                    $join->on('inbound.event_id', '=', 'items.consignment_id');
                    $join->on('inbound.product_style_id', '=', 'items.product_style_id');
                })
                ->leftJoin('prd_product_styles as styles', 'styles.id', '=', 'items.product_style_id')
                ->leftJoin('prd_products as products', 'products.id', '=', 'styles.product_id')
                ->leftJoin('usr_users as users', 'users.id', '=', 'products.user_id')
                ->select('consignment.id as consignment_id' //採購ID
                    , 'items.product_style_id as product_style_id' //商品款式ID
                    , 'products.title as product_title' //商品名稱
                    , 'styles.title as style_title' //款式名稱
                    , 'users.name as user_name' //商品負責人
                    , 'inbound.inbound_user_name as inbound_user_name' //入庫人員
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
                ->whereNull('consignment.deleted_at')
                ->whereNull('items.deleted_at')
//                ->where('consignment.id', '=', $event_id)
                ->groupBy('consignment.id'
                    , 'items.product_style_id'
                    , 'products.title'
                    , 'styles.title'
                    , 'users.name'
                    , 'inbound.inbound_num'
                    , 'inbound.inbound_user_name'
                )
                ->orderBy('consignment.id')
                ->orderBy('items.product_style_id');
            if ($event_id) {
                $result->where('consignment.id', $event_id);
            }
        }
        return $result;
    }

    public static function purchaseInboundList($purchase_id) {
        $result = DB::table('pcs_purchase as purchase')
            ->leftJoin('pcs_purchase_inbound as inbound', function($join) {
                $join->on('inbound.event_id', '=', 'purchase.id');
                $join->where('inbound.event', '=', Event::purchase()->value);
            })
            ->whereNull('purchase.deleted_at')
            ->where('purchase.id', '=', $purchase_id);
        return $result;
    }


    /**
     * 取得可入庫單 可出貨列表
     * @param $param [product_style_id]
     * @param false $showNegativeVal 顯示負值 若為true 則只顯示大於1的數量 預設為false 不顯示
     * @return \Illuminate\Database\Query\Builder
     */
    public static function getSelectInboundList($param, $showNegativeVal = false) {
        $receive_depotQuerySub = DB::table('dlv_delivery')
            ->leftJoin('dlv_receive_depot', function ($join) {
                $join->on('dlv_receive_depot.delivery_id', '=', 'dlv_delivery.id');
                $join->where('dlv_delivery.event', '=', Event::consignment()->value);
            })
            ->select('dlv_receive_depot.inbound_id as inbound_id'
                , 'dlv_receive_depot.product_style_id as product_style_id'
                , 'dlv_receive_depot.product_title as product_title'
            )
            ->selectRaw('sum(dlv_receive_depot.qty) as qty')
            ->whereNotNull('qty')
            ->whereNull('dlv_delivery.audit_date')
            ->whereNull('dlv_receive_depot.audit_date')
            ->whereNull('dlv_receive_depot.deleted_at')
            ->groupBy('dlv_receive_depot.inbound_id')
            ->groupBy('dlv_receive_depot.product_style_id')
            ->groupBy('dlv_receive_depot.product_title');

        $logistic_consumQuerySub = DB::table('dlv_logistic')
            ->leftJoin('dlv_consum', 'dlv_consum.logistic_id', '=', 'dlv_logistic.id')
            ->select('dlv_consum.inbound_id as inbound_id'
                , 'dlv_consum.product_style_id as product_style_id'
                , 'dlv_consum.product_title as product_title'
            )
            ->selectRaw('sum(dlv_consum.qty) as qty')
            ->whereNotNull('qty')
            ->whereNull('dlv_logistic.audit_date')
            ->whereNull('dlv_logistic.deleted_at')
            ->groupBy('dlv_consum.inbound_id')
            ->groupBy('dlv_consum.product_style_id')
            ->groupBy('dlv_consum.product_title');

        $receive_depotQuerySub->union($logistic_consumQuerySub);


        $rdlcQuerySub = DB::table(DB::raw("({$receive_depotQuerySub->toSql()}) as tb_rd"))
            ->select('tb_rd.inbound_id as inbound_id'
                , 'tb_rd.product_style_id as product_style_id'
                , 'tb_rd.product_title as product_title'
            )
            ->selectRaw('sum(tb_rd.qty) as qty')
            ->mergeBindings($receive_depotQuerySub)
            ->whereNotNull('qty')
            ->groupBy('tb_rd.inbound_id')
            ->groupBy('tb_rd.product_style_id')
            ->groupBy('tb_rd.product_title');

        $calc_qty = '(case when tb_rd.qty is null then inbound.inbound_num - inbound.sale_num - inbound.csn_num - inbound.consume_num
       else inbound.inbound_num - inbound.sale_num - inbound.csn_num - inbound.consume_num - tb_rd.qty end)';

        $result = DB::table('pcs_purchase_inbound as inbound')
            ->leftJoin('prd_product_styles as style', 'style.id', '=', 'inbound.product_style_id')
            ->leftJoin('prd_products as product', 'product.id', '=', 'style.product_id')
            ->leftJoinSub($rdlcQuerySub, 'tb_rd', function($join) {
                $join->on('tb_rd.inbound_id', '=', 'inbound.id');
            })
            ->select(
                'product.title as product_title' //商品名稱
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
            ->whereNull('inbound.deleted_at');

        if (isset($param['product_style_id'])) {
            $result->where('inbound.product_style_id', '=', $param['product_style_id']);
        }

        if (isset($param['inbound_id'])) {
            $result->where('inbound.id', '=', $param['inbound_id']);
        }

        if (isset($param['depot_id'])) {
            $result->where('inbound.depot_id', '=', $param['depot_id']);
        }
        if (false == $showNegativeVal) {
            $result->where(DB::raw($calc_qty), '>', 0);
        }
        $result->orderBy('inbound.expiry_date');
        return $result;
    }

    //取得商品款式現有數量
    public static function getExistInboundProductStyleList($depot_id) {
        $result = DB::table('pcs_purchase_inbound as inbound')
            ->select(
                'inbound.product_style_id'
                , 'inbound.depot_id'
                , DB::raw('sum(inbound.inbound_num) as inbound_num')
                , DB::raw('sum(inbound.sale_num) as sale_num')
                , DB::raw('sum(inbound.csn_num) as csn_num')
                , DB::raw('sum(inbound.consume_num) as consume_num')
            )
            ->whereNull('inbound.deleted_at')
            ->where(DB::raw('(inbound.inbound_num - inbound.sale_num - inbound.csn_num - inbound.consume_num)'), '>', 0)
//            ->whereNotNull('inbound.close_date')
            ->groupBy('inbound.product_style_id')
            ->groupBy('inbound.depot_id')
        ;
        return $result;
    }
}
