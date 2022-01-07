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
    const purchase = 'purchase';
    const order = 'order';
    const combo = 'combo';

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
            case self::combo:
                $result = '組合包';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }

}
