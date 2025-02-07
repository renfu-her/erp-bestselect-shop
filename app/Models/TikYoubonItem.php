<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TikYoubonItem extends Model
{
    protected $table = 'tik_youbon_items';

    protected $fillable = [
        'delivery_id',
        'event_item_id',
        'rcv_depot_id',
        'productnumber',
        'prodid',
        'batchid',
        'ordernumber',
        'price',
        'use_time',
        'back_time'
    ];

    protected $casts = [
        'use_time' => 'datetime',
        'back_time' => 'datetime'
    ];

    // 與出貨單的關聯
    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    // 與收貨倉的關聯
    public function rcvDepot()
    {
        return $this->belongsTo(ReceiveDepot::class, 'rcv_depot_id');
    }

    public static function createData($delivery_id, $event_item_id, $rcv_depot_id, $productnumber, $prodid, $batchid, $ordernumber, $price)
    {
        return self::create([
            'delivery_id' => $delivery_id,
            'event_item_id' => $event_item_id,
            'rcv_depot_id' => $rcv_depot_id,
            'productnumber' => $productnumber,
            'prodid' => $prodid,
            'batchid' => $batchid,
            'ordernumber' => $ordernumber,
            'price' => $price,
        ]);
    }
}
