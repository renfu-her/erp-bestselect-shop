<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\Event;
use App\Enums\Purchase\LogEventFeature;
use App\Enums\StockEvent;
use App\Exports\Stock\OldNewStockDiffExport;
use App\Exports\Stock\OldNewStockDiffOnlyExport;
use App\Http\Controllers\Controller;
use App\Imports\PurchaseInbound\CompareOldNonStock;
use App\Models\PcsErrStock0917;
use App\Models\Purchase;
use App\Models\PurchaseInbound;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel;

class InboundFix0917ImportCtrl extends Controller
{
    //找舊系統沒有庫存，新系統卻是有庫存的
    public function index(Request $request)
    {
        $pcsErrStock0917 = PcsErrStock0917::find(1);
        $can_upload = true;
        if (isset($pcsErrStock0917)) {
            $can_upload = false;
        }
        return view('cms.commodity.fix.inbound_fix0917_import.compare_old_to_diff_new_stock', [
            'can_upload' => $can_upload,
            'discription' => '找舊系統沒有庫存，新系統卻是有庫存的商品 ( 最後結果將紀錄在資料庫 )',
            'formAction' => Route('cms.inbound_fix0917_import.compare_old_to_diff_new_stock', [], true),
        ]);
    }

    public function compare_old_to_diff_new_stock(Excel $excel, Request $request)
    {
        $pcsErrStock0917 = PcsErrStock0917::find(1);
        if (isset($pcsErrStock0917)) {
            //已匯入過，不再匯入
            return (new OldNewStockDiffOnlyExport())->download("stock-diff-" . date('Ymd His', strtotime($pcsErrStock0917->created_at)) . ".xlsx");
        } else {
            ini_set('memory_limit', '-1');
            $request->validate([
                'file' => 'required|max:10000|mimes:xlsx,xls',
            ]);
            $path = $request->file('file')->store('excel');

            $inboundImport = new CompareOldNonStock;
            $excel->import($inboundImport, storage_path('app/' . $path));
            $prdStyle = $inboundImport->prdStyle;

            $oldNewStockDiffExport = new OldNewStockDiffExport($prdStyle);

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
            return ($oldNewStockDiffExport)->download("stock-diff-" . date('Ymd His') . ".xlsx");
        }
    }

    //找到採購單ID是在'2022/09/18'之前建立的
    private function getErr0918Pcs($is_delivery, $param) {
        $pcsErrStock0917 = PcsErrStock0917::all();
        if (0 >= count($pcsErrStock0917)) {
            dd('DB無資料 請先上傳檔案 找舊系統沒有庫存，新系統卻是有庫存的商品');
        }
        //找出擁有這些商品的採購單ID
        $query_pcs_items = self::getErr0918DiffPcsInbound()
            ->select('item.purchase_id')
            ->where(function ($query) use($is_delivery) {
                if ($is_delivery) {
                    //找有出貨的
                    $query->where('inbound.sale_num', '>', 0)
                        ->orWhere('inbound.csn_num', '>', 0)
                        ->orWhere('inbound.consume_num', '>', 0)
                        ->orWhere('inbound.back_num', '>', 0)
                        ->orWhere('inbound.scrap_num', '>', 0);
                } else {
                    //找還沒出貨的
                    $query->where('inbound.sale_num', '=', 0)
                        ->where('inbound.csn_num', '=', 0)
                        ->where('inbound.consume_num', '=', 0)
                        ->where('inbound.back_num', '=', 0)
                        ->where('inbound.scrap_num', '=', 0);
                }
            });
        $query_pcs_items = $query_pcs_items->groupBy('item.purchase_id');

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
        $pcs_id_before_0918 = array_map(
            function ($ar) {
                return $ar->purchase_id;
            }
            , $query_pcs->get()->toArray()
        );

        return $pcs_id_before_0918;
    }

    private function getErr0918DiffPcsInbound() {
        $query_pcs_ib = DB::table(app(PurchaseItem::class)->getTable(). ' as item')
            ->leftJoin(app(PurchaseInbound::class)->getTable(). ' as inbound', function($join){
                $join->on('inbound.event_id', '=', 'item.purchase_id');
                $join->on('inbound.event_item_id', '=', 'item.id');
                $join->where('inbound.event', '=',Event::purchase()->value);
            })
            ->leftJoin(app(PcsErrStock0917::class)->getTable(). ' as err_0917', 'err_0917.sku', '=', 'inbound.sku')
            ->whereNotNull('err_0917.id')
            ->whereNull('item.deleted_at')
            ->whereNull('inbound.deleted_at')
            ->where('inbound.created_at', '<', '2022/09/18')
        ;
        return $query_pcs_ib;
    }

    //找採購單 相關入庫單
    private function getErr0918InboundGroupByPcsID($purchaseIDs, $param) {
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
            'diff_sku' => DB::raw('ifnull(err_0917.sku, "")'),
        ]);
        $query_pcs = DB::table(app(Purchase::class)->getTable(). ' as pcs')
            ->leftJoin(app(PurchaseInbound::class)->getTable(). ' as inbound', function ($join) {
                $join->on('inbound.event_id', '=', 'pcs.id');
                $join->where('inbound.event', '=', Event::purchase()->value);
            })
            //標記差異的SKU
            ->leftJoin(app(PcsErrStock0917::class)->getTable(). ' as err_0917', 'err_0917.sku', '=', 'inbound.sku')
            ->select('pcs.id'
                , 'pcs.sn'
                , 'pcs.created_at'
                , DB::raw($concat_string_inbound. ' as inbound_data')
            )
            ->whereIn('pcs.id', $purchaseIDs)
            ->where('pcs.created_at', '<', '2022/09/18')
            ->whereNull('pcs.deleted_at')
            ->whereNull('inbound.deleted_at')
            ->groupBy('pcs.id')
        ;

        if (isset($param['purchase_sn'])) {
            $query_pcs->where('pcs.sn', '=', $param['purchase_sn']);
        }
        if (isset($param['inbound_sn'])) {
            $query_pcs->where('inbound.sn', '=', $param['inbound_sn']);
        }
        return $query_pcs;
    }

    //找到差異的商品款式
    private function getErr0918DiffPcsItemWithPcsID($purchase_id) {
        //找到差異的商品款式
        $query_pcs_items = self::getErr0918DiffPcsInbound()
            ->where('item.purchase_id', '=', $purchase_id)
            ->select(
                'item.purchase_id'
                , 'item.id as item_id'
            )
            ->groupBy('item.id')
            ->get()->toArray();

        $pcs_item_id = array_map(
            function ($ar) {
                return $ar->item_id;
            }, $query_pcs_items
        );
        $pcs_item_id = array_unique($pcs_item_id);
        return $pcs_item_id;
    }

    //若舊系統沒有庫存，而匯入的採購單有採購單且未出貨則將該筆採購單和入庫單刪掉
    public function import_no_delivery_page(Request $request)
    {
        $query = $request->query();
        $cond = [];
        $cond['purchase_sn'] = Arr::get($query, 'purchase_sn', null);
        $cond['inbound_sn'] = Arr::get($query, 'inbound_sn', null);
        $cond['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page', 100)) > 0 ? getPageCount(Arr::get($query, 'data_per_page', 100)) : 100;;
        $pcs_id_before_0918 = $this->getErr0918Pcs(false, $cond);

        $pcs_ids = $pcs_id_before_0918;
        $datalist = $this->getErr0918InboundGroupByPcsID($pcs_ids, $cond)
            ->paginate($cond['data_per_page'])->appends($query);
        if (0 < count($datalist)) {
            foreach ($datalist as $key_pcs => $val_pcs) {
                $val_pcs->inbound_data = json_decode($val_pcs->inbound_data);
            }
        }
//        dd($datalist);

        return view('cms.commodity.fix.inbound_fix0917_import.import_has_delivery_list', [
            'showDelBtn' => true,
            'dataList' => $datalist,
            'searchParam' => $cond,
            'formAction' => Route('cms.inbound_fix0917_import.import_no_delivery', []),
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

        $pcs_id_before_0918 = $this->getErr0918Pcs(true, $cond);

        $pcs_ids = $pcs_id_before_0918;
        $datalist = $this->getErr0918InboundGroupByPcsID($pcs_ids, $cond)
            ->paginate($cond['data_per_page'])->appends($query);
        if (0 < count($datalist)) {
            foreach ($datalist as $key_pcs => $val_pcs) {
                $val_pcs->inbound_data = json_decode($val_pcs->inbound_data);
            }
        }
//        dd($datalist);

        return view('cms.commodity.fix.inbound_fix0917_import.import_has_delivery_list', [
            'showDelBtn' => false,
            'dataList' => $datalist,
            'searchParam' => $cond,
            'formAction' => Route('cms.inbound_fix0917_import.import_has_delivery', []),
        ]);
    }

    public function del_purchase_diff_item(Request $request, $purchase_id)
    {
//        dd('del_purchase_diff_item');
        $errors = [];
        $result = DB::transaction(function () use ($request, $purchase_id, $errors) {
            $pcs_item_ids = self::getErr0918DiffPcsItemWithPcsID($purchase_id);
            if (isset($pcs_item_ids) && 0 < count($pcs_item_ids)) {
                foreach ($pcs_item_ids as $key => $val) {
                    $delItemAndIB = PurchaseItem::deleteItemAndInbound($val, $request->user()->id, $request->user()->name);
                    if ($delItemAndIB['success'] == 0) {
                        $errors[] = $delItemAndIB['error_msg'];
                    }
                }
                if (0 < count($errors)) {
                    DB::rollBack();
                    return ['success' => 0, 'error_msg' => implode(" ",$errors)];
                }
            }
            return ['success' => 1, 'error_msg' => ""];
        });

        if ($result['success'] == 0) {
            $errors[] = $result['error_msg'];
            wToast($result['error_msg'], ['type'=>'danger']);
        } else {
            wToast(__('Delete finished.'));
        }
        return redirect()->back()->withInput()->withErrors($errors);
    }

    public function del_multi_purchase_diff_item(Request $request)
    {
        $errors = [];
        $result = [];
        if (isset($request['del_item_id']) && null != $request['del_item_id']) {
            $del_pcs_id_arr = explode(",", $request['del_item_id']);
            if (isset($del_pcs_id_arr) && 0 < count($del_pcs_id_arr)) {
                DB::beginTransaction();
                $errors = [];
                foreach ($del_pcs_id_arr as $key_del => $val_del_id) {
                    $pcs_ids = self::getErr0918DiffPcsItemWithPcsID($val_del_id);
                    if (isset($pcs_ids) && 0 < count($pcs_ids)) {
                        foreach ($pcs_ids as $key => $val) {
                            $delItemAndIB = PurchaseItem::deleteItemAndInbound($val, $request->user()->id, $request->user()->name);
                            if ($delItemAndIB['success'] == 0) {
                                $errors[] = $delItemAndIB['error_msg'];
                                break;
                            }
                        }
                    }
                }
                if (0 < count($errors)) {
                    DB::rollBack();
                    $result = ['success' => 0, 'error_msg' => implode(" ",$errors)];
                } else {
                    $result = ['success' => 1, 'error_msg' => ""];
                }
                DB::commit();
            } else {
                $errors[] = '未輸入欲刪除的ID';
                $result = ['success' => 0, 'error_msg' => implode(" ",$errors)];
            }
        }

        if ($result['success'] == 0) {
            wToast($result['error_msg'], ['type'=>'danger']);
        } else {
            wToast(__('Delete finished.'));
        }
        return redirect()->back()->withInput()->withErrors($errors);
    }

    //修正2022/10/11 18:09:00執行的採購單軟刪除
    public function recovery_purchase_1011(Request $request)
    {
//        dd('recovery_purchase_1011');
        $errors = [];
        $result = DB::transaction(function () use ($request) {
//            $purchase = DB::table(app(Purchase::class)->getTable(). ' as pcs')
//                ->whereNotNull('pcs.deleted_at')
//                ->whereBetween('pcs.deleted_at', ['2022/10/11 18:09:00', '2022/10/11 18:11:00'])
//                ->get()->toArray();
//
//            if (0 < count($purchase)) {
//                foreach ($purchase as $key_ib => $val_ib) {
//                    Purchase::withTrashed()->where('id', '=', $val_ib->id)->update(['deleted_at' => null]);
//                }
//            }
//            $purchaseItem = DB::table(app(PurchaseItem::class)->getTable(). ' as item')
//                ->whereNotNull('item.deleted_at')
//                ->whereBetween('item.deleted_at', ['2022/10/11 18:09:00', '2022/10/11 18:11:00'])
//                ->get()->toArray()
//            ;
//
//            $inboundList = DB::table(app(PurchaseInbound::class)->getTable(). ' as inbound')
//                ->whereNotNull('inbound.deleted_at')
//                ->whereBetween('inbound.deleted_at', ['2022/10/11 18:09:00', '2022/10/11 18:11:00'])
////                ->whereBetween('inbound.deleted_at', ['2022/10/07 09:05:00', '2022/10/07 09:15:00'])
//                ->where('inbound.event', '=', Event::purchase()->value)
////                ->offset(1200)
////                ->limit(1)
//                ->get()->toArray()
//            ;
//
//            //2022/10/13 執行 因為秀慧需要寄倉
//            $purchaseItem = PurchaseItem::withTrashed()->whereIn('id', [513, 1179, 1204])->get();
//            dd('purchaseItem', $purchaseItem);
//            if (0 < count($purchaseItem)) {
//                foreach ($purchaseItem as $key_ib => $val_ib) {
//                    PurchaseItem::withTrashed()->where('id', '=', $val_ib->id)->update(['deleted_at' => null]);
//                }
//            }
//            //2022/10/13 執行 因為秀慧需要寄倉
//            $inboundList = PurchaseInbound::withTrashed()->whereIn('id', [513, 1179, 1204])->get();
//            if (0 < count($inboundList)) {
////                dd('111', count($inboundList), $inboundList);
//                foreach ($inboundList as $key_ib => $val_ib) {
////                    dd('111', $val_ib, $val_ib->id);
//                    PurchaseInbound::withTrashed()->where('id', '=', $val_ib->id)->update(['deleted_at' => null]);
//                    $updateLog = PurchaseInbound::addLogAndUpdateStock(LogEventFeature::purchase_recovery()->value, $val_ib->id
//                        , $val_ib->event, $val_ib->event_id, $val_ib->event_item_id
//                        , $val_ib->product_style_id
//                        , $val_ib->prd_type, $val_ib->title, $val_ib->inbound_num, true, '恢復舊庫存零的商品', StockEvent::purchase_recovery()->value, '恢復舊庫存零的商品', $request->user()->id, $request->user()->name);
//                    if ($updateLog['success'] == 0) {
//                        DB::rollBack();
//                        dd('error', $updateLog, $val_ib);
//                        return $updateLog;
//                    }
//                }
//            }
        });
        dd('end', $result);
    }

}
