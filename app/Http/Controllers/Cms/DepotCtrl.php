<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Addr;
use App\Models\Depot;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

use App\Models\DepotProduct;
use App\Models\Product;
use App\Models\ProductStyle;
use App\Models\SaleChannel;

use Illuminate\Support\Facades\DB;

class DepotCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $request->query();
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;
        $dataList =  Depot::paginate($data_per_page)->appends($query);

        return view('cms.settings.depot.list', [
            'dataList' => $dataList,
            'data_per_page' => $data_per_page,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $recentCity = $request->old('city_id');
        $regions = [];
        if ($recentCity) {
            $regions = Addr::getRegions($recentCity);
        }

        return view('cms.settings.depot.edit', [
            'method' => 'create',
            'formAction' => Route('cms.depot.create'),
            'citys' => Addr::getCitys(),
            'regions' => $regions
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
        $request->validate([
            'name' => 'required|string',
            'sender' => 'required|string',
            'can_pickup' => 'required|string',
            'can_tally' => 'required|string',
            'addr' => 'required|string',
            'city_id' => 'required|numeric',
            'region_id' => 'required|numeric',
            'tel' => 'required|numeric',
        ]);

        $v = $request->all();

        Depot::create([
            'name'     => $v['name'],
            'sender'      => $v['sender'],
            'can_pickup' => $v['can_pickup'],
            'can_tally' => $v['can_tally'],
            'addr'      => $v['addr'],
            'city_id'   => $v['city_id'],
            'region_id' => $v['region_id'],
            'tel'       => $v['tel'],
            'address' => Addr::fullAddr($v['region_id'], $v['addr']),
        ]);
        return redirect(Route('cms.depot.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Depot  $depot
     * @return \Illuminate\Http\Response
     */
    public function show(Depot $depot)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Depot  $depot
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, int $id)
    {
        $data = Depot::where('id', '=', $id)->first();

        if (!$data) {
            return abort(404);
        }
        $recentCity = $request->old('city_id');
        if ($recentCity) {
            $regions = Addr::getRegions($recentCity);
        } else {
            $regions = Addr::getRegions($data['city_id']);
        }

        return view('cms.settings.depot.edit', [
            'method' => 'edit',
            'formAction' => Route('cms.depot.edit', ['id' => $id]),
            'citys' => Addr::getCitys(),
            'regions' => $regions,
            'data' => $data,
            'id' => $id
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Depot  $depot
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'name' => 'required|string',
            'sender' => 'required|string',
            'can_pickup' => 'required|string',
            'can_tally' => 'required|string',
            'addr' => 'required|string',
            'city_id' => 'required|numeric',
            'region_id' => 'required|numeric',
            'tel' => 'required|numeric',
            'id' => "required|in:$id"
        ]);

        $d = $request->only(
            'name',
            'sender',
            'can_pickup',
            'can_tally',
            'addr',
            'city_id',
            'region_id',
            'tel',
            'phone'
        );
        $d['address'] = Addr::fullAddr($d['region_id'], $d['addr']);
        Depot::where('id', '=', $id)->update($d);
        return redirect(Route('cms.depot.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Depot  $depot
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        Depot::destroy($id);
        return redirect(Route('cms.depot.index'));
    }


    public function product_index(Request $request, int $id)
    {
        $depot = Depot::findOrFail($id);

        $query = $request->query();
        $page = getPageCount(Arr::get($query, 'data_per_page'));

        $keyword = Arr::get($query, 'keyword', '');
        $type = Arr::get($query, 'type', 'all');//c,p,all

        $products = DepotProduct::product_list($id, $keyword, $type)
            ->orderBy('product_id', 'ASC')
            ->orderBy('id', 'ASC')
            ->paginate($page);

        return view('cms.settings.depot.product_list', [
            'dataList' => $products,
            'depot' => $depot,
            'data_per_page' => $page,
        ]);
    }


    public function product_create(Request $request, int $id)
    {
        $depot = Depot::findOrFail($id);

        if($request->isMethod('post')){
            $request->validate([
                'selected' => 'required|array',
                'selected.*' => 'numeric',
                'product_style_id' => 'required|array',
                'product_style_id.*' => 'required|exists:prd_product_styles,id',
            ]);

            $data = [];

            foreach(request('selected') as $key => $value){
                $dp_count = DepotProduct::where([
                    'depot_id'=>$depot->id,
                    'product_style_id'=>$value,
                ])->whereNull('deleted_at')->get()->count();

                if($value != '0' && $value == request('product_style_id')[$key] && $dp_count == 0){
                    $product_style = ProductStyle::findOrFail($value);
                    $master_channel_product_style = SaleChannel::stylePriceList($value)->where('is_master', '=', 1)->first();

                    $data[] = [
                        'depot_id'=>$depot->id,
                        'product_id'=>$product_style->product_id,
                        'product_style_id'=>$product_style->id,
                        'ost_price'=>$master_channel_product_style ? $master_channel_product_style->price : 0,
                        'depot_price'=>0,
                        'updated_users_id'=>auth('user')->user()->id,
                        'created_at'=>date("Y-m-d H:i:s"),
                    ];
                }
            }

            if(count($data) > 0){
                DepotProduct::insert($data);
                wToast(__('Add finished.'));

            } else {
                wToast(__('無資料新增'));
            }

            return redirect(Route('cms.depot.product-index', [
                'id' => $id,
            ]));

        } else {
            $query = $request->query();
            $page = getPageCount(Arr::get($query, 'data_per_page'));

            $keyword = Arr::get($query, 'keyword', '');
            $type = Arr::get($query, 'type', 'all');//p,c,all

            $selected_product = DepotProduct::product_list($id)->pluck('id')->toArray();

            $products = Product::productStyleList(
                $keyword,
                $type,
            )
            ->whereNotIn('s.id', $selected_product)
            ->orderBy('product_id', 'ASC')
            ->orderBy('s.id', 'ASC')
            ->paginate($page);
            // ->get();

            return view('cms.settings.depot.product_select', [
                'dataList' => $products,
                'depot' => $depot,
                'method' => 'create',
                'form_action' => Route('cms.depot.product-create', ['id' => $depot->id]),
                'data_per_page' => $page,
                'breadcrumb_data' => $id,
            ]);
        }
    }


    public function product_edit(Request $request, int $id)
    {
        $depot = Depot::findOrFail($id);

        if($request->isMethod('post')){
            $request->validate([
                'selected' => 'required|array',
                'selected.*' => 'required|in:0,' . implode(',', ProductStyle::whereNull('deleted_at')->pluck('id')->toArray()),
                'select_id' => 'required|array',
                'select_id.*' => 'required|exists:prd_product_depot_select,id',
                'product_style_id' => 'required|array',
                'product_style_id.*' => 'required|exists:prd_product_styles,id',
                'depot_product_no' => 'required|array',
                'depot_product_no.*' => 'nullable|string',
                'depot_price' => 'required|array',
                'depot_price.*' => 'required|numeric|between:0,9999999999.99',
            ]);

            DB::beginTransaction();

            try {
                foreach(request('selected') as $key => $value){
                    $dp_count = DepotProduct::where([
                        'id'=>request('select_id')[$key],
                        'depot_id'=>$depot->id,
                        'product_style_id'=>$value,
                    ])->whereNull('deleted_at')->get()->count();

                    if($value != '0' && $value == request('product_style_id')[$key] && $dp_count == 1){
                        DepotProduct::where([
                            'id'=>request('select_id')[$key],
                            'depot_id'=>$depot->id,
                            'product_style_id'=>$value,
                        ])->whereNull('deleted_at')->update([
                            'depot_product_no'=>request('depot_product_no')[$key],
                            'depot_price'=>request('depot_price')[$key],
                            'updated_users_id'=>auth('user')->user()->id,
                            'updated_at'=>date("Y-m-d H:i:s"),
                        ]);
                    }
                }

                DB::commit();
                wToast(__('資料更新成功'));

            } catch (\Exception $e) {
                DB::rollback();
                wToast(__('無資料更新'));
            }

            return redirect(Route('cms.depot.product-index', [
                'id' => $id,
            ]));

        } else {
            $query = $request->query();
            $page = getPageCount(Arr::get($query, 'data_per_page'));

            $keyword = Arr::get($query, 'keyword', '');
            $type = Arr::get($query, 'type', 'all');//p,c,all

            $selected_product = DepotProduct::product_list($id, $keyword, $type)
            ->orderBy('product_id', 'ASC')
            ->orderBy('id', 'ASC')
            ->paginate($page);
            // ->get();

            return view('cms.settings.depot.product_select', [
                'dataList' => $selected_product,
                'depot' => $depot,
                'method' => 'edit',
                'form_action' => Route('cms.depot.product-edit', ['id' => $depot->id]),
                'data_per_page' => $page,
                'breadcrumb_data' => $id,
            ]);
        }
    }


    public function product_delete($id)
    {
        $dp = DepotProduct::findOrFail($id);
        $dp->delete();
        wToast('商品刪除成功');
        return redirect(Route('cms.depot.product-edit', [
            'id' => $dp->depot_id,
        ]));
    }
}
