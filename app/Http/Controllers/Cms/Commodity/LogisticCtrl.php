<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\Event;
use App\Http\Controllers\Controller;
use App\Models\Consum;
use App\Models\Delivery;
use App\Models\Logistic;
use App\Models\SubOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogisticCtrl extends Controller
{
    public function create($sub_order_id)
    {
        $sub_order = SubOrders::getListWithShiGroupById($sub_order_id)->get()->first();
        if (null == $sub_order) {
            return abort(404);
        }

        // 出貨單號ID
        $delivery = Delivery::getData(Event::order()->value, $sub_order->id)->get()->first();
        $delivery_id = null;
        if (null != $delivery) {
            $delivery_id = $delivery->id;
        }

        if (null == $delivery) {
            return abort(404);
        }
        $logistic = Logistic::where('delivery_id', $delivery_id)->get()->first();
        $logistic_id = null;
        //若沒有則新增
        if (null == $logistic) {
            $re = Logistic::createData($delivery_id);
            if ($re['success'] == 0) {
                DB::rollBack();
            } else {
                $logistic_id = $re['id'];
            }
        } else {
            $logistic_id = $logistic->id;
        }

        //顯示出貨商品列表product_title ; 單價price ; 數量send_qty ; 小計price*數量send_qty
        //組合包判斷兩者欄位不同都顯示:product_title rec_product_title，否則只顯示product_title
        $deliveryList = Delivery::getListToLogistic()->get();

        //取得出貨耗材列表
        //打API post api/product/get-product-styles 帶參數 'consume':1

        //取得原出貨單 預設基本設定的物流成本
//        ShipmentGroup::getDataWithCost();
        $deliveryCost = Delivery::getListWithCost($delivery_id)->get()->first();

        return view('cms.commodity.logistic.edit', [
            'delivery' => $delivery,
            'logistic' => $logistic,
            'sub_order_id' => $sub_order_id,
            'deliveryList' => $deliveryList,
            'deliveryCost' => $deliveryCost,
            'formAction' => Route('cms.logistic.create', [$logistic_id], true)
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'logistic_id' => 'required|numeric',
            'package_sn' => 'sometimes|string',
            'ship_group_id' => 'required|numeric',
            'cost' => 'required|numeric|min:0',
            'memo' => 'sometimes|string',
        ]);
        $logistic_id = $request->input('logistic_id');
        $input = $request->only('logistic_id', 'ship_group_id', 'cost', 'package_sn', 'memo');

        $errors = [];
        $reLgt = Logistic::updateData(
            $input['logistic_id']
            , $input['package_sn']
            , $input['ship_group_id']
            , $input['cost']
            , $input['memo']
        );
        if ($reLgt['success'] == '0') {
            $errors['error_msg'] = $reLgt['error_msg'];
            return redirect()->back()->withInput()->withErrors($errors);
        }
        $logistic = Logistic::where('id', '=', $input['logistic_id'])->get()->first();
        if (null != $logistic->audit_date) {
            $errors['error_msg'] = '不可重複送出審核';
        } else {
            $re = null;
            if ($re['success'] == '1') {
                $re = Consum::setUpLogisticData($logistic_id);
                wToast('儲存成功');
                return redirect(Route('cms.logistic.create', [$logistic->delivery_id], true));
            }
            $errors['error_msg'] = $re['error_msg'];
        }

        return redirect()->back()->withInput()->withErrors($errors);
    }

    //刪除物流單耗材
    public function destroyItem(Request $request, $event, $eventId, int $consumId)
    {
        Consum::deleteById($consumId);
        wToast('刪除成功');
        if(Event::order()->value == $event) {
            return redirect(Route('cms.logistic.create', [$eventId], true));
        }
    }
}
