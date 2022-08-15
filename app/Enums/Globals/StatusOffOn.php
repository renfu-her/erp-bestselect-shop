<?php

namespace App\Enums\Globals;

use BenSampo\Enum\Enum;

/**
 * @method static static Off()
 * @method static static On()
 */
final class StatusOffOn extends Enum
{
    const Off = 0;
    const On = 1;

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::Off:
                $result = '關';
                break;
            case self::On:
                $result = '開';
                break;
        }

        return $result;
    }
}
