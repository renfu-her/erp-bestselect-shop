<?php

namespace App\Enums\Supplier;

use BenSampo\Enum\Enum;

/**
 * 廠商付款方式
 * @method static static Cash() 現金
 * @method static static Cheque() 支票
 * @method static static Remittance() 匯款
 * @method static static ForeignCurrency() 外幣
 * @method static static AccountsPayable() 應付帳款
 * @method static static Other() 其他
 */
class Payment extends Enum
{
    const Cash = 0;
    const Cheque = 1;
    const Remittance = 2;
    const ForeignCurrency = 3;
    const AccountsPayable = 4;
    const Other = 5;

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::Cash:
                $result = '現金';
                break;
            case self::Cheque:
                $result = '支票';
                break;
            case self::Remittance:
                $result = '匯款';
                break;
            case self::ForeignCurrency:
                $result = '外幣';
                break;
            case self::AccountsPayable:
                $result = '應付帳款';
                break;
            case self::Other:
                $result = '其他';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
