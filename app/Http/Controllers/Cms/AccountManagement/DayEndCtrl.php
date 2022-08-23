<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Enums\Supplier\Payment;
use App\Enums\Payable\ChequeStatus;

use App\Models\AllGrade;
use App\Models\AccountPayable;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Depot;
use App\Models\GeneralLedger;
use App\Models\Order;
use App\Models\PayableDefault;
use App\Models\DayEnd;
use App\Models\PayableAccount;
use App\Models\PayableCash;
use App\Models\PayableCheque;
use App\Models\PayableForeignCurrency;
use App\Models\PayableOther;
use App\Models\PayableRemit;
use App\Models\StituteOrder;
use App\Models\Supplier;
use App\Models\User;

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
        if(date('Y-m') >= date('Y-m', strtotime($s_date))){
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

        // $data_list = DayEnd::date_list($d_range)->appends($query);

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
            // DB::beginTransaction();

            try {
                foreach(request('closing_date') as $key => $value){
                    $day_end = DayEnd::match_day_end_order($value);
                }

                // DB::commit();
                wToast(__('整批日結成功'));

                return redirect()->route('cms.day_end.index');

            } catch (\Exception $e) {
                // DB::rollback();
                wToast(__('整批日結失敗', ['type'=>'danger']));
                return redirect()->back();
            }
        }

        wToast(__('整批日結失敗', ['type'=>'danger']));
        return redirect()->back();
    }


    public function show($id)
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
}