<?php

namespace App\Http\Controllers\Cms\Settings;

use App\Exports\OrderInvoice\OrderInvoiceExport;
use App\Http\Controllers\Controller;
use App\Models\OrderInvoice;
use Illuminate\Http\Request;

use Illuminate\Support\Arr;

class OrderInvoiceManagerCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();
        $cond = [];
        $cond['invoice_number'] = Arr::get($query, 'invoice_number', null);
        $cond['buyer_name'] = Arr::get($query, 'buyer_name', null);
        $cond['buyer_ubn'] = Arr::get($query, 'buyer_ubn', null);
        $cond['invoice_sdate'] = Arr::get($query, 'invoice_sdate', null);
        $cond['invoice_edate'] = Arr::get($query, 'invoice_edate', null);

        $cond['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page', 100)) > 0 ? getPageCount(Arr::get($query, 'data_per_page', 100)) : 100;;

        $data_list = OrderInvoice::getData($cond)->paginate($cond['data_per_page'])->appends($query);

        return view('cms.settings.order_invoice_manager.list', [
            'data_per_page' => $cond['data_per_page'],
            'data_list' => $data_list,
            'cond' => $cond,
        ]);
    }

    public function month(Request $request)
    {
        $query = $request->query();
        $cond = [];
        $cond['invoice_month'] = Arr::get($query, 'invoice_month', null);
        $cond['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page', 100)) > 0 ? getPageCount(Arr::get($query, 'data_per_page', 100)) : 100;;

        if (isset($cond['invoice_month'])) {
            $cond['invoice_sdate'] = date("Y-m-1", strtotime($cond['invoice_month']));
            $cond['invoice_edate'] = date("Y-m-t", strtotime($cond['invoice_month']));
        }
        $data_list = OrderInvoice::getData($cond)->paginate($cond['data_per_page'])->appends($query);

        return view('cms.settings.order_invoice_manager.month', [
            'data_per_page' => $cond['data_per_page'],
            'data_list' => $data_list,
            'cond' => $cond,
        ]);
    }

    public function export_excel_month(Request $request)
    {
        $cond = [];
        $cond['invoice_month'] = $request->input('invoice_month', null);
        if (isset($cond['invoice_month'])) {
            $cond['invoice_sdate'] = date("Y-m-1", strtotime($cond['invoice_month']));
            $cond['invoice_edate'] = date("Y-m-t", strtotime($cond['invoice_month']));
        }
        return (new OrderInvoiceExport($cond))->download("report-" . date('YmdHis') . ".xlsx");
    }
}
