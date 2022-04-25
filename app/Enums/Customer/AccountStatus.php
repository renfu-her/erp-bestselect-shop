<?php

namespace App\Enums\Customer;

use BenSampo\Enum\Enum;

/**
 * @method static static close()
 * @method static static open()
 */
final class AccountStatus extends Enum
{
    const close = 0;
    const open = 1;

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::close:
                $result = '關閉';
                break;
            case self::open:
                $result = '開通';
                break;
        }

        return $result;
    }

}
