<?php

namespace App\Enums\Order;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class UserAddrType extends Enum
{
    const reciver = 'reciver';
    const orderer = 'orderer';
    const sender = 'sender';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::reciver:
                $result = '收件者';
                break;
            case self::orderer:
                $result = '訂購者';
                break;
            case self::sender:
                $result = '寄件者';
                break;
        }
        return $result;
    }
}
