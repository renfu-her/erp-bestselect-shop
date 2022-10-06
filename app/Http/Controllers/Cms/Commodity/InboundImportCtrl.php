<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Consignment\AuditStatus;
use App\Enums\Delivery\Event;
use App\Enums\Globals\Status;
use App\Enums\Purchase\LogEventFeature;
use App\Exports\Stock\OldNewStockDiffExport;
use App\Helpers\IttmsUtils;
use App\Http\Controllers\Controller;
use App\Imports\PurchaseInbound\CompareOldNonStock;
use App\Imports\PurchaseInbound\InboundImport;
use App\Models\Consignment;
use App\Models\CsnOrder;
use App\Models\Depot;
use App\Models\PcsErrStock0917;
use App\Models\PcsInboundInventory;
use App\Models\ProductStyle;
use App\Models\Purchase;
use App\Models\PurchaseImportLog;
use App\Models\PurchaseInbound;
use App\Models\PurchaseItem;
use App\Models\PurchaseLog;
use App\Models\SubOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Excel;

class InboundImportCtrl extends Controller
{
    public function index(Request $request)
    {
        $depotList = Depot::all()->toArray();
        return view('cms.commodity.inbound_import.index', [
            'depotList' => $depotList,
        ]);
    }

    public function uploadExcel(Excel $excel, Request $request)
    {
        ini_set('memory_limit', '-1');
        $request->validate([
            'depot_id' => 'required|numeric',
            'file' => 'required|max:10000|mimes:xlsx,xls',
        ]);
        $errors = [];
        $errMsg = null;

        $query = $request->query();
        $depot_id = $request->input('depot_id');
        $path = $request->file('file')->store('excel');

        $depot = Depot::where('id', '=', $depot_id)->get()->first();
        if (false == isset($depot)) {
            $errMsg = '無此倉庫';
        }

        $inboundImport = new InboundImport;
        $excel->import($inboundImport, storage_path('app/' . $path));
        $purchase = $inboundImport->purchase;
        if (0 < count($purchase)) {
            foreach ($purchase as $key_pcs => $key_val) {
                $purchase[$key_pcs]['supplier_name'] = array_unique($purchase[$key_pcs]['supplier_name'], SORT_STRING);
            }
        }
        $data = $purchase;
        if (isset($data) && 0 < count($data) && false == isset($errMsg)) {
            //判斷是否有重複採購單號
            $purchase_sn = [];
            foreach ($data as $key_pcs => $val_pcs) {
                $purchase_sn[] = $val_pcs['purchase_sn'];
                if (1 < count($val_pcs['supplier_name'])) {
                    $errMsg = '採購單號:'. $val_pcs['purchase_sn']. ' '. '有多個廠商 請改為一個廠商';
                }
            }
            if (count($purchase_sn) != count(array_unique($purchase_sn))) {
                $errMsg = '請將相同採購單號的商品放在一起';
            }
            if(isset($errMsg)) {
                throw ValidationException::withMessages(['error_msg' => $errMsg]);
            }

            $curr_date = date('Y-m-d H:i:s');
            foreach ($data as $key_pcs => $val_pcs) {
                $curr_pcs = null;
                $errMsg = null;
                // 判斷是否有相同採購單
                //  有 則跳過
                //  無 則新增
                $purchase = Purchase::where('sn', '=', $val_pcs['purchase_sn'])->first();
                if (isset($purchase)) {
                    continue;
                } else {
                    $curr_pcs = $val_pcs;
                }
                // 判斷全部SKU是否都存在
                $checkSKU = PurchaseImportLog::checkSKU($val_pcs);
                if ($checkSKU['success'] != '1') {
                    $errMsg = $checkSKU['error_msg'];
                } else {
                    $val_pcs = $checkSKU['val_pcs'];
                    $curr_pcs = $val_pcs;
                }

                //判斷採購人員是否存在
                $checkUser = PurchaseImportLog::checkUser($val_pcs);
                $user = null;
                if ($checkUser['success'] != '1') {
                    $errMsg = $checkUser['error_msg'];
                } else {
                    $user = $checkUser['data'];
                    $val_pcs = $checkUser['val_pcs'];
                    $curr_pcs = $val_pcs;
                }

                // 判斷是否有此廠商
                $checkSupplier = PurchaseImportLog::checkSupplier($val_pcs);
                $supplier = null;
                if ($checkSupplier['success'] != '1') {
                    $errMsg = $checkSupplier['error_msg'];
                } else {
                    $supplier = $checkSupplier['data'];
                    $val_pcs = $checkSupplier['val_pcs'];
                    $curr_pcs = $val_pcs;
                }
                if (isset($errMsg) && isset($curr_pcs)) {
                    PurchaseImportLog::createData($curr_pcs, null, null, $errMsg, $request->user());
                    continue;
                }

                $msg = DB::transaction(function () use (
                    $request
                    , $curr_date
                    , $depot
                    , $val_pcs
                    , $user
                    , $supplier
                ) {
                    //建立採購單
                    $purchase = Purchase::createPurchase(
                        $val_pcs['purchase_sn'],
                        $supplier->id,
                        $supplier->name,
                        $supplier->nickname,
                        $supplier->vat_no,
                        $user->id,
                        $user->name,
                        date('Y-m-d H:i:s'),
                    );
                    if ($purchase['success'] != '1') {
                        DB::rollBack();
                        return ['success' => 0, 'error_msg' => $purchase['error_msg']];
                    }
                    //自動匯入 直接寫入應稅、審核狀態為核可
                    $updArr['has_tax'] = 1;
                    $updArr['audit_date'] = $curr_date;
                    $updArr['audit_user_id'] = $request->user()->id;
                    $updArr['audit_user_name'] = $request->user()->name;
                    $updArr['audit_status'] = AuditStatus::approved()->value;
                    Purchase::where('id', $purchase['id'])->update($updArr);

                    //建立採購商品
                    foreach ($val_pcs['data'] as $key_style => $val_style) {
                        $purchaseItem = PurchaseItem::createPurchase(
                            [
                                'purchase_id' => $purchase['id'],
                                'product_style_id' => $val_style['product_style_id'],
                                'title' => $val_style['product_title'],
                                'sku' => $val_style['sku'],
                                'price' => $val_style['remaining_qty'] * $val_style['unit_cost'],
                                'num' => $val_style['remaining_qty'],
                                'temp_id' => null,
                            ],
                            $request->user()->id,
                            $request->user()->name
                        );
                        if ($purchaseItem['success'] != '1') {
                            DB::rollBack();
                            return ['success' => 0, 'error_msg' => $purchaseItem['error_msg']];
                        }
                        //寫入採購倉品ID 以利入庫用
                        $val_pcs['data'][$key_style]['pcs_item_id'] = $purchaseItem['id'];

                    }
                    //建立入庫單
                    foreach ($val_pcs['data'] as $key_style => $val_style) {
                        $purchaseInbound = PurchaseInbound::createInbound(
                            Event::purchase()->value,
                            $purchase['id'],
                            $val_style['pcs_item_id'],
                            $val_style['product_style_id'],
                            $val_style['product_title'],
                            $val_style['sku'],
                            $val_style['unit_cost'],
                            $val_style['expiry_date'],
                            $val_style['inbound_date'],
                            $val_style['remaining_qty'],
                            $depot->id,
                            $depot->name,
                            $request->user()->id,
                            $request->user()->name
                        );
                        if ($purchaseInbound['success'] != '1') {
                            DB::rollBack();
                            return ['success' => 0, 'error_msg' => $purchaseInbound['error_msg']];
                        } else {
                            $inbound_sn = $purchaseInbound['sn'];
                            //紀錄新增LOG
                            PurchaseImportLog::createData($val_pcs, $val_style, $inbound_sn, null, $request->user());
                        }
                    }
                    return ['success' => 1, 'error_msg' => ""];
                });

                //判斷新增採購庫存時中斷 則紀錄LOG
                if ($msg['success'] != '1') {
                    $errMsg = $msg['error_msg'];
                    PurchaseImportLog::createData($curr_pcs, null, null, $errMsg, $request->user());
                    continue;
                }
            }
        }
        wToast('匯入成功！請前往匯入紀錄查看結果');
        return redirect()->back();
    }

    public function import_log(Request $request)
    {
        $all_status = [];
        foreach (Status::asArray() as $data) {
            $all_status[$data] = Status::getDescription($data);
        }

        $query = $request->query();
        $cond = [];
        $cond['sn'] = Arr::get($query, 'sn', null);
        $cond['purchase_sn'] = Arr::get($query, 'purchase_sn', null);
        $cond['title'] = Arr::get($query, 'title', null);
        $cond['status'] = Arr::get($query, 'status', 'all');

        $cond['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page', 100));

        $pcsImportLog = DB::table(app(PurchaseImportLog::class)->getTable(). ' as pcs_import_log');

        if (isset($cond['sn'])) {
            $pcsImportLog->where('pcs_import_log.sn', $cond['sn']);
        }
        if (isset($cond['purchase_sn'])) {
            $pcsImportLog->where('pcs_import_log.purchase_sn', $cond['purchase_sn']);
        }
        if (isset($cond['title'])) {
            $pcsImportLog->where('pcs_import_log.title', 'LIKE', "%{$cond['title']}%");
        }

        if (isset($cond['status']) && true == is_numeric($cond['status'])
            && true == Status::hasValue(intval($cond['status'], null))) {
            $pcsImportLog->where('pcs_import_log.status', '=', $cond['status']);
        }
        $pcsImportLog = $pcsImportLog
            ->orderByDesc('pcs_import_log.id')
            ->paginate($cond['data_per_page'])->appends($query);


        return view('cms.commodity.inbound_import.import_log', [
            'dataList' => $pcsImportLog,
            'searchParam' => $cond,
            'all_status' => $all_status,
            'data_per_page' => $cond['data_per_page']
        ]);
    }

    public function inbound_list(Request $request)
    {
        $query = $request->query();
        $cond = [];
        $cond['event'] = Arr::get($query, 'event', null);
        $cond['purchase_sn'] = Arr::get($query, 'purchase_sn', null);
        $cond['inbound_sn'] = Arr::get($query, 'inbound_sn', null);
        $cond['title'] = Arr::get($query, 'title', null);

        $cond['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page', 100));
        $cond['inventory_status'] = Arr::get($query, 'inventory_status', 'all');

        $param = ['event' => null, 'purchase_sn' => $cond['purchase_sn'], 'inbound_sn' => $cond['inbound_sn'], 'keyword' => $cond['title'], 'inventory_status' => $cond['inventory_status']];
        $inboundList_purchase = PurchaseInbound::getInboundListWithEventSn(app(Purchase::class)->getTable(), [Event::purchase()->value], $param);
        $inboundList_order = PurchaseInbound::getInboundListWithEventSn(app(SubOrders::class)->getTable(), [Event::order()->value, Event::ord_pickup()->value], $param);
        $inboundList_consignment = PurchaseInbound::getInboundListWithEventSn(app(Consignment::class)->getTable(), [Event::consignment()->value], $param);
        $inboundList_csn_order = PurchaseInbound::getInboundListWithEventSn(app(CsnOrder::class)->getTable(), [Event::csn_order()->value], $param);
        $inboundList_purchase->union($inboundList_order);
        $inboundList_purchase->union($inboundList_consignment);
        $inboundList_purchase->union($inboundList_csn_order);
        $inboundList_purchase = $inboundList_purchase->orderByDesc('created_at')
            ->paginate($cond['data_per_page'])->appends($query);

        return view('cms.commodity.inbound_import.inbound_list', [
            'dataList' => $inboundList_purchase,
            'searchParam' => $cond,
            'data_per_page' => $cond['data_per_page']
        ]);
    }

    public function inbound_edit(Request $request, $inbound_id)
    {
        $inbound = DB::table(app(PurchaseInbound::class)->getTable(). ' as inbound')
            ->where('inbound.id', '=', $inbound_id);
        $inboundGet = $inbound->first();

        $event_table = null;
        if (Event::purchase()->value == $inboundGet->event) {
            $event_table = app(Purchase::class)->getTable();
        } else if (Event::order()->value == $inboundGet->event || Event::ord_pickup()->value == $inboundGet->event) {
            $event_table = app(SubOrders::class)->getTable();
        } else if (Event::consignment()->value == $inboundGet->event) {
            $event_table = app(Consignment::class)->getTable();
        } else if (Event::csn_order()->value == $inboundGet->event) {
            $event_table = app(CsnOrder::class)->getTable();
        }
        $inbound = $inbound
            ->leftJoin(app(PcsInboundInventory::class)->getTable(). ' as inventory', 'inventory.inbound_id', '=', 'inbound.id')
            ->leftJoin($event_table. ' as event', function ($join) {
                $join->on('event.id', '=', 'inbound.event_id');
            })
            ->leftJoin(app(ProductStyle::class)->getTable(). ' as style', 'style.id', '=', 'inbound.product_style_id')
            ->select('event.sn as event_sn', 'style.sku', 'inbound.*'
                , DB::raw('DATE_FORMAT(inbound.expiry_date,"%Y-%m-%d") as expiry_date')
                , DB::raw('(inbound.inbound_num - inbound.sale_num - inbound.csn_num - inbound.consume_num - inbound.back_num - inbound.scrap_num) as remaining_qty') //庫存剩餘數量

                , 'inventory.status as inventory_status'
                , DB::raw('(case when "' . AuditStatus::unreviewed()->value . '" = inventory.status then "' . AuditStatus::getDescription(AuditStatus::unreviewed) . '"
					when "' . AuditStatus::approved()->value . '" = inventory.status then "' . AuditStatus::getDescription(AuditStatus::approved) . '"
					when "' . AuditStatus::veto()->value . '" = inventory.status then "' . AuditStatus::getDescription(AuditStatus::veto) . '"
                    else "' . AuditStatus::getDescription(AuditStatus::unreviewed) . '" end) as inventory_status_str')
                , 'inventory.create_user_id as inventory_create_user_id'
                , 'inventory.create_user_name as inventory_create_user_name'
                , 'inventory.created_at as inventory_created_at'
                , 'inventory.updated_at as inventory_updated_at'
            )
            ->first();

        return view('cms.commodity.inbound_import.inbound_edit', [
            'inboundData' => $inbound,
            'formAction' => Route('cms.inbound_import.inbound_edit_store', ['inboundId' => $inbound_id]),
            ])
            ->with('backUrl', old('backUrl', Session::get('backUrl', URL::previous())));
    }

    public function inbound_edit_store(Request $request, $inbound_id)
    {
        $request->validate([
            'id' => 'required|numeric',
            'update_num' => 'required|numeric',
            'expiry_date' => 'nullable|date_format:"Y-m-d"',
            'inventory_status' => 'required|numeric',
        ]);
        $update_num = $request->input('update_num', 0);
        $expiry_date = $request->input('expiry_date', null);
        $memo = $request->input('memo', null);
        $inventory_status = $request->input('inventory_status', AuditStatus::unreviewed()->value);

        if (!AuditStatus::hasValue($inventory_status)) {
            throw ValidationException::withMessages(['status_error' => '無此審核狀態']);
        }

        //判斷若有改到入庫單相關資料
        $inbound = PurchaseInbound::where('id', '=', $inbound_id);
        $inboundGet = $inbound->first();
        $inboundGet->inbound_num = $inboundGet->inbound_num + $update_num;
        $inboundGet->expiry_date = date('Y-m-d H:i:s', strtotime($expiry_date));
        $updIb = null;
        if ($inboundGet->isDirty()) {
            //若有改到入庫單相關資料 則需填寫memo
            $request->validate([
                'memo' => 'required|string',
            ]);
            $updIb = PurchaseInbound::updateInbound($inbound_id, $update_num, $expiry_date, $memo, $request->user()->id, $request->user()->name);
        }
        if ( (false == $inboundGet->isDirty() && null == $updIb) || '1' == $updIb['success']) {
            wToast('儲存成功');

            PcsInboundInventory::updateOrCreate(['inbound_id' => $inbound_id], [
                'status' => $inventory_status
                , 'create_user_id' => $request->user()->id
                , 'create_user_name' => $request->user()->name
            ]);

            return Redirect::away($request->get('backUrl'));
        }

        $errors['error_msg'] = $updIb['error_msg'];

        return redirect()->back()->withInput()->withErrors($errors);
    }

    public function inbound_log(Request $request)
    {
        $query = $request->query();
        $data_per_page = Arr::get($query, 'data_per_page', 100);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 100;

        $logEvent = [
            Event::purchase()->value
            , Event::order()->value
            , Event::ord_pickup()->value
            , Event::consignment()->value
            , Event::csn_order()->value
        ];
        $logPurchase_purchase = PurchaseLog::getStockDataWithEventSn(app(Purchase::class)->getTable(), [Event::purchase()->value], null, null, [LogEventFeature::inbound_update()->value]);
        $logPurchase_order = PurchaseLog::getStockDataWithEventSn(app(SubOrders::class)->getTable(), [Event::order()->value, Event::ord_pickup()->value], null, null, [LogEventFeature::inbound_update()->value]);
        $logPurchase_consignment = PurchaseLog::getStockDataWithEventSn(app(Consignment::class)->getTable(), [Event::consignment()->value], null, null, [LogEventFeature::inbound_update()->value]);
        $logPurchase_csn_order = PurchaseLog::getStockDataWithEventSn(app(CsnOrder::class)->getTable(), [Event::csn_order()->value], null, null, [LogEventFeature::inbound_update()->value]);

        $logPurchase_purchase->union($logPurchase_order);
        $logPurchase_purchase->union($logPurchase_consignment);
        $logPurchase_purchase->union($logPurchase_csn_order);
        $logPurchase = $logPurchase_purchase->orderByDesc('id');
        $logPurchase = $logPurchase->paginate($data_per_page)->appends($query);

        return view('cms.commodity.inbound_import.inbound_log', [
            'data_per_page' => $data_per_page,
            'purchaseLog' => $logPurchase,
        ]);
    }

    //找舊系統沒有庫存，新系統卻是有庫存的
    public function compare_old_to_diff_new_stock_page(Request $request)
    {
        return view('cms.commodity.inbound_import.compare_old_to_diff_new_stock', [
            'discription' => '找舊系統沒有庫存，新系統卻是有庫存的商品 ( 最後結果將紀錄在資料庫 )',
            'formAction' => Route('cms.inbound_import.compare_old_to_diff_new_stock', [], true),
        ]);
    }

    public function compare_old_to_diff_new_stock_todo(Excel $excel, Request $request)
    {
        ini_set('memory_limit', '-1');
        $request->validate([
            'file' => 'required|max:10000|mimes:xlsx,xls',
        ]);
        $path = $request->file('file')->store('excel');

        $inboundImport = new CompareOldNonStock;
        $excel->import($inboundImport, storage_path('app/' . $path));
        $prdStyle = $inboundImport->prdStyle;

        $oldNewStockDiffExport = new OldNewStockDiffExport($prdStyle);

        $pcsErrStock0917 = PcsErrStock0917::all();
        if (0 < count($pcsErrStock0917)) {
            dd('已匯入過，不可在匯入');
        } else {
            //寫入DB
            $curr_date = date('Y-m-d H:i:s');
            PcsErrStock0917::truncate();
            foreach ($oldNewStockDiffExport->array() as $key_ps => $val_ps) {
                PcsErrStock0917::create([
                    'no' => $val_ps['no']
                    , 'type_title' => $val_ps['type_title']
                    , 'product_title' => $val_ps['product_title']
                    , 'spec' => $val_ps['spec']
                    , 'sku' => $val_ps['sku']
                    , 'total_in_stock_num' => $val_ps['total_in_stock_num']
                    , 'user_name' => $val_ps['user_name']
                ]);
            }
        }
        return ($oldNewStockDiffExport)->download("stock-diff-" . date('Ymd His') . ".xlsx");
    }

    //找到採購單ID是在'2022/09/18'之前建立的
    private function getErr0918Pcs($param) {
        $pcsErrStock0917 = PcsErrStock0917::all();
        if (0 >= count($pcsErrStock0917)) {
            dd('DB無資料 請先執行 找舊沒庫存，新有的');
        }
        //找出擁有這些商品的採購單ID
        $query_pcs_items = DB::table(app(PurchaseItem::class)->getTable(). ' as item')
            ->leftJoin(app(PcsErrStock0917::class)->getTable(). ' as err_0917', 'err_0917.sku', '=', 'item.sku')
            ->select('item.purchase_id')
            ->groupBy('item.purchase_id');
        $pcs_id = array_map(
            function ($ar) {
                return $ar->purchase_id;
            }
            , $query_pcs_items->get()->toArray()
        );
        //找到採購單是在'2022/09/18'之前建立的
        $query_pcs = DB::table(app(Purchase::class)->getTable(). ' as pcs')
            ->select('pcs.id as purchase_id')
            ->whereIn('pcs.id', $pcs_id)
            ->where('pcs.created_at', '<', '2022/09/18')
            ->whereNull('pcs.deleted_at');
        if (isset($param['purchase_sn'])) {
            $query_pcs->where('pcs.sn', '=', $param['purchase_sn']);
        }
        $pcs_id_before_0918 = array_map(
            function ($ar) {
                return $ar->purchase_id;
            }
            , $query_pcs->get()->toArray()
        );
        $query_inbound_total = DB::table(app(PurchaseInbound::class)->getTable(). ' as inbound')
            ->whereIn('inbound.event_id', $pcs_id_before_0918)
            ->where('inbound.event', '=', Event::purchase()->value)
            ->select('inbound.event_id as purchase_id'
                , DB::raw('SUM(inbound.sale_num) as sale_num')
                , DB::raw('SUM(inbound.csn_num) as csn_num')
                , DB::raw('SUM(inbound.consume_num) as consume_num')
                , DB::raw('SUM(inbound.back_num) as back_num')
                , DB::raw('SUM(inbound.scrap_num) as scrap_num')
            )
            ->groupBy('inbound.event_id')
        ;
        if (isset($param['inbound_sn'])) {
            $query_inbound_total->where('inbound.sn', '=', $param['inbound_sn']);
        }
        return $query_inbound_total;
    }

    //找採購單 相關入庫單
    private function getErr0918InboundGroupByPcsID($purchaseIDs) {
        $concat_string_inbound = concatStr([
            'id' => 'inbound.id',
            'sn' => 'inbound.sn',
            'purchase_id' => 'inbound.event_id',
            'title' => 'inbound.title',
            'product_style_id' => 'inbound.product_style_id',
            'sku' => 'inbound.sku',
            'depot_name' => 'inbound.depot_name',
            'inbound_num' => 'inbound.inbound_num',
            'sale_num' => 'inbound.sale_num',
            'csn_num' => 'inbound.csn_num',
            'consume_num' => 'inbound.consume_num',
            'back_num' => 'inbound.back_num',
            'scrap_num' => 'inbound.scrap_num',
        ]);
        $query_pcs = DB::table(app(Purchase::class)->getTable(). ' as pcs')
            ->leftJoin(app(PurchaseInbound::class)->getTable(). ' as inbound', function ($join) {
                $join->on('inbound.event_id', '=', 'pcs.id');
                $join->where('inbound.event', '=', Event::purchase()->value);
            })
            ->select('pcs.id'
                , 'pcs.sn'
                , 'pcs.created_at'
                , DB::raw($concat_string_inbound. ' as inbound_data')
            )
            ->whereIn('pcs.id', $purchaseIDs)
            ->where('pcs.created_at', '<', '2022/09/18')
            ->whereNull('pcs.deleted_at')
            ->groupBy('pcs.id')
        ;
        return $query_pcs;
    }

    //若舊系統沒有庫存，而匯入的採購單有採購單且未出貨則將該筆採購單和入庫單刪掉
    public function import_no_delivery_page(Request $request)
    {
        $query = $request->query();
        $cond = [];
        $cond['purchase_sn'] = Arr::get($query, 'purchase_sn', null);
        $cond['inbound_sn'] = Arr::get($query, 'inbound_sn', null);
        $cond['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page', 100)) > 0 ? getPageCount(Arr::get($query, 'data_per_page', 100)) : 100;;
        $pcs_id_before_0918 = $this->getErr0918Pcs($cond);
        //找尚未出貨的採購單ID
        $query_inbound_non_sale = DB::query()->fromSub($pcs_id_before_0918, 'tb')
            ->where('tb.sale_num', '=', 0)
            ->where('tb.csn_num', '=', 0)
            ->where('tb.consume_num', '=', 0)
            ->where('tb.back_num', '=', 0)
            ->where('tb.scrap_num', '=', 0)
            ->get()->toArray();
        ;
        $pcs_ids = array_map(
            function ($ar) { return $ar->purchase_id; }, $query_inbound_non_sale
        );
        $datalist = $this->getErr0918InboundGroupByPcsID($pcs_ids, $cond)
            ->paginate($cond['data_per_page'])->appends($query);
        if (0 < count($datalist)) {
            foreach ($datalist as $key_pcs => $val_pcs) {
                $val_pcs->inbound_data = json_decode($val_pcs->inbound_data);
            }
        }
//        dd($datalist);

        return view('cms.commodity.inbound_import.import_has_delivery_list', [
            'showDelBtn' => true,
            'dataList' => $datalist,
            'searchParam' => $cond,
            'formAction' => Route('cms.inbound_import.import_no_delivery', []),
        ]);
    }

    //若舊系統沒有庫存，而匯入的採購單有採購單但已出貨則表示出是那些採購單
    public function import_has_delivery_page(Request $request)
    {
        $query = $request->query();
        $cond = [];
        $cond['purchase_sn'] = Arr::get($query, 'purchase_sn', null);
        $cond['inbound_sn'] = Arr::get($query, 'inbound_sn', null);
        $cond['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page', 100)) > 0 ? getPageCount(Arr::get($query, 'data_per_page', 100)) : 100;;

        $pcs_id_before_0918 = $this->getErr0918Pcs($cond);
        //找已出貨的採購單ID
        $query_inbound_already_sale = DB::query()->fromSub($pcs_id_before_0918, 'tb')
            ->where(function ($query) {
                $query->where('tb.sale_num', '>', 0)
                    ->orWhere('tb.csn_num', '>', 0)
                    ->orWhere('tb.consume_num', '>', 0)
                    ->orWhere('tb.back_num', '>', 0)
                    ->orWhere('tb.scrap_num', '>', 0);
            })
            ->get()->toArray();

        $pcs_ids = array_map(
            function ($ar) { return $ar->purchase_id; }, $query_inbound_already_sale
        );
        $datalist = $this->getErr0918InboundGroupByPcsID($pcs_ids, $cond)
            ->paginate($cond['data_per_page'])->appends($query);
        if (0 < count($datalist)) {
            foreach ($datalist as $key_pcs => $val_pcs) {
                $val_pcs->inbound_data = json_decode($val_pcs->inbound_data);
            }
        }
//        dd($datalist);

        return view('cms.commodity.inbound_import.import_has_delivery_list', [
            'showDelBtn' => false,
            'dataList' => $datalist,
            'searchParam' => $cond,
            'formAction' => Route('cms.inbound_import.import_has_delivery', []),
        ]);
    }

    public function del_purchase(Request $request, $purchase_id)
    {
        $errors = [];
        $result = Purchase::forceDel($purchase_id, $request->user()->id, $request->user()->name);
        if ($result['success'] == 0) {
            $errors[] = $result['error_msg'];
            wToast($result['error_msg'], ['type'=>'danger']);
        } else {
            wToast(__('Delete finished.'));
        }
        return redirect()->back()->withInput()->withErrors($errors);
    }

}
