<?php

namespace App\Enums\Delivery;

use BenSampo\Enum\Enum;

class Event extends Enum
{
    //訂單 轉倉 寄倉
    const order = 'order';
    const turnover = 'turnover';
    const consignment = 'consignment';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::order:
                $result = '訂單';
                break;
            case self::turnover:
                $result = '轉倉';
                break;
            case self::consignment:
                $result = '寄倉';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
