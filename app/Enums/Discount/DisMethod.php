<?php

namespace App\Enums\Discount;

use App\Enums\Helper;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class DisMethod extends Helper
{
    const cash = 'cash';
    const percent = 'percent';
    const coupon = 'coupon';

    public static function getDescription($value): string
    {
        
        $result = '';
        switch ($value) {
            case self::cash:
                $result = '現金';
                break;
            case self::percent:
                $result = '百分比';
                break;
            case self::coupon:
                $result = '優惠券';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
    
}
