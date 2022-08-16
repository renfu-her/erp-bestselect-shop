<?php

namespace App\Enums\Globals\SharedPreference;

use BenSampo\Enum\Enum;

/**
 * @method static static mail_order()
 */
final class Event extends Enum
{
    const mail_order = 'mail_order';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::mail_order:
                $result = '訂單';
                break;
        }

        return $result;
    }
}
