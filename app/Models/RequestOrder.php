<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

class RequestOrder extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'acc_request_orders';
    protected $guarded = [];


    public static function request_order_list(
        $request_o_id = null,
        $client = null,
        $request_sn = null,
        $source_sn = null,
        $request_price = null,
        $request_posting_date = null,
        $check_posting = 'all'
    ){
        $sq = '
            SELECT
                acc_all_grades.id,
                CASE
                    WHEN acc_first_grade.code IS NOT NULL THEN acc_first_grade.code
                    WHEN acc_second_grade.code IS NOT NULL THEN acc_second_grade.code
                    WHEN acc_third_grade.code IS NOT NULL THEN acc_third_grade.code
                    WHEN acc_fourth_grade.code IS NOT NULL THEN acc_fourth_grade.code
                    ELSE ""
                END AS code,
                CASE
                    WHEN acc_first_grade.name IS NOT NULL THEN acc_first_grade.name
                    WHEN acc_second_grade.name IS NOT NULL THEN acc_second_grade.name
                    WHEN acc_third_grade.name IS NOT NULL THEN acc_third_grade.name
                    WHEN acc_fourth_grade.name IS NOT NULL THEN acc_fourth_grade.name
                    ELSE ""
                END AS name
            FROM acc_all_grades
            LEFT JOIN acc_first_grade ON acc_all_grades.grade_id = acc_first_grade.id AND acc_all_grades.grade_type = "App\\\Models\\\FirstGrade"
            LEFT JOIN acc_second_grade ON acc_all_grades.grade_id = acc_second_grade.id AND acc_all_grades.grade_type = "App\\\Models\\\SecondGrade"
            LEFT JOIN acc_third_grade ON acc_all_grades.grade_id = acc_third_grade.id AND acc_all_grades.grade_type = "App\\\Models\\\ThirdGrade"
            LEFT JOIN acc_fourth_grade ON acc_all_grades.grade_id = acc_fourth_grade.id AND acc_all_grades.grade_type = "App\\\Models\\\FourthGrade"
            GROUP BY acc_all_grades.id
        ';

        $query = DB::table('acc_request_orders AS request_o')
            ->leftJoin(DB::raw('(
                SELECT
                    request_o_item.request_order_id,
                    CONCAT(\'[\', GROUP_CONCAT(\'{
                            "id":"\', request_o_item.id, \'",
                            "price":"\', request_o_item.price, \'",
                            "qty":"\', request_o_item.qty, \'",
                            "total_price":"\', request_o_item.total_price, \'",
                            "tw_dollar":"\', COALESCE(request_o_item.tw_dollar, ""), \'",
                            "rate":"\', COALESCE(request_o_item.rate, ""), \'",
                            "currency_id":"\', COALESCE(acc_currency.id, ""), \'",
                            "currency_name":"\', COALESCE(acc_currency.name, ""), \'",
                            "grade_id":"\', request_o_item.grade_id, \'",
                            "grade_code":"\', COALESCE(grade.code, ""), \'",
                            "grade_name":"\', COALESCE(grade.name, ""), \'",
                            "summary":"\', COALESCE(request_o_item.summary, ""), \'",
                            "memo":"\', COALESCE(request_o_item.memo, ""), \'",
                            "ro_note":"\', COALESCE(request_o_item.ro_note, ""), \'",
                            "taxation":"\', request_o_item.taxation,\'"
                        }\' ORDER BY request_o_item.id), \']\') AS items
                FROM acc_request_order_items AS request_o_item
                LEFT JOIN (' . $sq . ') AS grade ON grade.id = request_o_item.grade_id
                LEFT JOIN acc_currency ON acc_currency.id = request_o_item.currency_id
                GROUP BY request_o_item.request_order_id
                ) AS request_items_table'), function ($join){
                    $join->on('request_items_table.request_order_id', '=', 'request_o.id');
            })
            ->leftJoin('ord_received_orders AS ro', function ($join) {
                $join->on('request_o.received_order_id', '=', 'ro.id');
                $join->where([
                    'ro.deleted_at'=>null,
                ]);
            })
            ->leftJoin('usr_users AS creator', function($join){
                $join->on('request_o.creator_id', '=', 'creator.id');
                $join->where([
                    'creator.deleted_at'=>null,
                    'ro.deleted_at'=>null,
                ]);
            })
            ->leftJoin('usr_users AS accountant', function($join){
                $join->on('request_o.accountant_id', '=', 'accountant.id');
                $join->where([
                    'accountant.deleted_at'=>null,
                    'ro.deleted_at'=>null,
                ]);
            })

            ->whereNull('request_o.deleted_at')

            ->where(function ($q) use ($request_o_id) {
                if($request_o_id){
                    if(gettype($request_o_id) == 'array') {
                        $q->whereIn('request_o.id', $request_o_id);
                    } else {
                        $q->where('request_o.id', $request_o_id);
                    }
                }
            })

            ->select(
                'request_o.id AS request_o_id',
                'request_o.sn AS request_o_sn',
                'request_o.price AS request_o_price',
                'request_o.client_id AS request_o_client_id',
                'request_o.client_name AS request_o_client_name',
                'request_o.client_phone AS request_o_client_phone',
                'request_o.client_address AS request_o_client_address',
                'request_o.posting_date AS request_o_posting_date',
                'request_o.created_at AS request_o_created_at',

                'request_items_table.items AS request_o_items',

                'ro.id AS ro_id',
                'ro.sn AS ro_sn',
                'ro.price AS ro_price',// 收款單金額(應收)
                'ro.logistics_grade_id AS ro_logistics_grade_id',
                'ro.product_grade_id AS ro_product_grade_id',
                'ro.receipt_date AS ro_receipt_date',// 收款單入帳審核日期
                'ro.balance_date AS ro_balance_date',
                'ro.drawee_id AS ro_target_id',
                'ro.drawee_name AS ro_target_name',
                'ro.drawee_phone AS ro_target_phone',
                'ro.drawee_address AS ro_target_address',

                'creator.name AS creator_name',
                'creator.department AS creator_department',
                'accountant.name AS accountant_name'
            );

        if ($client) {
            if (gettype($client) == 'array') {
                $query->where([
                    'request_o.client_id'=>$client['id'],
                ])->where('request_o.client_name', 'LIKE', "%{$client['name']}%");
            }
        }

        if ($request_sn) {
            $query->where(function ($query) use ($request_sn) {
                $query->where('request_o.sn', 'LIKE', "%{$request_sn}%");
            });
        }

        if ($source_sn) {
            $query->where(function ($query) use ($source_sn) {
                $query->where('ro.sn', 'LIKE', "%{$source_sn}%");
                    // ->orWhere('request_o.sn', 'LIKE', "%{$source_sn}%");
            });
        }

        if ($request_price) {
            if (gettype($request_price) == 'array' && count($request_price) == 2) {
                $min_price = $request_price[0] ?? null;
                $max_price = $request_price[1] ?? null;
                if($min_price){
                    $query->where('request_o.total_price', '>=', $min_price);
                }
                if($max_price){
                    $query->where('request_o.total_price', '<=', $max_price);
                }
            }
        }

        if ($request_posting_date) {
            $s_posting_date = $request_posting_date[0] ? date('Y-m-d', strtotime($request_posting_date[0])) : null;
            $e_posting_date = $request_posting_date[1] ? date('Y-m-d', strtotime($request_posting_date[1] . ' +1 day')) : null;

            if($s_posting_date){
                $query->where('request_o.posting_date', '>=', $s_posting_date);
            }
            if($e_posting_date){
                $query->where('request_o.posting_date', '<', $e_posting_date);
            }
        }

        if ($check_posting == 'all') {
            //
        } else if ($check_posting == 0) {
            $query->whereNull('request_o.posting_date');
        } else if($check_posting == 1){
            $query->whereNotNull('request_o.posting_date');
        }

        return $query->orderBy('request_o.created_at', 'DESC');
    }


    public static function create_request_order($request = [])
    {
        $result = self::create([
            'sn'=> 'KSG' . str_pad((self::get()->count()) + 1, 9, '0', STR_PAD_LEFT),
            'price' =>null,
            'client_id' =>$request['client_id'],
            'client_name' =>$request['client_name'],
            'client_phone' =>$request['client_phone'],
            'client_address' =>$request['client_address'],
            'creator_id' =>auth('user')->user() ? auth('user')->user()->id : null,
            'accountant_id' => null,
            'posting_date' => null,
            'received_order_id' => null,
        ]);

        return $result;
    }


    public static function update_request_order_approval($request = [], $clear = false)
    {
        if($clear){
            self::where('id', $request['id'])->update([
                'accountant_id'=>null,
                'posting_date'=>null,
                'received_order_id'=>null,
                'updated_at'=>date('Y-m-d H:i:s'),
            ]);

        } else {
            self::where('id', $request['id'])->update([
                'accountant_id'=>auth('user')->user()->id,
                'posting_date'=>date('Y-m-d'),
                'received_order_id'=>$request['received_order_id'],
                'updated_at'=>date('Y-m-d H:i:s'),
            ]);
        }
    }
}
