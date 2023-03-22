<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Consignment\AuditStatus;
use App\Enums\Delivery\Event;
use App\Enums\DlvBack\DlvBackType;
use App\Enums\PcsScrap\PcsScrapType;
use App\Enums\Purchase\LogEventFeature;
use App\Helpers\IttmsDBB;
use App\Http\Controllers\Controller;
use App\Models\Depot;
use App\Models\GeneralLedger;
use App\Models\PcsScrapItem;
use App\Models\PcsScraps;
use App\Models\ProductStock;
use App\Models\ProductStyle;
use App\Models\PurchaseInbound;
use App\Models\PurchaseLog;
use App\Models\PayableDefault;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScrapCtrl extends Controller
{
    public function index(Request $request)
    {
        $searchParam = [];
        $query = $request->query();
        $searchParam['scrap_sn'] = Arr::get($query, 'scrap_sn', null);
        $searchParam['purchase_sn'] = Arr::get($query, 'purchase_sn', null);
        $searchParam['inbound_sn'] = Arr::get($query, 'inbound_sn', null);
        $searchParam['keyword'] = Arr::get($query, 'keyword', null);

        $searchParam['audit_status'] = Arr::get($query, 'audit_status', 'all');
        $searchParam['inbound_depot_id'] = Arr::get($query, 'inbound_depot_id', []);

        $data_per_page = Arr::get($query, 'data_per_page', 100);
        $searchParam['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page', 100));
        $dataList = PcsScrapItem::getProductItemList($searchParam)
            ->paginate($searchParam['data_per_page'])->appends($query);

        $depotList = Depot::all();
        return view('cms.commodity.scrap.list', [
            'dataList' => $dataList,
            'data_per_page' => $data_per_page,
            'searchParam' => $searchParam,
            'depotList' => $depotList,
        ]);
    }

    public function create(Request $request)
    {
        $rsp_arr = [];
        $rsp_arr['dlv_other_items'] = [];
        $rsp_arr['method'] = 'create';
        $rsp_arr['formAction'] = Route('cms.scrap.create');
        $total_grades = GeneralLedger::total_grade_list();
        $rsp_arr['total_grades'] = $total_grades;

        return view('cms.commodity.scrap.edit', $rsp_arr);
    }

    public function store(Request $request)
    {
        $this->validInputValue($request);

        $msg = IttmsDBB::transaction(function () use ($request) {
            $scrap_memo = $request->input('scrap_memo', null);

            $rePSCD = PcsScraps::createData(PcsScrapType::scrap()->key, $scrap_memo);
            if ($rePSCD['success'] == 0) {
                DB::rollBack();
                return $rePSCD;
            }
            $scrap_id = $rePSCD['id'];

            $reSS = $this->do_scrap_store($request, $scrap_id);
            if ($reSS['success'] == 0) {
                DB::rollBack();
                return $reSS;
            }

            return ['success' => 1, 'scrap_id' => $scrap_id];
        });
        if ($msg['success'] == 0) {
            throw ValidationException::withMessages(['item_error' => $msg['error_msg']]);
        } else {
            wToast('儲存成功');
            return redirect(Route('cms.scrap.edit', [
                'id' => $msg['scrap_id'],
            ], true));
        }
    }

    public function edit(Request $request, $id)
    {
        $scrapData = PcsScraps::find($id);
        $scrapItemData = PcsScrapItem::getItemWithInboundQtyList(['scrap_id' => $id])->get();
        $dlv_other_items = PcsScrapItem::where('scrap_id', $id)->where('type', '<>', 0)->whereNull('deleted_at')->get();
        if (!$scrapData) {
            return abort(404);
        }
        $rsp_arr = [];
        $rsp_arr['id'] = $id;
        $rsp_arr['scrapData'] = $scrapData;
        $rsp_arr['scrapItemData'] = $scrapItemData;
        $rsp_arr['dlv_other_items'] = $dlv_other_items;
        $rsp_arr['method'] = 'edit';
        $rsp_arr['formAction'] = Route('cms.scrap.edit', [
            'id' => $id,
        ]);
        $total_grades = GeneralLedger::total_grade_list();
        $rsp_arr['total_grades'] = $total_grades;
        $rsp_arr['breadcrumb_data'] = ['id' => $id, 'sn' => $scrapData->sn];

        return view('cms.commodity.scrap.edit', $rsp_arr);
    }

    private function validInputValue(Request $request)
    {
        $request->validate([
            'method' => 'nullable|string',
            'scrap_memo' => 'nullable|string',
            'item_id.*' => 'nullable|numeric',
            'inbound_id.*' => 'required|numeric',
            'product_style_id.*' => 'required|string',
            'product_title.*' => 'required|string',
            'sku.*' => 'required|string',
            'to_scrap_qty.*' => 'required|numeric',
            'memo.*' => 'nullable|string',

            'back_item_id.*' => 'nullable|numeric',
            'bgrade_id.*' => 'required_with:btype|numeric',
            'btitle.*' => 'required|string',
            'bprice.*' => 'required|numeric',
            'bqty.*' => 'required|numeric',
            'bmemo.*' => 'nullable|string',
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validInputValue($request);

        $scrapData = PcsScraps::find($id);
        $msg = IttmsDBB::transaction(function () use ($request, $id, $scrapData) {
            $scrap_memo = $request->input('scrap_memo', null);
            $audit_status = $request->input('audit_status', AuditStatus::unreviewed()->value);

            if (AuditStatus::approved()->value == $scrapData->audit_status && AuditStatus::approved()->value == $audit_status) {
                DB::rollBack();
                return ['success' => 0, 'error_msg' => '請先改為其他狀態才可編輯'];
            }
            //判斷audit_status變成核可，則須扣除數量
            else if(AuditStatus::approved()->value != $scrapData->audit_status && AuditStatus::approved()->value == $audit_status) {
                //inbound若為採購 需扣除可售數量；若庫存和可售數量小於扣除數量 則回傳錯誤
                $input_items = $request->only('item_id', 'inbound_id', 'product_style_id', 'product_title', 'sku', 'to_scrap_qty', 'memo');
                if (isset($input_items['item_id']) && 0 < count($input_items['item_id'])) {
                    //先將inbound_id寫入一陣列
                    $inbound_ids = [];
                    for($i = 0; $i < count($input_items['item_id']); $i++) {
                        $inbound_ids[] = $input_items['inbound_id'][$i];
                    }
                    //取得inbound_id的資料
                    $inbounds = DB::table(app(PurchaseInbound::class)->getTable() . ' as inbound')
                        ->leftJoin(app(ProductStyle::class)->getTable() . ' as style', 'inbound.product_style_id', '=', 'style.id')
                        ->select(
                            'inbound.id',
                            'inbound.event',
                            DB::raw('(inbound.inbound_num - inbound.sale_num - inbound.csn_num - inbound.consume_num - inbound.back_num - inbound.scrap_num) as remaining_qty'), //庫存剩餘數量
                            'style.in_stock'
                        )
                        ->whereIn('inbound.id', $inbound_ids)
                        ->get();
                    //檢查庫存和可售數量是否大於等於預計報廢的庫存
                    if(isset($inbounds) && 0 < count($inbounds)) {
                        foreach ($inbounds as $inbound) {
                            for($i = 0; $i < count($input_items['item_id']); $i++) {
                                //找到inbound_id 相同 判斷數量
                                if ($input_items['inbound_id'][$i] == $inbound->id) {
                                    if($inbound->remaining_qty < $input_items['to_scrap_qty'][$i]) {
                                        DB::rollBack();
                                        return ['success' => 0, 'error_msg' => '庫存不足'];
                                    }
                                    //採購的話 需判斷可售數量
                                    if($inbound->in_stock < $input_items['to_scrap_qty'][$i] && Event::purchase()->value == $inbound->event) {
                                        DB::rollBack();
                                        return ['success' => 0, 'error_msg' => '可售數量不足'];
                                    }
                                }
                            }
                        }
                    }

                    for($i = 0; $i < count($input_items['item_id']); $i++) {
                        $inbound = PurchaseInbound::findorfail($input_items['inbound_id'][$i]);
                        PurchaseInbound::willBeScrapped($input_items['inbound_id'][$i], $input_items['to_scrap_qty'][$i]);

                        $rePcsLSC = PurchaseLog::stockChange($inbound->event_id, $inbound->product_style_id, $inbound->event, $inbound->event_item_id
                            , LogEventFeature::scrapped()->value, $inbound->inbound_id, $input_items['to_scrap_qty'][$i] * -1, $scrapData->sn. ' 報廢'
                            , $inbound->product_title, $inbound->prd_type
                            , Auth::user()->id, Auth::user()->name, $scrapData->id);
                        if ($rePcsLSC['success'] == 0) {
                            DB::rollBack();
                            return $rePcsLSC;
                        }
                        if ($inbound->event == Event::purchase()->value) {
                            //寫入ProductStock
                            $rePSSC = ProductStock::stockChange($input_items['product_style_id'][$i], $input_items['to_scrap_qty'][$i] * -1, 'scrap', $scrapData->id, $scrapData->sn. ' 報廢', false, true);
                            if ($rePSSC['success'] == 0) {
                                DB::rollBack();
                                return $rePSSC;
                            }
                        }
                    }
                }
            }
            //判斷audit_status從核可變成其他狀態，則須加回數量
            else if (AuditStatus::approved()->value == $scrapData->audit_status && AuditStatus::approved()->value != $audit_status) {
                //inbound若為採購 需加回可售數量
                $scrap_items = PcsScrapItem::where('scrap_id', $scrapData->id)->where('type', '=', DlvBackType::product()->value)->whereNotNull('inbound_id')->whereNull('deleted_at')->get();
                if (0 < $scrap_items->count()) {
                    foreach ($scrap_items as $scrap_item) {
                        $inbound = PurchaseInbound::findorfail($scrap_item->inbound_id);
                        PurchaseInbound::willBeScrapped($scrap_item->inbound_id, $scrap_item->qty * -1);
                        $rePcsLSC = PurchaseLog::stockChange($inbound->event_id, $inbound->product_style_id, $inbound->event, $inbound->event_item_id
                            , LogEventFeature::scrap_del()->value, $inbound->inbound_id, $scrap_item->qty, $scrapData->sn. ' 報廢取消'
                            , $inbound->product_title, $inbound->prd_type
                            , Auth::user()->id, Auth::user()->name, $scrapData->id);
                        if ($rePcsLSC['success'] == 0) {
                            DB::rollBack();
                            return $rePcsLSC;
                        }
                        if ($inbound->event == Event::purchase()->value) {
                            //寫入ProductStock
                            $rePSSC = ProductStock::stockChange($scrap_item->product_style_id, $scrap_item->qty, 'scrap', $scrapData->id, $scrapData->sn. ' 報廢取消', false, true);
                            if ($rePSSC['success'] == 0) {
                                DB::rollBack();
                                return $rePSSC;
                            }
                        }
                    }
                }
            }

            $curr_date = date('Y-m-d H:i:s');
            PcsScraps::where('id', $id)->update([
                'memo' => $scrap_memo,
                'audit_user_id' => Auth::user()->id,
                'audit_user_name' => Auth::user()->name,
                'audit_date' => $curr_date,
                'audit_status' => $audit_status,
            ]);
            $reSS = $this->do_scrap_store($request, $scrapData->id);
            if ($reSS['success'] == 0) {
                DB::rollBack();
                return $reSS;
            }

            return ['success' => 1];
        });
        if ($msg['success'] == 0) {
            wToast($msg['error_msg'], ['type' => 'danger']);
            throw ValidationException::withMessages(['item_error' => $msg['error_msg']]);
        } else {
            wToast('儲存成功');
            return redirect(Route('cms.scrap.edit', [
                'id' => $id,
            ], true));
        }
    }

    private function do_scrap_store(Request $request, $scrap_id) {
        $msg = IttmsDBB::transaction(function () use ($request, $scrap_id) {
            $del_items = $request->input('del_item_id', '');
            $del_item_id_arr = explode(",", $del_items);
            if (isset($del_item_id_arr) && 0 < count($del_item_id_arr)) {
                PcsScrapItem::whereIn('id', $del_item_id_arr)->delete();
            }
            $input_items = $request->only('item_id', 'inbound_id', 'product_style_id', 'product_title', 'sku', 'to_scrap_qty', 'memo');
            if (isset($input_items['item_id']) && 0 < count($input_items['item_id'])) {
                $default_grade_id = PayableDefault::where('name', '=', 'product')->first()->default_grade_id;

                $curr_date = date('Y-m-d H:i:s');
                for($i = 0; $i < count($input_items['item_id']); $i++) {
                    if(true == isset($input_items['item_id'][$i])) {
                        //已有資料 做編輯
                        PcsScrapItem::where('id', '=', $input_items['item_id'][$i])->update([
                            'qty' => $input_items['to_scrap_qty'][$i],
                            'memo' => $input_items['memo'][$i],
                        ]);
                    } else {
                        $data = [];

                        if (0 == $input_items['to_scrap_qty'][$i]) {
                            //判斷數量零的就跳過
                            continue;
                        }
                        $inbound = PurchaseInbound::findorfail($input_items['inbound_id'][$i]);

                        if (!$inbound) {
                            return ['success' => 0, 'error_msg' => '找不到入庫單 '. $input_items['inbound_id'][$i]];
                        }
                        $addItem = [
                            'scrap_id' => $scrap_id,
                            'inbound_id' => $input_items['inbound_id'][$i],
                            'product_style_id' => $input_items['product_style_id'][$i],
                            'sku' => $input_items['sku'][$i],
                            'product_title' => $input_items['product_title'][$i],
                            'price' => $inbound->unit_cost,
                            'qty' => $input_items['to_scrap_qty'][$i],
                            'memo' => $input_items['memo'][$i],
                            'type' => DlvBackType::product()->value,
                            'grade_id' => $default_grade_id,
                            'created_at' => $curr_date,
                            'updated_at' => $curr_date,
                        ];
                        $data[] = $addItem;
                        PcsScrapItem::insert($data);
                    }
                }
            }

            $input_other_items = $request->only('back_item_id', 'bgrade_id', 'btitle', 'bprice', 'bqty', 'bmemo');

            $dArray = array_diff(PcsScrapItem::where('scrap_id', $scrap_id)->where('type', '<>', DlvBackType::product()->value)->pluck('id')->toArray()
                , array_intersect_key($input_other_items['back_item_id']?? [], $input_other_items['bgrade_id']?? [] )
            );
            if($dArray) PcsScrapItem::destroy($dArray);

            if (isset($input_other_items['bgrade_id']) && 0 < count($input_other_items['bgrade_id'])) {
                foreach(request('back_item_id') as $key => $value){
                    if(true == isset($input_other_items['bgrade_id'][$key])) {
                        if(true == isset($input_other_items['back_item_id'][$key])) {
                            PcsScrapItem::where('id', '=', $input_other_items['back_item_id'][$key])->update([
                                'grade_id' => $input_other_items['bgrade_id'][$key],
                                'product_title' => $input_other_items['btitle'][$key],
                                'price' => $input_other_items['bprice'][$key],
                                'qty' => $input_other_items['bqty'][$key],
                                'memo' => $input_other_items['bmemo'][$key],
                            ]);
                        } else {
                            if (false == isset($input_other_items['bgrade_id'][$key])) {
                                DB::rollBack();
                                return ['success' => 0, 'error_msg' => '未填入會計科目'];
                            }
                            PcsScrapItem::create([
                                'scrap_id' => $scrap_id,
                                'grade_id' => $input_other_items['bgrade_id'][$key],
                                'type' => DlvBackType::other()->value,
                                'product_title' => $input_other_items['btitle'][$key],
                                'price' => $input_other_items['bprice'][$key],
                                'qty' => $input_other_items['bqty'][$key],
                                'memo' => $input_other_items['bmemo'][$key],
                            ]);
                        }
                    }
                }
            }
            return ['success' => 1];
        });
        return $msg;
    }

    public function destroy(Request $request, $id)
    {
        $scrapData = PcsScraps::where('id', '=', $id)->first();
        if(null != $scrapData && AuditStatus::approved()->value == $scrapData->audit_status) {
            wToast('已核可 無法刪除', ['type' => 'danger']);
            return redirect()->back();
        }

        PcsScraps::where('id', $id)->delete();
        PcsScrapItem::where('scrap_id', $id)->delete();

        wToast('刪除成功');
        return redirect()->back();
    }

    public function printScrap(Request $request, $id)
    {

    }
}

