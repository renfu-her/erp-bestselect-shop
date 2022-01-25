<?php

namespace App\Enums\Homepage;

use BenSampo\Enum\Enum;

class BannerEventType extends Enum
{
    const none = 'none';
    const collection = 'collection';
    const url = 'url';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::none:
                $result = '無';
                break;
            case self::collection:
                $result = '群組';
                break;
            case self::url:
                $result = '連結';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
