<?php

namespace App\Enums\Discount;

use App\Enums\Helper;

/**
 * 點數來源類型
 * @method static static Order() 購物訂單
 * @method static static Cyberbiz() Cyberbiz匯入
 */
final class DividendCategory extends Helper
{
    const Order = 'order';
    const Cyberbiz = 'cyberbiz';
    const M_b2e = 'm_b2e';
    const M_b2c = 'm_b2c';
    const M_b2b = 'm_b2b';
    const M_b2ec = 'm_b2ec';
    const Employee_gift = 'employee_gift';
    const Guide = 'guide';
    const Other = 'other';

    public static function getDescription($value): string
    {
        switch ($value) {
            case self::Order:
                $result = '購物訂單';
                break;
            case self::Cyberbiz:
                $result = 'CYBERBIZ';
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
            case self::M_b2ec:
                $result = '企業會員個人紅利折扣';
                break;
            case self::Employee_gift:
                $result = '旅遊員工購物金';
                break;
            case self::Guide:
                $result = '導遊領隊購物金';
                break;
            case self::Other:
                $result = '其他';
                break;
            default:
                return parent::getDescription($value);
        }
        return $result;
    }
}
