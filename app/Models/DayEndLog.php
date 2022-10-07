<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class DayEndLog extends Model
{
    use HasFactory;

    protected $table = 'acc_day_end_grade_log';
    protected $guarded = [];


    public static function day_end_log_list($grade_id = null, $closing_date = null, $source_price)
    {
        $query = DB::table('acc_day_end_grade_log AS de_log')
            ->leftJoin('acc_day_end_items AS de_item', function($join){
                $join->on('de_log.source_sn', '=', 'de_item.source_sn');
            })
            ->where(function ($q) use ($grade_id, $closing_date, $source_price) {
                if($grade_id){
                    $q->where('de_log.grade_id', $grade_id);

                    // if(gettype($closing_date) == 'array') {
                    //     $q->whereIn(DB::raw('DATE(deo.closing_date)'), $closing_date);
                    // } else {
                    //     $q->whereDate(DB::raw('deo.closing_date'), $closing_date);
                    // }
                }

                if ($closing_date) {
                    if (gettype($source_price) == 'array' && count($source_price) == 2) {
                        $s_closing_date = $closing_date[0] ? date('Y-m-d', strtotime($closing_date[0])) : null;
                        $e_closing_date = $closing_date[1] ? date('Y-m-d', strtotime($closing_date[1] . ' +1 day')) : null;

                        if($s_closing_date){
                            $q->where('de_log.closing_date', '>=', $s_closing_date);
                        }
                        if($e_closing_date){
                            $q->where('de_log.closing_date', '<', $e_closing_date);
                        }
                    }
                }

                if ($source_price) {
                    if (gettype($source_price) == 'array' && count($source_price) == 2) {
                        $min_price = $source_price[0] ?? null;
                        $max_price = $source_price[1] ?? null;
                        if($min_price){
                            $q->whereRaw('IF(de_log.debit_price = 0, de_log.credit_price, de_log.debit_price) >= ' . $min_price);
                        }
                        if($max_price){
                            $q->whereRaw('IF(de_log.debit_price = 0, de_log.credit_price, de_log.debit_price) <= ' . $max_price);
                        }
                    }
                }
            })

            ->select(
                'de_log.closing_date AS closing_date',
                'de_log.source_type AS source_type',
                'de_log.source_id AS source_id',
                'de_log.source_sn AS source_sn',
                'de_log.source_summary AS source_summary',
                'de_log.debit_price AS debit_price',
                'de_log.credit_price AS credit_price',
                'de_log.net_price AS net_price',
                'de_log.grade_id AS grade_id',
                'de_log.grade_code AS grade_code',
                'de_log.grade_name AS grade_name',
                'de_item.sn AS sn'
            )

            ->orderBy('de_log.closing_date', 'ASC')
            ->orderByRaw('(
                CASE
                    WHEN de_log.source_sn REGEXP "^MSG" THEN 0
                    WHEN de_log.source_sn REGEXP "^ZSG" THEN 2
                    ELSE 1
                END
            ) ASC, de_log.source_sn ASC');

        return $query;
    }


    public static function delete_log($closing_date)
    {
        $date = date('Y-m-d', strtotime($closing_date));

        $target = self::whereDate('closing_date', $date)->delete();
    }


    public static function create_day_end_log($parm)
    {
        $day_end_id = $parm['day_end_id'];
        $closing_date = $parm['closing_date'];
        $source_type = $parm['source_type'];
        $source_id = $parm['source_id'];
        $source_sn = $parm['source_sn'];
        $source_summary = $parm['source_summary'] ?? null;
        $debit_price = $parm['debit_price'] ?? 0;
        $credit_price = $parm['credit_price'] ?? 0;
        $net_price = $debit_price - $credit_price;
        $grade_id = $parm['grade_id'] ?? null;
        $grade_code = $parm['grade_code'] ?? (AllGrade::find($grade_id) ? AllGrade::find($grade_id)->eachGrade->code : null);
        $grade_name = $parm['grade_name'] ?? (AllGrade::find($grade_id) ? AllGrade::find($grade_id)->eachGrade->name : null);

        $target = self::create([
            'day_end_id'=>$day_end_id,
            'closing_date'=>$closing_date,
            'source_type'=>$source_type,
            'source_id'=>$source_id,
            'source_sn'=>$source_sn,
            'source_summary'=>$source_summary,
            'debit_price'=>$debit_price,
            'credit_price'=>$credit_price,
            'net_price'=>$net_price,
            'grade_id'=>$grade_id,
            'grade_code'=>$grade_code,
            'grade_name'=>$grade_name,
        ]);
    }


    public static function remit_log($current_date)
    {
        $date = date('Y-m-d', strtotime($current_date));

        $remit_grade_list = self::where('grade_code', 'like', '1102%')->groupBy('grade_name')->orderBy('grade_id', 'asc')->distinct()->pluck('grade_name', 'grade_code')->toArray();
        $array = [];
        foreach($remit_grade_list as $g_key => $g_value){
            $_previous = self::where([
                'grade_code'=>$g_key,
                'grade_name'=>$g_value,
            ])->whereDate('closing_date', '<', $date)->get();

            $_current = self::where([
                'grade_code'=>$g_key,
                'grade_name'=>$g_value,
            ])->whereDate('closing_date', '=', $date)->get();

            $pre_price = $_previous ? $_previous->sum('net_price') : 0;
            $cur_price = $_current ? $_current->sum('net_price') : 0;
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
        $date = date('Y-m-d', strtotime($current_date));

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
            ])->whereDate('closing_date', '<', $date)->get();

            $_current = DayEndLog::where([
                'grade_code'=>$g_key,
                'grade_name'=>$g_value,
            ])->whereDate('closing_date', '=', $date)->get();

            $pre_price = $_previous ? $_previous->sum('net_price') : 0;
            $pre_count = $_previous ? $_previous->count() : 0;
            $cur_price = $_current ? $_current->sum('net_price') : 0;
            $cur_count = $_current ? $_current->count() : 0;

            if(strpos($g_key, '1109') !== false && strpos($g_value, '信用卡') !== false) {
                $tmp_pre_price += $pre_price;
                $tmp_pre_count += $pre_count;
                $tmp_cur_price += $cur_price;
                $tmp_cur_count += $cur_count;

            } else {
                if(strpos($g_key, '1104') !== false && strpos($g_value, '應收票據') !== false){
                    $pre_received_cashed_cheque = DB::table('acc_received_cheque')->whereNotNull('sn')->whereDate('cashing_date', '<', $date)->get();
                    $cur_received_cashed_cheque = DB::table('acc_received_cheque')->whereNotNull('sn')->whereDate('cashing_date', '=', $date)->get();
                    $nex_received_cashed_cheque = DB::table('acc_received_cheque')->whereNotNull('sn')->whereDate('cashing_date', '=', date('Y-m-d', strtotime($date . ' +1 day')) )->get();

                    $pre_price -= $pre_received_cashed_cheque->sum('amt_net');
                    $pre_count -= $pre_received_cashed_cheque->count();
                    $cur_cashed_price = $cur_received_cashed_cheque->sum('amt_net');
                    $cur_cashed_count = $cur_received_cashed_cheque->count();
                    $nex_cashed_price = $nex_received_cashed_cheque->sum('amt_net');
                    $nex_cashed_count = $nex_received_cashed_cheque->count();

                } else if(strpos($g_key, '2101') !== false && strpos($g_value, '應付票據') !== false){
                    $pre_payable_cashed_cheque = DB::table('acc_payable_cheque')->whereNotNull('sn')->whereDate('cashing_date', '<', $date)->get();
                    $cur_payable_cashed_cheque = DB::table('acc_payable_cheque')->whereNotNull('sn')->whereDate('cashing_date', '=', $date)->get();
                    $nex_payable_cashed_cheque = DB::table('acc_payable_cheque')->whereNotNull('sn')->whereDate('cashing_date', '=', date('Y-m-d', strtotime($date . ' +1 day')) )->get();

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
}
