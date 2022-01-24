<?php

namespace App\Enums\Homepage;

use BenSampo\Enum\Enum;

class ApiUrl extends Enum
{
    const collection = 'collection';
    const product = 'product';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::collection:
                $result = '群組';
                break;
            case self::product:
                $result = '商品';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
