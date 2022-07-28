<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Consignment\AuditStatus;
use App\Enums\Delivery\Event;
use App\Enums\Globals\Status;
use App\Http\Controllers\Controller;
use App\Imports\PurchaseInbound\InboundImport;
use App\Models\Depot;
use App\Models\Purchase;
use App\Models\PurchaseImportLog;
use App\Models\PurchaseInbound;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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
        $data = $inboundImport->data;
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
            $purchaseImportLog = null;
            $curr_pcs = null;
            foreach ($data as $key_pcs => $val_pcs) {
                $errMsg = null;
                // 判斷是否有相同採購單
                //  與目前同 狀態成功 則跳過
                //  與目前同 狀態失敗 則取得該筆
                //  無 則新增
                $purchaseImportLog = PurchaseImportLog::where('purchase_sn', '=', $val_pcs['purchase_sn'])->first();
                if (isset($purchaseImportLog)) {
                    if (Status::success()->value == $purchaseImportLog->status) {
                        $curr_pcs = null;
                        continue;
                    } else {
                        $curr_pcs = $val_pcs;
                    }
                } else {
                    $curr_pcs = $val_pcs;
                }
                // 判斷全部SKU是否都存在
                $checkSKU = PurchaseImportLog::checkSKU($val_pcs);
                if ($checkSKU['success'] != '1') {
                    $errMsg = $checkSKU['error_msg'];
                    break;
                } else {
                    $val_pcs = $checkSKU['val_pcs'];
                    $curr_pcs = $val_pcs;
                }

                //判斷採購人員是否存在
                $checkUser = PurchaseImportLog::checkUser($val_pcs);
                $user = null;
                if ($checkUser['success'] != '1') {
                    $errMsg = $checkUser['error_msg'];
                    break;
                } else {
                    $user = $checkUser['data'];
                    $val_pcs = $checkSKU['val_pcs'];
                    $curr_pcs = $val_pcs;
                }

                // 判斷是否有此廠商
                $checkSupplier = PurchaseImportLog::checkSupplier($val_pcs);
                $supplier = null;
                if ($checkSupplier['success'] != '1') {
                    $errMsg = $checkSupplier['error_msg'];
                    break;
                } else {
                    $supplier = $checkSupplier['data'];
                    $val_pcs = $checkSKU['val_pcs'];
                    $curr_pcs = $val_pcs;
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
                            PurchaseImportLog::createData($val_pcs, $val_style, $inbound_sn, null, $request->user());
                        }
                    }
                    return ['success' => 1, 'error_msg' => ""];
                });

                if ($msg['success'] != '1') {
                    $errMsg = $msg['error_msg'];
                    break;
                }
            }
        }
        if (isset($errMsg) && isset($curr_pcs)) {
            PurchaseImportLog::createData($curr_pcs, null, null, $errMsg, $request->user());
            $errors['error_msg'] = '採購單號:'. $curr_pcs['purchase_sn']. ' '. $errMsg;
            return redirect()->back()->withInput()->withErrors($errors);
        }
        wToast('匯入成功');
        return redirect()->back();
    }

    public function import_log(Request $request)
    {
        $query = $request->query();
        $cond = [];
        $cond['sn'] = Arr::get($query, 'sn', null);
        $cond['purchase_sn'] = Arr::get($query, 'purchase_sn', null);
        $cond['title'] = Arr::get($query, 'title', null);

        $cond['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page', 10));

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

        $pcsImportLog = $pcsImportLog->paginate($cond['data_per_page'])->appends($query);


        return view('cms.commodity.inbound_import.import_log', [
            'dataList' => $pcsImportLog,
            'searchParam' => $cond,
            'data_per_page' => $cond['data_per_page']
        ]);
    }
}
