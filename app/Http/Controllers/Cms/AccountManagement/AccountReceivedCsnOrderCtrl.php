<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Enums\Order\PaymentStatus;
use App\Models\CsnOrder;
use App\Models\CsnOrderFlow;
use App\Models\CsnOrderItem;
use App\Models\Depot;
use App\Enums\Order\OrderStatus;
use App\Models\ReceivedOrder;

class AccountReceivedCsnOrderCtrl extends AccountReceivedPapaCtrl
{
    public function getOrderData($order_id)
    {
        return CsnOrder::findOrFail($order_id);
    }

    public function getOrderListData($order_id)
    {
        return CsnOrderItem::item_order($order_id)->get();
    }

    public function getOrderListItemMsg($item)
    {
        return $item->product_title . '（' . $item->product_price . ' * ' . $item->product_qty . '）';
    }

    public function getOrderPurchaser($order_data)
    {
        return Depot::where('id', '=', $order_data->depot_id)->first();
    }

    public function getSource_type()
    {
        return app(CsnOrder::class)->getTable();
    }

    public function getViewEdit()
    {
        return 'cms.account_management.account_received_csn_order.edit';
    }

    public function getRouteStore()
    {
        return 'cms.ar_csnorder.store';
    }

    public function getRouteCreate()
    {
        return 'cms.ar_csnorder.create';
    }

    public function getRouteDetail()
    {
        return 'cms.consignment-order.edit';
    }

    public function getRouteReceipt()
    {
        return 'cms.ar_csnorder.receipt';
    }

    public function getViewReceipt()
    {
        return 'cms.account_management.account_received_csn_order.receipt';
    }

    public function getRouteReview()
    {
        return 'cms.ar_csnorder.review';
    }

    public function getViewReview()
    {
        return 'cms.account_management.account_received_csn_order.review';
    }

    public function getRouteTaxation()
    {
        return 'cms.ar_csnorder.taxation';
    }

    public function getViewTaxation()
    {
        return 'cms.account_management.account_received_csn_order.taxation';
    }

    public function setDestroyStatus($source_id)
    {
        CsnOrderFlow::changeOrderStatus($source_id, OrderStatus::Add());
        $r_method['value'] = '';
        $r_method['description'] = '';
        CsnOrder::change_order_payment_status($source_id, PaymentStatus::Unpaid(), (object) $r_method);
    }

    public function doReviewWhenReceived($id)
    {
        CsnOrderFlow::changeOrderStatus($id, OrderStatus::Received());
        // 配發啟用日期
//            CsnOrder::assign_dividend_active_date($id);
    }

    public function doReviewWhenReceiptCancle($id)
    {
        CsnOrderFlow::changeOrderStatus($id, OrderStatus::Paided());
    }

    public function doTaxationWhenUpdate()
    {
//        if(request('order_dlv') && is_array(request('order_dlv'))){
//            $order = request('order_dlv');
//            foreach($order as $key => $value){
//                $value['order_id'] = $key;
//                Order::update_dlv_taxation($value);
//            }
//        }
//
//        if(request('discount') && is_array(request('discount'))){
//            $discount = request('discount');
//            foreach($discount as $key => $value){
//                $value['discount_id'] = $key;
//                Discount::update_order_discount_taxation($value);
//            }
//        }
    }
}
