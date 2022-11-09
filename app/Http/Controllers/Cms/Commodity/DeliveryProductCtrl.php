<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DeliveryProductCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();

        $cond['search_supplier'] = Arr::get($query, 'search_supplier', []);
        $cond['keyword'] = Arr::get($query, 'keyword');
        $cond['delivery_sdate'] = Arr::get($query, 'delivery_sdate', null);
        $cond['delivery_edate'] = Arr::get($query, 'delivery_edate', null);
        $cond['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page'));

        $data_list = Delivery::getListByProduct($cond)->paginate($cond['data_per_page'])->appends($query);

        return view('cms.commodity.delivery.product_list', [
            'dataList' => $data_list,
            'searchParam' => $cond,
            'data_per_page' => $cond['data_per_page'],
            'suppliers' => Supplier::select('name', 'id', 'vat_no')->get()->toArray(),
        ]);

    }
}

