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

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::Paided:
                $result = '已付款';
                break;
            case self::Unpaid:
                $result = '尚未付款';
                break;
        }
        return $result;
    }
}
