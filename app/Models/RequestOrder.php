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
        $client = null,
        $request_sn = null,
        $source_sn = null,
        $request_price = null,
        $request_posting_date = null,
        $check_posting = 'all'
    ){
        $query = DB::table('acc_request_orders AS request_o')
            ->leftJoin('ord_received_orders AS ro', function ($join) {
                $join->on('request_o.received_order_id', '=', 'ro.id');
                $join->where([
                    'ro.deleted_at'=>null,
                ]);
            })
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'grade', function($join) {
                $join->on('grade.primary_id', 'request_o.request_grade_id');
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

            ->select(
                'request_o.id AS request_o_id',
                'request_o.sn AS request_o_sn',
                'request_o.price AS request_o_price',
                'request_o.qty AS request_o_qty',
                'request_o.total_price AS request_o_total_price',
                'request_o.summary AS request_o_summary',
                'request_o.memo AS request_o_memo',
                'request_o.taxation AS request_o_taxation',
                'request_o.client_id AS request_o_client_id',
                'request_o.client_name AS request_o_client_name',
                'request_o.client_phone AS request_o_client_phone',
                'request_o.client_address AS request_o_client_address',

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

                'grade.code AS grade_code',
                'grade.name AS grade_name',
                'creator.name AS creator_name',
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
            'price' =>$request['price'],
            'qty' =>$request['qty'],
            'total_price' =>$request['price'] * $request['qty'],
            'tw_dollar' => null,
            'rate' =>$request['rate'],
            'currency_id' =>$request['currency_id'],
            'request_grade_id' =>$request['request_grade_id'],
            'summary' =>$request['summary'],
            'memo' =>$request['memo'],
            'taxation' =>1,
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
                'posting_date'=>date('Y-m-d H:i:s'),
                'received_order_id'=>$request['received_order_id'],
                'updated_at'=>date('Y-m-d H:i:s'),
            ]);
        }
    }


    public static function update_request_order($request = [])
    {
        self::where('id', $request['id'])->update([
            'request_grade_id'=>$request['request_grade_id'],
            'summary'=>$request['summary'],
            'memo'=>$request['memo'],
            'taxation'=>$request['taxation'],
            'updated_at'=>date('Y-m-d H:i:s'),
        ]);
    }
}
