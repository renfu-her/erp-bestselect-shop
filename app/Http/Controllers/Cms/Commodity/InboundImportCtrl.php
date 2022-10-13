<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Consignment\AuditStatus;
use App\Enums\Delivery\Event;
use App\Enums\Globals\Status;
use App\Enums\Purchase\LogEventFeature;
use App\Http\Controllers\Controller;
use App\Imports\PurchaseInbound\InboundImport;
use App\Models\Consignment;
use App\Models\CsnOrder;
use App\Models\Depot;
use App\Models\PcsInboundInventory;
use App\Models\ProductStyle;
use App\Models\Purchase;
use App\Models\PurchaseImportLog;
use App\Models\PurchaseInbound;
use App\Models\PurchaseItem;
use App\Models\PurchaseLog;
use App\Models\SubOrders;
use App\Models\User;
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
        $cond['inbound_depot_id'] = Arr::get($query, 'inbound_depot_id', []);
        $cond['inbound_user_id'] = Arr::get($query, 'inbound_user_id', null);
        $cond['inbound_sdate'] = Arr::get($query, 'inbound_sdate', null);
        $cond['inbound_edate'] = Arr::get($query, 'inbound_edate', null);
        $cond['expire_day'] = Arr::get($query, 'expire_day', '');
        $cond['prd_user_id'] = Arr::get($query, 'prd_user_id', []);
        if (count($cond['prd_user_id']) == 0) {
            $condUser = true;
        } else {
            $condUser = $cond['prd_user_id'];
        }
        $cond['has_remain_qty'] = Arr::get($query, 'has_remain_qty', 0);

        $param = ['event' => null, 'purchase_sn' => $cond['purchase_sn'], 'inbound_sn' => $cond['inbound_sn'], 'keyword' => $cond['title']
            , 'inventory_status' => $cond['inventory_status']
            , 'inbound_depot_id' => $cond['inbound_depot_id']
            , 'inbound_user_id' => $cond['inbound_user_id']
            , 'expire_day' => $cond['expire_day']
            , 'prd_user_id' => $condUser
            , 'inbound_sdate' => $cond['inbound_sdate'], 'inbound_edate' => $cond['inbound_edate']
            , 'has_remain_qty' => $cond['has_remain_qty'] ?? 0
        ];
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
            'dataList' => $inboundList_purchase
            , 'userList' => User::all()
            , 'depotList' => Depot::all()
            , 'searchParam' => $cond
            , 'data_per_page' => $cond['data_per_page']
        ]);
    }

    public function inbound_edit(Request $request, $inbound_id)
    {
        $inbound = DB::table(app(PurchaseInbound::class)->getTable(). ' as inbound')
            ->where('inbound.id', '=', $inbound_id)
            ->whereNull('inbound.deleted_at');
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

        $depot_id = null;
        $style_id = null;
        $logFeature = [LogEventFeature::inbound_update()->value];
        $cond = [];
        $log_purchase = PurchaseLog::getStockDataAndEventSn(app(Purchase::class)->getTable(), [Event::purchase()->value], $depot_id, $style_id, $logFeature, $cond);
        $log_order = PurchaseLog::getStockDataAndEventSn(app(SubOrders::class)->getTable(), [Event::order()->value, Event::ord_pickup()->value], $depot_id, $style_id, $logFeature, $cond);
        $log_consignment = PurchaseLog::getStockDataAndEventSn(app(Consignment::class)->getTable(), [Event::consignment()->value], $depot_id, $style_id, $logFeature, $cond);
        $log_csn_order = PurchaseLog::getStockDataAndEventSn(app(CsnOrder::class)->getTable(), [Event::csn_order()->value], $depot_id, $style_id, $logFeature, $cond);

        $log_purchase->union($log_order);
        $log_purchase->union($log_consignment);
        $log_purchase->union($log_csn_order);

        $log_purchase = $log_purchase->orderByDesc('id');
        $log_purchase = $log_purchase->paginate($data_per_page)->appends($query);

        return view('cms.commodity.inbound_import.inbound_log', [
            'data_per_page' => $data_per_page,
            'purchaseLog' => $log_purchase,
        ]);
    }
}
