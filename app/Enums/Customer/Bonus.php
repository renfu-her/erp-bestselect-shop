<?php

namespace App\Enums\Customer;

use BenSampo\Enum\Enum;

/**
 * @method static static bonus()
 */
final class Bonus extends Enum
{
    const bonus = 0.05;
    public static function getDescription($value): string
    {
        $result = '';

        switch ($value) {
            case self::bonus:
                $result = '業務獎金';
                break;
        }

        return $result;
    }

}
