<?php

namespace App\Enums\SaleChannel;

use BenSampo\Enum\Enum;

class UseCoupon extends Enum
{
    const no = 0;
    const yes = 1;

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::no:
                $result = '不可';
                break;
            case self::yes:
                $result = '可';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
