<?php

namespace App\Enums\Accounting;

use BenSampo\Enum\Enum;

/**
 * 數值對應到各級會計科目的Model Class Name
 * @method static static FirstGrade() 第1級科目
 * @method static static SecondGrade() 第2級科目
 * @method static static ThirdGrade() 第3級科目
 * @method static static FourthGrade() 第4級科目
 */
final class GradeModelClass extends Enum
{
    const FirstGrade = 1;
    const SecondGrade = 2;
    const ThirdGrade = 3;
    const FourthGrade = 4;

    /**
     * @param $value int 1,2,3,4
     * @return string 各級科目的Model Class Name
     */
    public static function getDescription($value): string
    {
        switch ($value) {
            case self::FirstGrade:
                return 'App\Models\FirstGrade';
            case self::SecondGrade:
                return 'App\Models\SecondGrade';
            case self::ThirdGrade:
                return 'App\Models\ThirdGrade';
            case self::FourthGrade:
                return 'App\Models\FourthGrade';
            default:
                return parent::getDescription($value);
        }
    }
}
