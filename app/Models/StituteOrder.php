<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class StituteOrder extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'acc_stitute_orders';
    protected $guarded = [];


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


    public static function update_stitute_order_approval($request = [])
    {
        self::where('id', $request['id'])->update([
            'accountant_id'=>auth('user')->user()->id,
            'payment_date'=>date('Y-m-d H:i:s'),
            'pay_order_id'=>$request['pay_order_id'],
            'updated_at'=>date('Y-m-d H:i:s'),
        ]);
    }
}
