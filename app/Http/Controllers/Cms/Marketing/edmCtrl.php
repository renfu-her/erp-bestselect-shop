<?php

namespace App\Http\Controllers\Cms\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class edmCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Collection $collection)
    {
        //
        $mcode = $request->user()->getUserCustomer($request->user()->id)->sn;

        $query = $request->query();
        $name = Arr::get($query, 'name');
        $dataList = $collection::where('edm', 1);
        if ($name) {
            $dataList->where('name', 'like', "%$name%");
        }
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;

        return view('cms.marketing.edm.list', [
            'dataList' => $dataList->paginate(100)->appends($query),
            'data_per_page' => $data_per_page,
            'topList' => Collection::where('is_liquor', 0)->get(),
            'mcode' => $mcode,
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
     * 列印頁
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    function print(Request $request, $id, $type, $mcode) {
        //
        $query = $request->query();
        $paginate = Arr::get($query, 'paginate');
        $bg = Arr::get($query, 'bg', 'r');
        $qr = Arr::get($query, 'qr', '1');
        $btn = Arr::get($query, 'btn', '1');
        $x = Arr::get($query, 'x', 1);
        $x = intval($x) < 1 ? 1 : intval($x);

        $re = Collection::getProductsEdmVer($id, $type, $paginate ? true : false);
        if (!$re) {
            return abort(404);
        }
        $user = Customer::getUserByMcode($mcode);
        $name = '';
        if ($user) {
            $name = $user->name;
        }
       
        return view('cms.marketing.edm.print', [
            'type' => $type,
            'mcode' => $mcode,
            'name' => $name,
            'products' => $re["product"],
            'collection' => $re["collection"],
            'bg' => $bg,
            'qr' => $qr,
            'btn' => $btn,
            'x' => $x,
        ]);
    }
}
