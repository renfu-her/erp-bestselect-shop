<?php

namespace App\Enums\Delivery;

use BenSampo\Enum\Enum;

class Event extends Enum
{
    const purchase = 'purchase'; //採購

    const order = 'order'; //訂單
    const turnover = 'turnover'; //轉倉
    const consignment = 'consignment'; //寄倉

    const consume = 'consume'; //耗材

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::purchase:
                $result = '採購';
                break;

            case self::order:
                $result = '訂單';
                break;
            case self::turnover:
                $result = '轉倉';
                break;
            case self::consignment:
                $result = '寄倉';
                break;
            case self::consume:
                $result = '耗材';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
