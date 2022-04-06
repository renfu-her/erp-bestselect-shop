<?php

namespace App\Enums\Customer;

use BenSampo\Enum\Enum;

/**
 * 對應到 table usr_identity的 code欄位
 * @method static static customer() 消費者
 * @method static static employee() 喜鴻員工
 * @method static static company() 企業會員
// * @method static static agent() 同業
// * @method static static group_buyer() 團購
 */
class Identity extends Enum
{
    const customer = 'customer';
    const employee = 'employee';
    const company = 'company';
//    const agent = 'agent';
//    const group_buyer = 'group_buyer';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::customer:
                $result = '消費者';
                break;
            case self::employee:
                $result = '喜鴻員工';
                break;
            case self::company:
                $result = '企業會員';
                break;
//            case self::agent:
//                $result = '同業';
//                break;
//            case self::group_buyer:
//                $result = '團購';
//                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
