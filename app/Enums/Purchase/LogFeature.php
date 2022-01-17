<?php

namespace App\Enums\Purchase;

use BenSampo\Enum\Enum;

class LogFeature extends Enum
{
    //採購、入庫、付款
    const purchase = 'purchase';
    const inbound = 'inbound';
    const pay = 'pay';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::purchase:
                $result = '採購';
                break;
            case self::inbound:
                $result = '入庫';
                break;
            case self::pay:
                $result = '付款';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
