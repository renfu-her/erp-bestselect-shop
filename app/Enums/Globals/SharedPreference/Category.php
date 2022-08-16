<?php

namespace App\Enums\Globals\SharedPreference;

use BenSampo\Enum\Enum;

/**
 * @method static static mail()
 */
final class Category extends Enum
{
    const mail = 'mail';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::mail:
                $result = '通知信';
                break;
        }

        return $result;
    }
}
