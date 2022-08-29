<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AllGrade;
use App\Models\DayEnd;
use App\Models\DayEndLog;
use App\Models\GeneralLedger;


class LedgerCtrl extends Controller
{
    public function index()
    {
        $total_grades = GeneralLedger::total_grade_list();

        return view('cms.account_management.ledger.index', [
            'form_action'=>route('cms.ledger.detail'),
            'total_grades'=>$total_grades,
        ]);
    }


    public function detail(Request $request)
    {
        $request->merge([
            'grade_id'=>request('grade_id'),
            'sdate'=>request('sdate'),
            'edate'=>request('edate'),
            'min_price'=>request('min_price'),
            'max_price'=>request('max_price'),
        ]);

        $request->validate([
            'grade_id' => 'required|exists:acc_all_grades,id',
            'sdate' => 'nullable|date|date_format:Y-m-d',
            'edate' => 'nullable|date|date_format:Y-m-d',
            'min_price' => 'nullable|numeric|gt:0',
            'max_price' => 'nullable|numeric|gt:0',
        ]);

        $date = [
            request('sdate'),
            request('edate')
        ];
        $price = [
            request('min_price'),
            request('max_price')
        ];

        $data_list = DayEndLog::day_end_log_list(request('grade_id'), $date, $price)->get();
        foreach($data_list as $value){
            $value->link = DayEnd::source_path($value->source_type, $value->source_id);
        }

        $pre_data = DayEndLog::where(function ($q) use($date, $price) {
                if($date[0]){
                    $q->whereDate('closing_date', '<', $date[0]);
                } else {
                    $q->whereDate('closing_date', '=', $date[0]);
                }
                if($price[0]){
                    $q->whereRaw('IF(debit_price = 0, credit_price, debit_price) >= ' . $price[0]);
                }
                if($price[1]){
                    $q->whereRaw('IF(debit_price = 0, credit_price, debit_price) <= ' . $price[1]);
                }
            })
            ->where('grade_id', '=', request('grade_id'))
            ->groupBy('grade_id')
            ->selectRaw('
                SUM(debit_price) AS debit_price,
                SUM(credit_price) AS credit_price,
                SUM(net_price) AS net_price,
                grade_code,
                grade_name
            ')->first();

        if(! $pre_data){
            $pre_data = (object)[
                'debit_price' => 0,
                'credit_price' => 0,
                'net_price' => 0,
                'grade_code' => AllGrade::find(request('grade_id')) ? AllGrade::find(request('grade_id'))->eachGrade->code : null,
                'grade_name' => AllGrade::find(request('grade_id')) ? AllGrade::find(request('grade_id'))->eachGrade->name : null,
            ];
        }

        return view('cms.account_management.ledger.detail', [
            'data_list' => $data_list,
            'pre_data' => $pre_data,
        ]);
    }
}