<?php

namespace App\Http\Controllers\Cms\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Customer;
use App\Models\SaleChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;

class EdmCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Collection $collection)
    {
        $mcode = $request->user()->getUserCustomer($request->user()->id)->sn;
        $query = $request->query();
        $name = Arr::get($query, 'name');
        $dataList = $collection::dataList()->where('edm', 1);
        if ($name) {
            $dataList->where('name', 'like', "%$name%");
        }
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;

        $salechannels = SaleChannel::select(['id', 'title'])->get()->toArray();

        return view('cms.marketing.edm.list', [
            'dataList' => $dataList->paginate(100)->appends($query),
            'data_per_page' => $data_per_page,
            'topList' => Collection::where('is_liquor', 0)->get(),
            'mcode' => $mcode,
            'salechannels' => $salechannels,
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
    function print(Request $request, $id, $type, $mcode = '') {
        //
        $query = $request->query();
        $paginate = Arr::get($query, 'paginate');
        $bg = Arr::get($query, 'bg', 'r');
        $qr = Arr::get($query, 'qr', '1');
        $mc = Arr::get($query, 'mc', '1');
        $a4 = Arr::get($query, 'a4', '0');
        $ch = Arr::get($query, 'ch', '1');
        $btn = Arr::get($query, 'btn', '0');
        $x = Arr::get($query, 'x', 1);
        $x = intval($x) < 1 ? 1 : intval($x);

        $re = Collection::getProductsEdmVer($id, $type, $paginate ? true : false, $ch);
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
            'mc' => $mc,
            'a4' => $a4,
            'btn' => $btn,
            'x' => $x,
        ]);
    }

    public function download(Request $request, $filename = null)
    {
        if (!$filename) {
            return abort(404);
        }
        $path = storage_path() . '/app/edm/' . $filename;
        if (file_exists($path)) {
            return Response::download($path);
        } else {
            return abort(404);
        }
    }
}
