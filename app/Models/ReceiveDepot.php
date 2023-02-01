<?php

namespace App\Models;

use App\Enums\Delivery\Event;
use App\Enums\Delivery\LogisticStatus;
use App\Enums\DlvBack\DlvBackType;
use App\Enums\Purchase\LogEventFeature;
use App\Helpers\IttmsDBB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ReceiveDepot extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'dlv_receive_depot';
    public $timestamps = true;
    protected $guarded = [];

    public static function setData($id = null, $delivery_id, $event_item_id = null, $combo_id = null, $prd_type = null, $freebies, $inbound_id, $inbound_sn, $depot_id, $depot_name, $product_style_id, $sku, $product_title, $unit_cost, $qty, $expiry_date)
    {
        $data = null;
        $dataGet = null;
        if (null != $id) {
            $data = ReceiveDepot::where('id', $id);
            $dataGet = $data->get()->first();
        }
        $result = null;
        if (null == $dataGet) {
            $result = ReceiveDepot::create([
                'delivery_id' => $delivery_id,
                'event_item_id' => $event_item_id,
                //'combo_id' => $combo_id, //剛創建不會有 須等到出貨送出 確認是組合包的元素商品 才會回寫
                'prd_type' => $prd_type,
                'freebies' => $freebies,
                'inbound_id' => $inbound_id,
                'inbound_sn' => $inbound_sn,
                'depot_id' => $depot_id,
                'depot_name' => $depot_name,
                'product_style_id' => $product_style_id,
                'sku' => $sku,
                'product_title' => $product_title,
                'unit_cost' => $unit_cost,
                'qty' => $qty,
                'expiry_date' => $expiry_date,
            ])->id;
        } else {
            $result = IttmsDBB::transaction(function () use ($data, $dataGet, $combo_id, $prd_type, $freebies, $inbound_id, $inbound_sn, $depot_id, $depot_name, $product_style_id, $sku, $product_title, $unit_cost, $qty, $expiry_date
            ) {
                $data->update([
                    //'freebies' => $freebies,
                    'combo_id' => $combo_id, //剛創建不會有 須等到出貨送出 確認是組合包的元素商品 才會回寫
                    //'prd_type' => $prd_type,
                    //'inbound_id' => $inbound_id,
                    //'inbound_sn' => $inbound_sn,
                    //'depot_id' => $depot_id,
                    //'depot_name' => $depot_name,
                    //'product_style_id' => $product_style_id,
                    //'sku' => $sku,
                    //'product_title' => $product_title,
                    //'unit_cost' => $unit_cost, //修改時不會動到
                    'qty' => $qty,
                    //'expiry_date' => $expiry_date,
                ]);
                return ['success' => 1, 'id' => $dataGet->id];
            });
            $result = $result['id'] ?? null;
        }
        return ['success' => 1, 'error_msg' => "", 'id' => $result];
    }

    /**
     * 新增對應的入庫商品款式
     * @param $input_arr inbound_id:入庫單ID ; qty:數量
     * @param $delivery_id 出貨單ID
     * @param $itemId 子訂單對應商品 ord_items id
     * @return array|mixed|void
     */
    public static function setDatasWithDeliveryIdWithItemId($input_arr, $delivery_id, $itemId) {
        $delivery = Delivery::where('id', $delivery_id)->get()->first();

        return IttmsDBB::transaction(function () use ($delivery_id, $delivery, $itemId, $input_arr
        ) {
            if (null != $input_arr['qty'] && 0 < count($input_arr['qty'])) {
                $addIds = [];
                foreach($input_arr['qty'] as $key => $val) {
                    //取得delivery event = consignment
                    //	用event_item_id取得csn_consignment_items prd_type
                    //		若c 則寫入element
                    //		若p 則寫入product
                    $rcv_depot_type = '';
                    $item = null;
                    $select_consignment = false;
                    if (Event::order()->value == $delivery->event) {
                        $item = DB::table('ord_items as items')
                            ->leftJoin('prd_product_styles as styles', 'styles.id', '=', 'items.product_style_id')
                            ->select('items.*', 'styles.type as prd_type')
                            ->where('items.id', $itemId)
                            ->get()->first();
                        if ('p' == $item->prd_type) {
                            $rcv_depot_type = 'p';
                        } elseif ('c' == $item->prd_type) {
                            $rcv_depot_type = 'ce';
                        }
                    } else if (Event::consignment()->value == $delivery->event) {
                        $item = ConsignmentItem::where('id', $itemId)->get()->first();
                        if ('p' == $item->prd_type) {
                            $rcv_depot_type = 'p';
                        } elseif ('c' == $item->prd_type) {
                            $rcv_depot_type = 'ce';
                        }
                    } else if (Event::csn_order()->value == $delivery->event) {
                        $item = CsnOrderItem::where('id', $itemId)->get()->first();
                        $select_consignment = true;
                        if ('p' == $item->prd_type) {
                            $rcv_depot_type = 'p';
                        } elseif ('c' == $item->prd_type) {
                            $rcv_depot_type = 'c';
                        }
                    }
                    $inbound = PurchaseInbound::getSelectInboundList(['inbound_id' => $input_arr['inbound_id'][$key], 'select_consignment' => $select_consignment])->get()->first();
                    if (null != $inbound) {
                        if (0 > $inbound->qty - $val) {
                            return ['success' => 0, 'error_msg' => "庫存數量不足"];
                        }
                        $reSD = ReceiveDepot::setData(
                            null,
                            $delivery_id, //出貨單ID
                            $itemId ?? null, //子訂單商品ID
                            null, //組合包商品ID
                            $rcv_depot_type ?? null, //商品類型
                            $input_arr['freebies'][$key] ?? 0, //是否為贈品 0:否
                            $inbound->inbound_id,
                            $inbound->inbound_sn,
                            $inbound->depot_id,
                            $inbound->depot_name,
                            $inbound->product_style_id,
                            $inbound->style_sku,
                            $inbound->product_title. '-'. $inbound->style_title,
                            $inbound->unit_cost,
                            $val, //數量
                            $inbound->expiry_date);
                        if ($reSD['success'] == 0) {
                            DB::rollBack();
                            return $reSD;
                        } else {
                            array_push($addIds, $reSD['id']);
                        }
                    }
                }
                return ['success' => 1, 'error_msg' => "", 'id' => $addIds];
            } else {
                return ['success' => 0, 'error_msg' => "未輸入數量"];
            }
        });
    }

    //將收貨資料變更為成立
    public static function setUpShippingData($event, $event_id, $delivery_id, $user_id, $user_name) {
        $delivery = Delivery::where('id', $delivery_id)->get()->first();
        $rcvDepotGet = null;
        if (null != $delivery_id) {
            $rcvDepot = ReceiveDepot::where('delivery_id', $delivery_id);
            $rcvDepotGet = $rcvDepot->get();
        }
        if (null != $delivery &&null != $rcvDepotGet && 0 < count($rcvDepotGet)) {
            $result = IttmsDBB::transaction(function () use ($delivery, $rcvDepot, $rcvDepotGet, $event, $event_id, $delivery_id, $user_id, $user_name
            ) {
                //判斷都需是同一個倉庫出貨
                $first_rcv_depot_item = $rcvDepot->where('delivery_id', $delivery_id)->where('depot_id', '<>', 0)->first();
                if (null == $first_rcv_depot_item) {
                    DB::rollBack();
                    return ['success' => 0, 'error_msg' => "資料有誤 請回報給工程師 並說明相關單號與情況"];
                }
                $curr_depot_id = $first_rcv_depot_item->depot_id;
                foreach ($rcvDepotGet as $item) {
                    if ($curr_depot_id != $item->depot_id && 0 != $item->depot_id) {
                        DB::rollBack();
                        return ['success' => 0, 'error_msg' => "請確認是否都是同一倉庫出貨"];
                    }
                }

                //判斷若為組合包商品 則需新建立一筆資料組合成組合包 並回寫新id
                $queryComboElement = null;
                $logisticStatus = [LogisticStatus::A5000()];
                if (Event::order()->value == $delivery->event) {
                    if ('deliver' == $delivery->ship_category) {

                    } else if ('pickup' == $delivery->ship_category) {
                        $event = 'ord_pickup';
                    }
                    $queryComboElement = DB::table('dlv_delivery as delivery')
                        ->leftJoin('dlv_receive_depot as rcv_depot', 'rcv_depot.delivery_id', '=', 'delivery.id')
                        ->leftJoin('ord_items as items', 'items.id', '=', 'rcv_depot.event_item_id')
                        ->leftJoin('ord_sub_orders as sub_orders', function ($join) {
                            $join->on('sub_orders.id', '=', 'items.sub_order_id');
                            $join->on('sub_orders.order_id', 'items.order_id');
                        })
                        ->leftJoin(app(DlvOutStock::class)->getTable(). ' as outs', function ($join) use($delivery_id) {
                            $join->on('outs.event_item_id', '=', 'items.id');
                            $join->where('outs.delivery_id', '=', $delivery_id);
                        })
                        ->where('delivery.id', $delivery->id)
                        ->where('delivery.event', $delivery->event)
                        ->where('rcv_depot.prd_type', 'ce')
                        ->where('sub_orders.ship_category', $delivery->ship_category)
                        ->whereNull('delivery.deleted_at')
                        ->whereNull('rcv_depot.deleted_at')
                        ->select(
                            'rcv_depot.delivery_id'
                            , 'rcv_depot.event_item_id'
                            , DB::raw('min(rcv_depot.expiry_date) as expiry_date')
                            , 'items.product_style_id'
                            , 'items.product_title as title'
                            , 'rcv_depot.prd_type as prd_type'
                            , 'items.sku'
                            , DB::raw('(items.qty - ifnull(outs.qty, 0)) as num')
                            , 'rcv_depot.depot_id as depot_id'
                            , 'rcv_depot.depot_name as depot_name'
                        )
                        ->groupBy('rcv_depot.delivery_id')
                        ->groupBy('rcv_depot.event_item_id')
                        ->groupBy('items.product_style_id')
                        ->groupBy('items.product_title')
                        ->groupBy('rcv_depot.prd_type')
                        ->groupBy('items.sku')
                        ->get();
                }
                else if (Event::consignment()->value == $delivery->event) {
                    $queryComboElement = DB::table('dlv_delivery as delivery')
                        ->leftJoin('dlv_receive_depot as rcv_depot', 'rcv_depot.delivery_id', '=', 'delivery.id')
                        ->leftJoin('csn_consignment_items as items', 'items.id', '=', 'rcv_depot.event_item_id')
                        ->leftJoin('csn_consignment as consignment', 'consignment.id', '=', 'items.consignment_id')
                        ->where('delivery.id', $delivery->id)
                        ->where('delivery.event', $delivery->event)
                        ->where('rcv_depot.prd_type', 'ce')
                        ->whereNull('delivery.deleted_at')
                        ->whereNull('rcv_depot.deleted_at')
                        ->whereNull('items.deleted_at')
                        ->whereNull('consignment.deleted_at')
                        ->select(
                            'rcv_depot.delivery_id'
                            , 'rcv_depot.event_item_id'
                            , DB::raw('min(rcv_depot.expiry_date) as expiry_date')
                            , 'items.product_style_id'
                            , 'items.title'
                            , 'items.prd_type'
                            , 'items.sku'
                            , 'items.num'
                            , 'consignment.send_depot_id as depot_id'
                            , 'consignment.send_depot_name as depot_name'
                        )
                        ->groupBy('rcv_depot.delivery_id')
                        ->groupBy('rcv_depot.event_item_id')
                        ->groupBy('items.product_style_id')
                        ->groupBy('items.title')
                        ->groupBy('items.prd_type')
                        ->groupBy('items.sku')
                        ->groupBy('consignment.send_depot_id')
                        ->groupBy('consignment.send_depot_name')
                        ->get();
                } else if (Event::csn_order()->value == $delivery->event) {
                    array_push($logisticStatus, LogisticStatus::D9000());
                }
                $user = new \stdClass();
                $user->id = $user_id;
                $user->name = $user_name;
                $reLFCDS = LogisticFlow::createDeliveryStatus($user, $delivery->id, $logisticStatus);
                if ($reLFCDS['success'] == 0) {
                    DB::rollBack();
                    return $reLFCDS;
                }
                // 寄倉、訂單自取才會有
                if (null != $queryComboElement && 0 < count($queryComboElement)) {
                    //新增並回寫ID
                    foreach($queryComboElement as $key => $element) {
                        //計算個別成本
                        $queryRcvDepotCost_byone = DB::table('dlv_receive_depot as rcv_depot')
                            ->select('rcv_depot.event_item_id'
                                , 'rcv_depot.product_title'
                                , 'rcv_depot.product_style_id'
                                , DB::raw('(rcv_depot.unit_cost) as unit_cost')
                                , DB::raw('(rcv_depot.qty) as qty')
                                , DB::raw('(rcv_depot.unit_cost * rcv_depot.qty) as cost')
                            )
                            ->whereNull('rcv_depot.deleted_at')
                            ->where('rcv_depot.delivery_id', '=', $delivery_id)
                            ->where('rcv_depot.event_item_id', '=', $element->event_item_id)
                            ->where('rcv_depot.prd_type', '=', 'ce');

                        //計算總成本
                        $queryRcvDepotCost = DB::query()->fromSub($queryRcvDepotCost_byone, 'tb')
                            ->select('tb.event_item_id'
                                , 'tb.product_title'
                                , 'tb.product_style_id'
                                , DB::raw('sum(tb.cost) / sum(tb.qty) as unit_cost')
                                , DB::raw('sum(tb.qty) / '. $element->num.' as unit_qty')
                            )
                            ->groupBy('tb.event_item_id')
                            ->groupBy('tb.product_style_id')
                            ->get()->toArray();
                        //計算該組合包成本價 (把每個元素的成本價加起來)
                        $total_cost = 0;
                        foreach($queryRcvDepotCost as $elementCost) {
                            $total_cost = $total_cost + ($elementCost->unit_cost * $elementCost->unit_qty);
                        }

                        $reSD = ReceiveDepot::setData(
                            null,
                            $delivery_id, //出貨單ID
                            $element->event_item_id, //子訂單商品ID
                            null, //組合包商品ID
                            'c', //商品類型
                            0, //是否為贈品 0:否
                            0,
                            '',
                            0,
                            '',
                            $element->product_style_id,
                            $element->sku,
                            $element->title,
                            $total_cost,
                            $element->num, //數量
                            $element->expiry_date);
                        if ($reSD['success'] == 0) {
                            DB::rollBack();
                            return $reSD;
                        } else {
                            $rcvDepot_elements = DB::table(app(ReceiveDepot::class)->getTable(). ' as rcv_depot')
                                ->where('rcv_depot.delivery_id', $delivery_id)
                                ->where('rcv_depot.event_item_id', $element->event_item_id)
                                ->where('rcv_depot.prd_type', 'ce')
                                ->whereNull('rcv_depot.deleted_at')
                            ;

                            $reStockChange =PurchaseLog::stockChange($event_id, $element->product_style_id, $event, $reSD['id'],
                                LogEventFeature::combo()->value, null, $element->num * -1, null, $element->title, 'c', $user_id, $user_name);
                            if ($reStockChange['success'] == 0) {
                                DB::rollBack();
                                return $reStockChange;
                            }
                            $rcvDepot_elements->update([
                                'rcv_depot.combo_id' => $reSD['id'],
                            ]);
                        }
                    }
                }

                //扣除入庫單庫存
                foreach ($rcvDepotGet as $item) {
                    $reShipIb = PurchaseInbound::shippingInbound($event, $event_id, $item->id, LogEventFeature::delivery()->value, $item->inbound_id, $item->qty, $user_id, $user_name);
                    if ($reShipIb['success'] == 0) {
                        DB::rollBack();
                        return $reShipIb;
                    }
                }

                //計算 寫入成本單價
                if (Event::order()->value == $delivery->event
                    || Event::consignment()->value == $delivery->event
                    || Event::csn_order()->value == $delivery->event) {
                    $query_total_cost = DB::table('dlv_receive_depot as rcv_depot')
                        ->where('rcv_depot.delivery_id', '=', $delivery_id)
                        ->whereNull('rcv_depot.deleted_at')
                        ->whereIn('prd_type', ['p', 'c'])
                        ->select(
                            'rcv_depot.delivery_id'
                            , 'rcv_depot.event_item_id'
                            , 'rcv_depot.product_style_id'
                            , 'rcv_depot.qty'
                            , DB::raw('(rcv_depot.unit_cost * rcv_depot.qty) as total_cost')
                        );
                    $query_unit_cost = DB::query()->fromSub($query_total_cost, 'tb')
                        ->select(
                            'tb.delivery_id'
                            , 'tb.event_item_id'
                            , 'tb.product_style_id'
                            , DB::raw('sum(tb.total_cost) / sum(tb.qty) as unit_cost')
                        )
                        ->groupBy('tb.delivery_id')
                        ->groupBy('tb.event_item_id')
                        ->groupBy('tb.product_style_id')
                        ->get();
                    if (isset($query_unit_cost) && 0 < count($query_unit_cost)) {
                        foreach ($query_unit_cost as $item) {
                            if (Event::order()->value == $delivery->event) {
                                //判斷若為訂單 則將成本回寫到 ord_items.unit_cost
                                OrderItem::where('id', '=', $item->event_item_id)->update([
                                    'unit_cost' => $item->unit_cost,]);
                            }
                            else if (Event::consignment()->value == $delivery->event) {
                                //判斷若為寄倉單 則將成本回寫到 csn_consignment_items.unit_cost
                                ConsignmentItem::where('id', '=', $item->event_item_id)->update([
                                    'unit_cost' => $item->unit_cost,]);
                            }
                            else if (Event::csn_order()->value == $delivery->event) {
                                //判斷若為寄倉訂購單 則將成本回寫到 csn_order_items.unit_cost
                                CsnOrderItem::where('id', '=', $item->event_item_id)->update([
                                    'unit_cost' => $item->unit_cost,]);
                            }
                        }
                    }
                }

                $curr_date = date('Y-m-d H:i:s');
                Delivery::where('id', '=', $delivery_id)->update([
                    'audit_date' => $curr_date,
                    'audit_user_id' => $user_id,
                    'audit_user_name' => $user_name,]);

                $rcvDepot->update([ 'audit_date' => $curr_date ]);

                //20220714 Hans:將出貨日填到子訂單
                if (Event::order()->value == $delivery->event) {
                    SubOrders::where('id', '=', $delivery->event_id)->update([ 'dlv_audit_date' => $curr_date ]);
                    //若為訂單 則在出貨審核後 寄送已出貨信件
                    Order::sendMail_OrderShipped($delivery->event_id);
                } else if (Event::consignment()->value == $delivery->event) {
                    Consignment::where('id', '=', $delivery->event_id)->update([ 'dlv_audit_date' => $curr_date ]);
                } else if (Event::csn_order()->value == $delivery->event) {
                    CsnOrder::where('id', '=', $delivery->event_id)->update([ 'dlv_audit_date' => $curr_date ]);
                }

                //寫入待出貨
                $orditems = null;
                if (Event::order()->value == $delivery->event) {
                    $orditems = DlvOutStock::getOrderToDlvQty($delivery->id, $delivery->event_id)->get();
                } else if (Event::consignment()->value == $delivery->event) {
                    $orditems = DlvOutStock::getCsnToDlvQty($delivery->id, $delivery->event_id)->get();
                }
                if (null != $orditems && 0 < count($orditems)) {
                    foreach ($orditems as $value_item) {
                        ProductStyle::willBeShipped($value_item->product_style_id, $value_item->stock_qty * -1);
                    }
                }

                return ['success' => 1, 'error_msg' => ""];
            });
        } else {
            return ['success' => 0, 'error_msg' => "無此出貨單"];
        }
        return $result;
    }

    //將成立的收貨資料變更為尚未成立
    public static function cancleShippingData($event, $event_id, $delivery_id, $user_id, $user_name) {
        $delivery = Delivery::where('id', $delivery_id)->get()->first();
        $rcvDepotGet = null;
        if (null != $delivery_id) {
            //找出不是組合包的商品款式
            $rcvDepot = ReceiveDepot::where('delivery_id', $delivery_id);
            if (Event::order()->value == $delivery->event
                || Event::consignment()->value == $delivery->event
            ) {
                //訂單、寄倉出貨時的組合包都是另外組 所以必須去除c
                $rcvDepot->where('prd_type', '<>' , 'c');
            }
            $rcvDepotGet = $rcvDepot->get();
        }

        if (null != $delivery &&null != $rcvDepotGet && 0 < count($rcvDepotGet)) {
            $result = IttmsDBB::transaction(function () use ($delivery, $rcvDepot, $rcvDepotGet, $event, $event_id, $delivery_id, $user_id, $user_name
            ) {
                // 判斷是否有入庫 有則回傳錯誤
                $inbound_already = PurchaseInbound::where('event', '=', $event)->where('event_id', '=' , $event_id)->get();
                if (isset($inbound_already) && 0 < count($inbound_already)) {
                    DB::rollBack();
                    return ['success' => 0, 'error_msg' => "無法復原! 此次出貨已有入庫"];
                }

                if (Event::order()->value == $delivery->event) {
                    // 訂單需另外判斷是否有退貨入庫，有則不可取消
                    $receiveDepotData = ReceiveDepot::where('delivery_id', $delivery_id)->where('back_qty', '>' , 0)->get();
                    if (isset($receiveDepotData) && 0 < count($receiveDepotData)) {
                        DB::rollBack();
                        return ['success' => 0, 'error_msg' => "無法復原! 已有退貨入庫"];
                    }

                    if ('pickup' == $delivery->ship_category) {
                        $event = 'ord_pickup';
                    }
                }
                if (Event::order()->value == $delivery->event
                    || Event::consignment()->value == $delivery->event
                ) {
                    //訂單、寄倉出貨時的組合包都是另外組 所以之前篩選時去掉，現在要加回做判斷
                    //找出相關組合包
                    $rcvDepotComboGet = ReceiveDepot::where('delivery_id', $delivery_id)->where('prd_type', '=' , 'c')->get();
                    if (null != $rcvDepotComboGet && 0 < count($rcvDepotComboGet)) {
                        //先取出個別數量紀錄 再刪除組合包
                        foreach($rcvDepotComboGet as $key_cb => $val_cb) {
                            if (Event::order()->value == $delivery->event || Event::consignment()->value == $delivery->event) {
                                //訂單、寄倉 須分解成元素，並刪除組合包 因出貨時會組成組合包
                                $reStockChange =PurchaseLog::stockChange($event_id, $val_cb->product_style_id, $event, $delivery->event_id,
                                    LogEventFeature::combo_del()->value, null, $val_cb->qty, null, $val_cb->product_title, 'c', $user_id, $user_name);
                                if ($reStockChange['success'] == 0) {
                                    DB::rollBack();
                                    return $reStockChange;
                                }
                                ReceiveDepot::where('id', $val_cb->id)->where('prd_type', '=' , 'c')->forceDelete();
                            } else if (Event::csn_order()->value == $delivery->event){
                                $reShipIb = PurchaseInbound::shippingInbound($event, $event_id, $val_cb->id, LogEventFeature::delivery_cancle()->value, $val_cb->inbound_id, $val_cb->qty * -1, $user_id, $user_name);
                                if ($reShipIb['success'] == 0) {
                                    DB::rollBack();
                                    return $reShipIb;
                                }
                            } else {
                                DB::rollBack();
                                return ['success' => 0, 'error_msg' => "無法判斷事件 ". json_encode($val_cb)];
                            }
                        }
                    }
                }

                //扣除入庫單庫存
                foreach ($rcvDepotGet as $item) {
                    $reShipIb = PurchaseInbound::shippingInbound($event, $event_id, $item->id, LogEventFeature::delivery_cancle()->value, $item->inbound_id, $item->qty * -1, $user_id, $user_name);
                    if ($reShipIb['success'] == 0) {
                        DB::rollBack();
                        return $reShipIb;
                    }
                }

                //取消出貨 所以將成本單價恢復成null
                if (Event::order()->value == $delivery->event) {
                    //判斷若為訂單 則將成本回寫到 ord_items.unit_cost
                    OrderItem::where('sub_order_id', '=', $delivery->event_id)->update([
                        'unit_cost' => null,]);
                }
                else if (Event::consignment()->value == $delivery->event) {
                    //判斷若為寄倉單 則將成本回寫到 csn_consignment_items.unit_cost
                    ConsignmentItem::where('consignment_id', '=', $delivery->event_id)->update([
                        'unit_cost' => null,]);
                }
                else if (Event::csn_order()->value == $delivery->event) {
                    //判斷若為寄倉訂購單 則將成本回寫到 csn_order_items.unit_cost
                    CsnOrderItem::where('csnord_id', '=', $delivery->event_id)->update([
                        'unit_cost' => null,]);
                }

                Delivery::where('id', '=', $delivery_id)->update([
                    'audit_date' => null,
                    'audit_user_id' => null,
                    'audit_user_name' => null,]);

                $rcvDepot->update([ 'audit_date' => null ]);

                //20220714 Hans:將出貨日填到子訂單
                if (Event::order()->value == $delivery->event) {
                    SubOrders::where('id', '=', $delivery->event_id)->update([ 'dlv_audit_date' => null ]);
                } else if (Event::consignment()->value == $delivery->event) {
                    Consignment::where('id', '=', $delivery->event_id)->update([ 'dlv_audit_date' => null ]);
                } else if (Event::csn_order()->value == $delivery->event) {
                    CsnOrder::where('id', '=', $delivery->event_id)->update([ 'dlv_audit_date' => null ]);
                }

                //寫入待出貨
                $orditems = null;
                if (Event::order()->value == $delivery->event) {
                    $orditems = DlvOutStock::getOrderToDlvQty($delivery->id, $delivery->event_id)->get();
                } else if (Event::consignment()->value == $delivery->event) {
                    $orditems = DlvOutStock::getCsnToDlvQty($delivery->id, $delivery->event_id)->get();
                }
                if (null != $orditems && 0 < count($orditems)) {
                    foreach ($orditems as $value_item) {
                        ProductStyle::willBeShipped($value_item->product_style_id, $value_item->stock_qty);
                    }
                }
                return ['success' => 1, 'error_msg' => ""];
            });
        } else {
            return ['success' => 0, 'error_msg' => "無此出貨單"];
        }
        return $result;
    }

    public static function deleteById($id)
    {
        ReceiveDepot::where('id', $id)->forceDelete();
    }

    //更新寄倉到貨數量
    public static function updateCSNArrivedNum($id, $addnum) {
        DB::beginTransaction();
        try {
            $updateArr = [];
            $updateArr['csn_arrived_qty'] = DB::raw("csn_arrived_qty + $addnum");
            ReceiveDepot::where('id', $id)
                ->update($updateArr);

            DB::commit();
            return ['success' => 1, 'error_msg' => ""];
        } catch (\Exception $e) {
            DB::rollback();
            return ['success' => 0, 'error_msg' => $e->getMessage()];
        }
    }

    public static function getDataList($param) {
        $query = DB::table('dlv_receive_depot as rcv_depot')
            ->whereNull('rcv_depot.deleted_at')
            ->select('rcv_depot.id as id'
                , 'rcv_depot.delivery_id as delivery_id'
                , 'rcv_depot.event_item_id as event_item_id'
                , 'rcv_depot.inbound_id as inbound_id'
                , 'rcv_depot.depot_id as depot_id'
                , 'rcv_depot.depot_name as depot_name'
                , 'rcv_depot.product_style_id as product_style_id'
                , 'rcv_depot.qty as qty'
                , 'rcv_depot.back_qty as back_qty'
                , 'rcv_depot.expiry_date as expiry_date'
            );

        if (isset($param['delivery_id'])) {
            $query->where('rcv_depot.delivery_id', '=', $param['delivery_id']);
        }
        return $query;
    }

    //取得寄倉入庫商品應進數量
    public static function getShouldEnterNumDataList($event, $event_id) {
        $raw = '( COALESCE(rcv_depot.qty, 0) - COALESCE(rcv_depot.csn_arrived_qty, 0) )';
        $result = DB::table('dlv_delivery as delivery')
            ->leftJoin('dlv_receive_depot as rcv_depot', 'rcv_depot.delivery_id', '=', 'delivery.id')
            ->select('*'
                , 'rcv_depot.id as rcv_deppot_id'
            )
            ->selectRaw('DATE_FORMAT(expiry_date,"%Y-%m-%d") as expiry_date')
            ->selectRaw($raw.' as should_enter_num')
//            ->where(DB::raw($raw), '>', 0)
            ->where('delivery.event', $event)
            ->where('delivery.event_id', $event_id)
            ->whereNotNull('delivery.audit_date') //判斷有做過出貨審核才給入
            ->whereNotNull('rcv_depot.id')
            ->whereNull('rcv_depot.deleted_at');
        //判斷寄倉 則會有組合包 需去除組合元素
        $result->where('rcv_depot.prd_type', '<>', 'ce');
        return $result;
    }

    //取得出貨列表
    public static function getDeliveryWithReceiveDepotList($event = null, $event_id = null, $delivery_id = null, $bac_papa_id = null, $product_style_id = null)
    {
        $result = DB::table('dlv_delivery as delivery')
            ->leftJoin('dlv_receive_depot as rcv_depot', 'rcv_depot.delivery_id', '=', 'delivery.id')
            ->leftJoin(app(ReceiveDepot::class)->getTable(). ' as rcv_depot_papa', function ($join) {
                $join->on('rcv_depot_papa.id', '=', 'rcv_depot.combo_id')
                    ->whereNotNull('rcv_depot.combo_id');
            })
            ->leftJoin(app(PurchaseInbound::class)->getTable() . ' as inbound', function ($join) {
                $join->on('inbound.id', '=', 'rcv_depot.inbound_id');
            })
            ->leftJoin(app(Purchase::class)->getTable() . ' as pcs', function ($join) {
                $join->on('pcs.id', '=', 'inbound.event_id')
                    ->where('inbound.event', '=', Event::purchase()->value);
            })
            ->leftJoin(app(Consignment::class)->getTable() . ' as csn', function ($join) {
                $join->on('csn.id', '=', 'inbound.event_id')
                    ->where('inbound.event', '=', Event::consignment()->value);
            })
            ->select('delivery.sn as delivery_sn'
                , 'rcv_depot.delivery_id as delivery_id'
                , 'rcv_depot.id as id'
                , 'rcv_depot.event_item_id as event_item_id'
                , 'rcv_depot.freebies as freebies'
                , 'rcv_depot.inbound_id as inbound_id'
                , 'rcv_depot.inbound_sn as inbound_sn'
                , 'rcv_depot.depot_id as depot_id'
                , 'rcv_depot.depot_name as depot_name'
                , 'rcv_depot_papa.product_style_id as papa_product_style_id'
                , 'rcv_depot.product_style_id as product_style_id'
                , 'rcv_depot.sku as sku'
                , 'rcv_depot.product_title as product_title'
                , 'rcv_depot.qty as qty'
                , 'rcv_depot.back_qty as back_qty'
                , 'rcv_depot.expiry_date as expiry_date'
                , 'rcv_depot.audit_date as audit_date'
                , DB::raw('case when "'. Event::purchase()->value. '" = inbound.event then pcs.sn'
                    . ' when "'. Event::consignment()->value. '" = inbound.event then csn.sn'
                    . ' else null end as event_sn'
                )
            )
            ->whereNull('rcv_depot.deleted_at')
            ->whereNull('rcv_depot_papa.deleted_at');

        if (null != $event) {
            $result->where('delivery.event', $event);
        }
        if (null != $event_id) {
            $result->where('delivery.event_id', $event_id);
        }
        if (null != $delivery_id) {
            $result->where('rcv_depot.delivery_id', $delivery_id);
        }
        if (null != $bac_papa_id) {
            $result->leftJoin(app(DlvElementBack::class)->getTable(). ' as elebac', function ($join) use($bac_papa_id) {
                $join->on('elebac.rcv_depot_id', '=', 'rcv_depot.id')
                    ->where('elebac.bac_papa_id', $bac_papa_id);;
            });
            $result->addSelect(
                'elebac.qty as elebac_qty'
                , 'elebac.memo as elebac_memo');
        }
        if (null != $product_style_id) {
            $result->where('rcv_depot.product_style_id', $product_style_id);
        }

        $result->orderBy('rcv_depot.id');
        return $result;
    }

    //取得子訂單商品列表 與對應的出貨列表
    public static function getOrderShipItemWithDeliveryWithReceiveDepotList($event, $sub_order_id, $delivery_id, $bac_papa_id = null, $product_style_id = null) {
        // 子訂單的商品列表
        $ord_items = OrderItem::getShipItem($sub_order_id)->get();
        // 對應的出貨資料
        $ord_items_arr = ReceiveDepot::getReceiveDepotParseData($event, $sub_order_id, $delivery_id, $bac_papa_id, $product_style_id, $ord_items);
        return $ord_items_arr;
    }

    public static function getCSNShipItemWithDeliveryWithReceiveDepotList($event, $consignment_id, $delivery_id, $bac_papa_id = null, $product_style_id = null) {
        $query_combo = DB::table('prd_style_combos')
            ->leftJoin('prd_product_styles', 'prd_product_styles.id', '=', 'prd_style_combos.product_style_child_id')
            ->select('prd_style_combos.product_style_id'
                , 'prd_style_combos.product_style_child_id'
                , 'prd_style_combos.qty'
                , 'prd_product_styles.id'
                , 'prd_product_styles.product_id'
                , 'prd_product_styles.title'
                , 'prd_product_styles.sku'
                , 'prd_product_styles.type'
            );

        //取得子訂單商品內 組合包拆解內容
        $query_ship_combo = DB::table('csn_consignment_items as item')
            ->leftJoin(DB::raw("({$query_combo->toSql()}) as tb_combo"), function ($join) {
                $join->on('tb_combo.product_style_id', '=', 'item.product_style_id');
            })
            ->leftJoin('prd_products', 'prd_products.id', '=', 'tb_combo.product_id')
            ->leftJoin(app(DlvOutStock::class)->getTable(). ' as outs', function ($join) use($delivery_id) {
                $join->on('outs.event_item_id', '=', 'item.id');
                $join->where('outs.delivery_id', '=', $delivery_id);
            })
            ->select('item.id AS item_id'
                , 'item.consignment_id AS consignment_id'
                , 'item.price'
                , 'item.prd_type'
                , 'item.memo'
                , 'item.created_at'
                , 'item.updated_at'
                , 'item.deleted_at'

                , 'item.title AS combo_product_title'
                , 'tb_combo.product_style_id AS papa_product_style_id'
                , 'tb_combo.id AS product_style_id'
                , 'tb_combo.sku'
            )
            ->selectRaw(DB::raw('( (item.num - ifnull(outs.qty, 0)) * tb_combo.qty ) AS qty'))
            ->selectRaw(DB::raw('Concat(prd_products.title, "-", tb_combo.title) AS product_title'))
            ->whereNotNull('tb_combo.type')
            ->whereNull('item.deleted_at')
            ->where('item.consignment_id', $consignment_id)
            ->mergeBindings($query_combo);

        //取得寄倉單 一般商品
        $csn_items = DB::table('csn_consignment_items as item')
            ->leftJoin('prd_product_styles', 'prd_product_styles.id', '=', 'item.product_style_id')
            ->leftJoin(app(DlvOutStock::class)->getTable(). ' as outs', function ($join) use($delivery_id) {
                $join->on('outs.event_item_id', '=', 'item.id');
                $join->where('outs.delivery_id', '=', $delivery_id);
            })
            ->select('item.id as item_id'
                , 'item.consignment_id'
                , 'item.price'
                , 'item.prd_type'
                , 'item.memo'
                , 'item.created_at'
                , 'item.updated_at'
                , 'item.deleted_at'
                , DB::raw('@item.title:=null as combo_product_title')
                , DB::raw('null as papa_product_style_id')
                , 'item.product_style_id'
                , 'item.sku'
                , DB::raw('( (item.num - ifnull(outs.qty, 0)) ) AS qty')
                , 'item.title as product_title'
            )
            ->whereNull('item.deleted_at')
            ->where('prd_product_styles.type', '=', 'p')
            ->where('item.consignment_id', $consignment_id);
        $query_ship_overview = $csn_items->union($query_ship_combo);

        // 對應的出貨資料
        $ord_items_arr = ReceiveDepot::getReceiveDepotParseData($event, $consignment_id, $delivery_id, $bac_papa_id, $product_style_id, $query_ship_overview->get());
        return $ord_items_arr;
    }

    public static function getCSNOrderShipItemWithDeliveryWithReceiveDepotList($event, $csn_order_id, $delivery_id, $bac_papa_id = null, $product_style_id = null) {
        // 子訂單的商品列表
        $query_ship = DB::table('csn_order_items as items')
            ->leftJoin('prd_product_styles', 'prd_product_styles.id', '=', 'items.product_style_id')
            ->leftJoin(app(DlvOutStock::class)->getTable(). ' as outs', function ($join) use($delivery_id) {
                $join->on('outs.event_item_id', '=', 'items.id');
                $join->where('outs.delivery_id', '=', $delivery_id);
            })
            //->where('prd_product_styles.type', '=', 'p')
            ->where('items.csnord_id', '=', $csn_order_id)
            ->select('items.id AS item_id'
                , 'items.csnord_id AS csnord_id'
                , 'items.title as product_title'
                , 'prd_product_styles.type as prd_type'
                , DB::raw('null as papa_product_style_id')
                , 'prd_product_styles.id  AS product_style_id'
                , 'prd_product_styles.product_id'
                , 'prd_product_styles.sku'
                , DB::raw('(items.num - ifnull(outs.qty, 0)) AS qty')
                //, DB::raw('Concat("") AS combo_product_title')
                , DB::raw('case when prd_product_styles.type = "c" then "組合包"
                    else  Concat("") end combo_product_title ')
            )
        ;

        // 對應的出貨資料
        $ord_items_arr = ReceiveDepot::getReceiveDepotParseData($event, $csn_order_id, $delivery_id, $bac_papa_id, $product_style_id, $query_ship->get());
        return $ord_items_arr;
    }

    private static function getReceiveDepotParseData($event, $event_id, $delivery_id, $bac_papa_id = null, $product_style_id, $obj_items) {
        $obj_items_arr = null;
        if (null != $obj_items && 0 < count($obj_items)) {
            $receiveDepotList = ReceiveDepot::getDeliveryWithReceiveDepotList($event, $event_id, $delivery_id, $bac_papa_id, $product_style_id)->get();
            $obj_items_arr = $obj_items;
            foreach ($obj_items_arr as $ord_key => $ord_item) {
                $obj_items_arr[$ord_key]->receive_depot = [];
            }
            if (0 < count($receiveDepotList)) {
                $receiveDepotList_arr = $receiveDepotList->toArray();
                foreach ($obj_items_arr as $ord_key => $ord_item) {
                    $obj_items_arr[$ord_key]->receive_depot = [];
                    foreach ($receiveDepotList_arr as $revd_key => $revd_item) {
                        if ($obj_items_arr[$ord_key]->item_id == $revd_item->event_item_id
                            && $obj_items_arr[$ord_key]->product_style_id == $revd_item->product_style_id
                        ) {
                            array_push($obj_items_arr[$ord_key]->receive_depot, $receiveDepotList_arr[$revd_key]);
                            unset($receiveDepotList_arr[$revd_key]);
                        }
                    }
                }
            }
        }
        return $obj_items_arr;
    }

    /**
     * 檢查退貨 組合包商品的數量正確性
     * @param $delivery_id  出貨單ID
     * @param $id_and_qty_list  出貨商品dlv_receive_depot.id 和數量
     * @return array|void
     */
    public static function checkBackDlvComboItemSameCount($delivery_id, $bac_papa_id, $id_and_qty_list) {
        //判斷prd_type = ce 則找到組合包 個別元素所需數量
        // 將同combo_id 的同款式加總
        // 同款式加總除上個別元素所需數量 判斷餘數為零 且各商數也相同

        if (0 < count($id_and_qty_list)) {
            //整理出貨商品對應的combo_id prd_type product_style_id
            for($i = 0 ; $i < count($id_and_qty_list['id']); $i++) {
                $rcv_repot = ReceiveDepot::where('id', $id_and_qty_list['id'][$i])->first();
                $id_and_qty_list['product_style_id'][$i] = $rcv_repot->product_style_id;
                $id_and_qty_list['product_title'][$i] = $rcv_repot->product_title;
                $id_and_qty_list['inbound_id'][$i] = $rcv_repot->inbound_id;
                $id_and_qty_list['product_title'][$i] = $rcv_repot->product_title;
                $id_and_qty_list['prd_type'][$i] = $rcv_repot->prd_type;
                $id_and_qty_list['combo_id'][$i] = $rcv_repot->combo_id;
                $id_and_qty_list['depot_id'][$i] = $rcv_repot->depot_id;
            }
        }
        $rcv_repot_combo = ReceiveDepot::where('delivery_id', $delivery_id)->where('prd_type', '=', 'c')->get();
        $delivery = Delivery::where('id', $delivery_id)->first();

        $rcv_repot_tobackqty = null;
        if(Event::order()->value == $delivery->event || Event::consignment()->value == $delivery->event) {
            $rcv_repot_tobackqty = ReceiveDepot::getRcvDepotToBackQty($delivery_id, $bac_papa_id);
        } elseif (Event::csn_order()->value == $delivery->event) {
            $rcv_repot_tobackqty = ReceiveDepot::getCsnOrderRcvDepotToBackQty($delivery_id, $bac_papa_id);
        }
        //將POST上來的資料依照combo_id、product_style_id加總
        $elements = array();
        if (0 < count($id_and_qty_list['id'])) {
            for($i = 0 ; $i < count($id_and_qty_list['id']); $i++) {
                $key_combo = $id_and_qty_list['combo_id'][$i].'';
                $key_styleId = $id_and_qty_list['product_style_id'][$i].'';
                if (0 == count($elements)) {
                    $elements = self::setDataToComboElement($elements, $id_and_qty_list, $i, $rcv_repot_tobackqty);
                }
                else {
                    $isExist = false;
                    $count_curr = 0;
                    $ele_curr = null;
                    foreach ($elements as $ele_item) {
                        $ele_curr = $ele_item;
                        if ($key_combo == $ele_item['combo_id'] && $key_styleId == $ele_item['product_style_id']) {
                            $isExist = true;
                            break;
                        }
                        $count_curr++;
                    }
                    if (true == $isExist) {
                        $elements[$count_curr]['back_qty'] = $ele_curr['back_qty'] + $id_and_qty_list['back_qty'][$i];
                    } else {
                        $elements = self::setDataToComboElement($elements, $id_and_qty_list, $i, $rcv_repot_tobackqty);
                    }
                }
            }
        }

        //檢查數量是否超過
        if (0 < count($elements)) {
            for($num_e = 0 ; $num_e < count($elements); $num_e++) {
                if (false == isset($elements[$num_e]['combo_id'])) {
                    if(isset($rcv_repot_tobackqty) && 0 < count($rcv_repot_tobackqty)) {
                        for($num_tbq = 0 ; $num_tbq < count($rcv_repot_tobackqty); $num_tbq++) {
                            if ($rcv_repot_tobackqty[$num_tbq]->product_style_child_id == $elements[$num_e]['product_style_id']
                            ) {
                                $elements[$num_e]['combo_product_style_id'] = intval($rcv_repot_tobackqty[$num_tbq]->product_style_id);
                                $elements[$num_e]['to_back_qty'] = intval($rcv_repot_tobackqty[$num_tbq]->to_back_qty);
                            }
                        }
                    }
                }
                if ($elements[$num_e]['to_back_qty'] < $elements[$num_e]['back_qty']) {
                    return ['success' => 0, 'error_msg' => "欲退數量超過原數量 ".$elements[$num_e]['product_title']. " ". json_encode($elements)];
                }
            }
        }

        //判斷有出貨組合包
        if(isset($rcv_repot_combo) && 0 < count($rcv_repot_combo)) {

            for($i = 0 ; $i < count($rcv_repot_combo); $i++) {
                //找到目前選擇的組合包內的元素最小單位
                $rcv_repot_element = DB::table(app(ReceiveDepot::class)->getTable(). ' as rcv_depot')
                    ->where('rcv_depot.delivery_id', $delivery_id)
                    ->select(
                        'rcv_depot.event_item_id'
                        , 'rcv_depot.delivery_id'
                        , 'rcv_depot.combo_id'
                        , 'rcv_depot.product_style_id'
                        , DB::raw('sum(rcv_depot.qty) as qty')
                        , DB::raw('concat('. $rcv_repot_combo[$i]->qty. ') as combo_qty')
                        , DB::raw('FORMAT(sum(rcv_depot.qty / '. $rcv_repot_combo[$i]->qty. '), 0) as unit_qty')
                    )
                    ->groupBy('rcv_depot.product_style_id')
                    ->where('rcv_depot.prd_type', '=', 'ce')
                    ->where('rcv_depot.combo_id', '=', $rcv_repot_combo[$i]->id)
                    ->whereNull('rcv_depot.deleted_at')
                    ->get();

                if (0 < count($elements)) {
                    $shangsoo = -1; //TODO 商數 之後必須從退貨數量寫到此 例如組合包退2件，商數就是2、組合包退一件，商數就是1
                    for($num_rre = 0 ; $num_rre < count($rcv_repot_element); $num_rre++) {
                        //取出POST資料 相同combo_id的
                        $elements_same_combo = [];
                        foreach ($elements as $ele_key => $ele_val) {
                            if ($ele_val['combo_id'] == $rcv_repot_element[$num_rre]->combo_id) {
                                array_push($elements_same_combo, $ele_val);
                            }
                        }
                        if (0 == count($elements_same_combo)) {
                            continue;
                        } else if (count($elements_same_combo) != count($rcv_repot_element)) {
                            //組合包元素個數和POST資料的元素個數不一致
                            return ['success' => 0, 'error_msg' => "個數錯誤，請檢查其他單品未退"];
                        } else {
                            // 同款式加總除上個別元素所需數量 判斷餘數為零 且各商數也相同
                            for($num_ele = 0 ; $num_ele < count($elements_same_combo); $num_ele++) {
                                if ($elements_same_combo[$num_ele]['product_style_id'] == $rcv_repot_element[$num_rre]->product_style_id) {
                                    if ($elements_same_combo[$num_ele]['back_qty'] > $rcv_repot_element[$num_rre]->qty) {
                                        return ['success' => 0, 'error_msg' => "退貨數量超出範圍"];
                                    }
                                    if (-1 == $shangsoo) {
                                        $shangsoo = $elements_same_combo[$num_ele]['back_qty'] / $rcv_repot_element[$num_rre]->unit_qty; //商數
                                    } else {
                                        $shangsoo_curr = $elements_same_combo[$num_ele]['back_qty'] / $rcv_repot_element[$num_rre]->unit_qty;
                                        if ($shangsoo != $shangsoo_curr) {
                                            return ['success' => 0, 'error_msg' => "數量不符 ". $shangsoo . ' vs '.  $shangsoo_curr];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return ['success' => 1, 'error_msg' => "", 'data' => $id_and_qty_list];
        }
        return ['success' => 1, 'error_msg' => "", 'data' => $id_and_qty_list];
    }

    private static function setDataToComboElement(array $elements, $id_and_qty_list, int $i, $rcv_repot_tobackqty)
    {
        $data = [];
        $data['combo_id'] = $id_and_qty_list['combo_id'][$i];
        $data['product_style_id'] = $id_and_qty_list['product_style_id'][$i];
        $data['product_title'] = $id_and_qty_list['product_title'][$i];
        $data['back_qty'] = intval($id_and_qty_list['back_qty'][$i]);
        if (isset($data['combo_id'])) {
            if (isset($rcv_repot_tobackqty) && 0 < count($rcv_repot_tobackqty)) {
                for ($num_tbq = 0; $num_tbq < count($rcv_repot_tobackqty); $num_tbq++) {
                    if ($rcv_repot_tobackqty[$num_tbq]->id == $data['combo_id']
                        && $rcv_repot_tobackqty[$num_tbq]->product_style_child_id == $data['product_style_id']
                    ) {
                        $data['combo_product_style_id'] = intval($rcv_repot_tobackqty[$num_tbq]->product_style_id);
                        $data['to_back_qty'] = intval($rcv_repot_tobackqty[$num_tbq]->to_back_qty * $rcv_repot_tobackqty[$num_tbq]->unit_qty);
                    }
                }
            }
        }
        array_push($elements, $data);
        return $elements;
    }

    public static function getCsnOrderRcvDepotToBackQty($delivery_id, $bac_papa_id) {
        //找一般商品欲退貨入庫資料
        $rcv_repot_p = DB::table(app(ReceiveDepot::class)->getTable(). ' as rcv_depot')
            ->where('rcv_depot.delivery_id', $delivery_id)
//            ->where('rcv_depot.prd_type', '=', 'p')
            ->whereNull('rcv_depot.combo_id')
            ->whereNull('rcv_depot.deleted_at')
            ->leftJoin(app(DlvBack::class)->getTable(). ' as back', function ($join) use($delivery_id, $bac_papa_id) {
                $join->on('back.product_style_id', '=', 'rcv_depot.product_style_id')
                    ->where('back.delivery_id', '=', $delivery_id)
                    ->where('back.bac_papa_id', '=', $bac_papa_id)
                    ->where('back.type', DlvBackType::product()->value);
            })
            ->select(
                'rcv_depot.event_item_id'
                , 'rcv_depot.id'
                , 'rcv_depot.delivery_id'
                , 'rcv_depot.product_style_id'
                , 'rcv_depot.product_style_id as product_style_child_id'
                , DB::raw('rcv_depot.qty as qty') //出貨數量
                , DB::raw('1 as unit_qty') //單位數量
                , 'back.qty as to_back_qty' //欲退數量
            );
        $rcv_repot_p = $rcv_repot_p->get();
        return $rcv_repot_p;
    }

    //找欲退貨數量
    public static function getRcvDepotToBackQty($delivery_id, $bac_papa_id) {
        //找一般商品欲退貨入庫資料
        $rcv_repot_p = DB::table(app(ReceiveDepot::class)->getTable(). ' as rcv_depot')
            ->where('rcv_depot.delivery_id', $delivery_id)
            ->where('rcv_depot.prd_type', '=', 'p')
            ->whereNull('rcv_depot.combo_id')
            ->whereNull('rcv_depot.deleted_at')
            ->leftJoin(app(DlvBack::class)->getTable(). ' as back', function ($join) use($delivery_id, $bac_papa_id) {
                $join->on('back.product_style_id', '=', 'rcv_depot.product_style_id')
                    ->where('back.delivery_id', '=', $delivery_id)
                    ->where('back.bac_papa_id', '=', $bac_papa_id)
                    ->where('back.type', DlvBackType::product()->value);
            })
            ->groupBy('rcv_depot.product_style_id')
            ->groupBy('rcv_depot.event_item_id')
            ->select(
                'rcv_depot.event_item_id'
                , 'rcv_depot.id'
                , 'rcv_depot.delivery_id'
                , 'rcv_depot.product_style_id'
                , 'rcv_depot.product_style_id as product_style_child_id'
                , DB::raw('sum(rcv_depot.qty) as qty') //出貨數量
                , DB::raw('1 as unit_qty') //單位數量
                , 'back.qty as to_back_qty' //欲退數量
            );
        //找組合包欲退貨入庫資料
        $rcv_repot_combo = DB::table(app(ReceiveDepot::class)->getTable(). ' as rcv_depot')
            ->where('rcv_depot.delivery_id', $delivery_id)
            ->where('rcv_depot.prd_type', '=', 'c')
            ->whereNull('rcv_depot.combo_id')
            ->whereNull('rcv_depot.deleted_at')
            ->leftJoin(app(ProductStyleCombo::class)->getTable(). ' as style_combo', function ($join) {
                $join->on('style_combo.product_style_id', '=', 'rcv_depot.product_style_id');
            })
            ->leftJoin(app(DlvBack::class)->getTable(). ' as back', function ($join) use($delivery_id, $bac_papa_id) {
                $join->on('back.product_style_id', '=', 'rcv_depot.product_style_id')
                    ->where('back.delivery_id', '=', $delivery_id)
                    ->where('back.bac_papa_id', '=', $bac_papa_id)
                    ->where('back.type', DlvBackType::product()->value);
            })
            ->groupBy('style_combo.product_style_id')
            ->groupBy('style_combo.product_style_child_id')
            ->groupBy('rcv_depot.event_item_id')
            ->select(
                'rcv_depot.event_item_id'
                , 'rcv_depot.id'
                , 'rcv_depot.delivery_id'
                , 'style_combo.product_style_id'
                , 'style_combo.product_style_child_id'
                , DB::raw('rcv_depot.qty as qty') //出貨數量
                , DB::raw('style_combo.qty as unit_qty') //單位數量
                , DB::raw('sum(back.qty) as to_back_qty') //欲退數量
            );
        $rcv_repot_combo = $rcv_repot_combo->union($rcv_repot_p)->get();
        return $rcv_repot_combo;
    }

    //找已退貨數量
    public static function getRcvDepotBackQty($delivery_id, $bac_papa_id, $event, $event_id) {
        $ord_items_arr = null;
        $rcv_repot_combo = null;
        if(Event::order()->value == $event) {
            $rcv_repot_combo = ReceiveDepot::getRcvDepotToBackQty($delivery_id, $bac_papa_id);
            $ord_items_arr = ReceiveDepot::getOrderShipItemWithDeliveryWithReceiveDepotList($event, $event_id, $delivery_id, $bac_papa_id);
        } else if(Event::consignment()->value == $event) {
            $rcv_repot_combo = ReceiveDepot::getRcvDepotToBackQty($delivery_id, $bac_papa_id);
            $ord_items_arr = ReceiveDepot::getCSNShipItemWithDeliveryWithReceiveDepotList($event, $event_id, $delivery_id, $bac_papa_id);
        } else if(Event::csn_order()->value == $event) {
            $rcv_repot_combo = ReceiveDepot::getCsnOrderRcvDepotToBackQty($delivery_id, $bac_papa_id);
            $ord_items_arr = ReceiveDepot::getCSNOrderShipItemWithDeliveryWithReceiveDepotList($event, $event_id, $delivery_id, $bac_papa_id);
        }
        if (isset($ord_items_arr) && 0 < count($ord_items_arr) && isset($rcv_repot_combo) && 0 < count($rcv_repot_combo)) {
            //出貨商品
            for ($num_item = 0; $num_item < count($ord_items_arr); $num_item++) {
                //組合包元素
                for ($num_combo = 0; $num_combo < count($rcv_repot_combo); $num_combo++) {
//                    dd("getRcvDepotBackQty", $ord_items_arr[0], $rcv_repot_combo);
                    //訂單、寄倉的組合包需要打散
                    if(Event::order()->value == $event || Event::consignment()->value == $event) {
                        if ($ord_items_arr[$num_item]->prd_type == 'c'
                            && $ord_items_arr[$num_item]->papa_product_style_id == $rcv_repot_combo[$num_combo]->product_style_id
                            && $ord_items_arr[$num_item]->product_style_id == $rcv_repot_combo[$num_combo]->product_style_child_id
                        ) {
                            $ord_items_arr[$num_item]->total_to_back_qty = $rcv_repot_combo[$num_combo]->to_back_qty * $rcv_repot_combo[$num_combo]->unit_qty;
                        } else if ($ord_items_arr[$num_item]->prd_type == 'p'
                            && null == $ord_items_arr[$num_item]->papa_product_style_id
                            && $ord_items_arr[$num_item]->product_style_id == $rcv_repot_combo[$num_combo]->product_style_id) {
                            $ord_items_arr[$num_item]->total_to_back_qty = $rcv_repot_combo[$num_combo]->to_back_qty;
                        }
                    } else {
                        if ($ord_items_arr[$num_item]->product_style_id == $rcv_repot_combo[$num_combo]->product_style_id) {
                            $ord_items_arr[$num_item]->total_to_back_qty = $rcv_repot_combo[$num_combo]->to_back_qty;
                        }
                    }
                }
            }
        }
        return $ord_items_arr;
    }
}
