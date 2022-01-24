<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ShipmentGroup;
use App\Models\Temps;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

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

        $uniqueGroupId = array();
        $uniqueDataList = array();
        foreach ($dataList as $datum) {
            if (!in_array($datum->group_id_fk, $uniqueGroupId)) {
                $uniqueGroupId[] = $datum->group_id_fk;
                $group = $shipment->getEditShipmentData($datum->group_id_fk);
                $datum->group = $group;
                $uniqueDataList[] = $datum;
            }
        }
        return view('cms.settings.shipment.list', [
            'dataList'          => $dataList,
            'uniqueDataList'    => $uniqueDataList,
            'data_per_page'     => $data_per_page,
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
        $request->validate([
            'name' => ['required',
                       'string',
                       'unique:App\Models\ShipmentGroup'],
            'temps' => 'required|string',
            'method' => 'required|string',
            'is_above.*' => 'required|string',
            'note' => 'string|nullable',
            'min_price.*' => 'required|integer|min:0',
            'max_price.*' => 'required|integer|min:0',
            'dlv_fee.*' => 'required|integer|min:0',
            'dlv_cost.*' => 'nullable|integer|min:0',
            'at_most.*' => 'nullable|integer|min:0',
        ]);

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
    public function update(Request $request, Shipment $shipment, int $groupId, ShipmentGroup $shipmentGroup)
    {
        $ignoreId = $shipmentGroup->where('id', $groupId)
                                ->get()
                                ->first()
                                ->id;

        $request->validate([
            'name' => ['required',
                       'string',
                       Rule::unique('shipment_group')->ignore($ignoreId)],
            'temps' => 'required|string',
            'method' => 'required|string',
            'is_above.*' => 'required|string',
            'note' => 'string|nullable',
            'min_price.*' => 'required|integer|min:0',
            'max_price.*' => 'required|integer|min:0',
            'dlv_fee.*' => 'required|integer|min:0',
            'dlv_cost.*' => 'nullable|integer|min:0',
            'at_most.*' => 'nullable|integer|min:0',
        ]);

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
