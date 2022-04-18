<?php

namespace App\Enums\Order;

use BenSampo\Enum\Enum;

/**
 * @method static static receiver() 收件者
 * @method static static orderer()  訂購者
 * @method static static sender() 寄件者
 */
final class UserAddrType extends Enum
{
    const receiver = 'receiver';
    const orderer = 'orderer';
    const sender = 'sender';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::receiver:
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
