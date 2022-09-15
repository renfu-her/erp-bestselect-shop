<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

class NotePayableOrder extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'acc_note_payable_orders';
    protected $guarded = [];


    public static function get_cheque_payable_list(
        $payable_cheque_id = null,
        $cheque_status_code = null,

        $account_payable_grade_id = null,
        $ticket_number = null,
        $payable_price = null,
        $payment_date = null,
        $cheque_due_date = null,
        $cheque_cashing_date = null
    ){
        $query = DB::table('acc_payable AS payable')
            ->join('pcs_paying_orders AS po', function($join){
                $join->on('payable.pay_order_id', '=', 'po.id');
                $join->where([
                    'po.deleted_at'=>null,
                ]);
            })
            ->leftJoin('acc_income_type AS payable_method', function($join){
                $join->on('payable.accountant_id_fk', '=', 'payable_method.id');
                $join->where([
                    'payable_method.id'=>2,
                ]);
            })
            ->leftJoin('usr_users AS undertaker', function($join){
                $join->on('po.usr_users_id', '=', 'undertaker.id');
                $join->where([
                    'undertaker.deleted_at'=>null,
                ]);
            })

            ->join('acc_payable_cheque AS _cheque', function($join){
                $join->on('payable.payable_id', '=', '_cheque.id');
                $join->where([
                    'payable.acc_income_type_fk'=>2,
                ]);
            })
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'payable_grade', function($join) {
                $join->on('payable_grade.primary_id', 'payable.all_grades_id');
            })

            ->leftJoin('acc_note_payable_orders AS npo', function($join){
                $join->on('_cheque.note_payable_order_id', '=', 'npo.id');
                $join->where([
                    'npo.deleted_at'=>null,
                ]);
            })
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'npo_grade', function($join) {
                $join->on('npo_grade.primary_id', 'npo.net_grade_id');
            })

            ->where([
                //
            ])
            ->whereNotNull('po.balance_date')
            ->whereNotNull('po.payment_date')
            ->whereNotNull('payable.payment_date')

            ->selectRaw('
                po.id AS po_id,
                po.source_type AS po_source_type,
                po.source_id AS po_source_id,
                po.source_sub_id AS po_source_sub_id,
                po.type AS po_type,
                po.sn AS po_sn,
                undertaker.name AS po_undertaker,
                po.payee_id AS po_target_id,
                po.payee_name AS po_target_name,
                po.payee_phone AS po_target_phone,
                po.payee_address AS po_target_address,
                po.created_at AS po_created,

                payable.id AS payable_id,
                payable_method.type AS payable_method,
                payable.payable_id AS payable_method_id,
                payable.all_grades_id AS po_payable_grade_id,
                payable.tw_price,
                payable.payment_date,
                payable.taxation,
                payable.summary,
                payable.note,

                payable_grade.code AS po_payable_grade_code,
                payable_grade.name AS po_payable_grade_name,

                _cheque.id AS cheque_payable_id,
                _cheque.ticket_number AS cheque_ticket_number,
                _cheque.due_date AS cheque_due_date,

                _cheque.grade_code AS cheque_grade_code,
                _cheque.grade_name AS cheque_grade_name,

                _cheque.status_code AS cheque_status_code,
                _cheque.status AS cheque_status,
                _cheque.cashing_date AS cheque_cashing_date,
                _cheque.bounce_date AS cheque_bounce_date,
                _cheque.note_payable_order_id AS cheque_note_payable_order_id,
                _cheque.sn AS cheque_sn,
                _cheque.amt_net AS cheque_amt_net,

                npo.amt_total_net AS npo_amt_total_net,
                npo.net_grade_id AS npo_net_grade_id,
                npo.creator_id AS npo_creator_id,
                npo.affirmant_id AS npo_affirmant_id,

                npo_grade.code AS npo_grade_code,
                npo_grade.name AS npo_grade_name
            ')
            ->orderBy('_cheque.id', 'asc');

        if($payable_cheque_id) {
            if(gettype($payable_cheque_id) == 'array') {
                $query->whereIn('_cheque.id', $payable_cheque_id);
            } else {
                $query->where('_cheque.id', $payable_cheque_id);
            }
        }

        if($cheque_status_code){
            if(gettype($cheque_status_code) == 'array') {
                $query->whereIn('_cheque.status_code', $cheque_status_code);
            } else {
                $query->where('_cheque.status_code', $cheque_status_code);
            }
        }

        if($account_payable_grade_id) {
            if(gettype($account_payable_grade_id) == 'array') {
                $query->whereIn('payable.all_grades_id', $account_payable_grade_id);
            } else {
                $query->where('payable.all_grades_id', $account_payable_grade_id);
            }
        }

        if($ticket_number){
            if(gettype($ticket_number) == 'array') {
                $query->whereIn('_cheque.ticket_number', $ticket_number);
            } else {
                $query->where('_cheque.ticket_number', 'like', "%{$ticket_number}%");
            }
        }

        if($payable_price) {
            if (gettype($payable_price) == 'array' && count($payable_price) == 2) {
                $min_price = $payable_price[0] ?? null;
                $max_price = $payable_price[1] ?? null;
                if($min_price){
                    $query->where('payable.tw_price', '>=', $min_price);
                }
                if($max_price){
                    $query->where('payable.tw_price', '<=', $max_price);
                }
            }
        }

        if($payment_date){
            $s_payment_date = $payment_date[0] ? date('Y-m-d', strtotime($payment_date[0])) : null;
            $e_payment_date = $payment_date[1] ? date('Y-m-d', strtotime($payment_date[1] . ' +1 day')) : null;

            if($s_payment_date){
                $query->where('payable.payment_date', '>=', $s_payment_date);
            }
            if($e_payment_date){
                $query->where('payable.payment_date', '<', $e_payment_date);
            }
        }

        if($cheque_due_date){
            $s_cheque_due_date = $cheque_due_date[0] ? date('Y-m-d', strtotime($cheque_due_date[0])) : null;
            $e_cheque_due_date = $cheque_due_date[1] ? date('Y-m-d', strtotime($cheque_due_date[1] . ' +1 day')) : null;

            if($s_cheque_due_date){
                $query->where('_cheque.due_date', '>=', $s_cheque_due_date);
            }
            if($e_cheque_due_date){
                $query->where('_cheque.due_date', '<', $e_cheque_due_date);
            }
        }

        if($cheque_cashing_date){
            $s_cheque_cashing_date = $cheque_cashing_date[0] ? date('Y-m-d', strtotime($cheque_cashing_date[0])) : null;
            $e_cheque_cashing_date = $cheque_cashing_date[1] ? date('Y-m-d', strtotime($cheque_cashing_date[1] . ' +1 day')) : null;

            if($s_cheque_cashing_date){
                $query->where('_cheque.cashing_date', '>=', $s_cheque_cashing_date);
            }
            if($e_cheque_cashing_date){
                $query->where('_cheque.cashing_date', '<', $e_cheque_cashing_date);
            }
        }

        return $query;
    }


    public static function update_cheque_payable_method($request)
    {
        $note_payable_order = null;

        if(in_array($request['status_code'], ['collection', 'nd'])){
            // DB::table('acc_payable_cheque')->whereIn('id', $request['cheque_payable_id'])->update([
            //     'status_code'=>$request['status_code'],
            //     'status'=>$request['status'],
            //     'updated_at'=>date('Y-m-d H:i:s'),
            // ]);

        } else if($request['status_code'] === 'cashed'){
            foreach($request['cheque_payable_id'] as $key => $value){
                DB::table('acc_payable_cheque')->where('id', $value)->update([
                    'status_code'=>$request['status_code'],
                    'status'=>$request['status'],
                    'amt_net'=>$request['amt_net'][$key],
                    'cashing_date'=>$request['cashing_date'],
                    'updated_at'=>date('Y-m-d H:i:s'),
                ]);
            }

            $note_payable_order = self::store_note_payable_order($request['cashing_date']);

            foreach($request['cheque_payable_id'] as $key => $value){
                DB::table('acc_payable_cheque')->where('id', $value)->update([
                    'note_payable_order_id'=>$note_payable_order->id,
                    'sn'=>$note_payable_order->sn,
                    'updated_at'=>date('Y-m-d H:i:s'),
                ]);
            }

        } else if($request['status_code'] === 'po_delete'){
            DB::table('acc_payable_cheque')->whereIn('id', $request['cheque_payable_id'])->update([
                'status_code'=>$request['status_code'],
                'status'=>$request['status'],

                'amt_net'=>0,
                'note_payable_order_id'=>null,
                'sn'=>null,
                'updated_at'=>date('Y-m-d H:i:s'),
            ]);
        }

        foreach($request['cheque_payable_id'] as $key => $value){
            NotePayableLog::create_cheque_log($value, $request['status_code']);
        }

        return $note_payable_order;
    }


    public static function store_note_payable_order($cashing_date)
    {
        $date = date('Y-m-d', strtotime($cashing_date));

        $target = self::whereDate('cashing_date', $date)->first();
        $net = DB::table('acc_payable_cheque')->where('status_code', 'cashed')->whereDate('cashing_date', $date)->sum('amt_net');

        if($target){
            $target->update([
                'amt_total_net'=>$net,
                'affirmant_id'=>auth('user')->user() ? auth('user')->user()->id : null,
                'updated_at'=>date('Y-m-d H:i:s'),
            ]);

        } else {
            $default_net_grade = PayableDefault::where('name', 'cheque')->first() ? PayableDefault::where('name', 'cheque')->first()->default_grade_id : 44;

            $target = self::create([
                'sn'=>'BSG' . date('ymd', strtotime($cashing_date)),
                'amt_total_net'=>$net,
                'net_grade_id'=>$default_net_grade,
                'cashing_date'=>$cashing_date,
                'creator_id'=>auth('user')->user() ? auth('user')->user()->id : null,
                'affirmant_id'=>auth('user')->user() ? auth('user')->user()->id : null,
            ]);
        }

        return $target;
    }
}
