<?php

namespace App\Enums\Customer;

use BenSampo\Enum\Enum;

final class Newsletter extends Enum
{
    const un_subscribe = 0;
    const subscribe = 1;

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::un_subscribe:
                $result = '不訂閱';
                break;
            case self::subscribe:
                $result = '訂閱';
                break;
        }

        return $result;
    }

}
