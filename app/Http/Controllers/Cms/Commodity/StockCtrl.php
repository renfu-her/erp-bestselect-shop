<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\Depot;
use App\Models\Product;
use App\Models\PurchaseInbound;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class StockCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $request->query();
        $searchParam = [];
        $searchParam['keyword'] = Arr::get($query, 'keyword');
        $searchParam['type'] = Arr::get($query, 'type');
        $searchParam['consume'] = Arr::get($query, 'consume', 'all');
        $searchParam['user'] = Arr::get($query, 'user');
        $searchParam['supplier'] = Arr::get($query, 'supplier');
        $searchParam['stock'] = Arr::get($query, 'stock',[]);
        $searchParam['depot_id'] = Arr::get($query, 'depot_id',[]);
        $searchParam['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page', 10));
      //  dd($searchParam['stock']);
        $typeRadios = [
            'all' => '不限',
            'p' => '一般',
            'c' => '組合包',
        ];
        $consumes = [['all', '不限'], ['1', '耗材'], ['0', '商品']];

        $stockRadios = [
            'warning' => '低於安全庫存',
            'out_of_stock' => '無庫存',
        ];

        if (!in_array($searchParam['type'], array_keys($typeRadios))) {
            $searchParam['type'] = 'all';
        }
        $depot_id = $searchParam['depot_id'];
        //   dd( $searchParam['user']);

        $extPrdStyleList_send = PurchaseInbound::getExistInboundProductStyleList($depot_id);
        $products = Product::productStyleList($searchParam['keyword'], $searchParam['type'], $searchParam['stock'],
            ['supplier' => ['condition' => $searchParam['supplier'], 'show' => true],
                'user' => ['show' => true, 'condition' => $searchParam['user']],
                'consume' => $searchParam['consume'] == 'all' ? null : $searchParam['consume'],
            ])

            ->leftJoinSub($extPrdStyleList_send, 'inbound', function($join) use($depot_id) {
                //對應到入庫倉可入到進貨倉 相同的product_style_id
                $join->on('inbound.product_style_id', '=', 's.id');
                if (null != $depot_id && 0 < count($depot_id)) {
                    $join->whereIn('inbound.depot_id', $depot_id);
                }
            })
            ->leftJoin('depot', 'depot.id', '=', 'inbound.depot_id')
            ->addSelect(
                'inbound.product_style_id'
                , 'inbound.event'
                , 'inbound.depot_id'
                , 'depot.name as depot_name'
                , 'inbound.total_inbound_num'
                , 'inbound.total_sale_num'
                , 'inbound.total_csn_num'
                , 'inbound.total_consume_num'
                , 'inbound.total_in_stock_num'
                , 'inbound.total_in_stock_num_csn'
            )
        ;
        if (null != $depot_id && 0 < count($depot_id)) {
            $products->whereIn('inbound.depot_id', $depot_id);
        }
        $products = $products->paginate($searchParam['data_per_page'])
            ->appends($query);

        return view('cms.commodity.stock.list', [
            'dataList' => $products,
            'suppliers' => Supplier::select('name', 'id', 'vat_no')->get()->toArray(),
            'depotList' => Depot::all(),
            'users' => User::select('id', 'name')->get()->toArray(),
            'typeRadios' => $typeRadios,
            'stockRadios' => $stockRadios,
            'consumes' => $consumes,
            'searchParam' => $searchParam,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
