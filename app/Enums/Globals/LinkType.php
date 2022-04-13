<?php

namespace App\Enums\Globals;

use BenSampo\Enum\Enum;

/**
 * 前端內部、外部連結 Enum
 * @method static static internal 內部連結常數
 * @method static static external 外部連結常數
 */
class LinkType extends Enum
{
    const internal = '1';
    const external = '2';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::internal:
                $result = '內部連結';
                break;
            case self::external:
                $result = '外部連結';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
