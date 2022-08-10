<?php

namespace App\Enums\AdminManagement;

use BenSampo\Enum\Enum;

/**
 * 重要性：
 * @method static static OneStar() 一般
 * @method static static TwoStar() 重要
 * @method static static ThreeStar() 極重要
 */
final class Weight extends Enum
{
    const OneStar = 1;
    const TwoStar = 2;
    const ThreeStar = 3;

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::OneStar:
                $result = '一般';
                break;
            case self::TwoStar:
                $result = '重要';
                break;
            case self::ThreeStar:
                $result = '極重要';
                break;
        }
        return $result;
    }
}
