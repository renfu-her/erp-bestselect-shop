<?php

namespace App\Http\Controllers\Api\Cms\Commodity;

use App\Enums\Delivery\Event;
use App\Http\Controllers\Controller;
use App\Models\Consignment;
use App\Models\CsnOrder;
use App\Models\Purchase;
use App\Models\PurchaseInbound;
use App\Models\SubOrders;
use Illuminate\Http\Request;

class StockCtrl extends Controller
{
    public static function inboundlist(Request $request) {
        $cond = [];
        $cond['data_per_page'] = getPageCount($request->input('data_per_page')?? 50);

        $cond['event'] = $request->input('event')?? null;
        $cond['purchase_sn'] = $request->input('purchase_sn')?? null;
        $cond['inbound_sn'] = $request->input('inbound_sn')?? null;
        $cond['title'] = $request->input('title')?? null;
        $cond['inventory_status'] = 'all';

        $cond['inbound_depot_id'] = $request->input('inbound_depot_id')?? [];
        $cond['inbound_user_id'] = $request->input('inbound_user_id')?? null;
        $cond['inbound_sdate'] = $request->input('inbound_sdate')?? null;
        $cond['inbound_edate'] = $request->input('inbound_edate')?? null;
        $cond['expire_day'] = $request->input('expire_day')?? null;
        $cond['prd_user_id'] = $request->input('prd_user_id')?? [];

        if (count($cond['prd_user_id']) == 0) {
            $condUser = true;
        } else {
            $condUser = $cond['prd_user_id'];
        }
        $cond['has_remain_qty'] = 1;

        $param = ['event' => null, 'purchase_sn' => $cond['purchase_sn'], 'inbound_sn' => $cond['inbound_sn'], 'keyword' => $cond['title']
            , 'inventory_status' => $cond['inventory_status']
            , 'inbound_depot_id' => $cond['inbound_depot_id']
            , 'inbound_user_id' => $cond['inbound_user_id']
            , 'expire_day' => $cond['expire_day']
            , 'prd_user_id' => $condUser
            , 'inbound_sdate' => $cond['inbound_sdate'], 'inbound_edate' => $cond['inbound_edate']
            , 'has_remain_qty' => $cond['has_remain_qty'] ?? 0
        ];

        $inboundList_purchase = PurchaseInbound::getInboundListWithEventSn(app(Purchase::class)->getTable(), [Event::purchase()->value], $param);
        $inboundList_order = PurchaseInbound::getInboundListWithEventSn(app(SubOrders::class)->getTable(), [Event::order()->value, Event::ord_pickup()->value], $param);
        $inboundList_consignment = PurchaseInbound::getInboundListWithEventSn(app(Consignment::class)->getTable(), [Event::consignment()->value], $param);
        $inboundList_csn_order = PurchaseInbound::getInboundListWithEventSn(app(CsnOrder::class)->getTable(), [Event::csn_order()->value], $param);
        $inboundList_purchase->union($inboundList_order);
        $inboundList_purchase->union($inboundList_consignment);
        $inboundList_purchase->union($inboundList_csn_order);
        $inboundList_purchase = $inboundList_purchase->orderByDesc('expiry_date')
            ->paginate($cond['data_per_page'])->toArray();

        $re = $inboundList_purchase;
        $re['status'] = '0';

        return response()->json($re);
    }
}
