<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TikAutoOrderErrorLog extends Model
{
    protected $table = 'tik_auto_order_error_logs';

    protected $fillable = [
        'order_id',
        'order_sn',
        'note',
        'error_message',
        'error_trace'
    ];

    /**
     * 與訂單的關聯
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * 建立錯誤記錄
     *
     * @param int|null $orderId 訂單ID
     * @param string|null $orderSn 訂單編號
     * @param string $note 錯誤備註
     * @param string $errorMessage 錯誤訊息
     * @param string|null $errorTrace 錯誤追蹤資訊
     * @return self
     */
    public static function createLog($orderId, $orderSn, $note, $errorMessage, $errorTrace = null)
    {
        return self::create([
            'order_id' => $orderId,
            'order_sn' => $orderSn,
            'note' => $note,
            'error_message' => $errorMessage,
            'error_trace' => $errorTrace
        ]);
    }

    public static function getDataQuery($order_sn = null) {
        $query = TikAutoOrderErrorLog::query()
            ->select([
                'tik_auto_order_error_logs.id',
                'tik_auto_order_error_logs.order_sn as sn',
                'tik_auto_order_error_logs.error_message',
                'tik_auto_order_error_logs.note',
                'tik_auto_order_error_logs.created_at'
            ]);

        if ($order_sn) {
            $query->where('order_sn', 'like', '%' . $order_sn . '%');
        }
        $query->orderBy('id', 'desc');

        return $query;
    }
}
