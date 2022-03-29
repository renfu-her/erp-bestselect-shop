<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\Event;
use App\Http\Controllers\Controller;
use App\Models\Consignment;
use App\Models\ConsignmentItem;
use App\Models\Delivery;
use App\Models\Depot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsignmentCtrl extends Controller
{

    public function index(Request $request)
    {
        //return view('cms.commodity.consignment.list', []);
        return redirect(Route('cms.consignment.create'));
    }

    public function create(Request $request)
    {
        return view('cms.commodity.consignment.edit', [
            'method' => 'create',
            'depotList' => Depot::all(),
            'formAction' => Route('cms.consignment.create'),
        ]);
    }


    public function store(Request $request)
    {
        $query = $request->query();
        $this->validInputValue($request);

        $csnReq = $request->only('send_depot_id', 'receive_depot_id', 'scheduled_date');
        $csnItemReq = $request->only('product_style_id', 'name', 'sku', 'num', 'price', 'memo');
//        $purchasePayReq = $request->only('logistics_price', 'logistics_memo', 'invoice_num', 'invoice_date');

        $send_depot = Depot::where('id', $csnReq['send_depot_id'])->get()->first();
        $receive_depot = Depot::where('id', $csnReq['receive_depot_id'])->get()->first();

        $reCsn = Consignment::createData($send_depot->id, $send_depot->name, $receive_depot->id, $receive_depot->name
            , $request->user()->id, $request->user()->name
            , $csnReq['scheduled_date']);

        $consignmentID = null;
        if (isset($reCsn['id'])) {
            $consignmentID = $reCsn['id'];
        }

        $result = null;
        $result = DB::transaction(function () use ($csnItemReq, $request, $consignmentID
        ) {
            if (isset($csnItemReq['product_style_id']) && isset($consignmentID)) {
                foreach ($csnItemReq['product_style_id'] as $key => $val) {
                    $reCsnIC = ConsignmentItem::createData(
                        [
                            'consignment_id' => $consignmentID,
                            'product_style_id' => $val,
                            'title' => $csnItemReq['name'][$key],
                            'sku' => $csnItemReq['sku'][$key],
                            'price' => $csnItemReq['price'][$key],
                            'num' => $csnItemReq['num'][$key],
                            'temp_id' => $csnItemReq['temp_id'][$key] ?? null,
                            'memo' => $csnItemReq['memo'][$key],
                        ],
                        $request->user()->id, $request->user()->name
                    );
                    if ($reCsnIC['success'] == 0) {
                        DB::rollBack();
                        return $reCsnIC;
                    }
                }
            }
            return ['success' => 1, 'error_msg' => ""];
        });
        $csn = Consignment::where('id', $consignmentID)->get()->first();
        $reDelivery = Delivery::createData(
            Event::consignment()->value
            , $consignmentID
            , $csn->sn
        );
        if ($reDelivery['success'] == 0) {
            return $reDelivery;
        }

        if ($result['success'] == 0) {
            wToast($result['error_msg']);
        } else {
            wToast(__('Add finished.'));
        }

        return redirect(Route('cms.consignment.edit', [
            'id' => $consignmentID,
            'query' => $query
        ]));
    }

    //驗證資料
    private function validInputValue(Request $request)
    {
        $request->validate([
            'send_depot_id' => 'required|numeric',
            'receive_depot_id' => 'required|numeric',
            'scheduled_date' => 'required|string',
            'product_style_id.*' => 'required|numeric',
            'name.*' => 'required|string',
            'sku.*' => 'required|string',
            'price.*' => 'required|numeric',
            'num.*' => 'required|numeric',
        ]);
    }

    public function edit(Request $request, $id)
    {
        $query = $request->query();
        $consignmentData  = Consignment::getData($id)->get()->first();
        $consignmentItemData = ConsignmentItem::where('consignment_id', $id)->get()->toArray();

        if (!$consignmentData) {
            return abort(404);
        }

        return view('cms.commodity.consignment.edit', [
            'id' => $id,
            'query' => $query,
            'consignmentData' => $consignmentData,
            'method' => 'edit',
            'formAction' => Route('cms.consignment.edit', ['id' => $id]),
            'breadcrumb_data' => ['id' => $id, 'sn' => $consignmentData->consignment_sn],
        ]);
    }
}

