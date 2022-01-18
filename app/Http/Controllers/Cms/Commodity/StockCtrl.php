<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\Product;
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
        $searchParam['user'] = Arr::get($query, 'user');
        $searchParam['supplier'] = Arr::get($query, 'supplier');
        $searchParam['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page', 10));

        $typeRadios = [
            'all' => '不限',
            'p' => '一般',
            'c' => '組合包',
        ];
        $stockRadios = [
            'safe' => '達安全庫存',
            'out_of_stock' => '無庫存',
        ];

        if (!in_array($searchParam['type'], array_keys($typeRadios))) {
            $searchParam['type'] = 'all';
        }
        //   dd( $searchParam['user']);
        $products = Product::productStyleList($searchParam['keyword'], $searchParam['type'],
            ['supplier' => ['condition' => $searchParam['supplier'], 'show' => true],
                'user' => ['show' => true, 'condition' => $searchParam['user']]])
            ->paginate(1)
            ->appends($query);

        return view('cms.commodity.stock.list', [
            'data_per_page' => 10,
            'dataList' => $products,
            'suppliers' => Supplier::select('name', 'id', 'vat_no')->get()->toArray(),
            'users' => User::select('id', 'name')->get()->toArray(),
            'typeRadios' => $typeRadios,
            'stockRadios' => $stockRadios,
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
