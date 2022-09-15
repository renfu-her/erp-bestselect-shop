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
    const M_b2e = 'm_b2e';
    const M_b2c = 'm_b2c';
    const M_b2b = 'm_b2b';

    public static function getDescription($value): string
    {
        switch ($value) {
            case self::Order:
                $result = '購物訂單';
                break;
            case self::Cyberbiz:
                $result = '喜鴻購物2.0';
                break;
            case self::M_b2e:
                $result = '旅遊企業紅利折扣';
                break;
            case self::M_b2c:
                $result = '旅遊會員紅利折扣';
                break;
            case self::M_b2b:
                $result = '旅遊同業紅利折扣';
                break;
            default:
                return parent::getDescription($value);
        }
        return $result;
    }

}
