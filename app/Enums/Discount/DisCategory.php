<?php

namespace App\Enums\Discount;

// use BenSampo\Enum\Enum;
use App\Enums\Helper;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class DisCategory extends Helper
{
    const normal = 'normal';
    const coupon = 'coupon';
    const code = 'code';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::coupon:
                $result = '優惠券';
                break;
            case self::code:
                $result = '優惠代碼';
                break;
            case self::normal:
                $result = '全館優惠';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }

    public static function getSort($value): string
    {
        $result = '';
        switch ($value) {
            case self::coupon:
                $result = 9;
                break;
            case self::code:
                $result = 2;
                break;
            case self::normal:
                $result = 1;
                break;
            default:
                $result = parent::getSort($value);
                break;
        }
        return $result;
    }

}
