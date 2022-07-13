<?php

namespace App\Enums\Order;

use BenSampo\Enum\Enum;

final class CarrierType extends Enum
{
    const mobile = 0;
    const certificate = 1;
    const member = 2;

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::mobile:
                $result = '手機條碼載具';
                break;
            case self::certificate:
                $result = '自然人憑證條碼載具';
                break;
            case self::member:
                $result = '會員載具';
                break;
        }
        return $result;
    }
}
