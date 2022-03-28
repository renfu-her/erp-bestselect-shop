<?php

namespace App\Enums\Purchase;

use BenSampo\Enum\Enum;

class LogEvent extends Enum
{
    //採購、入庫、付款
    const purchase = 'purchase';
    const pcs_pay = 'pcs_pay';
    const consignment = 'consignment'; //寄倉

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::purchase:
                $result = '採購';
                break;
            case self::pcs_pay:
                $result = '採購付款';
                break;
            case self::consignment:
                $result = '寄倉';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
