<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

use App\Enums\Supplier\Payment;
use App\Enums\Received\ReceivedMethod;


class DayEnd extends Model
{
    use HasFactory;

    protected $table = 'acc_day_end_orders';
    protected $guarded = [];


    public static function date_list($closing_date = null)
    {
        $query = DB::table('acc_day_end_orders AS deo')
            ->leftJoin(DB::raw('(
                SELECT
                    day_end_id,
                    CONCAT(\'[\', GROUP_CONCAT(\'{
                        "sn":"\', sn, \'",
                        "source_type":"\', source_type, \'",
                        "source_id":"\', source_id, \'",
                        "source_sn":"\', source_sn, \'",
                        "d_c_net":"\', d_c_net,\'"
                    }\' ORDER BY (CASE
                            WHEN source_sn REGEXP "^MSG" THEN 0
                            WHEN source_sn REGEXP "^ZSG" THEN 2
                            ELSE 1
                        END) ASC, sn ASC), \']\') AS items
                FROM acc_day_end_items AS deo_item
                GROUP BY day_end_id
                ) AS deo_items_table'), function ($join){
                    $join->on('deo_items_table.day_end_id', '=', 'deo.id');
            })
            ->leftJoin('usr_users AS creator', function($join){
                $join->on('deo.creator_id', '=', 'creator.id');
                $join->where([
                    'creator.deleted_at'=>null,
                ]);
            })
            ->leftJoin('usr_users AS clearinger', function($join){
                $join->on('deo.clearinger_id', '=', 'clearinger.id');
                $join->where([
                    'clearinger.deleted_at'=>null,
                ]);
            })

            ->where(function ($q) use ($closing_date) {
                if($closing_date){
                    if(gettype($closing_date) == 'array') {
                        $q->whereIn(DB::raw('DATE(deo.closing_date)'), $closing_date);
                    } else {
                        $q->whereDate(DB::raw('deo.closing_date'), $closing_date);
                    }
                }
            })

            ->select(
                'deo.id AS deo_id',
                'deo.closing_date AS deo_closing_date',
                'deo.p_date AS deo_p_date',
                'deo.times AS deo_times',
                'deo.count AS deo_count',
                'deo.status AS deo_status',
                'deo.remark AS deo_remark',

                'deo_items_table.items AS deo_items',

                'creator.name AS creator_name',
                'clearinger.name AS clearinger_name'
            );

        return $query;
    }


    public static function match_day_end_order($closing_date)
    {
        $target = self::whereDate('closing_date', $closing_date)->first();

        $count = self::counter($closing_date);

        if($target){
            $target->update([
                'p_date'=>date('Y-m-d H:i:s'),
                'times'=> DB::raw('times+1'),
                'status'=>null,
                'count'=>$count,
                'clearinger_id'=>auth('user')->user() ? auth('user')->user()->id : null,
                'updated_at'=>date('Y-m-d H:i:s'),
            ]);

        } else {
            $target = self::create([
                'closing_date'=>$closing_date,
                'p_date'=>date('Y-m-d H:i:s'),
                'times'=>1,
                'count'=>$count,
                'status'=>null,
                'remark'=>null,
                'creator_id'=>auth('user')->user() ? auth('user')->user()->id : null,
                'clearinger_id'=>auth('user')->user() ? auth('user')->user()->id : null,
            ]);
        }

        $remark = self::check_day_end_item($target->id, $closing_date);
        $target->update([
            'remark'=>$remark,
        ]);
        $target = self::find($target->id);

        return $target;
    }


    public static function counter($closing_date)
    {
        $po = PayingOrder::whereDate('payment_date', $closing_date)->get();
        $ro = ReceivedOrder::whereDate('receipt_date', $closing_date)->get();
        $tv = TransferVoucher::whereDate('created_at', $closing_date)->get();
        $counter = $po->count() + $ro->count() + $tv->count();

        return $counter;
    }


    public static function check_day_end_item($day_end_id, $closing_date)
    {
        $po = PayingOrder::whereDate('payment_date', $closing_date)->get();
        $ro = ReceivedOrder::whereDate('receipt_date', $closing_date)->get();
        $tv = TransferVoucher::whereDate('created_at', $closing_date)->get();
        $remark = null;

        DayEndLog::delete_log($closing_date);

        foreach([$po, $ro, $tv] as $collection){
            foreach($collection as $real_value){
                $day_end_item = DayEndItem::where([
                    'source_type' => $real_value->getTable(),
                    'source_id' => $real_value->id,
                    'source_sn' => $real_value->sn,
                ])->first();

                if($real_value->getTable() == 'ord_received_orders'){
                    $t_data = ReceivedOrder::received_order_list(null, $real_value->sn)->first();

                    $d_price = 0;
                    $c_price = 0;
                    if($t_data->received_list){
                        foreach(json_decode($t_data->received_list) as $r_value){
                            $d_price += $r_value->tw_price;

                            $method_name = ReceivedMethod::getDescription($r_value->received_method);
                            if($method_name == '現金'){
                                $suffix = $t_data->ro_target_name;

                            } else if($method_name == '支票'){
                                $method_name = '應收票據';//支票
                                $suffix = $r_value->cheque_ticket_number . '（' . date('Y-m-d', strtotime($r_value->cheque_due_date)) . '）';

                            } else if($method_name == '匯款'){
                                $suffix = $r_value->grade_name . ' - ' . $r_value->remit_memo;

                            } else if($method_name == '信用卡'){
                                $suffix = $r_value->credit_card_number . '（' . $r_value->credit_card_owner . '）';
                            } else {
                                $suffix = $r_value->grade_name;
                            }
                            $source_summary = $method_name . ' ' . $suffix . ' ' . $r_value->summary;

                            $data = [
                                'day_end_id'=>$day_end_id,
                                'closing_date'=>$closing_date,
                                'source_type' => $real_value->getTable(),
                                'source_id' => $real_value->id,
                                'source_sn' => $real_value->sn,
                                'source_summary' => $source_summary,
                                'debit_price' => $r_value->tw_price,
                                'credit_price' => null,
                                'grade_id' => $r_value->all_grades_id,
                                'grade_code' => $r_value->grade_code,
                                'grade_name' => $r_value->grade_name,
                            ];
                            DayEndLog::create_day_end_log($data);
                        }
                    }
                    if($t_data->order_items){
                        foreach(json_decode($t_data->order_items) as $o_value){
                            $c_price += $o_value->origin_price;
                        }
                    }
                    if($t_data->order_dlv_fee){
                        $c_price += $t_data->order_dlv_fee;
                    }
                    if($t_data->order_discount_value > 0){
                        foreach(json_decode($t_data->order_discount) as $d_value){
                            $c_price -= $d_value->discount_value;
                        }
                    }
                    $d_c_net = $d_price - $c_price;

                } else if($real_value->getTable() == 'pcs_paying_orders'){
                    $t_data = PayingOrder::paying_order_list(null, $real_value->sn)->first();

                    $d_price = 0;
                    $c_price = 0;
                    if($t_data->payable_list){
                        foreach(json_decode($t_data->payable_list) as $pay_v){
                            $c_price += $pay_v->tw_price;

                            $method_name = Payment::getDescription($pay_v->acc_income_type_fk);
                            if($method_name == '現金'){
                                $suffix = $t_data->po_target_name;

                            } else if($method_name == '支票'){
                                $method_name = '應付票據';//支票
                                $suffix = $pay_v->cheque_ticket_number . '（' . date('Y-m-d', strtotime($pay_v->cheque_due_date)) . '）';

                            } else if($method_name == '匯款'){
                                $suffix = $pay_v->grade_name;

                            } else {
                                $suffix = $pay_v->grade_name;
                            }
                            $source_summary = $method_name . ' ' . $suffix . ' ' . $pay_v->summary;

                            $data = [
                                'day_end_id'=>$day_end_id,
                                'closing_date'=>$closing_date,
                                'source_type' => $real_value->getTable(),
                                'source_id' => $real_value->id,
                                'source_sn' => $real_value->sn,
                                'source_summary' => $source_summary,
                                'debit_price' => null,
                                'credit_price' => $pay_v->tw_price,
                                'grade_id' => $pay_v->all_grades_id,
                                'grade_code' => $pay_v->grade_code,
                                'grade_name' => $pay_v->grade_name,
                            ];
                            DayEndLog::create_day_end_log($data);
                        }
                    }
                    if($t_data->product_items){
                        foreach(json_decode($t_data->product_items) as $p_value){
                            $d_price += $p_value->price;
                        }
                    }
                    if($t_data->logistics_price){
                        $d_price += $t_data->logistics_price;
                    }
                    if($t_data->discount_value > 0){
                        foreach(json_decode($t_data->order_discount) as $d_value){
                            $d_price -= $d_value->discount_value;
                        }
                    }
                    $d_c_net = $d_price - $c_price;

                } else if($real_value->getTable() == 'acc_transfer_voucher'){
                    $t_data = TransferVoucher::voucher_list(null, null, $real_value->sn)->first();

                    $d_price = $t_data->tv_debit_price;
                    $c_price = $t_data->tv_credit_price;
                    $d_c_net = $d_price - $c_price;

                    if($t_data->tv_items){
                        foreach(json_decode($t_data->tv_items) as $tv_value){
                            $data = [
                                'day_end_id'=>$day_end_id,
                                'closing_date'=>$closing_date,
                                'source_type' => $real_value->getTable(),
                                'source_id' => $real_value->id,
                                'source_sn' => $real_value->sn,
                                'source_summary' => $tv_value->summary,
                                'debit_price' => $tv_value->debit_credit_code == 'debit' ? $tv_value->final_price : null,
                                'credit_price' => $tv_value->debit_credit_code == 'credit' ? $tv_value->final_price : null,
                                'grade_id' => $tv_value->grade_id,
                                'grade_code' => $tv_value->grade_code,
                                'grade_name' => $tv_value->grade_name,
                            ];

                            DayEndLog::create_day_end_log($data);
                        }
                    }
                }

                if($day_end_item){
                    $day_end_item->update([
                        'day_end_id' => $day_end_id,
                        'd_c_net' => $d_c_net,
                    ]);

                } else {
                    $day_end_item = DayEndItem::create([
                        'day_end_id' => $day_end_id,
                        'sn' => date('ymd', strtotime($closing_date)) . str_pad( count(DayEndItem::where('day_end_id', '=', $day_end_id)->get() ) + 1, 4, '0', STR_PAD_LEFT),
                        'source_type' => $real_value->getTable(),
                        'source_id' => $real_value->id,
                        'source_sn' => $real_value->sn,
                        'd_c_net' => $d_c_net,
                    ]);
                }

                if($d_c_net){
                    $remark .= $real_value->sn . ':' . number_format($d_c_net, 2) . '|';
                }
            }
        }

        return str_replace('|', "\r\n", rtrim($remark, '|'));
    }


    public static function source_path($source_type, $source_id)
    {
        $link = 'javascript:void(0);';

        if($source_type == 'ord_received_orders') {
            $target = ReceivedOrder::find($source_id);

            if($target->source_type == 'ord_orders'){
                $link = route('cms.collection_received.receipt', ['id' => $target->source_id]);

            } else if($target->source_type == 'csn_orders'){
                $link = route('cms.ar_csnorder.receipt', ['id' => $target->source_id]);

            } else if($target->source_type == 'ord_received_orders'){
                $link = route('cms.account_received.ro-receipt', ['id' => $target->source_id]);

            } else if($target->source_type == 'acc_request_orders'){
                $link = route('cms.request.ro-receipt', ['id' => $target->source_id]);
            }

        } else if($source_type == 'pcs_paying_orders') {
            $target = PayingOrder::find($source_id);

            if($target->source_type == 'pcs_purchase'){
                $link = route('cms.purchase.view-pay-order', ['id' => $target->source_id, 'type' => $target->type]);

            } else if($target->source_type == 'ord_orders' && $target->source_sub_id != null){
                $link = route('cms.order.logistic-po', ['id' => $target->source_id, 'sid' => $target->source_sub_id]);

            } else if($target->source_type == 'acc_stitute_orders'){
                $link = route('cms.stitute.po-show', ['id' => $target->source_id]);

            } else if($target->source_type == 'ord_orders' && $target->source_sub_id == null){
                $link = route('cms.order.return-pay-order', ['id' => $target->source_id]);

            } else if($target->source_type == 'dlv_delivery'){
                $link = route('cms.delivery.return-pay-order', ['id' => $target->source_id]);

            } else if($target->source_type == 'pcs_paying_orders'){
                $link = route('cms.accounts_payable.po-show', ['id' => $target->source_id]);
            }

        } else if($source_type == 'acc_transfer_voucher') {
            $link = route('cms.transfer_voucher.show', ['id' => $source_id]);

        }

        return $link;
    }


    public static function match_day_end_status($closing_date, $sn)
    {
        $target = self::whereDate('closing_date', $closing_date)->first();
        if($target){
            $status = $target->status ?? '';

            if(mb_substr($sn, 0, 1) == 'I'){
                if (! strstr($status, '*')) {
                    $str_arr = str_split($status);
                    $str_arr[] = '*';
                    sort($str_arr);
                    $status = implode('', $str_arr);
                }

            } else if(mb_substr($sn, 0, 1) == 'M'){
                if (! strstr($status, 'M')) {
                    $str_arr = str_split($status);
                    $str_arr[] = 'M';
                    sort($str_arr);
                    $status = implode('', $str_arr);
                }

            } else if(mb_substr($sn, 0, 1) == 'Z'){
                if (! strstr($status, 'Z')) {
                    $str_arr = str_split($status);
                    $str_arr[] = 'Z';
                    sort($str_arr);
                    $status = implode('', $str_arr);
                }
            }

            if($status){
                $target->update([
                    'status'=>$status,
                    'updated_at'=>date('Y-m-d H:i:s'),
                ]);
            }
        }

        $target_item = DayEndItem::where('source_sn', $sn)->first();
        if($target_item){
            $o_target = self::find($target_item->day_end_id);

            if($o_target && $target->id != $o_target->id){
                $o_status = $o_target->status ?? '';

                if(mb_substr($sn, 1, 1) == 'I'){
                    if (! strstr($o_status, '*')) {
                        $str_arr = str_split($o_status);
                        $str_arr[] = '*';
                        sort($str_arr);
                        $o_status = implode('', $str_arr);
                    }

                } else if(mb_substr($sn, 1, 1) == 'M'){
                    if (! strstr($o_status, 'M')) {
                        $str_arr = str_split($o_status);
                        $str_arr[] = 'M';
                        sort($str_arr);
                        $o_status = implode('', $str_arr);
                    }

                } else if(mb_substr($sn, 1, 1) == 'Z'){
                    if (! strstr($o_status, 'Z')) {
                        $str_arr = str_split($o_status);
                        $str_arr[] = 'Z';
                        sort($str_arr);
                        $o_status = implode('', $str_arr);
                    }
                }

                if($o_status){
                    $o_target->update([
                        'status'=>$o_status,
                        'updated_at'=>date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }
    }


    public static function match_day_end_detail(&$data = [], $source_type, $source_id, $source_sn, $de_sn)
    {
        if($source_type == 'ord_received_orders'){
            $t_data = ReceivedOrder::received_order_list(null, $source_sn)->first();

            if($t_data->received_list){
                foreach(json_decode($t_data->received_list) as $r_value){
                    $method_name = ReceivedMethod::getDescription($r_value->received_method);
                    $method_code = null;
                    $suffix = null;
                    if($method_name == '現金'){
                        $method_code = 'cash';
                        $suffix = $t_data->ro_target_name;

                    } else if($method_name == '支票'){
                        $method_name = '應收票據';//支票
                        $method_code = 'note_receivable';//cheque
                        $suffix = $r_value->cheque_ticket_number . '（' . date('Y-m-d', strtotime($r_value->cheque_due_date)) . '）';

                    } else if($method_name == '匯款'){
                        $method_code = 'remit';
                        $suffix = $r_value->grade_name . ' - ' . $r_value->remit_memo;

                    } else if($method_name == '信用卡'){
                        $method_code = 'credit_card';
                        $suffix = $r_value->credit_card_number . '（' . $r_value->credit_card_owner . '）';
                    }

                    if($method_code){
                        $data[$method_code][] = (object) [
                            'summary'=>$method_name . ' ' . $suffix . ' ' . $r_value->summary,
                            'grade_name'=>$r_value->grade_name,
                            'd_price'=>$r_value->tw_price,
                            'c_price'=>null,
                            'source_sn'=>$source_sn,
                            'source_link'=>self::source_path($source_type, $source_id),
                            'sn'=>$de_sn,
                        ];
                    }
                }
            }

        } else if($source_type == 'pcs_paying_orders'){
            $t_data = PayingOrder::paying_order_list(null, $source_sn)->first();

            if($t_data->payable_list){
                foreach(json_decode($t_data->payable_list) as $pay_v){
                    $method_name = Payment::getDescription($pay_v->acc_income_type_fk);
                    $method_code = null;
                    if($method_name == '現金'){
                        $method_code = 'cash';
                        $suffix = $t_data->po_target_name;

                    } else if($method_name == '支票'){
                        $method_name = '應付票據';//支票
                        $method_code = 'note_payable';//cheque
                        $suffix = $pay_v->cheque_ticket_number . '（' . date('Y-m-d', strtotime($pay_v->cheque_due_date)) . '）';

                    } else if($method_name == '匯款'){
                        $method_code = 'remit';
                        $suffix = $pay_v->grade_name;
                    }

                    if($method_code){
                        $data[$method_code][] = (object) [
                            'summary'=>$method_name . ' ' . $suffix . ' ' . $pay_v->summary,
                            'grade_name'=>$pay_v->grade_name,
                            'd_price'=>null,
                            'c_price'=>$pay_v->tw_price,
                            'source_sn'=>$source_sn,
                            'source_link'=>self::source_path($source_type, $source_id),
                            'sn'=>$de_sn,
                        ];
                    }
                }
            }

        } else if($source_type == 'acc_transfer_voucher'){
            $t_data = TransferVoucher::voucher_list($source_id)->first();

            if($t_data->tv_items){
                foreach(json_decode($t_data->tv_items) as $tv_value){
                    $method_code = null;

                    if($tv_value->debit_credit_code == 'debit'){
                        $grade = ReceivedDefault::whereIn('name', ['cash', 'cheque', 'remit', 'credit_card'])->where('default_grade_id', $tv_value->grade_id)->first();
                        $method_name = ReceivedMethod::getDescription($grade->name ?? null);
                        if($method_name == '現金'){
                            $method_code = 'cash';
                        } else if($method_name == '支票'){
                            $method_name = '應收票據';//支票
                            $method_code = 'note_receivable';//cheque
                        } else if($method_name == '匯款'){
                            $method_code = 'remit';
                        } else if($method_name == '信用卡'){
                            $method_code = 'credit_card';
                        }

                    } else if($tv_value->debit_credit_code == 'credit'){
                        $grade = PayableDefault::whereIn('name', ['cash', 'cheque', 'remit'])->where('default_grade_id', $tv_value->grade_id)->first();
                        $method_name = Payment::getDescription($grade->name ?? null);
                        if($method_name == '現金'){
                            $method_code = 'cash';
                        } else if($method_name == '支票'){
                            $method_name = '應付票據';//支票
                            $method_code = 'note_payable';//cheque
                        } else if($method_name == '匯款'){
                            $method_code = 'remit';
                        }
                    }

                    if($method_code){
                        $data[$method_code][] = (object) [
                            'summary'=>$method_name . ' ' . $tv_value->summary,
                            'grade_name'=>$tv_value->grade_name,
                            'd_price'=>$tv_value->debit_credit_code == 'debit' ? $tv_value->final_price : null,
                            'c_price'=>$tv_value->debit_credit_code == 'credit' ? $tv_value->final_price : null,
                            'source_sn'=>$source_sn,
                            'source_link'=>self::source_path($source_type, $source_id),
                            'sn'=>$de_sn,
                        ];
                    }
                }
            }
        }
    }
}
