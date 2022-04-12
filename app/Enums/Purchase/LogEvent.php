<?php

namespace App\Enums\Purchase;

use BenSampo\Enum\Enum;

class LogEvent extends Enum
{
    //採購付款
    const pcs_pay = 'pcs_pay';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::pcs_pay:
                $result = '採購付款';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
