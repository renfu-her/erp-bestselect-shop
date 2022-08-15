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
    const Paid = 'paid';
    const Cashed = 'cashed';
    const OnHold = 'onhold';
    const Returned = 'returned';
    const Issued = 'issued';

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


    public static function get_key_value()
    {
        $result = [];

        foreach (self::asArray() as $data) {
            $result[$data] = self::getDescription($data);
        }

        return $result;
    }
}
