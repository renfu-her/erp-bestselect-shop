<?php

namespace App\Enums\Globals\SharedPreference;

use BenSampo\Enum\Enum;

/**
 * @method static static offon()
 * @method static static failsuccess()
 * @method static static cenum()
 */
final class Type extends Enum
{
    const offon = 0;
    const failsuccess = 1;
    const cenum = 2;

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::offon:
                $result = '開關';
                break;
            case self::failsuccess:
                $result = '失敗成功';
                break;
            case self::cenum:
                $result = 'enum';
                break;
        }

        return $result;
    }
}
