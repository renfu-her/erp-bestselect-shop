<?php

namespace App\Enums\Globals;

use BenSampo\Enum\Enum;

class ResponseParam extends Enum
{
    const status = 'status';
    const msg = 'msg';
    const data = 'data';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::status:
                $result = '狀態';
                break;
            case self::msg:
                $result = '訊息';
                break;
            case self::data:
                $result = '資料';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
