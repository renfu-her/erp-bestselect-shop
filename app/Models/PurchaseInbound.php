<?php

namespace App\Models;

use App\Enums\Consignment\AuditStatus;
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

    public static function createInbound($event, $event_id, $event_item_id, $product_style_id, $title, $unit_cost = 0, $expiry_date = null, $inbound_date = null
        , $inbound_num = 0, $depot_id = null, $depot_name = null, $inbound_user_id = null, $inbound_user_name = null, $memo = null, $prd_type = null, $parent_inbound_id = null, $origin_inbound_id = null)
    {
        $can_tally = Depot::can_tally($depot_id);

        return DB::transaction(function () use (
            $event, $event_id, $event_item_id, $product_style_id, $title, $unit_cost, $expiry_date, $inbound_date,
            $inbound_num, $depot_id, $depot_name, $inbound_user_id, $inbound_user_name, $memo, $can_tally, $prd_type, $parent_inbound_id, $origin_inbound_id
        ) {
            $sn = "IB" . date("ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))
                        ->withTrashed()
                        ->get()
                        ->count()) + 1, 5, '0', STR_PAD_LEFT);

            $prd_type = $prd_type ?? 'p';
            $insert_data = [
                'sn' => $sn,
                "event" => $event,
                "event_id" => $event_id,
                "event_item_id" => $event_item_id,
                "product_style_id" => $product_style_id,
                "title" => $title,
                "unit_cost" => $unit_cost,
                "expiry_date" => $expiry_date,
                "inbound_date" => $inbound_date,
                "inbound_num" => $inbound_num,
                "depot_id" => $depot_id,
                "depot_name" => $depot_name,
                "inbound_user_id" => $inbound_user_id,
                "inbound_user_name" => $inbound_user_name,
                "memo" => $memo,
                "prd_type" => $prd_type,
                "parent_inbound_id" => $parent_inbound_id,
                "origin_inbound_id" => $origin_inbound_id,
            ];

            $id = self::create($insert_data)->id;
            $updateLog = PurchaseInbound::updateLog(LogEventFeature::inbound_add()->value, $id, $event, $event_id, $event_item_id, $product_style_id
                , $prd_type, $title, $inbound_num, $can_tally, $memo, $inbound_user_id, $inbound_user_name);
            if ($updateLog['success'] == 0) {
                DB::rollBack();
                return $updateLog;
            }
            return ['success' => 1, 'error_msg' => "", 'id' => $id, 'sn' => $sn];
        });
    }

    public static function updateInbound($inbound_id, int $add_qty, $memo, $update_user_id, $update_user_name) {
        if (0 == $add_qty) {
            return ['success' => 0, 'error_msg' => '增加數量不應為零'];
        }
        if (false == isset($memo)) {
            return ['success' => 0, 'error_msg' => '備註不可為空'];
        }
        if (false == isset($update_user_id) || false == isset($update_user_name)) {
            return ['success' => 0, 'error_msg' => '操作人員不可為空'];
        }
        $inbound = PurchaseInbound::where('id', '=', $inbound_id);
        $inboundGet = $inbound->first();
        if (false == isset($inboundGet)) {
            return ['success' => 0, 'error_msg' => '無此入庫單'];
        }
        $can_tally = Depot::can_tally($inboundGet->depot_id);

        return DB::transaction(function () use ($inboundGet, $can_tally, $inbound_id, $add_qty, $memo, $update_user_id, $update_user_name) {
            PurchaseInbound::where('id', '=', $inbound_id)->update([
                'inbound_num' => DB::raw("inbound_num + $add_qty")
            ]);

            $updateLog = PurchaseInbound::updateLog(LogEventFeature::inbound_update()->value, $inboundGet->id, $inboundGet->event, $inboundGet->event_id, $inboundGet->event_item_id, $inboundGet->product_style_id
                , $inboundGet->prd_type, $inboundGet->title, $add_qty, $can_tally, $memo, $update_user_id, $update_user_name);
            if ($updateLog['success'] == 0) {
                return $updateLog;
            }
            return ['success' => 1, 'error_msg' => "", 'id' => $inboundGet->id, 'sn' => $inboundGet->sn];
        });
    }

    private static function updateLog($eventFeature, $inbound_id, $event, $event_id, $event_item_id, $product_style_id, $prd_type, $title, $add_qty, $can_tally, $memo, $update_user_id, $update_user_name) {
        return DB::transaction(function () use ($eventFeature, $inbound_id, $event, $event_id, $event_item_id, $product_style_id, $prd_type, $title, $add_qty, $can_tally, $memo, $update_user_id, $update_user_name) {
            $is_pcs_inbound = false;
            //入庫 新增入庫數量
            $rePcsItemUAN = ['success' => 0, 'error_msg' => "未執行入庫"];
            if ($event == Event::purchase()->value) {
                $is_pcs_inbound = true;
                $rePcsItemUAN = PurchaseItem::updateArrivedNum($event_item_id, $add_qty, $can_tally);
            } else if ($event == Event::consignment()->value) {
                // 個別紀錄入庫單到達數
                $rePcsItemUAN = ReceiveDepot::updateCSNArrivedNum($event_item_id, $add_qty);
            } else if ($event == Event::ord_pickup()->value) {
                // 個別紀錄入庫單到達數
                $rePcsItemUAN = ReceiveDepot::updateCSNArrivedNum($event_item_id, $add_qty);
            }
            if ($rePcsItemUAN['success'] == 0) {
                DB::rollBack();
                return $rePcsItemUAN;
            }
            $rePcsLSC = PurchaseLog::stockChange($event_id, $product_style_id, $event, $event_item_id, $eventFeature, $inbound_id, $add_qty, $memo, $title, $prd_type, $update_user_id, $update_user_name);
            if ($rePcsLSC['success'] == 0) {
                DB::rollBack();
                return $rePcsLSC;
            }

            //只有採購才須記錄到ProductStock
            if ($event == Event::purchase()->value) {
                //寫入ProductStock
                $rePSSC = ProductStock::stockChange($product_style_id, $add_qty, StockEvent::inbound()->value, $inbound_id, null, $is_pcs_inbound, $can_tally);
                if ($rePSSC['success'] == 0) {
                    DB::rollBack();
                    return $rePSSC;
                }
            }
            return ['success' => 1, 'error_msg' => "", 'id' => $inbound_id];
        });
    }

    //取得創建入庫單所需資料 (名稱、款式、價格)
    public static function getCreateData($event, $event_id, array $event_item_id, array $product_style_id) {

        //取得名稱、款式
        $styles = DB::table('prd_products as product')
            ->leftJoin('prd_product_styles as style', 'style.product_id', '=', 'product.id')
            ->select('style.id'
                , 'product.title'
                , 'style.title as spec'
            )
            ->get()->toArray();
        $styles = json_decode(json_encode($styles), true);

        $pcs_items = null;
        //取得成本價
        if (Event::purchase()->value == $event) {
            $pcs_items = DB::table('pcs_purchase_items as items')
                ->select('items.purchase_id as purchase_id'
                    , 'items.id as item_id'
                    , 'items.product_style_id as product_style_id'
                    , DB::raw('(items.price / items.num) as unit_cost')
                )
                ->where('items.purchase_id', $event_id)
                ->whereIn('items.id', $event_item_id)
                ->get()->toArray();
        } else if (Event::consignment()->value == $event
            || Event::ord_pickup()->value == $event
        ) {
            $case_event = $event;
            if (Event::ord_pickup()->value == $event) {
                //訂單自取在delivery的event紀錄是order
                $case_event = Event::order()->value;
            }
            $pcs_items = DB::table('dlv_receive_depot as rcv_depot')
                ->leftJoin('dlv_delivery as delivery', function ($join) use($case_event) {
                    $join->on('delivery.id', '=', 'rcv_depot.delivery_id')
                        ->where('delivery.event', '=', $case_event);
                })
                ->select(
                    'rcv_depot.id as item_id'
                    , 'rcv_depot.product_style_id as product_style_id'
                    , 'rcv_depot.unit_cost as unit_cost'
                )
                ->where('delivery.event_id', $event_id)
                ->whereIn('rcv_depot.id', $event_item_id)
                ->get()->toArray();
        }
        $pcs_items = json_decode(json_encode($pcs_items), true);

        $style_arr = [];
        foreach ($product_style_id as $key => $val) {
            $style_arr[$key]['id'] = $val;
            $style_arr[$key]['event_item_id'] = $event_item_id[$key];
        }

        foreach ($style_arr as $key => $val) {
            foreach ($styles as $styleItem) {
                if ($style_arr[$key]['id'] == $styleItem['id']) {
                    $style_arr[$key]['item'] = $styleItem;
                    foreach ($pcs_items as $pcsItem) {
                        if ($style_arr[$key]['event_item_id'] == $pcsItem['item_id']) {
                            $style_arr[$key]['unit_cost'] = $pcsItem['unit_cost'];
                            break;
                        }
                    }
                    break;
                }
            }
        }

        return $style_arr;
    }

    //取消入庫 刪除資料
    public static function delInbound($id, $user_id)
    {
        $inboundData = PurchaseInbound::where('id', '=', $id);
        $inboundDataGet = $inboundData->get()->first();
        $purchase_id = '';
        if (null != $inboundDataGet) {
            $purchase_id = $inboundDataGet->event_id;
            if (0 < $inboundDataGet->sale_num) {
                return ['success' => 0, 'error_msg' => '已有售出紀錄 無法刪除'];
            } else if (0 < $inboundDataGet->csn_num) {
                return ['success' => 0, 'error_msg' => '已有寄倉紀錄 無法刪除'];
            } else if (0 < $inboundDataGet->consume_num) {
                return ['success' => 0, 'error_msg' => '已有耗材紀錄 無法刪除'];
            } else if (0 < $inboundDataGet->back_num) {
                return ['success' => 0, 'error_msg' => '已有退貨紀錄 無法刪除'];
            } else if (0 < $inboundDataGet->scrap_num) {
                return ['success' => 0, 'error_msg' => '已有報廢紀錄 無法刪除'];
            }
        }
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
                $main_table = '';
                if ($event == Event::purchase()->value) {
                    $main_table = 'pcs_purchase';
                } else if ($event == Event::consignment()->value) {
                    $main_table = 'csn_consignment';
                } else if ($event == Event::ord_pickup()->value) {
                    $main_table = 'ord_sub_orders';
                }
                if (false == empty($main_table)) {
                    $purchaseData = DB::table($main_table. ' as main_tb')
                        ->leftJoin('pcs_purchase_inbound as inbound', 'inbound.event_id', '=', 'purchase.id')
                        ->select('main_tb.id as id', 'main_tb.close_date as close_date')
                        ->where('main_tb.id', '=', $inboundDataGet->event_id)
                        ->where('inbound.event', '=', $event)
                        ->get()->first();
                    if (null != $purchaseData && null != $purchaseData->close_date) {
                        return ['success' => 0, 'error_msg' => '已結案 不可刪除'];
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
                    } else if ($event == Event::ord_pickup()->value) {
                        $rePcsItemUAN = ReceiveDepot::updateCSNArrivedNum($inboundDataGet->event_item_id, $qty);
                    }

                    if ($rePcsItemUAN['success'] == 0) {
                        DB::rollBack();
                        return $rePcsItemUAN;
                    }
                    $rePcsLSC = PurchaseLog::stockChange($purchaseData->id, $inboundDataGet->product_style_id, $event, $inboundDataGet->event_id, LogEventFeature::inbound_del()->value, $id, $qty, null, $inboundDataGet->title, $inboundDataGet->prd_type, $inboundDataGet->inbound_user_id, $inboundDataGet->inbound_user_name);
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
    public static function shippingInbound($event, $event_parent_id, $event_id, $feature, $id, $sale_num = 0)
    {
        return DB::transaction(function () use (
            $event,
            $event_parent_id,
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
                if (($inboundDataGet->inbound_num - $inboundDataGet->sale_num - $inboundDataGet->csn_num - $inboundDataGet->consume_num - $sale_num
                         - $inboundDataGet->back_num - $inboundDataGet->scrap_num) < 0) {
                    return ['success' => 0, 'error_msg' => '入庫單出貨數量超出範圍'];
                } else {
                    $update_arr = [];

                    if (Event::order()->value == $event || Event::ord_pickup()->value == $event) {
                        if (LogEventFeature::delivery()->value == $feature) {
                            $update_arr['sale_num'] = DB::raw("sale_num + $sale_num");
                        }
                    } else if (Event::consignment()->value == $event) {
                        if (LogEventFeature::delivery()->value == $feature) {
                            $update_arr['csn_num'] = DB::raw("csn_num + $sale_num");
                        }
                    } else if (Event::csn_order()->value == $event) {
                        if (LogEventFeature::delivery()->value == $feature) {
                            $update_arr['sale_num'] = DB::raw("sale_num + $sale_num");
                        }
                    }

                    $stock_event = '';
                    $stock_note = '';
                    //商品款式若在理貨倉
                    //除訂單、寄倉 已先扣除通路庫存以外
                    //訂單耗材、寄倉耗材 皆須另外扣除通路庫存
                    if (LogEventFeature::consume_delivery()->value == $feature) {
                        $update_arr['consume_num'] = DB::raw("consume_num + $sale_num");
                        $stock_event = StockEvent::consume()->value;
                        $stock_note = LogEventFeature::getDescription(LogEventFeature::consume_delivery()->value);
                    }

                    PurchaseInbound::where('id', $id)
                        ->update($update_arr);
                    $reStockChange =PurchaseLog::stockChange($event_parent_id, $inboundDataGet->product_style_id, $event, $event_id, $feature, $id, $sale_num, null, $inboundDataGet->title, $inboundDataGet->prd_type, $inboundDataGet->inbound_user_id, $inboundDataGet->inbound_user_name);
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
    public static function getInboundList($param, $showDelete = true)
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
            ->whereNotNull('inbound.id');

        //判斷不顯示刪除歷史
        if (false == $showDelete) {
            $result->whereNull('inbound.deleted_at');
        }
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

    public static function getInboundListWithEventSn($event, $param, $showDelete = true)
    {
        $result = DB::table('pcs_purchase_inbound as inbound')
            ->leftJoin('pcs_purchase as event', function ($join) use($event) {
                $join->on('event.id', '=', 'inbound.event_id')
                    ->whereIn('inbound.event', $event);
            })
            ->leftJoin('prd_product_styles as style', 'style.id', '=', 'inbound.product_style_id')
            ->leftJoin('prd_products as product', 'product.id', '=', 'style.product_id')
            ->select('inbound.event_id as event_id' //採購ID
                , 'event.sn as event_sn'
                , 'inbound.event as event'
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
                , DB::raw('(inbound.inbound_num - inbound.sale_num - inbound.csn_num - inbound.consume_num - inbound.back_num - inbound.scrap_num) as qty') //可出庫剩餘數量
            )
            ->selectRaw('DATE_FORMAT(inbound.expiry_date,"%Y-%m-%d") as expiry_date') //有效期限
            ->selectRaw('DATE_FORMAT(inbound.inbound_date,"%Y-%m-%d") as inbound_date') //入庫日期
            ->selectRaw('DATE_FORMAT(inbound.deleted_at,"%Y-%m-%d") as deleted_at') //刪除日期
            ->selectRaw('DATE_FORMAT(inbound.created_at,"%Y-%m-%d") as created_at') //新增日期
            ->selectRaw('DATE_FORMAT(inbound.updated_at,"%Y-%m-%d") as updated_at') //修改日期
            ->whereNotNull('inbound.id')
            ->whereNotNull('event.sn');

        //判斷不顯示刪除歷史
        if (false == $showDelete) {
            $result->whereNull('inbound.deleted_at');
        }
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
        if (isset($param['purchase_sn'])) {
            $result->where('event.sn', '=', $param['purchase_sn']);
        }
        if (isset($param['title'])) {
            $result->where(function ($q) use ($param) {
                $q->where('product.title', 'like', "%{$param['title']}%")
                    ->orWhere('style.title', 'like', "%{$param['title']}%");
            });
        }
        return $result;
    }

    //採購單入庫總覽
    public static function getOverviewInboundList($event, $event_id)
    {
        $tempInboundSql = DB::table('pcs_purchase_inbound as inbound')

            ->select('inbound.event_id as event_id'
                , 'inbound.product_style_id as product_style_id'
                , 'inbound.prd_type as prd_type')
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
        if (Event::ord_pickup()->value == $event) {
            $queryTotalInboundNum = '( COALESCE(sum(items.qty), 0) - COALESCE((inbound.inbound_num), 0) )'; //應進數量
        }

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
                    , 'inbound.prd_type as prd_type'
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
                    , 'inbound.prd_type'
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
                    , 'inbound.prd_type as prd_type'
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
                    , 'inbound.prd_type'
                )
                ->orderBy('consignment.id')
                ->orderBy('items.product_style_id');
            if ($event_id) {
                $result->where('consignment.id', $event_id);
            }
        } else if (Event::ord_pickup()->value == $event) {
            $result = DB::table('ord_sub_orders as sub_order')
                ->leftJoin('ord_items as items', function ($join) {
                    $join->on('items.sub_order_id', '=', 'sub_order.id')
                        ->on('items.order_id', '=', 'sub_order.order_id');
                })
                ->leftJoinSub($tempInboundSql, 'inbound', function($join) {
                    $join->on('inbound.event_id', '=', 'items.sub_order_id');
                    $join->on('inbound.product_style_id', '=', 'items.product_style_id');
                })

                ->leftJoin('prd_product_styles as styles', 'styles.id', '=', 'items.product_style_id')
                ->leftJoin('prd_products as products', 'products.id', '=', 'styles.product_id')
                ->leftJoin('usr_users as users', 'users.id', '=', 'products.user_id')
                ->select('sub_order.id as sub_order_id' //採購ID
                    , 'items.product_style_id as product_style_id' //商品款式ID
                    , 'products.title as product_title' //商品名稱
                    , 'styles.title as style_title' //款式名稱
                    , 'users.name as user_name' //商品負責人
                    , 'inbound.inbound_user_name as inbound_user_name' //入庫人員
                    , 'inbound.prd_type as prd_type'
                )
                ->selectRaw('min(items.sku) as sku') //款式SKU
                ->selectRaw('sum(items.qty) as num') //採購數量
                ->selectRaw('(inbound.inbound_num) as inbound_num') //已到數量
                ->selectRaw($queryTotalInboundNum.' AS should_enter_num') //應進數量

                ->selectRaw('(case
                    when '. $queryTotalInboundNum. ' = 0 and COALESCE(inbound.inbound_num, 0) <> 0 then "'.InboundStatus::getDescription(InboundStatus::normal()->value).'"
                    when COALESCE(inbound.inbound_num, 0) = 0 then "'.InboundStatus::getDescription(InboundStatus::not_yet()->value).'"
                    when COALESCE(sum(items.qty), 0) < COALESCE(inbound.inbound_num) then "'.InboundStatus::getDescription(InboundStatus::overflow()->value).'"
                    when COALESCE(sum(items.qty), 0) > COALESCE(inbound.inbound_num) then "'.InboundStatus::getDescription(InboundStatus::shortage()->value).'"
                end) as inbound_type') //採購狀態
//                ->whereNull('consignment.deleted_at')
//                ->whereNull('items.deleted_at')
//                ->where('consignment.id', '=', $event_id)
                ->groupBy('sub_order.id'
                    , 'items.product_style_id'
                    , 'products.title'
                    , 'styles.title'
                    , 'users.name'
                    , 'inbound.inbound_num'
                    , 'inbound.inbound_user_name'
                    , 'inbound.prd_type'
                )
                ->orderBy('sub_order.id')
                ->orderBy('items.product_style_id');
            if ($event_id) {
                $result->where('sub_order.id', $event_id);
            }
        }
        return $result;
    }

    //判斷是否已有購買紀錄
    public static function purchaseInboundList($purchase_id) {
        $result = DB::table('pcs_purchase as purchase')
            ->leftJoin('pcs_purchase_inbound as inbound', function($join) {
                $join->on('inbound.event_id', '=', 'purchase.id');
                $join->where('inbound.event', '=', Event::purchase()->value);
            })
            ->whereNull('purchase.deleted_at')
            ->whereNotNull('inbound.inbound_num')
            ->where('purchase.id', '=', $purchase_id);
        return $result;
    }

    // 取得寄倉單內 尚未出貨審核的預計出貨數量
    public static function getEstimatedShipmentsWithCsnItems($depot_id = []) {
        $re = DB::table('csn_consignment as consignment')
            ->leftJoin('csn_consignment_items as items', function ($join) {
                $join->on('items.consignment_id', '=', 'consignment.id');
            })
            ->leftJoin('dlv_delivery', function ($join) {
                $join->on('dlv_delivery.event_id', '=', 'consignment.id');
                $join->where('dlv_delivery.event', Event::consignment()->value);
            })
            ->select(
                'consignment.send_depot_id as depot_id'
                , 'items.product_style_id as product_style_id'
                , 'items.title as product_title'
            )
            ->selectRaw('sum(items.num) as qty')
            ->selectRaw('0 as back_qty') //配合其他地方union的欄位而加，否則未出貨根本不會有退貨數量
            ->whereNull('dlv_delivery.audit_date')
            ->whereNull('consignment.deleted_at')
            ->whereNull('items.deleted_at')
            ->whereIn('consignment.audit_status', [AuditStatus::unreviewed()->value, AuditStatus::approved()->value])
            ->groupBy('consignment.send_depot_id')
            ->groupBy('items.product_style_id')
            ->groupBy('items.title');
        if (null != $depot_id && 0 < count($depot_id)) {
            $re->whereIn('consignment.send_depot_id', $depot_id);
        }

        return $re;
    }

    // 取得訂單 正在選擇庫存的數量
    public static function getEstimatedShipmentsWithOrder($depot_id = []) {
        $re = DB::table('dlv_delivery')
            ->leftJoin('dlv_receive_depot', function ($join) {
                $join->on('dlv_receive_depot.delivery_id', '=', 'dlv_delivery.id');
            })
            ->select(//'dlv_receive_depot.inbound_id as inbound_id'
                'dlv_receive_depot.depot_id as depot_id'
                , 'dlv_receive_depot.product_style_id as product_style_id'
                , 'dlv_receive_depot.product_title as product_title'
            )
            ->selectRaw('sum(dlv_receive_depot.qty) as qty')
            ->selectRaw('sum(dlv_receive_depot.back_qty) as back_qty')
            ->whereNotNull('qty')
            ->whereNull('dlv_delivery.audit_date')
            ->whereNull('dlv_receive_depot.audit_date')
            ->whereNull('dlv_receive_depot.deleted_at')
            ->where('dlv_delivery.event', Event::order()->value)
            //->groupBy('dlv_receive_depot.inbound_id')
            ->groupBy('dlv_receive_depot.depot_id')
            ->groupBy('dlv_receive_depot.product_style_id')
            ->groupBy('dlv_receive_depot.product_title');
        if (null != $depot_id && 0 < count($depot_id)) {
            $re->whereIn('dlv_receive_depot.depot_id', $depot_id);
        }

        return $re;
    }

    // 取得出貨 正在選擇庫存的數量
    public static function getEstimatedShipmentsWithReceiveDepot() {
        $receive_depotQuerySub = DB::table('dlv_delivery')
            ->leftJoin('dlv_receive_depot', function ($join) {
                $join->on('dlv_receive_depot.delivery_id', '=', 'dlv_delivery.id');
            })
            ->select('dlv_receive_depot.inbound_id as inbound_id'
                , 'dlv_receive_depot.product_style_id as product_style_id'
                , 'dlv_receive_depot.product_title as product_title'
            )
            ->selectRaw('sum(dlv_receive_depot.qty) as qty')
            ->selectRaw('sum(dlv_receive_depot.back_qty) as back_qty')
            ->whereNotNull('qty')
            ->whereNull('dlv_delivery.audit_date')
            ->whereNull('dlv_receive_depot.audit_date')
            ->whereNull('dlv_receive_depot.deleted_at')
            ->groupBy('dlv_receive_depot.inbound_id')
            ->groupBy('dlv_receive_depot.product_style_id')
            ->groupBy('dlv_receive_depot.product_title');
        return $receive_depotQuerySub;
    }

    // 取得物流單內 耗材尚未出貨的數量
    public static function getEstimatedShipmentsWithLogisticConsum() {
        $logistic_consumQuerySub = DB::table('dlv_logistic')
            ->leftJoin('dlv_consum', 'dlv_consum.logistic_id', '=', 'dlv_logistic.id')
            ->select('dlv_consum.inbound_id as inbound_id'
                , 'dlv_consum.product_style_id as product_style_id'
                , 'dlv_consum.product_title as product_title'
            )
            ->selectRaw('sum(dlv_consum.qty) as qty')
            ->selectRaw('sum(dlv_consum.back_qty) as back_qty')
            ->whereNotNull('qty')
            ->whereNull('dlv_logistic.audit_date')
            ->whereNull('dlv_logistic.deleted_at')
            ->groupBy('dlv_consum.inbound_id')
            ->groupBy('dlv_consum.product_style_id')
            ->groupBy('dlv_consum.product_title');
        return $logistic_consumQuerySub;
    }

    /**
     * 取得可入庫單 可出貨列表
     * @param $param [product_style_id]
     *              [select_consignment false:計算採購庫存  true:改計算與扣寄倉庫存]
     * @param false $showNegativeVal 顯示負值 若為true 則只顯示大於1的數量 預設為false 不顯示
     * @return \Illuminate\Database\Query\Builder
     */
    public static function getSelectInboundList($param, $showNegativeVal = false) {
        $receive_depotQuerySub = PurchaseInbound::getEstimatedShipmentsWithReceiveDepot();
        $logistic_consumQuerySub = PurchaseInbound::getEstimatedShipmentsWithLogisticConsum();

        $receive_depotQuerySub->union($logistic_consumQuerySub);


        $rdlcQuerySub = DB::table(DB::raw("({$receive_depotQuerySub->toSql()}) as tb_rd"))
            ->select('tb_rd.inbound_id as inbound_id'
                , 'tb_rd.product_style_id as product_style_id'
                , 'tb_rd.product_title as product_title'
            )
            ->selectRaw('sum(tb_rd.qty) as qty')
            ->selectRaw('sum(tb_rd.back_qty) as back_qty')
            ->mergeBindings($receive_depotQuerySub)
            ->whereNotNull('qty')
            ->groupBy('tb_rd.inbound_id')
            ->groupBy('tb_rd.product_style_id')
            ->groupBy('tb_rd.product_title');

        $calc_qty = '(case when tb_rd.qty is null then inbound.inbound_num - inbound.sale_num - inbound.csn_num - inbound.consume_num - inbound.back_num - inbound.scrap_num
       else inbound.inbound_num - inbound.sale_num - inbound.csn_num - inbound.consume_num - inbound.back_num - inbound.scrap_num - tb_rd.qty end)';

        $result = DB::table('pcs_purchase_inbound as inbound')
            ->leftJoin('prd_product_styles as style', 'style.id', '=', 'inbound.product_style_id')
            ->leftJoin('prd_products as product', 'product.id', '=', 'style.product_id')
            ->leftJoinSub($rdlcQuerySub, 'tb_rd', function($join) {
                $join->on('tb_rd.inbound_id', '=', 'inbound.id');
            })
            ->select(
                'product.title as product_title' //商品名稱
                , 'product.type as prd_type' //商品類別
                , 'style.title as style_title' //款式名稱
                , 'style.sku as style_sku' //款式SKU
                , 'inbound.id as inbound_id' //入庫ID
                , 'inbound.sn as inbound_sn' //入庫sn
                , 'inbound.product_style_id as product_style_id'
                , 'inbound.unit_cost as unit_cost'
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
//            ->whereNotNull('inbound.close_date') //只篩選入庫有結案的
            ->whereNull('inbound.deleted_at');

        //判斷是否計算寄倉商品
        if (isset($param['select_consignment']) && true == $param['select_consignment']) {
            $result->where('inbound.event', '=', Event::consignment()->value);
        } else {
            $result->where('inbound.event', '<>', Event::consignment()->value);
        }

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
    public static function getExistInboundProductStyleList($depot_id = []) {
        $esCsnItems = PurchaseInbound::getEstimatedShipmentsWithCsnItems($depot_id);
        $esOrder = PurchaseInbound::getEstimatedShipmentsWithOrder($depot_id);
        $esCsnItems->union($esOrder);

        //計算預扣採購入庫單數量
        $querySub = DB::table(DB::raw("({$esCsnItems->toSql()}) as tb_rd"))
            ->select('tb_rd.depot_id as depot_id'
                , 'tb_rd.product_style_id as product_style_id'
                , 'tb_rd.product_title as product_title'
            )
            ->selectRaw('sum(tb_rd.qty) as qty')
            ->mergeBindings($esCsnItems)
            ->whereNotNull('qty')
            ->groupBy('tb_rd.depot_id')
            ->groupBy('tb_rd.product_style_id')
            ->groupBy('tb_rd.product_title');

        $queryInbound = DB::table('pcs_purchase_inbound as inbound')
            ->leftJoin('prd_product_styles as style', 'style.id', '=', 'inbound.product_style_id')
            ->leftJoin('prd_products as product', 'product.id', '=', 'style.product_id')
            ->select(
                'inbound.product_style_id'
                , 'inbound.event'
                , 'inbound.depot_id'
                , 'product.id as product_id'
                , 'product.title as product_title'
                , 'product.type as product_type'
                , 'style.title'
                , 'style.sku'
                , DB::raw('sum(inbound.inbound_num) as total_inbound_num')
                , DB::raw('sum(inbound.sale_num) as total_sale_num')
                , DB::raw('sum(inbound.csn_num) as total_csn_num')
                , DB::raw('sum(inbound.consume_num) as total_consume_num')
                , DB::raw('sum(inbound.back_num) as total_back_num')
                , DB::raw('sum(inbound.scrap_num) as total_scrap_num')
            )
            ->selectRaw('(sum(inbound.inbound_num) - sum(inbound.sale_num) - sum(inbound.csn_num) - sum(inbound.consume_num) - sum(inbound.back_num) - sum(inbound.scrap_num)) as total_in_stock_num')
            ->whereNull('inbound.deleted_at')
            ->whereNotNull('style.sku')
            ->whereNull('style.deleted_at')
//            ->whereNotNull('inbound.close_date') //只篩選入庫有結案的
//            ->where(DB::raw('(inbound.inbound_num - inbound.sale_num - inbound.csn_num - inbound.consume_num - inbound.back_num - inbound.scrap_num)'), '>', 0)
            ->groupBy('inbound.product_style_id')
            ->groupBy('inbound.event')
            ->groupBy('inbound.depot_id')
            ->groupBy('product.id')
            ->groupBy('product.title')
            ->groupBy('product.type')
            ->groupBy('style.title')
            ->groupBy('style.sku');

        $queryInbound_purchase = DB::query()->fromSub($queryInbound, 'ib_purchase')
            ->leftJoinSub($querySub, 'tb_rd', function($join) {
                $join->on('tb_rd.depot_id', '=', 'ib_purchase.depot_id');
                $join->on('tb_rd.product_style_id', '=', 'ib_purchase.product_style_id');
            })
            ->where('ib_purchase.event', Event::purchase()->value)
            ->select(
                'ib_purchase.product_style_id'
                , 'ib_purchase.event'
                , 'ib_purchase.depot_id'
                , 'ib_purchase.product_id'
                , 'ib_purchase.product_title'
                , 'ib_purchase.product_type'
                , 'ib_purchase.title'
                , 'ib_purchase.sku'
                , 'ib_purchase.total_inbound_num'
                , 'ib_purchase.total_sale_num'
                , 'ib_purchase.total_csn_num'
                , 'ib_purchase.total_consume_num'
                , 'ib_purchase.total_back_num'
                , 'ib_purchase.total_scrap_num'
                , DB::raw('(ifnull(ib_purchase.total_in_stock_num, 0) - ifnull(tb_rd.qty, 0)) as total_in_stock_num')
                , DB::raw('@0:="0" as total_in_stock_num_csn')
            );

        $queryInbound_consignment = DB::query()->fromSub($queryInbound, 'ib_consignment')
            ->where('ib_consignment.event', Event::consignment()->value)
            ->select(
                'ib_consignment.product_style_id'
                , 'ib_consignment.event'
                , 'ib_consignment.depot_id'
                , 'ib_consignment.product_id'
                , 'ib_consignment.product_title'
                , 'ib_consignment.product_type'
                , 'ib_consignment.title'
                , 'ib_consignment.sku'
                , DB::raw('@0:="0" as total_inbound_num')
                , DB::raw('@0:="0" as total_sale_num')
                , DB::raw('@0:="0" as total_csn_num')
                , DB::raw('@0:="0" as total_consume_num')
                , DB::raw('@0:="0" as total_back_num')
                , DB::raw('@0:="0" as total_scrap_num')
                , DB::raw('@0:="0" as total_in_stock_num')
                , DB::raw('(ifnull(ib_consignment.total_inbound_num, 0) - ifnull(ib_consignment.total_sale_num, 0) - ifnull(ib_consignment.total_csn_num, 0)
                - ifnull(ib_consignment.total_consume_num, 0)- ifnull(ib_consignment.total_back_num, 0)- ifnull(ib_consignment.total_scrap_num, 0)) as total_in_stock_num_csn')
            );

        $queryInbound_union_pcs_csn = $queryInbound_purchase->union($queryInbound_consignment);

        $queryInbound_group_pcs_csn = DB::query()->fromSub($queryInbound_union_pcs_csn, 'ib')
            ->select('ib.product_style_id'
                //, 'ib.event'
                , 'ib.depot_id'
                , 'ib.product_id'
                , 'ib.product_title'
                , 'ib.product_type'
                , 'ib.title'
                , 'ib.sku'
                , DB::raw('sum(ib.total_inbound_num) as total_inbound_num')
                , DB::raw('sum(ib.total_sale_num) as total_sale_num')
                , DB::raw('sum(ib.total_csn_num) as total_csn_num')
                , DB::raw('sum(ib.total_consume_num) as total_consume_num')
                , DB::raw('sum(ib.total_back_num) as total_back_num')
                , DB::raw('sum(ib.total_scrap_num) as total_scrap_num')
                , DB::raw('sum(ib.total_in_stock_num) as total_in_stock_num')
                , DB::raw('sum(ib.total_in_stock_num_csn) as total_in_stock_num_csn')
            )
            ->groupBy('ib.product_style_id')
            ->groupBy('ib.depot_id')
            ->groupBy('ib.product_id')
            ->groupBy('ib.product_title')
            ->groupBy('ib.product_type')
            ->groupBy('ib.title')
            ->groupBy('ib.sku');

        if (null != $depot_id && 0 < count($depot_id)) {
            $queryInbound_group_pcs_csn = DB::query()->fromSub($queryInbound_group_pcs_csn, 'inbound')
                ->whereIn('inbound.depot_id', $depot_id);
        }
        $result = $queryInbound_group_pcs_csn;

        return $result;
    }

    //取得寄倉商品款式現有數量
    public static function getCsnExistInboundProductStyleList($event) {
        $queryInbound = DB::table('pcs_purchase_inbound as inbound')
            ->where('inbound.event', $event)
            ->select(
                'inbound.product_style_id as product_style_id'
                , 'inbound.depot_id as depot_id'  //入庫倉庫ID
                , 'inbound.depot_name as depot_name'  //入庫倉庫名稱
                , 'inbound.prd_type as prd_type'
                , DB::raw('sum(inbound.inbound_num) as inbound_num')
                , DB::raw('sum(inbound.sale_num) as sale_num')
                , DB::raw('sum(inbound.csn_num) as csn_num')
                , DB::raw('sum(inbound.consume_num) as consume_num')
                , DB::raw('sum(inbound.back_num) as back_num')
                , DB::raw('sum(inbound.scrap_num) as scrap_num')
                , DB::raw('(sum(inbound.inbound_num) - sum(inbound.sale_num) - sum(inbound.csn_num) - sum(inbound.consume_num) - sum(inbound.back_num) - sum(inbound.scrap_num)) as available_num')
            )
            ->groupBy('inbound.product_style_id')
            ->groupBy('inbound.depot_id')
            ->groupBy('inbound.depot_name')
            ->groupBy('inbound.prd_type');

        return $queryInbound;
    }
}
