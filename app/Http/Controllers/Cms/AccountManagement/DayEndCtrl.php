<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\DayEnd;
use App\Models\DayEndLog;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DayEndCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();
        $cond = [];

        $cond['y'] = Arr::get($query, 'y', date('Y'));
        $cond['m'] = Arr::get($query, 'm', date('m'));

        $s_date = $cond['y'] . '-' . $cond['m'] . '-01';
        $current_day  = date('Y-m') == date('Y-m', strtotime($s_date)) ? date('d') : date('t', strtotime($s_date));
        $e_date = $cond['y'] . '-' . $cond['m'] . '-' . $current_day;

        $data_list = [];
        if(date('Y-m', strtotime($s_date)) <= date('Y-m')){
            while(strtotime($s_date) <= strtotime($e_date)) {
                // $day_num = date('d', strtotime($s_date));
                // $day_name = date('l', strtotime($s_date));
                $data_list[] = (object) [
                    'day' => date('Y-m-d', strtotime($s_date)),
                    'data' => DayEnd::date_list($s_date)->first()
                ];
                $s_date = date('Y-m-d', strtotime('+1 day', strtotime($s_date)));
            }
        }

        $year_range = [
            (date('Y') - 2),
            (date('Y') - 1),
            date('Y'),
            (date('Y') + 1),
            (date('Y') + 2),
        ];

        $month_rage = [];
        for($i = 1; $i <= 12; $i++){
            $month_rage[] = $i;
        }

        return view('cms.account_management.day_end.list', [
            'form_action' => route('cms.day_end.edit'),
            'data_list' => $data_list,
            'cond' => $cond,
            'year_range' => $year_range,
            'month_rage' => $month_rage,
        ]);
    }


    public function edit(Request $request)
    {
        $request->validate([
            'selected' => 'required|array',
            'selected.*' => 'date|date_format:Y-m-d|before:tomorrow',
            'closing_date' => 'required|array',
            'closing_date.*' => 'date|date_format:Y-m-d|before:tomorrow',
        ]);

        $compare = array_diff(request('selected'), request('closing_date'));
        if(count($compare) == 0){
            DB::beginTransaction();

            try {
                foreach(request('closing_date') as $key => $value){
                    $day_end = DayEnd::match_day_end_order($value);
                }

                DB::commit();
                wToast(__('整批日結成功'));

                return redirect()->route('cms.day_end.index');

            } catch (\Exception $e) {
                DB::rollback();
                wToast(__('整批日結失敗', ['type'=>'danger']));
                return redirect()->back();
            }
        }

        wToast(__('整批日結失敗', ['type'=>'danger']));
        return redirect()->back();
    }


    public function detail($id)
    {
        $q = DayEnd::findOrFail($id);
        $day_end = DayEnd::date_list($q->closing_date)->first();

        if($day_end->deo_items){
            $day_end->deo_items = json_decode($day_end->deo_items);

            foreach($day_end->deo_items as $di_value){
                $di_value->link = DayEnd::source_path($di_value->source_type, $di_value->source_id);
            }

        } else {
            $day_end->deo_items = [];
        }

        return view('cms.account_management.day_end.detail', [
            'day_end' => $day_end,
        ]);
    }


    public function balance(Request $request)
    {
        $query = $request->query();
        $cond = [];

        $cond['y'] = Arr::get($query, 'y', date('Y'));
        $cond['m'] = Arr::get($query, 'm', date('m'));

        $s_date = $cond['y'] . '-' . $cond['m'] . '-01';
        $current_day  = date('Y-m') == date('Y-m', strtotime($s_date)) ? date('d') : date('t', strtotime($s_date));
        $e_date = $cond['y'] . '-' . $cond['m'] . '-' . $current_day;

        // 1101
        // 1102
        // 11020001
        // 11020002
        // 11020003
        $data_list = DayEndLog::where(function ($query) use ($s_date, $e_date) {
                if($s_date){
                    $query->where('closing_date', '>=', $s_date);
                }
                if($e_date){
                    $query->where('closing_date', '<', $e_date);
                }
            })->where(function ($q) {
                $q->where('grade_code', 'like', '1101%');
                $q->orWhere('grade_code', 'like', '1102%');
            })->groupBy('grade_name')
            ->orderBy('grade_id', 'asc')
            ->selectRaw('
                SUM(debit_price) AS debit_price,
                SUM(credit_price) AS credit_price,
                grade_id,
                grade_code,
                grade_name
            ')->get();

        $year_range = [
            (date('Y') - 2),
            (date('Y') - 1),
            date('Y'),
            (date('Y') + 1),
            (date('Y') + 2),
        ];

        $month_rage = [];
        for($i = 1; $i <= 12; $i++){
            $month_rage[] = $i;
        }

        return view('cms.account_management.day_end.balance', [
            'data_list' => $data_list,
            'cond' => $cond,
            'year_range' => $year_range,
            'month_rage' => $month_rage,
        ]);
    }


    public function balance_check(Request $request, $id, $date)
    {
        $request->merge([
            'id'=>$id,
            'date'=>$date,
        ]);

        $request->validate([
            'id' => 'required|exists:acc_all_grades,id',
            'date' => 'required|date|date_format:Y-m|before:tomorrow',
        ]);

        $s_date =  date('Y-m-01', strtotime($date));
        $e_date = date('Y-m-t', strtotime($date));

        $data_list = DayEndLog::where(function ($query) use ($s_date, $e_date) {
                if($s_date){
                    $query->where('closing_date', '>=', $s_date);
                }
                if($e_date){
                    $query->where('closing_date', '<', $e_date);
                }
            })->where('grade_id', '=', $id)
            ->orderBy('closing_date', 'asc')
            ->get();

        if($data_list->count() < 1){
            return abort(404);
        }

        $pre_data = DayEndLog::whereDate('closing_date', '<', $s_date)
            ->where('grade_id', '=', $id)
            ->groupBy('grade_id')
            ->selectRaw('
                SUM(debit_price - credit_price) AS price,
                grade_code,
                grade_name
            ')->first();

        if(! $pre_data){
            $pre_data = (object)[
                'price' => 0,
                'grade_code' => $data_list->first()->grade_code,
                'grade_name' => $data_list->first()->grade_name,
            ];
        }

        return view('cms.account_management.day_end.balance_check', [
            'data_list' => $data_list,
            'pre_data' => $pre_data,
        ]);
    }


    public function show(Request $request)
    {
        $query = $request->query();
        $cond = [];

        $cond['current_date'] = Arr::get($query, 'current_date', date('Y-m-d'));

        if($cond['current_date'] > date('Y-m-d')){
            $cond['current_date'] = date('Y-m-d');
        }

        $data_list = [
            'cash'=>[],
            'credit_card'=>[],
            'note_payable'=>[],
            'note_receivable'=>[],
            'remit'=>[],
        ];

        $data_title = [
            'cash'=>'現金',
            'credit_card'=>'信用卡',
            'note_payable'=>'應付票據',
            'note_receivable'=>'應收票據',
            'remit'=>'匯款',
        ];

        $day_end = DayEnd::date_list($cond['current_date'])->first();

        if($day_end){
            if($day_end->deo_items){
                $day_end->deo_items = json_decode($day_end->deo_items);

                foreach($day_end->deo_items as $di_value){
                    DayEnd::match_day_end_detail($data_list, $di_value->source_type, $di_value->source_id, $di_value->source_sn, $di_value->sn);
                }

            } else {
                $day_end->deo_items = [];
            }
        }

        // 1102
        // 11020001
        // 11020002
        // 11020003
        $remit = DayEndLog::remit_log($cond['current_date']);

        // 1104
        // 2101
        // 1109
        // 11090116
        $note_credit = DayEndLog::note_credit_log($cond['current_date']);

        return view('cms.account_management.day_end.show', [
            'data_list' => $data_list,
            'data_title' => $data_title,
            'cond' => $cond,
            'remit' => $remit,
            'note_credit' => $note_credit,
        ]);
    }
}