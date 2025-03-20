<?php

namespace App\Services\ETickets;

use App\Enums\Delivery\Event;
use App\Enums\eTicket\ETicketVendor;
use App\Helpers\IttmsDBB;
use App\Models\Delivery;
use App\Models\Depot;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductStyle;
use App\Models\Purchase;
use App\Models\PurchaseInbound;
use App\Models\PurchaseItem;
use App\Models\ReceiveDepot;
use App\Models\SubOrders;
use App\Models\Supplier;
use App\Models\TikAutoOrderErrorLog;
use App\Models\TikType;
use App\Models\TikYoubonOrder;
use App\Models\User;
use App\Services\ThirdPartyApis\Youbon\YoubonOrderService;
use Illuminate\Support\Facades\DB;

class AutoEticketPurchaseDeliveryServices
{

    public function toDoFromPcsToOrderAndDlv($order_id) {
        set_time_limit(60); // 做緩衝，打很多隻API，避免timeout
        // 找到訂單→子訂單→商品，判斷若為電子票券，則自動採購→核可→入庫→出貨→打星全安API

        $sub_order_with_eticket = SubOrders::where('order_id', '=', $order_id)
            ->where('ship_category', '=', 'eTicket')
            ->get();

        if ($sub_order_with_eticket->isEmpty()) {
            return ['success' => 1, 'error_msg' => ''];
        }
        if (0 < count($sub_order_with_eticket)) {
            foreach ($sub_order_with_eticket as $sub_order_item) {
                $delivery = Delivery::where('event_id', '=', $sub_order_item->id)
                    ->where('event', '=', 'order')
                    ->first();

                if (!$delivery) {
                    continue;
                }
                $latestTikYoubonOrder = TikYoubonOrder::where('delivery_id', $delivery->id)->orderBy('id', 'desc')->first();
                if ($latestTikYoubonOrder) {
                    continue;
                }

                $sub_order = SubOrders::where('id', '=', $delivery->event_id)->first();
                if (!$sub_order) {
                    return [
                        'success' => 0,
                        'error_msg' => '找不到子訂單資料 ID:'. $delivery->event_id,
                        'delivery_id' => $delivery->id
                    ];
                }

                $autoEticketCreatePurchase = $this->autoEticketCreatePurchase($order_id, $sub_order_item->id);
                if ($autoEticketCreatePurchase['success'] == '1') {
                    $youbonOrderService = new YoubonOrderService();
                    $processResult = $youbonOrderService->handleMultiETicketOrder($delivery->id, $sub_order->order_id);
                    if (isset($processResult['success']) && $processResult['success'] == '0') {
                        // log error $delivery->id、error_msg、process:handleMultiETicketOrder
                        TikAutoOrderErrorLog::createLog($sub_order->id, $sub_order->sn, 'handleMultiETicketOrder', $processResult['error_msg']);
                        return ['success' => 0, 'error_msg' => $processResult['error_msg'], 'delivery_id' => $delivery->id];
                    }
                } else {
                    // log error $delivery->id、error_msg、process:autoEticketCreatePurchase
                        TikAutoOrderErrorLog::createLog($sub_order->id, $sub_order->sn, 'autoEticketCreatePurchase', $autoEticketCreatePurchase['error_msg']);
                    return ['success' => 0, 'error_msg' => $autoEticketCreatePurchase['error_msg'], 'delivery_id' => $delivery->id];
                }
            }
        }
        return ['success' => 1, 'error_msg' => ''];
    }

    public function autoEticketCreatePurchase($order_id, $ord_sub_order_id) {
        $supplier = $this->getYoubonSupplier();
        $user = $this->getPurchaseServiceUser();
        $depot = $this->getETicketDepot();

        $query_order = $this->getETicketOrderItems($order_id, $ord_sub_order_id);

        // 如果查詢結果為空，提前返回
        if ($query_order->isEmpty()) {
            return ['success' => 0, 'error_msg' => '找不到符合條件的訂單商品'];
        }
        // 找出全部商品共有那些廠商，區分出廠商->商品
        $delivery_ids = [];
        $supplier_items = [];
        foreach($query_order as $order) {
            // 用 tik_type_code 拆分出不同的廠商的商品
            $supplier_items[$order->tik_type_code][] = [
                'item_id' => $order->item_id,
                'sub_order_id' => $order->sub_order_id,
                'product_id' => $order->product_id,
                'style_id' => $order->style_id,
                'style_title' => $order->style_title,
                'style_sku' => $order->style_sku,
                'style_estimated_cost' => $order->style_estimated_cost,
                'qty' => $order->item_qty,
                'ship_category' => $order->ship_category,
                'delivery_id' => $order->delivery_id,
            ];
            $delivery_ids[] = $order->delivery_id;
        }
        $delivery_ids = array_unique($delivery_ids); // 去除重複的出貨單ID
        $eYoubon_items = $supplier_items[ETicketVendor::YOUBON_CODE] ?? [];

        $msg = IttmsDBB::transaction(function () use (
            $delivery_ids, $supplier, $user, $depot, $eYoubon_items
        ) {
            // foreach 廠商，建立採購單和商品
            if (isset($eYoubon_items) && 0 < count($eYoubon_items)) {
                // 建立採購單
                $purchase1 = Purchase::createPurchase(
                    null,
                    $supplier->id,
                    $supplier->name,
                    $supplier->nickname,
                    null,
                    $user->id,
                    $user->name,
                    now(),
                );
                if ($purchase1['success'] == 0) {
                    DB::rollBack();
                    return $purchase1;
                }

                $purchaseID1 = $purchase1['id'] ?? null;

                foreach ($eYoubon_items as $item) {
                    // 建立採購單商品款式
                    $purchaseItem1 = PurchaseItem::createPurchase(
                        [
                            'purchase_id' => $purchaseID1,
                            'product_style_id' => $item['style_id'],
                            'title' => $item['style_title'],
                            'sku' => $item['style_sku'],
                            'price' => $item['style_estimated_cost'],
                            'num' => $item['qty'],
                            'temp_id' => null,
                            'memo' => ''
                        ],
                        $user->id,
                        $user->name,
                    );
                    if ($purchaseItem1['success'] == 0) {
                        DB::rollBack();
                        return $purchaseItem1;
                    }

                    $purchaseItemID1 = $purchaseItem1['id'] ?? null;

                    // 建立採購單入庫
                    $purchaseInbound1 = PurchaseInbound::createInbound(
                        Event::purchase()->value,
                        $purchaseID1,
                        $purchaseItemID1,
                        $item['style_id'],
                        $item['style_title'],
                        $item['style_sku'],
                        $item['style_estimated_cost'],
                        null,
                        now(),
                        $item['qty'],
                        $depot->id,
                        $depot->name,
                        $user->id,
                        $user->name,
                        null,
                    );
                    if ($purchaseInbound1['success'] == 0) {
                        DB::rollBack();
                        return $purchaseInbound1;
                    }

                    $purchaseInboundID1 = $purchaseInbound1['id'] ?? null;

                    $input = [
                        'delivery_id' => $item['delivery_id'],
                        'item_id' => $item['item_id'],
                        'product_style_id' => $item['style_id'],
                        'inbound_id' => [$purchaseInboundID1],
                        'qty' => [$item['qty']],
                    ];
                    // 建立出貨單出貨商品款式
                    $reRDSetDatas = ReceiveDepot::setDatasWithDeliveryIdWithItemId($input, $item['delivery_id'], $item['item_id']);
                    if ($reRDSetDatas['success'] == 0) {
                        DB::rollBack();
                        return $reRDSetDatas;
                    }
                }

            }
            if (0 < count($delivery_ids)) {
                foreach ($delivery_ids as $delivery_id) {
                    $delivery = Delivery::where('id', '=', $delivery_id)->first();
                    if (null == $delivery) {
                        DB::rollBack();
                        return ['success' => 0, 'error_msg' => "找不到出貨單資料 ID:". $delivery_id];
                    }
                    // 如果出貨單已經審核過，則不再重複審核
                    if (null != $delivery->audit_date)
                    {
                        DB::rollBack();
                        return ['success' => 0, 'error_msg' => "不可重複送出審核"];
                    } else
                    {
                        // 出貨單審核成立
                        $re = ReceiveDepot::setUpShippingData($delivery->event, $delivery->event_id, $delivery->id, 0, $user->id, $user->name);
                        if ($re['success'] == 0) {
                            DB::rollBack();
                            return $re;
                        }
                    }
                }
            }

            return ['success' => 1, 'error_msg' => ""];
        });
        return $msg;
    }

    public function getYoubonSupplier() {
        $supplier = Supplier::getSupplierList('16776078')->first();
        if (null == $supplier) {
            $paramReq_supplier = [
                "name" => "星全安旅行社有限公司",
                "nickname" => "星全安旅行社",
                "vat_no" => "16776078",
                "postal_code" => "10543",
                "contact_address" => null,
                "contact_person" => "訂單聯絡人",
                "job" => "職稱",
                "contact_tel" => "(02)2546-2222",
                "extension" => null,
                "fax" => null,
                "mobile_line" => "LINE",
                "email" => null,
                "invoice_address" => null,
                "invoice_postal_code" => null,
                "invoice_recipient" => null,
                "invoice_email" => null,
                "invoice_phone" => null,
                "invoice_date" => "1",
                "invoice_date_other" => null,
                "invoice_ship_fk" => "1",
                "invoice_date_fk" => "1",
                "shipping_address" => null,
                "shipping_postal_code" => null,
                "shipping_recipient" => null,
                "shipping_phone" => null,
                "shipping_method_fk" => "4",
                "pay_date" => null,
                "account_fk" => "1",
                "account_date" => "1",
                "account_date_other" => null,
                "request_data" => null,
                "memo" => null,
                "def_paytype" => "1",
                "paytype" => ["1"]
            ];
            $id = Supplier::createData($paramReq_supplier);
            $supplier = Supplier::where('id', '=', $id)->first();
        }
        return $supplier;
    }

    public function getPurchaseServiceUser() {
        $user_name = '採購服務系統';
        $user = User::where('name', $user_name)->first();
        if (null == $user) {
            $id = User::createUser($user_name, $user_name, null, '!a123456A');
            $user = User::where('id', '=', $id)->first();
        }
        return $user;
    }

    public function getETicketDepot() {
        $name = '電子票券出貨倉';
        $depot = Depot::where('name', '=', $name)->first();
        if (null == $depot) {
            $depot = Depot::firstOrCreate(
                ['name'     => $name],
                [
                    'sender'      => $name,
                    'can_tally' => '0',
                    'can_pickup' => '0',
                    'addr'      => '',
                    'city_id'   => '1',
                    'region_id' => '4',
                    'tel'       => '',
                    'address' => '',
                ]
            );
        }
        return $depot;
    }

    /**
     * 找訂單中的子訂單 ord_sub_orders.ship_category = 'eTicket'
     * @param $order_id
     * @return \Illuminate\Support\Collection
     */
    public function getETicketOrderItems($order_id, $ord_sub_order_id): \Illuminate\Support\Collection
    {
        $query_order = DB::table('ord_orders as order')
            ->leftJoin(app(SubOrders::class)->getTable() . ' as ord_sub_orders', 'ord_sub_orders.order_id', '=', 'order.id')
            ->leftJoin(app(OrderItem::class)->getTable() . ' as ord_items', 'ord_items.sub_order_id', '=', 'ord_sub_orders.id')
            ->leftJoin(app(ProductStyle::class)->getTable() . ' as styles'
                , 'styles.id', '=', 'ord_items.product_style_id')
            ->leftJoin(app(Product::class)->getTable() . ' as products', 'products.id', '=', 'styles.product_id')
            ->leftjoin(app(TikType::class)->getTable() . ' as tik_types', 'tik_types.id', '=', 'products.tik_type_id')
            ->leftJoin(app(Delivery::class)->getTable() . ' as dlv_delivery', function ($join) {
                $join->on('dlv_delivery.event_id', '=', 'ord_sub_orders.id')
                    ->where('dlv_delivery.event', '=', 'order');
            })
            ->select('order.id as order_id'
                , 'order.status as order_status'
                , 'ord_sub_orders.id as sub_order_id'
                , 'ord_sub_orders.ship_category as ship_category'
                , 'ord_sub_orders.total_price'
                , 'ord_items.id as item_id'
                , 'ord_items.qty as item_qty'
                , 'styles.id as style_id'
                , 'styles.title as style_title'
                , 'styles.sku as style_sku'
                , 'styles.estimated_cost as style_estimated_cost'
                , 'products.id as product_id'
                , 'tik_types.code as tik_type_code'
                , 'dlv_delivery.id as delivery_id'
            )
            ->whereIn('ord_sub_orders.ship_category', ['eTicket'])
            ->where('order.id', '=', $order_id)
            ->where('ord_sub_orders.id', '=', $ord_sub_order_id)
            ->get();
        return $query_order;
    }

}
