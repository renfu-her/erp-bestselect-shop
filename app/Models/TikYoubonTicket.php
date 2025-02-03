<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TikYoubonTicket extends Model
{
    protected $table = 'tik_youbon_tickets';

    protected $fillable = [
        'delivery_id',
        'event_item_id',
        'rcv_depot_id',
        'productnumber',
        'prodid',
        'batchid',
        'ordernumber',
        'price',
        'start_time',
        'exp_time',
        'use_time',
        'back_time'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'exp_time' => 'datetime',
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
}
