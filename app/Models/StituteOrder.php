<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

class StituteOrder extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'acc_stitute_orders';
    protected $guarded = [];


    public static function stitute_order_list(
        $so_id = null,
        $client = null,
        $so_sn = null,
        $source_sn = null,
        $stitute_price = null,
        $stitute_payment_date = null,
        $check_payment = 'all'
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
        ';

        $query = DB::table('acc_stitute_orders AS so')
            ->leftJoin(DB::raw('(
                SELECT
                    so_item.stitute_order_id,
                    CONCAT(\'[\', GROUP_CONCAT(\'{
                            "id":"\', so_item.id, \'",
                            "price":"\', so_item.price, \'",
                            "qty":"\', so_item.qty, \'",
                            "total_price":"\', so_item.total_price, \'",
                            "tw_dollar":"\', COALESCE(so_item.tw_dollar, ""), \'",
                            "rate":"\', COALESCE(so_item.rate, ""), \'",
                            "currency_id":"\', COALESCE(acc_currency.id, ""), \'",
                            "currency_name":"\', COALESCE(acc_currency.name, ""), \'",
                            "grade_id":"\', so_item.grade_id, \'",
                            "grade_code":"\', COALESCE(grade.code, ""), \'",
                            "grade_name":"\', COALESCE(grade.name, ""), \'",
                            "summary":"\', COALESCE(so_item.summary, ""), \'",
                            "memo":"\', COALESCE(so_item.memo, ""), \'",
                            "taxation":"\', so_item.taxation,\'"
                        }\' ORDER BY so_item.id), \']\') AS items
                FROM acc_stitute_order_items AS so_item
                LEFT JOIN (' . $sq . ') AS grade ON grade.id = so_item.grade_id
                LEFT JOIN acc_currency ON acc_currency.id = so_item.currency_id
                GROUP BY so_item.stitute_order_id
                ) AS stitute_items_table'), function ($join){
                    $join->on('stitute_items_table.stitute_order_id', '=', 'so.id');
            })
            ->leftJoin('pcs_paying_orders AS po', function ($join) {
                $join->on('so.pay_order_id', '=', 'po.id');
                $join->where([
                    'po.deleted_at'=>null,
                ]);
            })
            ->leftJoin('usr_users AS creator', function($join){
                $join->on('so.creator_id', '=', 'creator.id');
                $join->where([
                    'creator.deleted_at'=>null,
                    'po.deleted_at'=>null,
                ]);
            })
            ->leftJoin('usr_users AS accountant', function($join){
                $join->on('so.accountant_id', '=', 'accountant.id');
                $join->where([
                    'accountant.deleted_at'=>null,
                    'po.deleted_at'=>null,
                ]);
            })

            ->whereNull('so.deleted_at')

            ->where(function ($q) use ($so_id) {
                if($so_id){
                    if(gettype($so_id) == 'array') {
                        $q->whereIn('so.id', $so_id);
                    } else {
                        $q->where('so.id', $so_id);
                    }
                }
            })

            ->select(
                'so.id AS so_id',
                'so.sn AS so_sn',
                'so.price AS so_price',
                'so.client_id AS so_client_id',
                'so.client_name AS so_client_name',
                'so.client_phone AS so_client_phone',
                'so.client_address AS so_client_address',
                'so.payment_date AS so_payment_date',
                'so.created_at AS so_created_at',

                'stitute_items_table.items AS so_items',

                'po.id AS po_id',
                'po.sn AS po_sn',
                'po.price AS po_price',// 付款單金額(應付)
                'po.logistics_grade_id AS po_logistics_grade_id',
                'po.product_grade_id AS po_product_grade_id',
                'po.balance_date AS po_balance_date',
                'po.payment_date AS po_payment_date',
                'po.payee_id AS po_target_id',
                'po.payee_name AS po_target_name',
                'po.payee_phone AS po_target_phone',
                'po.payee_address AS po_target_address',

                'creator.name AS creator_name',
                'creator.department AS creator_department',
                'accountant.name AS accountant_name'
            );

        if ($client) {
            if (gettype($client) == 'array') {
                $query->where([
                    'so.client_id'=>$client['id'],
                ])->where('so.client_name', 'LIKE', "%{$client['name']}%");
            }
        }

        if ($so_sn) {
            $query->where(function ($query) use ($so_sn) {
                $query->where('so.sn', 'LIKE', "%{$so_sn}%");
            });
        }

        if ($source_sn) {
            $query->where(function ($query) use ($source_sn) {
                $query->where('po.sn', 'LIKE', "%{$source_sn}%");
                    // ->orWhere('so.sn', 'LIKE', "%{$source_sn}%");
            });
        }

        if ($stitute_price) {
            if (gettype($stitute_price) == 'array' && count($stitute_price) == 2) {
                $min_price = $stitute_price[0] ?? null;
                $max_price = $stitute_price[1] ?? null;
                if($min_price){
                    $query->where('so.price', '>=', $min_price);
                }
                if($max_price){
                    $query->where('so.price', '<=', $max_price);
                }
            }
        }

        if ($stitute_payment_date) {
            $s_payment_date = $stitute_payment_date[0] ? date('Y-m-d', strtotime($stitute_payment_date[0])) : null;
            $e_payment_date = $stitute_payment_date[1] ? date('Y-m-d', strtotime($stitute_payment_date[1] . ' +1 day')) : null;

            if($s_payment_date){
                $query->where('so.payment_date', '>=', $s_payment_date);
            }
            if($e_payment_date){
                $query->where('so.payment_date', '<', $e_payment_date);
            }
        }

        if ($check_payment == 'all') {
            //
        } else if ($check_payment == 0) {
            $query->whereNull('so.payment_date');
        } else if($check_payment == 1){
            $query->whereNotNull('so.payment_date');
        }

        return $query->orderBy('so.created_at', 'DESC');
    }


    public static function create_stitute_order($request = [])
    {
        $result = self::create([
            'sn'=> 'PSG' . str_pad((self::get()->count()) + 1, 9, '0', STR_PAD_LEFT),
            'price' =>null,
            'client_id' =>$request['client_id'],
            'client_name' =>$request['client_name'],
            'client_phone' =>$request['client_phone'],
            'client_address' =>$request['client_address'],
            'creator_id' =>auth('user')->user() ? auth('user')->user()->id : null,
            'accountant_id' => null,
            'payment_date' => null,
            'pay_order_id' => null,
        ]);

        return $result;
    }


    public static function update_stitute_order_approval($request = [], $clear = false)
    {
        if($clear){
            self::where('id', $request['id'])->update([
                'accountant_id'=>null,
                'payment_date'=>null,
                'pay_order_id'=>null,
                'updated_at'=>date('Y-m-d H:i:s'),
            ]);

        } else {
            self::where('id', $request['id'])->update([
                'accountant_id'=>auth('user')->user()->id,
                'payment_date'=>date('Y-m-d'),
                'pay_order_id'=>$request['pay_order_id'],
                'updated_at'=>date('Y-m-d H:i:s'),
            ]);
        }
    }
}
