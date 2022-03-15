<?php

namespace App\Enums\Payable;

use BenSampo\Enum\Enum;

/**
 * @method static static Paid() 付款
 * @method static static Cashed() 兌現
 * @method static static OnHold() 押票
 * @method static static Returned() 退票
 * @method static static Issued() 開票
 */
final class ChequeStatus extends Enum
{
    const Paid = 1;
    const Cashed = 2;
    const OnHold = 3;
    const Returned = 4;
    const Issued = 5;

    public static function getDescription($value): string
    {
        switch ($value) {
            case self::Paid:
                return '付款';
            case self::Cashed:
                return '兌現';
            case self::OnHold:
                return '押票';
            case self::Returned:
                return '退票';
            case self::Issued:
                return '開票';
            default:
                return parent::getDescription($value);
        }
    }
}
