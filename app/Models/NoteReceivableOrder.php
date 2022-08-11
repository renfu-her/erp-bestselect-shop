<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

class NoteReceivableOrder extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'acc_note_receivable_orders';
    protected $guarded = [];


    public static function get_cheque_received_list(
        $received_cheque_id = null,
        $cheque_status_code = null,

        $banks = null,
        $deposited_area_code = null,
        $ticket_number = null,
        $drawer = null,
        $undertaker = null,

        $received_price = null,
        $ro_receipt_date = null,
        $cheque_c_n_date = null,
        $cheque_due_date = null,
        $cheque_cashing_date = null
    ){
        $query = DB::table('acc_received AS received')
            ->join('ord_received_orders AS ro', function($join){
                $join->on('received.received_order_id', '=', 'ro.id');
                $join->where([
                    'ro.deleted_at'=>null,
                ]);
            })
            ->leftJoin('usr_users AS undertaker', function($join){
                $join->on('ro.usr_users_id', '=', 'undertaker.id');
                $join->where([
                    'undertaker.deleted_at'=>null,
                ]);
            })

            ->join('acc_received_cheque AS _cheque', function($join){
                $join->on('received.received_method_id', '=', '_cheque.id');
                $join->where([
                    'received.received_method'=>'cheque',
                ]);
            })
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'received_grade', function($join) {
                $join->on('received_grade.primary_id', 'received.all_grades_id');
            })

            ->leftJoin('acc_note_receivable_orders AS nro', function($join){
                $join->on('_cheque.note_receivable_order_id', '=', 'nro.id');
                $join->where([
                    'nro.deleted_at'=>null,
                ]);
            })
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'nro_grade', function($join) {
                $join->on('nro_grade.primary_id', 'nro.net_grade_id');
            })

            ->where([
                //
            ])

            ->selectRaw('
                ro.id AS ro_id,
                ro.source_type AS ro_source_type,
                ro.source_id AS ro_source_id,
                ro.sn AS ro_sn,
                ro.receipt_date AS ro_receipt_date,
                ro.invoice_number AS ro_invoice_number,
                undertaker.name AS ro_undertaker,
                ro.drawee_id AS ro_target_id,
                ro.drawee_name AS ro_target_name,
                ro.drawee_phone AS ro_target_phone,
                ro.drawee_address AS ro_target_address,
                ro.created_at AS ro_created,

                received.id AS received_id,
                received.received_method,
                received.received_method_id,
                received.all_grades_id AS ro_received_grade_id,
                received.tw_price,
                received.taxation,
                received.summary,
                received.note,

                received_grade.code AS ro_received_grade_code,
                received_grade.name AS ro_received_grade_name,

                _cheque.id AS cheque_received_id,
                _cheque.ticket_number AS cheque_ticket_number,
                _cheque.due_date AS cheque_due_date,
                _cheque.banks AS cheque_banks,
                _cheque.accounts AS cheque_accounts,
                _cheque.drawer AS cheque_drawer,
                _cheque.deposited_area_code AS cheque_deposited_area_code,
                _cheque.deposited_area AS cheque_deposited_area,
                _cheque.status_code AS cheque_status_code,
                _cheque.status AS cheque_status,
                _cheque.c_n_date AS cheque_c_n_date,
                _cheque.cashing_date AS cheque_cashing_date,
                _cheque.draw_date AS cheque_draw_date,
                _cheque.note_receivable_order_id AS cheque_note_receivable_order_id,
                _cheque.sn AS cheque_sn,
                _cheque.amt_net AS cheque_amt_net,

                nro.amt_total_net AS nro_amt_total_net,
                nro.net_grade_id AS nro_net_grade_id,
                nro.creator_id AS nro_creator_id,
                nro.affirmant_id AS nro_affirmant_id,

                nro_grade.code AS nro_grade_code,
                nro_grade.name AS nro_grade_name
            ')
            ->orderBy('_cheque.id', 'asc');

        if($received_cheque_id) {
            if(gettype($received_cheque_id) == 'array') {
                $query->whereIn('_cheque.id', $received_cheque_id);
            } else {
                $query->where('_cheque.id', $received_cheque_id);
            }
        }

        if($cheque_status_code !== null){
            if(gettype($cheque_status_code) == 'array') {
                $query->whereIn('_cheque.status_code', $cheque_status_code);
            } else {
                $query->where('_cheque.status_code', $cheque_status_code);
            }
        }

        if($banks){
            $query->where('_cheque.banks', 'like', "%{$banks}%");
                // ->orWhere('append_ro.sn', 'like', "%{$banks}%");
        }

        if($deposited_area_code){
            $query->where('_cheque.deposited_area_code', 'like', "%{$deposited_area_code}%");
                // ->orWhere('append_ro.sn', 'like', "%{$deposited_area_code}%");
        }

        if($ticket_number){
            $query->where('_cheque.ticket_number', 'like', "%{$ticket_number}%");
        }

        if($drawer){
            $query->where('_cheque.drawer', 'like', "%{$drawer}%");
        }

        if($undertaker) {
            if(gettype($undertaker) == 'array') {
                $query->whereIn('undertaker.id', $undertaker);
            } else {
                $query->where('undertaker.id', $undertaker);
            }
        }

        if($received_price) {
            if (gettype($received_price) == 'array' && count($received_price) == 2) {
                $min_price = $received_price[0] ?? null;
                $max_price = $received_price[1] ?? null;
                if($min_price){
                    $query->where('received.tw_price', '>=', $min_price);
                }
                if($max_price){
                    $query->where('received.tw_price', '<=', $max_price);
                }
            }
        }

        if($ro_receipt_date){
            $s_ro_receipt_date = $ro_receipt_date[0] ? date('Y-m-d', strtotime($ro_receipt_date[0])) : null;
            $e_ro_receipt_date = $ro_receipt_date[1] ? date('Y-m-d', strtotime($ro_receipt_date[1] . ' +1 day')) : null;

            if($s_ro_receipt_date){
                $query->where('ro.receipt_date', '>=', $s_ro_receipt_date);
            }
            if($e_ro_receipt_date){
                $query->where('ro.receipt_date', '<', $e_ro_receipt_date);
            }
        }

        if($cheque_c_n_date){
            $s_cheque_c_n_date = $cheque_c_n_date[0] ? date('Y-m-d', strtotime($cheque_c_n_date[0])) : null;
            $e_cheque_c_n_date = $cheque_c_n_date[1] ? date('Y-m-d', strtotime($cheque_c_n_date[1] . ' +1 day')) : null;

            if($s_cheque_c_n_date){
                $query->where('_cheque.c_n_date', '>=', $s_cheque_c_n_date);
            }
            if($e_cheque_c_n_date){
                $query->where('_cheque.c_n_date', '<', $e_cheque_c_n_date);
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


    public static function update_cheque_received_method($request)
    {
        $note_receivable_order = null;

        if(in_array($request['status_code'], ['collection', 'nd'])){
            DB::table('acc_received_cheque')->whereIn('id', $request['cheque_received_id'])->update([
                'status_code'=>$request['status_code'],
                'status'=>$request['status'],
                'c_n_date'=>$request['c_n_date'],
                'updated_at'=>date("Y-m-d H:i:s"),
            ]);

        } else if($request['status_code'] === 'cashed'){
            foreach($request['cheque_received_id'] as $key => $value){
                DB::table('acc_received_cheque')->where('id', $value)->update([
                    'status_code'=>$request['status_code'],
                    'status'=>$request['status'],
                    'amt_net'=>$request['amt_net'][$key],
                    'cashing_date'=>$request['cashing_date'],
                    'updated_at'=>date("Y-m-d H:i:s"),
                ]);
            }

            $note_receivable_order = self::store_income_order($request['cashing_date']);

            foreach($request['cheque_received_id'] as $key => $value){
                DB::table('acc_received_cheque')->where('id', $value)->update([
                    'note_receivable_order_id'=>$note_receivable_order->id,
                    'sn'=>$note_receivable_order->sn,
                    'updated_at'=>date("Y-m-d H:i:s"),
                ]);
            }
        }

        foreach($request['cheque_received_id'] as $key => $value){
            NoteReceivableLog::create_cheque_log($value, $request['status_code']);
        }

        return $note_receivable_order;
    }


    public static function store_income_order($cashing_date)
    {
        $target = self::whereDate('cashing_date', $cashing_date)->first();
        $net = DB::table('acc_received_cheque')->whereDate('cashing_date', $cashing_date)->sum('amt_net');

        if($target){
            $target->update([
                'amt_total_net'=>$net,
                'affirmant_id'=>auth('user')->user() ? auth('user')->user()->id : null,
            ]);

        } else {
            $default_net_grade = ReceivedDefault::where('name', 'cheque')->first() ? ReceivedDefault::where('name', 'cheque')->first()->default_grade_id : 21;

            $target = self::create([
                'sn'=>'ASG' . date('ymd', strtotime($cashing_date)),
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
