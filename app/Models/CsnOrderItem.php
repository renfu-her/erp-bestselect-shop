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
            && isset($newData['product_style_id'])
            && isset($newData['prd_type'])
            && isset($newData['title'])
            && isset($newData['sku'])
            && isset($newData['price'])
            && isset($newData['num'])
        ) {
            return DB::transaction(function () use ($newData, $operator_user_id, $operator_user_name
            ) {
                $id = self::create([
                    "csnord_id" => $newData['csnord_id'],
                    "product_style_id" => $newData['product_style_id'],
                    "prd_type" => $newData['prd_type'],
                    "title" => $newData['title'],
                    "sku" => $newData['sku'],
                    "price" => $newData['price'],
                    "num" => $newData['num'],
                    "memo" => $newData['memo'] ?? null,
                ])->id;

                $rePcsLSC = PurchaseLog::stockChange($newData['csnord_id'], $newData['product_style_id'], Event::csn_order()->value, $id, LogEventFeature::style_add()->value, null, $newData['num'], null, $newData['title'], $newData['prd_type'], $operator_user_id, $operator_user_name);

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
            $purchaseItem->price = $purchaseItemReq['price'][$key];
            $purchaseItem->memo = $purchaseItemReq['memo'][$key];
            if ($purchaseItem->isDirty()) {
                foreach ($purchaseItem->getDirty() as $dirtykey => $dirtyval) {
                    $event = '';
                    $logEventFeature = null;
                    if($dirtykey == 'num') {
                        $event = '修改數量';
                        $logEventFeature = LogEventFeature::style_change_qty()->value;
                    } else if($dirtykey == 'price') {
                        $event = '修改價錢';
                        $logEventFeature = LogEventFeature::style_change_price()->value;
                    }
                    if ('' != $event && null != $logEventFeature) {
                        $rePcsLSC = PurchaseLog::stockChange($purchaseItem->id, $purchaseItem->product_style_id
                            , Event::csn_order()->value, $itemId
                            , $logEventFeature, null, $dirtyval, $event
                            , $purchaseItem->title, $purchaseItem->prd_type
                            , $operator_user_id, $operator_user_name);
                        if ($rePcsLSC['success'] == 0) {
                            DB::rollBack();
                            return $rePcsLSC;
                        }
                    }
                }
                CsnOrderItem::where('id', $itemId)->update([
                    "num" => $purchaseItemReq['num'][$key],
                    "price" => $purchaseItemReq['price'][$key],
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
                    PurchaseLog::stockChange($purchase_id, $item->product_style_id, Event::csn_order()->value, $item->id, LogEventFeature::style_del()->value, null, $item->num * -1, null, $item->title, $item->prd_type, $operator_user_id, $operator_user_name);
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

    public static function item_order($order_id)
    {
        $query = DB::table('csn_order_items as ord_items')
            ->leftJoin('csn_orders as ord_orders', 'ord_orders.id', '=', 'ord_items.csnord_id')
            ->leftJoin('prd_product_styles as styles', 'styles.id', '=', 'ord_items.product_style_id')
            ->leftJoin('prd_products as products', 'products.id', '=', 'styles.product_id')
            ->leftJoin('usr_users as users', 'users.id', '=', 'products.user_id')

            ->where([
                'ord_orders.id'=>$order_id,
            ])
            ->select(
                'ord_orders.id AS order_id',
                'ord_orders.status AS order_status',
                'ord_orders.memo AS order_note',

                'ord_orders.sn AS del_sn',
//                'ord_sub_orders.ship_category_name AS del_category_name',
//                'ord_sub_orders.ship_event AS del_even',
//                'ord_sub_orders.ship_temp AS del_temp',

                'ord_items.sku AS product_sku',
                'ord_items.title AS product_title',
                'ord_items.price AS product_price',
                'ord_items.num AS product_qty',
//                'ord_items.discount_value AS product_discount',
//                'ord_items.discounted_price AS product_after_discounting_price',

                'products.id as product_id',
                'products.has_tax as product_taxation',

                'users.id as product_user_id',
                'users.name as product_user_name'
            )
            ->selectRaw('(ord_items.price * ord_items.num) AS product_origin_price');

        return $query;
    }
}
