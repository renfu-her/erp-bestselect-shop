<?php

namespace App\Enums\Discount;

use BenSampo\Enum\Enum;

final class DividendCategory extends Enum
{
    const Order = 'order';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::Order:
                $result = '購物訂單';
                break;
        }
        return $result;
    }

}
