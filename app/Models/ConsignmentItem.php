<?php

namespace App\Models;

use App\Enums\Delivery\Event;
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
                    ConsignmentItem::whereIn('id', $del_item_id_arr)->delete();
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
}
