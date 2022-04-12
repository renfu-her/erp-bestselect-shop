<?php

namespace App\Models;

use App\Enums\Delivery\Event;
use App\Enums\Purchase\InboundStatus;
use App\Enums\Purchase\LogEvent;
use App\Enums\Purchase\LogEventFeature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ConsignmentItem extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'csn_consignment_items';
    protected $guarded = [];

    //建立採購單
    public static function createData(array $newData, $operator_user_id, $operator_user_name)
    {
        if (isset($newData['consignment_id'])
            && $newData['product_style_id']
            && $newData['title']
            && $newData['num']
            && $newData['price']
            && $newData['sku']
        ) {
            return DB::transaction(function () use ($newData, $operator_user_id, $operator_user_name
            ) {
                $id = self::create([
                    "consignment_id" => $newData['consignment_id'],
                    "product_style_id" => $newData['product_style_id'],
                    "title" => $newData['title'],
                    "num" => $newData['num'],
                    "price" => $newData['price'],
                    "sku" => $newData['sku'],
                    "memo" => $newData['memo'] ?? null,
                ])->id;

                $rePcsLSC = PurchaseLog::stockChange($newData['consignment_id'], $newData['product_style_id'], LogEvent::consignment()->value, $id, LogEventFeature::style_add()->value, $newData['num'], null, $operator_user_id, $operator_user_name);

                if ($rePcsLSC['success'] == 0) {
                    DB::rollBack();
                    return $rePcsLSC;
                }
                return ['success' => 1, 'error_msg' => "", 'id' => $id];
            });
        } else {
            return ['success' => 0, 'error_msg' => "未建立採購單"];
        }
    }
    public static function checkToUpdateItemData($itemId, array $purchaseItemReq, $key, string $changeStr, $operator_user_id, $operator_user_name)
    {
        return DB::transaction(function () use ($itemId, $purchaseItemReq, $key, $changeStr, $operator_user_id, $operator_user_name
        ) {
            $purchaseItem = ConsignmentItem::where('id', '=', $itemId)
                //->select('price', 'num')
                ->get()->first();
            $purchaseItem->num = $purchaseItemReq['num'][$key];
            if ($purchaseItem->isDirty()) {
                foreach ($purchaseItem->getDirty() as $dirtykey => $dirtyval) {
                    $changeStr .= ' itemID:' . $itemId . ' ' . $dirtykey . ' change to ' . $dirtyval;
                    $event = '';
                    $logEventFeature = null;
                    if($dirtykey == 'num') {
                        $event = '修改數量';
                        $logEventFeature = LogEventFeature::style_change_qty()->value;
                    }
                    if ('' != $event && null != $logEventFeature) {
                        $rePcsLSC = PurchaseLog::stockChange($purchaseItem->consignment_id, $purchaseItem->product_style_id
                            , Event::consignment()->value, $itemId
                            , $logEventFeature, $dirtyval, $event
                            , $operator_user_id, $operator_user_name);
                        if ($rePcsLSC['success'] == 0) {
                            DB::rollBack();
                            return $rePcsLSC;
                        }
                    }
                }
                ConsignmentItem::where('id', $itemId)->update([
                    "num" => $purchaseItemReq['num'][$key],
                ]);
            }
            return ['success' => 1, 'error_msg' => $changeStr];
        });
    }

    public static function deleteItems($purchase_id, array $del_item_id_arr, $operator_user_id, $operator_user_name) {
        if (0 < count($del_item_id_arr)) {
            //判斷若其一有到貨 則不可刪除

            $query = DB::table('csn_consignment_items as csn_items')
                ->leftJoin('dlv_receive_depot as rcv_depot', function ($join) {
                    $join->on('rcv_depot.event_item_id', '=', 'csn_items.id');
                    $join->where('rcv_depot.csn_arrived_qty', '>', 0);
                })
                ->whereNotNull('rcv_depot.id')
                ->whereIn('csn_items.id', $del_item_id_arr)->get();

            if (0 < count($query)) {
                return ['success' => 0, 'error_msg' => "有入庫 不可刪除"];
            } else {
                return DB::transaction(function () use ($purchase_id, $del_item_id_arr, $operator_user_id, $operator_user_name
                ) {
                    //寄倉商品改直接刪除 因需要審核後才會做入庫
                    ConsignmentItem::whereIn('id', $del_item_id_arr)->forceDelete();
                    foreach ($del_item_id_arr as $del_id) {
                        PurchaseLog::stockChange($purchase_id, null, Event::consignment()->value, $del_id, LogEventFeature::style_del()->value, null, null, $operator_user_id, $operator_user_name);
                    }
                    return ['success' => 1, 'error_msg' => ''];
                });
            }
        } else {
            return ['success' => 0, 'error_msg' => "未選擇預計刪除資料"];
        }
    }

    //更新到貨數量
    public static function updateArrivedNum($id, $addnum) {
        return DB::transaction(function () use ($id, $addnum
        ) {
            $updateArr = [];
            $updateArr['arrived_num'] = DB::raw("arrived_num + $addnum");
            ConsignmentItem::where('id', $id)
                ->update($updateArr);
            return ['success' => 1, 'error_msg' => ""];
        });
    }

    public static function getData($consignment_id) {
        return self::where('consignment_id', $consignment_id)->whereNull('deleted_at');
    }

    //取得寄倉商品和原本對應的採購入庫單
    public static function getOriginInboundDataList($consignment_id = null) {
        //取得原對應採購單
        $subQuery = DB::table('pcs_purchase_inbound as inbound1')
            ->leftJoin('pcs_purchase_inbound as inbound2', function ($join) {
                $join->on('inbound2.id', '=', 'inbound1.origin_inbound_id')
                    ->where('inbound1.event', '=', Event::consignment()->value);
            })
            ->select('inbound1.event'
                , 'inbound1.event_id'
                , 'inbound1.event_item_id'
                , 'inbound1.product_style_id'
                , DB::raw('GROUP_CONCAT(DISTINCT inbound1.id) as inbound_id')
                , DB::raw('GROUP_CONCAT(DISTINCT inbound2.sn) as inbound_sn')
            )
            ->groupBy('inbound1.event')
            ->groupBy('inbound1.event_id')
            ->groupBy('inbound1.event_item_id')
            ->groupBy('inbound1.product_style_id')
//            ->where('inbound1.event_id', '=', $consignment_id)
            ->where('inbound1.event', '=', Event::consignment()->value);
        if ($consignment_id) {
            $subQuery->where('inbound1.event_id', $consignment_id);
        }

        //將原採購單資料對應到目前出貨單商品
        $subQueryRcvDepot = DB::table('dlv_delivery as delivery')
            ->leftJoin('dlv_receive_depot as rcv_depot', 'rcv_depot.delivery_id', '=', 'delivery.id')
            ->leftJoinSub($subQuery, 'origin', function($join) use($consignment_id) {
                $join->on('origin.product_style_id', 'rcv_depot.product_style_id')
                    ->on('origin.event_item_id', 'rcv_depot.id');
//                    ->where('origin.event_id', $consignment_id);
            })
            ->select('rcv_depot.event_item_id as csn_item_id'
                , 'origin.event'
                , 'origin.event_id'
                , 'origin.product_style_id'
                , DB::raw('GROUP_CONCAT(DISTINCT origin.event_item_id) as origin_rcv_depot_id')
                , DB::raw('GROUP_CONCAT(DISTINCT origin.inbound_id) as origin_inbound_id')
                , DB::raw('GROUP_CONCAT(DISTINCT origin.inbound_sn) as origin_inbound_sn')
            )
//            ->where('delivery.event_id', $consignment_id)
            ->where('delivery.event', Event::consignment()->value)
            ->groupBy('rcv_depot.event_item_id')
            ->groupBy('origin.event')
            ->groupBy('origin.event_id')
            ->groupBy('origin.product_style_id');
        if ($consignment_id) {
            $subQueryRcvDepot->where('delivery.event_id', $consignment_id);
        }

        $inboundOverviewList = PurchaseInbound::getOverviewInboundList(Event::consignment()->value, $consignment_id);

        $consignmentItemData = DB::table('csn_consignment_items as items')
            ->leftJoinSub($subQueryRcvDepot, 'rcv_depot', function($join) {
                $join->on('rcv_depot.csn_item_id', '=', 'items.id')
                    ->on('rcv_depot.product_style_id', '=', 'items.product_style_id');
            })
            ->leftJoinSub($inboundOverviewList, 'inbound', function($join) {
                $join->on('inbound.consignment_id', 'items.consignment_id')
                    ->on('inbound.product_style_id', 'items.product_style_id');
            })
            ->select('items.id'
                , 'items.consignment_id'
                , 'items.product_style_id'
                , 'items.title'
                , 'items.sku'
                , 'items.price'
                , 'items.num'
                , 'items.memo'
                , DB::raw('DATE_FORMAT(items.created_at,"%Y-%m-%d") as created_at')
                , DB::raw('DATE_FORMAT(items.updated_at,"%Y-%m-%d") as updated_at')
                , DB::raw('DATE_FORMAT(items.deleted_at,"%Y-%m-%d") as deleted_at')
                , 'rcv_depot.origin_rcv_depot_id'
                , 'rcv_depot.origin_inbound_id'
                , 'rcv_depot.origin_inbound_sn'
                , 'inbound.inbound_user_name'
                , 'inbound.inbound_type'
            )
            ->whereNull('items.deleted_at');
        if ($consignment_id) {
            $consignmentItemData->where('items.consignment_id', $consignment_id);
        }

        return $consignmentItemData;
    }

    public static function getOriginInboundDataListWithCSN(
        $consignment_sn = null
        , $send_depot_id = null
        , $receive_depot_id = null
        , $csn_sdate = null
        , $csn_edate = null
        , $audit_status = null
        , $inbound_status = null) {

        $consignmentItemData = ConsignmentItem::getOriginInboundDataList();
        $consignmentData  = Consignment::getDeliveryData();
        $result = DB::table(DB::raw("({$consignmentData->toSql()}) as consignment"))
            ->leftJoinSub($consignmentItemData, 'items', function($join) {
                $join->on('items.consignment_id', '=', 'consignment.consignment_id');
            })
            ->mergeBindings($consignmentData);
        if ($consignment_sn) {
            $result->where('consignment.consignment_sn', $consignment_sn);
        }
        if ($send_depot_id) {
            $result->where('consignment.send_depot_id', $send_depot_id);
        }
        if ($receive_depot_id) {
            $result->where('consignment.receive_depot_id', $receive_depot_id);
        }
        if ($csn_sdate && $csn_edate) {
            $sDate = date('Y-m-d 00:00:00', strtotime($csn_sdate));
            $eDate = date('Y-m-d 23:59:59', strtotime($csn_edate));
            $result->whereBetween('consignment.created_at_withHIS', [$sDate, $eDate]);
        }
        if ($audit_status) {
            $result->where('consignment.audit_status', $audit_status);
        }
        if ($inbound_status) {
            $arr_status = [];
            if (in_array(InboundStatus::not_yet()->value, $inbound_status)) {
                array_push($arr_status, InboundStatus::getDescription(InboundStatus::not_yet()->value));
            }
            if (in_array(InboundStatus::normal()->value, $inbound_status)) {
                array_push($arr_status, InboundStatus::getDescription(InboundStatus::normal()->value));
            }
            if (in_array(InboundStatus::shortage()->value, $inbound_status)) {
                array_push($arr_status, InboundStatus::getDescription(InboundStatus::shortage()->value));
            }
            if (in_array(InboundStatus::overflow()->value, $inbound_status)) {
                array_push($arr_status, InboundStatus::getDescription(InboundStatus::overflow()->value));
            }

            $result->whereIn('inbound_type', $arr_status);
        }
        $result->orderByDesc('consignment.consignment_id');
        return $result;
    }
}
