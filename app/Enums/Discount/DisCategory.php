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
    const combine = 'combine';
    const dividend = 'dividend';
    const m_b2e = 'm_b2e';
    const m_b2c = 'm_b2c';
    const m_b2b = 'm_b2b';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::dividend:
                $result = '紅利折扣';
                break;
            case self::m_b2e:
                $result = '旅遊企業紅利折扣';
                break;
            case self::m_b2c:
                $result = '旅遊會員紅利折扣';
                break;
            case self::m_b2b:
                $result = '旅遊同業紅利折扣';
                break;
            case self::coupon:
                $result = '優惠券';
                break;
            case self::code:
                $result = '優惠代碼';
                break;
            case self::normal:
                $result = '全館優惠';
                break;
            case self::combine:
                $result = '任選折扣';
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
            case self::m_b2e:
                $result = 12;
                break;
            case self::m_b2c:
                $result = 12;
                break;
            case self::m_b2b:
                $result = 12;
                break;
            case self::dividend:
                $result = 12;
                break;
            case self::coupon:
                $result = 9;
                break;
            case self::code:
                $result = 4;
                break;
            case self::normal:
                $result = 2;
                break;
            case self::combine:
                $result = 0;
                break;
            default:
                $result = parent::getSort($value);
                break;
        }
        return $result;
    }

}
