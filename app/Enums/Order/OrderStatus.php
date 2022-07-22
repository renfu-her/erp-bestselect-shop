<?php

namespace App\Enums\Order;

use BenSampo\Enum\Enum;

final class OrderStatus extends Enum
{
    const Add = 'add';
    const Canceled = 'canceled';
    const Closed = 'closed';
    const Paided = 'paided';
    const Unpaid = 'unpaid';

    const Unbalance = 'unbalance';
    const Received = 'received';
    const ReturnProcessing = 'return_processing';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::Add:
                $result = '建立';
                break;
            case self::Canceled:
                $result = '取消';
                break;
            case self::Closed:
                $result = '結案';
                break;
            case self::Paided:
                $result = '已付款';//or 已收款
                break;
            case self::Unpaid:
                $result = '尚未付款';
                break;

            case self::Unbalance:
                $result = '收款單未平';
                break;
            case self::Received:
                $result = '已入款';
                break;
            case self::ReturnProcessing:
                $result = '退貨處理中';
                break;
        }
        return $result;
    }
}
