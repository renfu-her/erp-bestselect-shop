<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\LogisticStatus;
use App\Enums\Order\OrderStatus;
use App\Exports\Delivery\DeliveryProductListExport;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Facades\Excel;

class DeliveryProductCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();

        $cond['search_supplier'] = Arr::get($query, 'search_supplier', []);
        $cond['keyword'] = Arr::get($query, 'keyword');
        $cond['order_sdate'] = Arr::get($query, 'order_sdate', null);
        $cond['order_edate'] = Arr::get($query, 'order_edate', null);
        $cond['delivery_sdate'] = Arr::get($query, 'delivery_sdate', null);
        $cond['delivery_edate'] = Arr::get($query, 'delivery_edate', null);
        $cond['logistic_status_code'] = Arr::get($query, 'logistic_status_code', []);
        $cond['order_status'] = Arr::get($query, 'order_status', []);

        $cond['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page'));

        $data_list = Delivery::getListByProduct($cond)->paginate($cond['data_per_page'])->appends($query);

        $order_status = [];
        foreach (OrderStatus::asArray() as $item) {
            $order_status[$item] = OrderStatus::getDescription($item);
        }
        return view('cms.commodity.delivery.product_list', [
            'dataList' => $data_list,
            'searchParam' => $cond,
            'logisticStatus' => LogisticStatus::asArray(),
            'order_status' => $order_status,
            'data_per_page' => $cond['data_per_page'],
            'suppliers' => Supplier::select('name', 'id', 'vat_no')->get()->toArray(),
        ]);
    }

    public function exportList(Request $request)
    {
        $query = $request->query();

        $cond['search_supplier'] = Arr::get($query, 'search_supplier', []);
        $cond['keyword'] = Arr::get($query, 'keyword');
        $cond['order_sdate'] = Arr::get($query, 'order_sdate', null);
        $cond['order_edate'] = Arr::get($query, 'order_edate', null);
        $cond['delivery_sdate'] = Arr::get($query, 'delivery_sdate', null);
        $cond['delivery_edate'] = Arr::get($query, 'delivery_edate', null);
        $cond['logistic_status_code'] = Arr::get($query, 'logistic_status_code', []);
        $cond['order_status'] = Arr::get($query, 'order_status', []);

        $data_list = Delivery::getListByProduct($cond)
            ->get();

        $data_arr = [];
        if (null != $data_list) {
            foreach ($data_list as $key => $item) {
                $ord_item_data = (null != $item->ord_item_data)? json_decode($item->ord_item_data): null;
                $ord_item_id = "";
                $ord_title = "";
                $product_style_id = "";
                $ord_price = "";
                $ord_qty = "";
                $ord_origin_price = "";
                if(null != $ord_item_data && 0 < count($ord_item_data)) {
                    foreach ($ord_item_data as $item_key => $item_data) {
                        $endstr = ($item_key != count($ord_item_data) - 1) ? PHP_EOL: '';
                        $ord_item_id .= $item_data->ord_item_id. $endstr;
                        $ord_title .= $item_data->ord_title. $endstr;
                        $product_style_id .= $item_data->product_style_id. $endstr;
                        $ord_price .= $item_data->ord_price. $endstr;
                        $ord_qty .= $item_data->ord_qty. $endstr;
                        $ord_origin_price .= $item_data->ord_origin_price. $endstr;
                    }
                }

                $rcv_depot_data = (null != $item->rcv_depot_data)? json_decode($item->rcv_depot_data): null;
                $dlv_product_title = "";
                $dlv_qty = "";
                if(null != $rcv_depot_data && 0 < count($rcv_depot_data)) {
                    foreach ($rcv_depot_data as $item_key => $item_data) {
                        $endstr = ($item_key != count($rcv_depot_data) - 1) ? PHP_EOL: '';
                        $dlv_product_title .= $item_data->product_title. $endstr;
                        $dlv_qty .= $item_data->qty. $endstr;
                    }
                }

                $data_arr[] = [
                    $key + 1,
                    $item->event_sn,
                    $ord_title,
                    $item->ord_created_at,
                    $item->ord_status,
                    $item->logistic_status,
                    $ord_price,
                    $ord_qty,
                    $ord_origin_price,
                    $item->buyer_name,
                    $dlv_product_title,
                    $dlv_qty,
                    $item->audit_date,
                ];
            }
        }

        $column_name = [
            '#',
            '訂單編號',
            '訂單商品',
            '訂單日期',
            '訂單狀態',
            '出貨狀態',
            '訂單單價',
            '訂單數量',
            '訂單小計',
            '訂購人',
            '實際出貨商品',
            '實際出貨數量',
            '出貨日期',
        ];

        $export= new DeliveryProductListExport([
            $column_name,
            $data_arr,
        ]);

        return Excel::download($export, 'delivery_product_list.xlsx');
    }
}

