<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\Event;
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
    private function getErr0918Pcs($param) {
        $pcsErrStock0917 = PcsErrStock0917::all();
        if (0 >= count($pcsErrStock0917)) {
            dd('DB無資料 請先上傳檔案 找舊系統沒有庫存，新系統卻是有庫存的商品');
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

        return view('cms.commodity.fix.inbound_fix0917_import.import_has_delivery_list', [
            'showDelBtn' => false,
            'dataList' => $datalist,
            'searchParam' => $cond,
            'formAction' => Route('cms.inbound_fix0917_import.import_has_delivery', []),
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

    public function del_multi_purchase(Request $request)
    {
        dd('del_multi_purchase', $request['del_item_id']);
        $errors = [];
        $result = DB::transaction(function () use ($request) {
            if (isset($request['del_item_id']) && null != $request['del_item_id']) {
                $del_item_id_arr = explode(",", $request['del_item_id']);
                if (isset($del_item_id_arr) && 0 < count($del_item_id_arr)) {
                        $errors = [];
                        foreach ($del_item_id_arr as $key_del => $val_del) {
                            $result = Purchase::forceDel($val_del, $request->user()->id, $request->user()->name);
                            if ($result['success'] == 0) {
                                $errors[] = $result['error_msg'];
                            }
                        }
                        if (0 < count($errors)) {
                            DB::rollBack();
                            return ['success' => 0, 'error_msg' => implode(" ",$errors)];
                        } else {
                            return ['success' => 1, 'error_msg' => ""];
                        }
                }
            }
        });

        if ($result['success'] == 0) {
            $errors[] = $result['error_msg'];
            wToast($result['error_msg'], ['type'=>'danger']);
        } else {
            wToast(__('Delete finished.'));
        }
        return redirect()->back()->withInput()->withErrors($errors);
    }

}
