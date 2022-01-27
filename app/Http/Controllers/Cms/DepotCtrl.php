<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Addr;
use App\Models\Depot;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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
}
