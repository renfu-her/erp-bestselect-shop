<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class DayEndLog extends Model
{
    use HasFactory;

    protected $table = 'acc_day_end_rp_log';
    protected $guarded = [];


    public static function check_day_end_log($day_end_id, $closing_date, $parm)
    {
        $grade_id = $parm['grade_id'] ?? null;
        $grade_code = $parm['grade_code'] ?? (AllGrade::find($grade_id) ? AllGrade::find($grade_id)->eachGrade->code : null);
        $grade_name = $parm['grade_name'] ?? (AllGrade::find($grade_id) ? AllGrade::find($grade_id)->eachGrade->name : null);
        $debit_price = $parm['debit_price'] ?? 0;
        $credit_price = $parm['credit_price'] ?? 0;

        $target = self::where(function ($q) use ($closing_date, $grade_id, $grade_code, $grade_name) {
            if($closing_date){
                $q->whereDate('closing_date', $closing_date);
            }
            if($grade_id){
                $q->where('grade_id', $grade_id);
            }
            if($grade_code){
                $q->where('grade_code', 'like', "%$grade_code%");
            }
            if($grade_name){
                $q->where('grade_name', 'like', "%$grade_name%");
            }
        })->first();

        if($target){
            $target->update([
                'day_end_id'=>$day_end_id,
                'closing_date'=>$closing_date,
                'count'=>DB::raw('count+1'),
                'debit_price'=>$target->debit_price + $debit_price,
                'credit_price'=>$target->credit_price + $credit_price,
                'updated_at'=>date('Y-m-d H:i:s'),
            ]);

        } else {
            $target = self::create([
                'day_end_id'=>$day_end_id,
                'closing_date'=>$closing_date,
                'count'=>1,
                'debit_price'=>$debit_price,
                'credit_price'=>$credit_price,
                'grade_id'=>$grade_id,
                'grade_code'=>$grade_code,
                'grade_name'=>$grade_name,
            ]);
        }
    }


    public static function remit_log($current_date)
    {
        $remit_grade_list = self::where('grade_code', 'like', '1102%')->groupBy('grade_name')->orderBy('grade_id', 'asc')->distinct()->pluck('grade_name', 'grade_code')->toArray();
        $array = [];
        foreach($remit_grade_list as $g_key => $g_value){
            $_previous = self::where([
                'grade_code'=>$g_key,
                'grade_name'=>$g_value,
            ])->whereDate('closing_date', '<', $current_date)->get();

            $_current = self::where([
                'grade_code'=>$g_key,
                'grade_name'=>$g_value,
            ])->whereDate('closing_date', '=', $current_date)->get();

            $pre_price = $_previous ? ($_previous->sum('debit_price') - $_previous->sum('credit_price')) : 0;
            $cur_price = $_current ? ($_current->sum('debit_price') - $_current->sum('credit_price')) : 0;
            $cur_debit_price = $_current ? $_current->sum('debit_price') : 0;
            $cur_credit_price = $_current ? $_current->sum('credit_price') : 0;

            $array[] = (object) [
                'title' => $g_value,
                'pre_price' => $pre_price,
                'cur_price' => $cur_price,
                'cur_debit_price' => $cur_debit_price,
                'cur_credit_price' => $cur_credit_price,
            ];
        }

        return $array;
    }


    public static function note_credit_log($current_date)
    {
        $note_credit_grade_list = DayEndLog::where('grade_code', 'like', '1104%')
            ->orWhere('grade_code', 'like', '2101%')
            ->orWhere('grade_code', 'like', '1109%')
            ->groupBy('grade_name')
            ->orderBy('grade_id', 'asc')
            ->distinct()->pluck('grade_name', 'grade_code')
            ->toArray();
        $array = [];
        $tmp_pre_price = 0;
        $tmp_pre_count = 0;
        $tmp_cur_price = 0;
        $tmp_cur_count = 0;
        foreach($note_credit_grade_list as $g_key => $g_value){
            $_previous = DayEndLog::where([
                'grade_code'=>$g_key,
                'grade_name'=>$g_value,
            ])->whereDate('closing_date', '<', $current_date)->get();

            $_current = DayEndLog::where([
                'grade_code'=>$g_key,
                'grade_name'=>$g_value,
            ])->whereDate('closing_date', '=', $current_date)->get();

            $pre_price = $_previous ? ($_previous->sum('debit_price') - $_previous->sum('credit_price')) : 0;
            $pre_count = $_previous ? $_previous->sum('count') : 0;
            $cur_price = $_current ? ($_current->sum('debit_price') - $_current->sum('credit_price')) : 0;
            $cur_count = $_current ? $_current->sum('count') : 0;

            if(strpos($g_key, '1109') !== false && strpos($g_value, '信用卡') !== false) {
                $tmp_pre_price += $pre_price;
                $tmp_pre_count += $pre_count;
                $tmp_cur_price += $cur_price;
                $tmp_cur_count += $cur_count;

            } else {
                if(strpos($g_key, '1104') !== false && strpos($g_value, '應收票據') !== false){
                    $pre_received_cashed_cheque = DB::table('acc_received_cheque')->whereNotNull('sn')->whereDate('cashing_date', '<', $current_date)->get();
                    $cur_received_cashed_cheque = DB::table('acc_received_cheque')->whereNotNull('sn')->whereDate('cashing_date', '=', $current_date)->get();
                    $nex_received_cashed_cheque = DB::table('acc_received_cheque')->whereNotNull('sn')->whereDate('cashing_date', '=', date('Y-m-d', strtotime($current_date . ' +1 day')) )->get();

                    $pre_price -= $pre_received_cashed_cheque->sum('amt_net');
                    $pre_count -= $pre_received_cashed_cheque->count();
                    $cur_cashed_price = $cur_received_cashed_cheque->sum('amt_net');
                    $cur_cashed_count = $cur_received_cashed_cheque->count();
                    $nex_cashed_price = $nex_received_cashed_cheque->sum('amt_net');
                    $nex_cashed_count = $nex_received_cashed_cheque->count();

                } else if(strpos($g_key, '2101') !== false && strpos($g_value, '應付票據') !== false){
                    $pre_payable_cashed_cheque = DB::table('acc_payable_cheque')->whereNotNull('sn')->whereDate('cashing_date', '<', $current_date)->get();
                    $cur_payable_cashed_cheque = DB::table('acc_payable_cheque')->whereNotNull('sn')->whereDate('cashing_date', '=', $current_date)->get();
                    $nex_payable_cashed_cheque = DB::table('acc_payable_cheque')->whereNotNull('sn')->whereDate('cashing_date', '=', date('Y-m-d', strtotime($current_date . ' +1 day')) )->get();

                    $pre_price -= $pre_payable_cashed_cheque->sum('amt_net');
                    $pre_count -= $pre_payable_cashed_cheque->count();
                    $cur_cashed_price = $cur_payable_cashed_cheque->sum('amt_net');
                    $cur_cashed_count = $cur_payable_cashed_cheque->count();
                    $nex_cashed_price = $nex_payable_cashed_cheque->sum('amt_net');
                    $nex_cashed_count = $nex_payable_cashed_cheque->count();
                }

                $array[] = (object) [
                    'title' => $g_value,
                    'pre_price' => $pre_price,
                    'pre_count' => $pre_count,
                    'cur_price' => $cur_price,
                    'cur_count' => $cur_count,
                    'cur_cashed_price' => $cur_cashed_price,
                    'cur_cashed_count' => $cur_cashed_count,
                    'nex_cashed_price' => $nex_cashed_price,
                    'nex_cashed_count' => $nex_cashed_count,
                ];
            }
        }
        $array[] = (object) [
            'title' => '信用卡',
            'pre_price' => $tmp_pre_price,
            'pre_count' => $tmp_pre_count,
            'cur_price' => $tmp_cur_price,
            'cur_count' => $tmp_cur_count,
            'cur_cashed_price' => 0,
            'cur_cashed_count' => 0,
            'nex_cashed_price' => 0,
            'nex_cashed_count' => 0,
        ];

        return $array;
    }


    public static function reset_log($closing_date)
    {
        $target = self::whereDate('closing_date', $closing_date)->update([
            'count'=>0,
            'debit_price'=>0,
            'credit_price'=>0,
            'updated_at'=>date('Y-m-d H:i:s'),
        ]);
    }
}
