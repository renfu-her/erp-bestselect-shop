<?php

namespace App\Models;

use App\Enums\Delivery\Event;
use App\Enums\Purchase\LogEventFeature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CsnOrderItem extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'csn_order_items';
    protected $guarded = [];

    //建立採購單
    public static function createData(array $newData, $operator_user_id, $operator_user_name)
    {
        if (isset($newData['csnord_id'])
            && $newData['product_style_id']
            && $newData['prd_type']
            && $newData['product_title']
            && $newData['sku']
            && $newData['price']
            && $newData['num']
        ) {
            return DB::transaction(function () use ($newData, $operator_user_id, $operator_user_name
            ) {
                $id = self::create([
                    "csnord_id" => $newData['csnord_id'],
                    "product_style_id" => $newData['product_style_id'],
                    "prd_type" => $newData['prd_type'],
                    "product_title" => $newData['product_title'],
                    "sku" => $newData['sku'],
                    "price" => $newData['price'],
                    "num" => $newData['num'],
                    "memo" => $newData['memo'] ?? null,
                ])->id;

                $rePcsLSC = PurchaseLog::stockChange($newData['csnord_id'], $newData['product_style_id'], Event::csn_order()->value, $id, LogEventFeature::style_add()->value, null, $newData['num'], null, $operator_user_id, $operator_user_name);

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

    public static function checkToUpdateItemData($itemId, array $purchaseItemReq, $key, $operator_user_id, $operator_user_name)
    {
        return DB::transaction(function () use ($itemId, $purchaseItemReq, $key, $operator_user_id, $operator_user_name
        ) {
            $purchaseItem = CsnOrderItem::where('id', '=', $itemId)
                //->select('price', 'num')
                ->get()->first();
            $purchaseItem->num = $purchaseItemReq['num'][$key];
            $purchaseItem->memo = $purchaseItemReq['memo'][$key];
            if ($purchaseItem->isDirty()) {
                foreach ($purchaseItem->getDirty() as $dirtykey => $dirtyval) {
                    $event = '';
                    $logEventFeature = null;
                    if($dirtykey == 'num') {
                        $event = '修改數量';
                        $logEventFeature = LogEventFeature::style_change_qty()->value;
                    }
                    if ('' != $event && null != $logEventFeature) {
                        $rePcsLSC = PurchaseLog::stockChange($purchaseItem->id, $purchaseItem->product_style_id
                            , Event::csn_order()->value, $itemId
                            , $logEventFeature, null, $dirtyval, $event
                            , $operator_user_id, $operator_user_name);
                        if ($rePcsLSC['success'] == 0) {
                            DB::rollBack();
                            return $rePcsLSC;
                        }
                    }
                }
                CsnOrderItem::where('id', $itemId)->update([
                    "num" => $purchaseItemReq['num'][$key],
                    "memo" => $purchaseItemReq['memo'][$key],
                ]);
            }
            return ['success' => 1, 'error_msg' => ''];
        });
    }

    public static function deleteItems($purchase_id, array $del_item_id_arr, $operator_user_id, $operator_user_name) {
        if (0 < count($del_item_id_arr)) {
            return DB::transaction(function () use ($purchase_id, $del_item_id_arr, $operator_user_id, $operator_user_name
            ) {
                //寄倉商品改直接刪除 因需要審核後才會做入庫
                $items = CsnOrderItem::whereIn('id', $del_item_id_arr)->get();
                CsnOrderItem::whereIn('id', $del_item_id_arr)->forceDelete();

                foreach ($items as $item) {
                    PurchaseLog::stockChange($purchase_id, $item->product_style_id, Event::csn_order()->value, $item->id, LogEventFeature::style_del()->value, null, $item->num, null, $operator_user_id, $operator_user_name);
                }
                return ['success' => 1, 'error_msg' => ''];
            });
        } else {
            return ['success' => 0, 'error_msg' => "未選擇預計刪除資料"];
        }
    }

    public static function getData($consignment_id) {
        return self::where('csnord_id', $consignment_id)->whereNull('deleted_at');
    }

}
