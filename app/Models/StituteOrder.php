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
        $client = null,
        $so_sn = null,
        $source_sn = null,
        $stitute_price = null,
        $stitute_payment_date = null,
        $check_payment = 'all'
    ){
        $query = DB::table('acc_stitute_orders AS so')
            ->leftJoin('pcs_paying_orders AS po', function ($join) {
                $join->on('so.pay_order_id', '=', 'po.id');
                $join->where([
                    'po.deleted_at'=>null,
                ]);
            })
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'grade', function($join) {
                $join->on('grade.primary_id', 'so.stitute_grade_id');
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

            ->select(
                'so.id AS so_id',
                'so.sn AS so_sn',
                'so.price AS so_price',
                'so.qty AS so_qty',
                'so.total_price AS so_total_price',
                'so.summary AS so_summary',
                'so.memo AS so_memo',
                'so.taxation AS so_taxation',
                'so.client_id AS so_client_id',
                'so.client_name AS so_client_name',
                'so.client_phone AS so_client_phone',
                'so.client_address AS so_client_address',

                'po.id AS po_id',
                'po.sn AS po_sn',
                'po.price AS po_price',// 付款單金額(應付)
                'po.logistics_grade_id AS po_logistics_grade_id',
                'po.product_grade_id AS po_product_grade_id',
                'po.balance_date AS po_balance_date',
                'po.payee_id AS po_target_id',
                'po.payee_name AS po_target_name',
                'po.payee_phone AS po_target_phone',
                'po.payee_address AS po_target_address',

                'grade.code AS grade_code',
                'grade.name AS grade_name',
                'creator.name AS creator_name',
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
                    $query->where('so.total_price', '>=', $min_price);
                }
                if($max_price){
                    $query->where('so.total_price', '<=', $max_price);
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
            'price' =>$request['price'],
            'qty' =>$request['qty'],
            'total_price' =>$request['price'] * $request['qty'],
            'tw_dollar' => null,
            'rate' =>$request['rate'],
            'currency_id' =>$request['currency_id'],
            'stitute_grade_id' =>$request['stitute_grade_id'],
            'summary' =>$request['summary'],
            'memo' =>$request['memo'],
            'taxation' =>1,
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
