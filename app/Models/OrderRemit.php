<?php

namespace App\Models;

use App\Helpers\IttmsDBB;
use App\Mail\CustomerOrderRemit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/* 訂單匯款資料*/
class OrderRemit extends Model
{
    use HasFactory;
    protected $table = 'ord_remits';
    protected $guarded = [];
    public $timestamps = true;
    /*
    protected $casts = [
        'remit_date'  => 'datetime:Y-m-d H:i:s',
        'created_at'  => 'datetime:Y-m-d H:i:s',
        'updated_at'  => 'datetime:Y-m-d H:i:s',
    ];
    */
    public static function createRemit($order_id, $name, $price, $remit_date, $bank_code)
    {
        $order = Order::where('id', $order_id)->first();
        if (null == $order) {
            return ['success' => '0', 'error_msg' => '無此訂單'];
        }
        $remit = self::where('order_id', '=', $order_id)->first();
        if (null != $remit) {
            return ['success' => '0', 'error_msg' => '已有資料'];
        }

        return IttmsDBB::transaction(function () use ($order_id, $name, $price, $remit_date, $bank_code) {
            $id = self::create([
                'order_id'=> $order_id,
                'name'=> $name,
                'price'=> $price,
                'remit_date'=> $remit_date,
                'bank_code'=> $bank_code,
            ]);

            //正式機才做發送給會計
            if(env('APP_ENV') == 'rel'){
                $email = 'eve1717@hotmail.com.tw';
                $order = Order::where('id', '=', $order_id)->first();
                $data = [ 'sn' => $order->sn ?? ''];
                Mail::to($email)->queue(new CustomerOrderRemit($data));
            }

            return ['success' => '1', 'id' => $id->id];
        });
    }

    public static function updateRemit($order_id, $name, $price, $remit_date, $bank_code)
    {
        $remit = self::where('order_id', '=', $order_id)->first();
        if (null == $remit->first()) {
            return ['success' => '0', 'error_msg' => '無此資料'];
        }

        return IttmsDBB::transaction(function () use ($remit, $order_id, $name, $price, $remit_date, $bank_code) {
            $remit->update([
                'name'=> $name,
                'price'=> $price,
                'remit_date'=> $remit_date,
                'bank_code'=> $bank_code,
            ]);

            return ['success' => '1', 'data' => $remit];
        });
    }

    public static function getData($order_id) {
        $query = self::where('order_id', '=', $order_id);
        return $query;
    }
}
