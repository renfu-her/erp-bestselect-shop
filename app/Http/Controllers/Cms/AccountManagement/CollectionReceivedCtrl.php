<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Enums\Discount\DisCategory;
use App\Enums\Order\PaymentStatus;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderFlow;
use App\Models\OrderItem;
use App\Enums\Order\OrderStatus;
use App\Models\ReceivedDefault;

class CollectionReceivedCtrl extends AccountReceivedPapaCtrl
{
    public function getOrderData($order_id)
    {
        return Order::findOrFail($order_id);
    }

    public function getOrderListData($order_id)
    {
        return OrderItem::item_order($order_id)->get();
    }

    public function getOrderListItemMsg($item)
    {
        return $item->product_title . '（' . $item->del_even . ' - ' . $item->del_category_name . '）（' . $item->product_price . ' * ' . $item->product_qty . '）';
    }

    public function getOrderPurchaser($order_data)
    {
        return Customer::where([
            'email'=>$order_data->email,
            // 'deleted_at'=>null,
        ])->first();
    }

    public function getSource_type()
    {
        return app(Order::class)->getTable();
    }

    public function getViewEdit()
    {
        return 'cms.account_management.collection_received.edit';
    }

    public function getRouteStore()
    {
        return 'cms.collection_received.store';
    }

    public function getRouteCreate()
    {
        return 'cms.collection_received.create';
    }

    public function getRouteDetail()
    {
        return 'cms.order.detail';
    }

    public function getRouteReceipt()
    {
        return 'cms.collection_received.receipt';
    }

    public function getViewReceipt()
    {
        return 'cms.account_management.collection_received.receipt';
    }

    public function getRouteReview()
    {
        return 'cms.collection_received.review';
    }

    public function getViewReview()
    {
        return 'cms.account_management.collection_received.review';
    }

    public function getRouteTaxation()
    {
        return 'cms.collection_received.taxation';
    }

    public function getViewTaxation()
    {
        return 'cms.account_management.collection_received.taxation';
    }

    public function doDestroy($source_id)
    {
        OrderFlow::changeOrderStatus($source_id, OrderStatus::Add());
        $r_method['value'] = '';
        $r_method['description'] = '';
        Order::change_order_payment_status($source_id, PaymentStatus::Unpaid(), (object) $r_method);
    }

    public function doReviewWhenReceived($id)
    {
        OrderFlow::changeOrderStatus($id, OrderStatus::Received());
        // 配發啟用日期
        Order::assign_dividend_active_date($id);
    }

    public function doReviewWhenReceiptCancle($id)
    {
        OrderFlow::changeOrderStatus($id, OrderStatus::Paided());
    }

    public function doTaxationWhenGet()
    {
        $discount_category = DisCategory::asArray();
        $discount_type = [];
        foreach ($discount_category as $dis_value) {
            $discount_type[$dis_value] = DisCategory::getDescription($dis_value);
        }
        ksort($discount_type);

        $default_discount_grade = [];
        foreach ($discount_type as $key => $value) {
            $default_discount_grade[$key] = ReceivedDefault::where('name', $key)->first() ? ReceivedDefault::where('name', $key)->first()->default_grade_id : null;
        }
        return array($discount_type, $default_discount_grade);
    }

    public function doTaxationWhenUpdate()
    {
        if(request('order_dlv') && is_array(request('order_dlv'))){
            $order = request('order_dlv');
            foreach($order as $key => $value){
                $value['order_id'] = $key;
                Order::update_dlv_taxation($value);
            }
        }

        if(request('discount') && is_array(request('discount'))){
            $discount = request('discount');
            foreach($discount as $key => $value){
                $value['discount_id'] = $key;
                Discount::update_order_discount_taxation($value);
            }
        }
    }
}
