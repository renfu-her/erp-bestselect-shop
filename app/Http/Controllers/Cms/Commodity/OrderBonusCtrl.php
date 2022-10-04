<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\OrderCustomerProfitReport;
use App\Models\OrderMonthProfitReport;
use App\Models\OrderProfit;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class OrderBonusCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //

        $query = $request->query();
        //   dd($query);
        $cond = [];
        $page = getPageCount(Arr::get($query, 'data_per_page', 10));
        $cond['report_month'] = Arr::get($query, 'report_month');
        $cond['check_status'] = Arr::get($query, 'check_status', 'all');
        $cond['keyword'] = Arr::get($query, 'keyword');

        $dataList = OrderMonthProfitReport::dataList($cond['keyword'], $cond['report_month'])->paginate($page)
            ->appends($query);
        // dd( OrderProfitReport::dataList()->get());
        return view('cms.commodity.order_bonus.list', [
            'dataList' => $dataList,
            'cond' => $cond,
            'data_per_page' => $page]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('cms.commodity.order_bonus.edit');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'month' => 'date',
            'transfer_at' => 'date',
        ]);

        //  dd($_POST);

        $d = $request->all();
        // dd($d);
        $re = OrderMonthProfitReport::createReport($d['title'], $d['month'], $request->user()->id, $d['transfer_at']);

        if ($re['success'] == '1') {
            wToast('新增完成');
        } else {
            wToast('無該月份資料', ['type' => 'danger']);
        }
        return redirect(route('cms.order-bonus.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        OrderMonthProfitReport::deleteReport($id);
        wToast('刪除完成');
        return redirect(route('cms.order-bonus.index'));
    }

    public function detail($id)
    {
        $month_report = OrderMonthProfitReport::where('id', $id)->get()->first();
        
        if (!$month_report) {
            return abort(404);
        }
        $customer_reports = OrderCustomerProfitReport::dataList($id)->get();

        $baseData = $this->baseData();
        //   dd($customer_reports);
        // $profit = OrderProfit::dataList(null, $report->customer_id, $report->report_at . "/1")->get();

        return view('cms.commodity.order_bonus.detail_list', [
            'customer_reports' => $customer_reports,
            'month_report' => $month_report,
            'baseData' => $baseData,
            'breadcrumb_data' => ['month' => $month_report],
        ]);

    }

    public function personDetail($id, $customer_id)
    {
        $month_report = OrderMonthProfitReport::where('id', $id)->get()->first();
        if (!$month_report) {
            return abort(404);
        }

        //  dd($month_report);
        $profit = OrderProfit::dataList(null, $customer_id, $month_report->report_at)->get();
        //   dd($profit);
        $customer_reports = OrderCustomerProfitReport::dataList($id)->where('customer.id', $customer_id)->get()->first();
     //   dd($customer_reports);

        return view('cms.commodity.order_bonus.bonus', [
            'customer' => $customer_reports,
            'month_report' => $month_report,
            'dataList' => $profit,
            'breadcrumb_data' => ['month' => $month_report, 'title' => $customer_reports->mcode . " " . $customer_reports->name],
        ]);

    }

    public function exportCsv(Request $request, $id)
    {

        //  dd($this->baseData());
        $request->validate([
            "bank_type" => "required|in:a,b",
        ]);

        $bank_type = $request->input("bank_type");

        // dd($bank_type);

        $month_report = OrderMonthProfitReport::where('id', $id)->get()->first();

        // dd($month_report);
        $fileName = $month_report->title;

        if ($bank_type == 'a') {
            $fileName .= "(合庫)";
        } else {
            $fileName .= "(非合庫)";
        }
        $fileName .= ".csv";

        $baseData = $this->baseData();

        // 合庫
        $title_a = ["項目",
            "EDI用戶代碼",
            "手續費負擔別",
            "付款日期",
            "企業參考號碼",
            "付款金額",
            "付款人帳號",
            "付款銀行代號",
            "付款人身分證(統一編號)",
            "付款人戶名",
            "收款人帳號",
            "收款人銀行代號",
            "收款人身分證(統一編號)",
            "收款人戶名",
            "收款人聯絡姓名",
            "收款人傳真號碼",
            "入帳通知處理方式",
            "付款說明",
            "業務類別",
            "Email",
            "付款人存摺備註",
            "收款人存摺備註",
            "強制通匯選項",
            "預留1"];
        // 非合庫
        $title_b = ["客戶使用欄(勿刪)",
            "客戶使用欄(勿刪)",
            "姓名",
            "轉帳金額",
            "收款人帳號(最長14)",
            "ID(統編)",
            "付款日期(YYYYMMDD)",
            "收款銀行代號(7位)",
            "E-MAIL",
            "FAX",
            "備註"];
        $title = ${'title_' . $bank_type};
        // dd($title);
        if ($bank_type == 'a') {
            $title = $title_a;
        } else {
            $title = $title_b;
        }

        $datas = OrderCustomerProfitReport::dataList($id, $bank_type)->get();
        //    dd($datas);
        //  dd($baseData);
        //  exit;
        // return response()->stream($callback, 200, $headers);
        return response()->streamDownload(function () use ($title, $datas, $bank_type, $baseData,$month_report) {
            $file = fopen('php://output', 'w');
           
            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, $title);

            if ($bank_type == 'a') {
                foreach ($datas as $key => $data) {
                    fputcsv($file, [
                        str_pad($key + 1, 3, '0', STR_PAD_LEFT),
                        $baseData->pay_edi,
                        $baseData->pay_code,
                        $month_report->transfer_at,
                        "",
                        $data->bonus,
                        $baseData->pay_bank_account,
                        $baseData->pay_bank_code,
                        $baseData->pay_identity_sn,
                        $baseData->pay_bank_account_name,
                        $data->bank_account,
                        $data->new_bank_code,
                        $data->identity_sn,
                        $data->bank_account_name,
                        $data->bank_account_name,
                        "",
                        $baseData->pay_notify,
                        $baseData->pay_note,
                        $baseData->pay_category,
                        "",
                        "",
                        "",
                        "",
                        "",
                    ]);
                }
            } else {
                foreach ($datas as $key => $data) {
                    fputcsv($file, [
                        "",
                        "",
                        $data->bank_account_name,
                        $data->bonus,
                        $data->bank_account,
                        $data->identity_sn,
                        $month_report->transfer_at,
                        $data->new_bank_code,
                        "",
                        "",
                        "",
                    ]);

                }
            }

            fclose($file);
        }, $fileName);

    }

    private function baseData()
    {
        return (object) [
            "pay_code" => '13',
            "pay_bank_account" => '844871001158',
            "pay_bank_code" => '60844',
            "pay_bank_account_name" => '喜鴻國際企業股份有限公司',
            "pay_identity_sn" => '83183027',
            'pay_notify' => 'AF',
            'pay_note' => '獎金',
            'pay_category' => 'SAL',
            'pay_edi' => '831830270002',
        ];
    }

}
