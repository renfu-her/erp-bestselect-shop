<?php

namespace App\Enums\Order;

use BenSampo\Enum\Enum;

final class OrderStatus extends Enum
{
    const Add = 'add';
    const Canceled = 'canceled';
    const Closed = 'closed';
    const Paided = 'paided';
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
                $result = '已付款';
        }
        return $result;
    }
}
