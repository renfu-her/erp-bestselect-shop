<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class StockEvent extends Enum
{
    const order = 'order';
    const combo = 'combo';
    const inbound = 'inbound';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::order:
                $result = '訂單';
                break;
            case self::combo:
                $result = '組合包';
                break;
            case self::inbound:
                $result = '入庫';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }

}
