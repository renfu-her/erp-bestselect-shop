<?php

namespace App\Enums\Payable;

use BenSampo\Enum\Enum;

/**
 * @method static static Unpaid() 未付款
 * @method static static Paid() 已付款
 */
final class PayableStatus extends Enum
{
    const Unpaid = 1;
    const Paid = 2;

    public static function getDescription($value): string
    {
        switch ($value) {
            case self::Unpaid:
                return '未付款';
            case self::Paid:
                return '已付款';
            default:
                return parent::getDescription($value);
        }
    }
}
