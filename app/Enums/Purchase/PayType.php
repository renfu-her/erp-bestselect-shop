<?php

namespace App\Enums\Purchase;

use BenSampo\Enum\Enum;

class PayType extends Enum
{
    //採購付款方式 0:先付(訂金) / 1:先付(一次付清) / 2:貨到付款
    const deposit = 0;
    const pay_in_full = 1;
    const cash_on_delivery = 2;

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::deposit:
                $result = '先付(訂金)';
                break;
            case self::pay_in_full:
                $result = '先付(一次付清)';
                break;
            case self::cash_on_delivery:
                $result = '貨到付款';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
