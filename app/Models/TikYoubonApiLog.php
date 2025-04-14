<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TikYoubonApiLog extends Model
{
    protected $table = 'tik_youbon_api_logs';

    protected $fillable = [
        'delivery_id',
        'request',
        'response'
    ];

    // 與出貨單的關聯
    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    public static function createData($deliveryId, $request, $response)
    {
        return self::create([
            'delivery_id' => $deliveryId,
            'request' => $request,
            'response' => $response
        ]);
    }
}
