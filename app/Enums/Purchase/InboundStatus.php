<?php

namespace App\Enums\Purchase;

use BenSampo\Enum\Enum;

class InboundStatus extends Enum
{
    //採購入庫狀態(0:尚未入庫/1:正常/2:短缺/3:溢出)
    const not_yet = 0;
    const normal = 1;
    const shortage = 2;
    const overflow = 3;

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::not_yet:
                $result = '尚未入庫';
                break;
            case self::normal:
                $result = '正常';
                break;
            case self::shortage:
                $result = '短缺';
                break;
            case self::overflow:
                $result = '溢出';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
