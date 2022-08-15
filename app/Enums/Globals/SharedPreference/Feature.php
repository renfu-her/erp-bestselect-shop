<?php

namespace App\Enums\Globals\SharedPreference;

use BenSampo\Enum\Enum;

/**
 * @method static static mail_order_established()
 * @method static static mail_order_paid()
 * @method static static mail_order_shipped()
 */
final class Feature extends Enum
{
    const mail_order_established = 'mail_order_established';
    const mail_order_paid = 'mail_order_paid';
    const mail_order_shipped = 'mail_order_shipped';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::mail_order_established:
                $result = '訂單成立';
                break;
            case self::mail_order_paid:
                $result = '已付款';
                break;
            case self::mail_order_shipped:
                $result = '已出貨';
                break;
        }

        return $result;
    }
}
