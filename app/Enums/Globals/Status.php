<?php

namespace App\Enums\Globals;

use BenSampo\Enum\Enum;

/**
 * @method static static close()
 * @method static static open()
 */
final class Status extends Enum
{
    const fail = 0;
    const success = 1;

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::fail:
                $result = '失敗';
                break;
            case self::success:
                $result = '成功';
                break;
        }

        return $result;
    }

}
