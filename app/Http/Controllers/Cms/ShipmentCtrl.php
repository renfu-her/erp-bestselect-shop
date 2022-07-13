<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ShipmentCategory;
use App\Models\ShipmentGroup;
use App\Models\ShipmentMethod;
use App\Models\Supplier;
use App\Models\Temps;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $rules = [
            'shi_name' => 'nullable|string',
            'shi_method' => 'nullable||integer|min:1|exists:shi_method,id',
            'shi_temps' => 'nullable|integer|min:1|exists:shi_temps,id',
            'has_supplier' => 'nullable|integer|min:0|max:1',
            'supplier' => 'nullable|string',
        ];
        $this->validate($request, $rules);

        $shipData = $this->getShipData($request, $shipment);

        return view('cms.settings.shipment.list', [
            'categories' => ShipmentCategory::all(),
            'shi_method' => ShipmentMethod::all(),
            'shi_temps' => DB::table('shi_temps')->get(),
            'currentCategoryId' => $shipData['currentCategoryId'],
            'dataList'          => $shipData['dataList'],
            'uniqueDataList'    => $shipData['uniqueDataList'],
            'data_per_page'     => $shipData['data_per_page'],
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
        $supplierList = Supplier::getSupplierList()->get();
        return view('cms.settings.shipment.edit', [
            'method'      => 'create',
            'formAction'  => Route('cms.shipment.create'),
            'shipCategories' => ShipmentCategory::all(),
            'shipTemps'   => Temps::all(),
            'shipMethods' => ShipmentMethod::all()->unique('method'),
            'supplierList' => $supplierList,
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
            'category' => 'required|string',
            'temps' => 'required|string',
            'method' => 'required|string',
            'is_above.*' => 'required|string',
            'supplier_id' => 'nullable|integer',
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
            $dataField['category'],
            $dataField['name'],
            $dataField['temps'],
            $dataField['method'],
            $dataField['supplier_id'],
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
        $supplierList = Supplier::getSupplierList()->get();
        $supplierData = null;
        if (false == empty($dataList[0]->supplier_fk)) {
            $supplierData = Supplier::where('id', $dataList[0]->supplier_fk)->get()->first();
        }

        return view('cms.settings.shipment.edit', [
            'dataList'    => $dataList,
            'method'      => 'edit',
            'formAction'  => Route('cms.shipment.edit', $groupId),
            'shipCategories' => ShipmentCategory::all()->unique('category'),
            'shipName'    => $dataList[0]->name,
            'note'        => $dataList[0]->note,
            'shipTemps'   => Temps::all(),
            'shipMethods' => ShipmentMethod::all()->unique('method'),
            'supplierData' => $supplierData,
            'supplierList' => $supplierList,
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
            'category' => 'required|string',
            'name' => ['required',
                       'string',
                       Rule::unique('shi_group')->ignore($ignoreId)],
            'temps' => 'required|string',
            'method' => 'required|string',
            'is_above.*' => 'required|string',
            'supplier_id' => 'nullable|integer',
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
            $dataField['category'],
            $dataField['name'],
            $dataField['temps'],
            $dataField['method'],
            $dataField['supplier_id'],
            $dataField['note']
        );

        return redirect(Route('cms.shipment.index'));
    }

    public function categorize(Request $request, Shipment $shipment, int $categoryId)
    {
        $shipData = $this->getShipData($request, $shipment, $categoryId);

        return view('cms.settings.shipment.list', [
            'categories' => ShipmentCategory::all(),
            'currentCategoryId' => $shipData['currentCategoryId'],
            'dataList'          => $shipData['dataList'],
            'uniqueDataList'    => $shipData['uniqueDataList'],
            'data_per_page'     => $shipData['data_per_page'],
        ]);
    }

    public function getShipData(
        Request $request,
        Shipment $shipment,
        int $categoryId = 1
    ) {
        $query = $request->query();
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;
        $shipList = $shipment->getShipmentList($request, $categoryId);
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

        return [
            'currentCategoryId' => $categoryId,
            'data_per_page' => $data_per_page,
            'dataList' => $dataList,
            'uniqueDataList' => $uniqueDataList,
        ];
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
