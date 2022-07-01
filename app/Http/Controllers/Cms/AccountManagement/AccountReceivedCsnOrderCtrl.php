<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Enums\Order\PaymentStatus;
use App\Models\CsnOrder;
use App\Models\CsnOrderFlow;
use App\Models\CsnOrderItem;
use App\Models\Depot;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use App\Enums\Discount\DisCategory;
use App\Enums\Order\OrderStatus;
use App\Enums\Received\ReceivedMethod;

use App\Models\AllGrade;
use App\Models\Discount;
use App\Models\GeneralLedger;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReceivedDefault;
use App\Models\ReceivedOrder;
use App\Models\User;

class AccountReceivedCsnOrderCtrl extends AccountReceivedPapaCtrl
{
    public function destroy($id)
    {
        $target = ReceivedOrder::delete_received_order($id);
        if($target){
            if($target->source_type == app(CsnOrder::class)->getTable()){
                CsnOrderFlow::changeOrderStatus($target->source_id, OrderStatus::Add());
                $r_method['value'] = '';
                $r_method['description'] = '';
                CsnOrder::change_order_payment_status($target->source_id, PaymentStatus::Unpaid(), (object) $r_method);
            }

            wToast('刪除完成');

        } else {
            wToast('刪除失敗');
        }
        return redirect()->back();
    }

    public function getOrderData($order_id)
    {
        return CsnOrder::findOrFail($order_id);
    }

    public function getOrderListData($order_id)
    {
        return CsnOrderItem::item_order($order_id)->get();
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

    public function setDestroyStatus($source_id)
    {
        CsnOrderFlow::changeOrderStatus($source_id, OrderStatus::Add());
        $r_method['value'] = '';
        $r_method['description'] = '';
        CsnOrder::change_order_payment_status($source_id, PaymentStatus::Unpaid(), (object) $r_method);
    }
}
