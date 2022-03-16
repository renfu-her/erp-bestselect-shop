<?php

namespace App\Enums\Discount;

use App\Enums\Helper;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class DisStatus extends Helper
{
    const D00 = 'D00';
    const D01 = 'D01';
    const D02 = 'D02';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::D00:
                $result = '待進行';
                break;
            case self::D01:
                $result = '進行中';
                break;
            case self::D02:
                $result = '結束';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
