<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class RequestOrder extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'acc_request_orders';
    protected $guarded = [];


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


    public static function update_request_order_approval($request = [])
    {
        self::where('id', $request['id'])->update([
            'accountant_id'=>auth('user')->user()->id,
            'posting_date'=>date('Y-m-d H:i:s'),
            'received_order_id'=>$request['received_order_id'],
            'updated_at'=>date('Y-m-d H:i:s'),
        ]);
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
