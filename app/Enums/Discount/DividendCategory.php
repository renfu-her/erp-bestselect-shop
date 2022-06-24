<?php

namespace App\Enums\Discount;

use BenSampo\Enum\Enum;

/**
 * 點數來源類型
 * @method static static Order() 購物訂單
 * @method static static Cyberbiz() Cyberbiz匯入
 */
final class DividendCategory extends Enum
{
    const Order = 'order';
    const Cyberbiz = 'cyberbiz';

    public static function getDescription($value): string
    {
        switch ($value) {
            case self::Order:
                $result = '購物訂單';
                break;
            case self::Cyberbiz:
                $result = '喜鴻購物2.0';
                break;
            default:
                return parent::getDescription($value);
        }
        return $result;
    }

}
