<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\OrderCart;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class OrderCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $query = $request->query();
        $page = Arr::get($query, 'data_per_page', 10);

        return view('cms.commodity.order.list', [
            'dataList' => [],
            'data_per_page' => $page]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // dd(get_class($request->user()));
        // OrderCart::productAdd(1, get_class($request->user()), 1, 1, 1, 1, 1);
       // $items = OrderCart::productList($request->user()->id, get_class($request->user()))->get()->toArray();

        $customer_id = $request->user()->customer_id;

      
          //  $customer_id = $items[0]->customer_id;
        

        return view('cms.commodity.order.edit', [
          //  'items' => $items,
            'customer_id' => $customer_id,
            'customers' => Customer::get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the data for order detail.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function detail($id)
    {
        $sn = '21111801'; // 等有值改
        return view('cms.commodity.order.detail', [
            'sn' => $sn,
            'breadcrumb_data' => $sn]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
