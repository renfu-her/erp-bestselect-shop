<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Temps;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

class ShipmentCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Shipment $shipment)
    {
        $query = $request->query();
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;
        $shipList = $shipment->getShipmentList();
        $dataList = $shipList->paginate($data_per_page)->appends($query);

        $data = $dataList->items();
        $uniqueGroupId = array();
        foreach ($data as $datum) {
            $uniqueGroupId[] = $datum->group_id_fk;
        }
        $uniqueGroupId = array_unique($uniqueGroupId);
        $groupIdColorIndex = array();
        $index = 0;
        foreach ($uniqueGroupId as $group_id) {
            $groupIdColorIndex[$index] = $group_id;
            $index++;
        }

        return view('cms.settings.shipment.list', [
            'dataList'          => $dataList,
            'data_per_page'     => $data_per_page,
            'groupIdColorIndex' => $groupIdColorIndex
        ]);
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('cms.settings.shipment.edit', [
            'method'      => 'create',
            'formAction'  => Route('cms.shipment.create'),
            'shipTemps'   => Temps::all(),
            'shipMethods' => Shipment::all()->unique('method'),
        ]);
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Shipment $shipment)
    {
//        TODO validation in the backend
//        $request->validate([
//            'name' => 'required|string',
//            'temps' => 'required|string',
//            'method' => 'required|string',
//            'note' => 'string|nullable',
//            'min_price' => 'required|int',
//            'max_price' => 'required|int',
//            'dlv_fee' => 'required|int',
//            'dlv_cost' => 'required|int',
//            'at_most' => 'required|int',
//        ]);

        $dataField = $shipment->getDataFieldFromFormRequest($request);

        $shipment->storeShipRule(
            $dataField['ruleNumArray'],
            $dataField['name'],
            $dataField['temps'],
            $dataField['method'],
            $dataField['note']
        );

        return redirect(Route('cms.shipment.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Shipment  $shipment
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Shipment $shipment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Shipment  $shipment
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Shipment $shipment, Temps $temps, int $groupId)
    {
        $dataList = $shipment->getEditShipmentData($groupId);

        return view('cms.settings.shipment.edit', [
            'dataList'    => $dataList,
            'method'      => 'edit',
            'formAction'  => Route('cms.shipment.edit', $groupId),
            'shipName'    => $dataList[0]->name,
            'note'        => $dataList[0]->note,
            'shipTemps'   => Temps::all(),
            'shipMethods' => Shipment::all()->unique('method'),
        ]);
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Shipment  $shipment
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Shipment $shipment, int $groupId)
    {
        $dataField = $shipment->getDataFieldFromFormRequest($request);

        $shipment->updateShipRule(
            $groupId,
            $dataField['ruleNumArray'],
            $dataField['name'],
            $dataField['temps'],
            $dataField['method'],
            $dataField['note']
        );

        return redirect(Route('cms.shipment.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Shipment  $shipment
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shipment $shipment, int $groupId)
    {
        $shipment->deleteShipment($groupId);

        return redirect(Route('cms.shipment.index'));
    }
}
