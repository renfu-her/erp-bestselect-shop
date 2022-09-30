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
        $date = date('Y-m-d', strtotime($closing_date));
        $target = self::whereDate('closing_date', $date)->first();
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
        $date = date('Y-m-d', strtotime($closing_date));

        $ro = ReceivedOrder::whereDate('receipt_date', $date)->get();
        $po = PayingOrder::whereDate('payment_date', $date)->where('append_po_id', null)->get();
        $tv = TransferVoucher::whereDate('created_at', $date)->get();
        $io = IncomeOrder::whereDate('posting_date', $date)->get();

        $counter = $ro->count() + $po->count() + $tv->count() + $io->count();

        return $counter;
    }


    public static function check_day_end_item($day_end_id, $closing_date)
    {
        $date = date('Y-m-d', strtotime($closing_date));

        $ro = ReceivedOrder::whereDate('receipt_date', $date)->get();
        $po = PayingOrder::whereDate('payment_date', $date)->where('append_po_id', null)->get();
        $tv = TransferVoucher::whereDate('created_at', $date)->get();

        $io = IncomeOrder::whereDate('posting_date', $date)->get();

        $remark = null;

        DayEndLog::delete_log($closing_date);

        foreach([$ro, $po, $tv, $io] as $collection){
            foreach($collection as $real_value){
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
                                'day_end_id' => $day_end_id,
                                'closing_date' => $closing_date,
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

                            $source_summary = $o_value->product_title . '（' . $o_value->price . ' * ' . $o_value->qty . '）';
                            $data = [
                                'day_end_id' => $day_end_id,
                                'closing_date' => $closing_date,
                                'source_type' => $real_value->getTable(),
                                'source_id' => $real_value->id,
                                'source_sn' => $real_value->sn,
                                'source_summary' => $source_summary,
                                'debit_price' => null,
                                'credit_price' => $o_value->origin_price,
                                'grade_id' => $t_data->ro_product_grade_id,
                                'grade_code' => $t_data->ro_product_grade_code,
                                'grade_name' => $t_data->ro_product_grade_name,
                            ];
                            DayEndLog::create_day_end_log($data);
                        }
                    }
                    if($t_data->order_dlv_fee){
                        $c_price += $t_data->order_dlv_fee;

                        $source_summary = '物流費用';
                        $data = [
                            'day_end_id' => $day_end_id,
                            'closing_date' => $closing_date,
                            'source_type' => $real_value->getTable(),
                            'source_id' => $real_value->id,
                            'source_sn' => $real_value->sn,
                            'source_summary' => $source_summary,
                            'debit_price' => null,
                            'credit_price' => $t_data->order_dlv_fee,
                            'grade_id' => $t_data->ro_logistics_grade_id,
                            'grade_code' => $t_data->ro_logistics_grade_code,
                            'grade_name' => $t_data->ro_logistics_grade_name,
                        ];
                        DayEndLog::create_day_end_log($data);
                    }
                    if($t_data->order_discount_value > 0){
                        foreach(json_decode($t_data->order_discount) as $d_value){
                            $d_price += $d_value->discount_value;

                            $source_summary = $d_value->title;
                            $data = [
                                'day_end_id' => $day_end_id,
                                'closing_date' => $closing_date,
                                'source_type' => $real_value->getTable(),
                                'source_id' => $real_value->id,
                                'source_sn' => $real_value->sn,
                                'source_summary' => $source_summary,
                                'debit_price' => $d_value->discount_value,
                                'credit_price' => null,
                                'grade_id' => $d_value->discount_grade_id,
                                'grade_code' => $d_value->grade_code,
                                'grade_name' => $d_value->grade_name,
                            ];
                            DayEndLog::create_day_end_log($data);
                        }
                    }
                    $d_c_net = $d_price - $c_price;

                } else if($real_value->getTable() == 'pcs_paying_orders'){
                    $t_data = PayingOrder::paying_order_list(null, $real_value->sn, null, null, null, '1', true)->first();

                    $d_price = 0;
                    $c_price = 0;

                    if($t_data->po_source_type == 'pcs_paying_orders' && $t_data->po_type == 2) {
                        if($t_data->product_items){
                            $tmp_po_sn_array = collect(json_decode($t_data->product_items))->pluck('title')->toArray();
                            $tmp_po = PayingOrder::paying_order_list(null, $tmp_po_sn_array, null, null, null, 'all', true)->get();
                            $tmp_merge = [];

                            foreach($tmp_po as $po_value){
                                $product = [];
                                $logistics = [];
                                $discount = [];

                                if($po_value->product_items){
                                    $product = json_decode($po_value->product_items);
                                }
                                if($po_value->logistics_price <> 0){
                                    $logistics = [(object)[
                                        'product_owner'=>'',
                                        'title'=>$po_value->logistics_summary,
                                        'sku'=>'',
                                        'all_grades_id'=>$po_value->po_logistics_grade_id,
                                        'grade_code'=>$po_value->po_logistics_grade_code,
                                        'grade_name'=>$po_value->po_logistics_grade_name,
                                        'price'=>$po_value->logistics_price,
                                        'num'=>1,
                                        'summary'=>$po_value->logistics_summary,
                                        'memo'=>$po_value->logistics_memo,
                                    ]];
                                }
                                if($po_value->discount_value > 0){
                                    $discount = json_decode(str_replace('"price":"', '"price":"-', $po_value->order_discount));
                                }

                                $tmp_merge = array_merge($tmp_merge, $product, $logistics, $discount);
                            }

                            $t_data->product_items = json_encode($tmp_merge, JSON_UNESCAPED_UNICODE);
                        }
                    }

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
                            $p_value->price > 0 ? $d_price += $p_value->price : $c_price += (-$p_value->price);

                            $source_summary = $p_value->title;

                            if(isset($p_value->product_owner) && $p_value->product_owner != ''){
                                $source_summary = $p_value->title . '（' . ($p_value->num > 1 ? ($p_value->price / $p_value->num) : $p_value->price) . ' * ' . $p_value->num . '）';
                            }

                            $data = [
                                'day_end_id' => $day_end_id,
                                'closing_date' => $closing_date,
                                'source_type' => $real_value->getTable(),
                                'source_id' => $real_value->id,
                                'source_sn' => $real_value->sn,
                                'source_summary' => $source_summary,
                                'debit_price' => $p_value->price > 0 ? $p_value->price : null,
                                'credit_price' => $p_value->price > 0 ? null : (-$p_value->price),
                                'grade_id' => $p_value->all_grades_id,
                                'grade_code' => $p_value->grade_code,
                                'grade_name' => $p_value->grade_name,
                            ];
                            DayEndLog::create_day_end_log($data);
                        }
                    }
                    if($t_data->logistics_price){
                        $d_price += $t_data->logistics_price;

                        $source_summary = $t_data->logistics_summary;
                        $data = [
                            'day_end_id' => $day_end_id,
                            'closing_date' => $closing_date,
                            'source_type' => $real_value->getTable(),
                            'source_id' => $real_value->id,
                            'source_sn' => $real_value->sn,
                            'source_summary' => $source_summary,
                            'debit_price' => $t_data->logistics_price,
                            'credit_price' => null,
                            'grade_id' => $t_data->po_logistics_grade_id,
                            'grade_code' => $t_data->po_logistics_grade_code,
                            'grade_name' => $t_data->po_logistics_grade_name,
                        ];
                        DayEndLog::create_day_end_log($data);
                    }
                    if($t_data->discount_value > 0){
                        foreach(json_decode($t_data->order_discount) as $d_value){
                            $c_price += $d_value->discount_value;

                            $source_summary = $d_value->title;
                            $data = [
                                'day_end_id' => $day_end_id,
                                'closing_date' => $closing_date,
                                'source_type' => $real_value->getTable(),
                                'source_id' => $real_value->id,
                                'source_sn' => $real_value->sn,
                                'source_summary' => $source_summary,
                                'debit_price' => null,
                                'credit_price' => $d_value->discount_value,
                                'grade_id' => $d_value->discount_grade_id,
                                'grade_code' => $d_value->grade_code,
                                'grade_name' => $d_value->grade_name,
                            ];
                            DayEndLog::create_day_end_log($data);
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

                } else if($real_value->getTable() == 'acc_income_orders'){
                    $income = [
                        [
                            'price'=>$real_value->amt_total_service_fee,
                            'desc'=>"信用卡手續費",
                            'grade_id'=>$real_value->service_fee_grade_id,
                            'grade_code'=>AllGrade::find($real_value->service_fee_grade_id)->eachGrade->code,
                            'grade_name'=>AllGrade::find($real_value->service_fee_grade_id)->eachGrade->name,
                        ], [
                            'price'=>$real_value->amt_total_net,
                            'desc'=>"信用卡入款",
                            'grade_id'=>$real_value->net_grade_id,
                            'grade_code'=>AllGrade::find($real_value->net_grade_id)->eachGrade->code,
                            'grade_name'=>AllGrade::find($real_value->net_grade_id)->eachGrade->name,
                        ]
                    ];

                    $data_list = IncomeOrder::get_credit_card_received_list([], 2, $real_value->sn)->get();

                    $d_price = $real_value->amt_total_service_fee + $real_value->amt_total_net;
                    $c_price = $data_list->sum('credit_card_price');
                    $d_c_net = $d_price - $c_price;

                    foreach($income as $value){
                        if($value['price'] > 0){
                            $data = [
                                'day_end_id'=>$day_end_id,
                                'closing_date'=>$closing_date,
                                'source_type' => $real_value->getTable(),
                                'source_id' => $real_value->id,
                                'source_sn' => $real_value->sn,
                                'source_summary' => $value['desc'],
                                'debit_price' => $value['price'],
                                'credit_price' => null,
                                'grade_id' => $value['grade_id'],
                                'grade_code' => $value['grade_code'],
                                'grade_name' => $value['grade_name'],
                            ];

                            DayEndLog::create_day_end_log($data);
                        }
                    }
                }

                $day_end_item = DayEndItem::where([
                    'source_type' => $real_value->getTable(),
                    'source_id' => $real_value->id,
                    'source_sn' => $real_value->sn,
                ])->first();

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

            if($target){
                $link = ReceivedOrder::received_order_link($target->source_type, $target->source_id);
            }

        } else if($source_type == 'pcs_paying_orders') {
            $target = PayingOrder::find($source_id);

            if($target){
                $link = PayingOrder::paying_order_link($target->source_type, $target->source_id, $target->source_sub_id, $target->type);
            }

        } else if($source_type == 'acc_transfer_voucher') {
            $link = route('cms.transfer_voucher.show', ['id' => $source_id]);

        } else if($source_type == 'acc_income_orders') {
            $link = route('cms.credit_manager.income-detail', ['id' => $source_id]);

        }

        return $link;
    }


    public static function match_day_end_status($closing_date, $sn)
    {
        $date = date('Y-m-d', strtotime($closing_date));

        $target = self::whereDate('closing_date', $date)->first();
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
        if($target && $target_item){
            $o_target = self::find($target_item->day_end_id);

            if($o_target){
                if($target->id != $o_target->id){
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
