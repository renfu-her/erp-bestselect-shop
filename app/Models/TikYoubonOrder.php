<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TikYoubonOrder extends Model
{
    protected $table = 'tik_youbon_orders';

    protected $fillable = [
        'delivery_id',
        'custbillno',
        'billno',
        'borrowno',
        'billdate',
        'batchid',
        'statcode',
        'weburl',
    ];

    protected $casts = [
        'billdate' => 'datetime',
    ];

    // 與出貨單的關聯
    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    public static function createData($delivery_id, $custbillno, $billno, $borrowno, $billdate, $statcode, $weburl)
    {
        return self::create([
            'delivery_id' => $delivery_id,
            'custbillno' => $custbillno,
            'billno' => $billno,
            'borrowno' => $borrowno,
            'billdate' => $billdate,
            'statcode' => $statcode,
            'weburl' => $weburl,
        ]);
    }
}
