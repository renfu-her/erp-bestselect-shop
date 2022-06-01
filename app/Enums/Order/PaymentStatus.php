<?php

namespace App\Enums\Order;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class PaymentStatus extends Enum
{
    const Unpaid = 'unpaid';
    const Paided = 'paided';

    const Unbalance = 'unbalance';
    const Received = 'received';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::Paided:
                $result = '已付款';
                break;
            case self::Unpaid:
                $result = '尚未付款';// or 等待付款
                break;

            case self::Unbalance:
                $result = '未完成收款';
                break;
            case self::Received:
                $result = '已收到款項';
                break;
        }
        return $result;
    }
}
