<?php

namespace App\Http\Controllers\Cms\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Fruit;
use App\Models\FruitCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ActFruitsCtrl extends Controller
{
    public $SaleStatus = [
        0 => '販售中',
        13 => '已售罄',
        14 => '今年產季已過',
        1 => '1月開放預購',
        2 => '2月開放預購',
        3 => '3月開放預購',
        4 => '4月開放預購',
        5 => '5月開放預購',
        6 => '6月開放預購',
        7 => '7月開放預購',
        8 => '8月開放預購',
        9 => '9月開放預購',
        10 => '10月開放預購',
        11 => '11月開放預購',
        12 => '12月開放預購',
    ];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $query = $request->query();
        $data_per_page = Arr::get($query, 'data_per_page', 20);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 20;

        return view('cms.frontend.act_fruits.list', [
            'data_per_page' => $data_per_page,
            'dataList' => Fruit::paginate($data_per_page)->appends($query),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('cms.frontend.act_fruits.edit', [
            'method' => 'create',
            'saleStatus' => $this->SaleStatus,
            'actionUrl' => route('cms.act-fruits.create'),
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
        $request->validate([
            'title' => 'required',
            'sub_title' => 'required',
            'place' => 'required',
            'season' => 'required',
            'pic' => 'required',
            'link' => 'required',
            'text' => 'required',
            'status' => 'required',
        ]);

        $d = $request->all();

        Fruit::create([
            'title' => $d['title'],
            'sub_title' => $d['sub_title'],
            'place' => $d['place'],
            'season' => $d['season'],
            'pic' => $d['pic'],
            'link' => $d['link'],
            'text' => $d['text'],
            'status' => $d['status'],
        ]);

        return redirect(route('cms.act-fruits.index'));

    }

    /**
     * 水果分類設定
     *
     * @return \Illuminate\Http\Response
     */
    public function season()
    {

        $collectionFruits = [];
        foreach (FruitCollection::getFruitList() as $value) {
            $collectionFruits[$value->collection_id] = $value->fruits;
        }
        return view('cms.frontend.act_fruits.season', [
            'method' => 'create',
            'collection' => FruitCollection::get(),
            'fruits' => Fruit::get(),
            'collectionFruits' => $collectionFruits,
        ]);
    }

    public function seasonUpdate(Request $request)
    {
        //  dd($_POST);
        // FruitCollection::get()
        $request->validate([
            'tab_id' => 'array|required',
        ]);

        $d = $request->all();
        $f = [];
        foreach ($d['tab_id'] as $key => $value) {
            $fruitKey = 'fruit_' . ($key + 1);
            if (isset($d[$fruitKey])) {
                $f = array_merge($f, array_map(function ($n, $idx) use ($value) {
                    //   dd($idx, $value);
                    return [
                        'collection_id' => $value,
                        'sort' => $idx * 10,
                        'fruit_id' => $n,
                    ];
                }, $d[$fruitKey], array_keys($d[$fruitKey])));

            }
        }

        DB::table('fru_collection_fruit')->truncate();
        DB::table('fru_collection_fruit')->insert($f);

        return redirect(route('cms.act-fruits.season'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        return view('cms.frontend.act_fruits.edit', [
            'method' => 'edit',
            'saleStatus' => $this->SaleStatus,
            'data' => Fruit::where('id', $id)->get()->first(),
            'actionUrl' => route('cms.act-fruits.edit', ['id' => $id]),
        ]);
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
        $request->validate([
            'title' => 'required',
            'sub_title' => 'required',
            'place' => 'required',
            'season' => 'required',
            'pic' => 'required',
            'link' => 'required',
            'text' => 'required',
            'status' => 'required',
        ]);

        $d = $request->all();

        Fruit::where('id', $id)->update([
            'title' => $d['title'],
            'sub_title' => $d['sub_title'],
            'place' => $d['place'],
            'season' => $d['season'],
            'pic' => $d['pic'],
            'link' => $d['link'],
            'text' => $d['text'],
            'status' => $d['status'],
        ]);

        return redirect(route('cms.act-fruits.index'));
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

        Fruit::where('id', $id)->delete();
        return redirect(route('cms.act-fruits.index'));

    }

    public function api()
    {
        return response()->json(['status' => '0', 'data' => FruitCollection::getFruitListForApi()]);
    }
}
