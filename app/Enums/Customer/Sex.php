<?php

namespace App\Enums\Customer;

use BenSampo\Enum\Enum;

/**
 * @method static static female()
 * @method static static male()
 */
final class Sex extends Enum
{
    const female = 0;
    const male = 1;

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::female:
                $result = '女';
                break;
            case self::male:
                $result = '男';
                break;
        }

        return $result;
    }

}
