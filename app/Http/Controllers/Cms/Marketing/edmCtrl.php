<?php

namespace App\Http\Controllers\Cms\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Collection;
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
    function print(Request $request, $id, $type) {
        //
        $re = Collection::getProductsEdmVer($id, $type, $request->user()->id);
        if(!$re){
            return abort(404);
        }

        return view('cms.marketing.edm.print', [
            'type' => $type,
            'mcode' => $re["mcode"],
            'products' => $re["product"],
            'collection' => $re["collection"]
        ]);
    }
}
