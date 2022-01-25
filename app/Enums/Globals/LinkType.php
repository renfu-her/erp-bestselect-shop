<?php

namespace App\Enums\Globals;

use BenSampo\Enum\Enum;

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
