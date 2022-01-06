<?php

namespace App\Enums\Supplier;

use BenSampo\Enum\Enum;

class Payment extends Enum
{
    //廠商付款方式 0:現金 1:支票 2:匯款 3:外幣 4:應付帳款 5:其他
    const cash = 0;
    const cheque = 1;
    const remittance = 2;
    const foreign_currency = 3;
    const accounts_payable = 4;
    const other = 5;

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::cash:
                $result = '現金';
                break;
            case self::cheque:
                $result = '支票';
                break;
            case self::remittance:
                $result = '匯款';
                break;
            case self::foreign_currency:
                $result = '外幣';
                break;
            case self::accounts_payable:
                $result = '應付帳款';
                break;
            case self::other:
                $result = '其他';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
