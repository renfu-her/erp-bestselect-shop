<?php

namespace App\Enums\Homepage;

use BenSampo\Enum\Enum;

class BannerTarget extends Enum
{
    const _self = '_self';
//    const _parent = '_parent';
    const _blank = '_blank';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::_self:
                $result = '同視窗開啟';
                break;
//            case self::_parent:
//                $result = '當前視窗開啟';
//                break;
            case self::_blank:
                $result = '開啟新視窗';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
