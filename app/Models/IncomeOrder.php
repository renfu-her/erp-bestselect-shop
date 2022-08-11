<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

class IncomeOrder extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'acc_income_orders';
    protected $guarded = [];


    public static function get_credit_card_received_list(
        $received_credit_id = null,
        $credit_status_code = null,
        $sn = null,

        $bank_id = null,
        $area_id = null,
        $card_type_id = null,
        $card_number = null,
        $card_owner = null,
        $authamt_price = null,
        $mode = null,
        $checkout_date = null,
        $posting_date = null
    ){
        $query = DB::table('acc_received AS received')
            ->join('ord_received_orders as ro', function($join){
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
            ->join('acc_received_credit AS _credit', function($join){
                $join->on('received.received_method_id', '=', '_credit.id');
                $join->where([
                    'received.received_method'=>'credit_card',
                ]);
            })
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'all_grade', function($join) {
                $join->on('all_grade.primary_id', 'received.all_grades_id');
            })

            ->leftJoin(DB::raw('(
                SELECT crd_percent_bank_credit.percent,
                    crd_credit_cards.title AS crd_credit_card_type,
                    crd_banks.id AS bank_id,
                    crd_banks.title AS bank_name,
                    crd_banks.bank_code AS bank_code,
                    crd_banks.installment AS bank_installment
                FROM crd_percent_bank_credit
                LEFT JOIN crd_banks ON crd_banks.id = crd_percent_bank_credit.bank_fk
                LEFT JOIN crd_credit_cards ON crd_credit_cards.id = crd_percent_bank_credit.credit_fk
                GROUP BY crd_banks.installment
                ) AS v_table_1'), function ($join){
                    $join->on('v_table_1.bank_installment', '=', '_credit.installment');
                    // $join->where([
                    //     'v_table_1.crd_credit_card_type'=>'_credit.card_type',
                    // ]);
            })

            ->leftJoin(DB::raw('(
                SELECT
                    io.id AS io_id,
                    io.sn AS io_sn,
                    io.amt_total_service_fee AS io_amt_total_service_fee,
                    io.amt_total_net AS io_amt_total_net,
                    io.service_fee_grade_id,
                    io.net_grade_id,
                    io.posting_date AS io_posting_date,
                    creator.name AS creator_name,
                    affirmant.name AS affirmant_name
                FROM acc_income_orders AS io
                LEFT JOIN usr_users AS creator ON creator.id = io.creator_id
                LEFT JOIN usr_users AS affirmant ON affirmant.id = io.affirmant_id
                ) AS v_table_2'), function ($join){
                    $join->on('v_table_2.io_id', '=', '_credit.income_order_id');
            })
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'fee_grade', function($join){
                $join->on('fee_grade.primary_id', '=', 'v_table_2.service_fee_grade_id');
            })
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'net_grade', function($join){
                $join->on('net_grade.primary_id', '=', 'v_table_2.net_grade_id');
            })
            ->leftJoin('acc_income_orders AS io', function($join){
                $join->on('_credit.income_order_id', '=', 'io.id');
            })

            ->where([
                //
            ])
            ->selectRaw('
                ro.id AS ro_id,
                ro.source_type AS ro_source_type,
                ro.source_id AS ro_source_id,
                ro.sn AS ro_sn,
                undertaker.name AS ro_undertaker,

                received.id AS received_id,
                received.received_method,
                received.received_method_id,
                received.all_grades_id AS ro_received_grade_id,
                received.tw_price,
                received.taxation,
                received.summary,
                received.note,


                _credit.id AS credit_card_received_id,
                _credit.cardnumber AS credit_card_number,
                _credit.authamt AS credit_card_price,
                _credit.checkout_date AS credit_card_checkout_date,
                _credit.card_type_code AS credit_card_type_code,
                _credit.card_type AS credit_card_type,
                _credit.card_owner_name AS credit_card_owner_name,
                _credit.authcode AS credit_card_authcode,
                _credit.checkout_area_code AS credit_card_area_code,
                _credit.checkout_area AS credit_card_area,
                _credit.installment AS credit_card_installment,
                _credit.status_code AS credit_card_status_code,

                _credit.amt_percent AS credit_card_amt_percent,
                _credit.amt_service_fee AS credit_card_amt_service_fee,
                _credit.amt_net AS credit_card_amt_net,
                _credit.transaction_date AS credit_card_transaction_date,
                _credit.posting_date AS credit_card_posting_date,

                _credit.card_nat AS credit_card_nat,
                _credit.checkout_mode AS credit_card_checkout_mode,


                all_grade.code AS ro_received_grade_code,
                all_grade.name AS ro_received_grade_name,


                v_table_1.percent AS bank_percent,
                v_table_1.bank_id AS bank_id,
                v_table_1.bank_name AS bank_name,


                v_table_2.io_id AS io_id,
                v_table_2.io_sn AS io_sn,
                v_table_2.io_amt_total_service_fee AS io_amt_total_service_fee,
                v_table_2.io_amt_total_net AS io_amt_total_net,
                fee_grade.code AS io_fee_grade_code,
                fee_grade.name AS io_fee_grade_name,
                net_grade.code AS io_net_grade_code,
                net_grade.name AS io_net_grade_name,
                v_table_2.io_posting_date AS io_posting_date,
                v_table_2.creator_name AS io_creator_name,
                v_table_2.affirmant_name AS io_affirmant_name
            ')
            ->orderBy('_credit.id', 'asc');

        if($received_credit_id) {
            if(gettype($received_credit_id) == 'array') {
                $query->whereIn('_credit.id', $received_credit_id);
            } else {
                $query->where('_credit.id', $received_credit_id);
            }
        }

        if($credit_status_code !== null){
            $query->where('_credit.status_code', $credit_status_code);
        }

        if($sn){
            $query->where('ro.sn', 'like', "%{$sn}%")
                ->orWhere('v_table_2.io_sn', 'like', "%{$sn}%");
        }

        if($bank_id) {
            if(gettype($bank_id) == 'array') {
                $query->whereIn('v_table_1.bank_id', $bank_id);
            } else {
                $query->where('v_table_1.bank_id', $bank_id);
            }
        }

        if($area_id) {
            if(gettype($area_id) == 'array') {
                $query->whereIn('_credit.checkout_area_code', $area_id);
            } else {
                $query->where('_credit.checkout_area_code', $area_id);
            }
        }

        if($card_type_id) {
            if(gettype($card_type_id) == 'array') {
                $query->whereIn('_credit.card_type_code', $card_type_id);
            } else {
                $query->where('_credit.card_type_code', $card_type_id);
            }
        }

        if($card_number) {
            $query->where(function ($query) use ($card_number) {
                $query->where('_credit.cardnumber', 'like', "%{$card_number}%");
            });
        }

        if($card_owner) {
            $query->where(function ($query) use ($card_owner) {
                $query->where('_credit.card_owner_name', 'like', "%{$card_owner}%");
            });
        }

        if($authamt_price) {
            if (gettype($authamt_price) == 'array' && count($authamt_price) == 2) {
                $min_price = $authamt_price[0] ?? null;
                $max_price = $authamt_price[1] ?? null;
                if($min_price){
                    $query->where('_credit.authamt', '>=', $min_price);
                }
                if($max_price){
                    $query->where('_credit.authamt', '<=', $max_price);
                }
            }
        }

        if($mode){
            $query->where('_credit.checkout_mode', $mode);
        }

        if ($checkout_date) {
            $s_checkout_date = $checkout_date[0] ? date('Y-m-d', strtotime($checkout_date[0])) : null;
            $e_checkout_date = $checkout_date[1] ? date('Y-m-d', strtotime($checkout_date[1] . ' +1 day')) : null;

            if($s_checkout_date){
                $query->where('_credit.checkout_date', '>=', $s_checkout_date);
            }
            if($e_checkout_date){
                $query->where('_credit.checkout_date', '<', $e_checkout_date);
            }
        }

        if ($posting_date) {
            $s_posting_date = $posting_date[0] ? date('Y-m-d', strtotime($posting_date[0])) : null;
            $e_posting_date = $posting_date[1] ? date('Y-m-d', strtotime($posting_date[1] . ' +1 day')) : null;

            if($s_posting_date){
                $query->where('_credit.posting_date', '>=', $s_posting_date);
            }
            if($e_posting_date){
                $query->where('_credit.posting_date', '<', $e_posting_date);
            }
        }

        return $query;
    }


    public static function store_income_order($posting_date)
    {
        $target = self::whereDate('posting_date', $posting_date)->first();
        $service_fee = DB::table('acc_received_credit')->whereDate('posting_date', $posting_date)->sum('amt_service_fee');
        $net = DB::table('acc_received_credit')->whereDate('posting_date', $posting_date)->sum('amt_net');

        if($target){
            $target->update([
                'amt_total_service_fee'=>$service_fee,
                'amt_total_net'=>$net,
                'affirmant_id'=>auth('user')->user() ? auth('user')->user()->id : null,
            ]);

        } else {
            $default_service_fee_grade = ReceivedDefault::where('name', 'credit_card_service_fee')->first() ? ReceivedDefault::where('name', 'credit_card_service_fee')->first()->default_grade_id : 96;
            $default_net_grade = ReceivedDefault::where('name', 'credit_card_net')->first() ? ReceivedDefault::where('name', 'credit_card_net')->first()->default_grade_id : 113;

            $target = self::create([
                'sn'=>'CSG' . date('ymd', strtotime($posting_date)) . 'A',
                'amt_total_service_fee'=>$service_fee,
                'amt_total_net'=>$net,
                'service_fee_grade_id'=>$default_service_fee_grade,
                'net_grade_id'=>$default_net_grade,
                'posting_date'=>$posting_date,
                'creator_id'=>auth('user')->user() ? auth('user')->user()->id : null,
                'affirmant_id'=>auth('user')->user() ? auth('user')->user()->id : null,
            ]);
        }

        return $target;
    }
}
