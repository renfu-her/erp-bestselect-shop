<?php

namespace App\Enums\Globals;

use BenSampo\Enum\Enum;

/**
 * 前端連結API ENUM
 * @method static static collection 群組常數
 * @method static static product 產品常數
 * @method static static url 連結常數
 */
class FrontendApiUrl extends Enum
{
    const collection = 'collection';
    const product = 'product';
    const url = 'url';

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
