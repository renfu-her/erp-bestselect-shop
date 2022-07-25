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
    const sale = 'sale';
    const inbound_del = 'inbound_del';
    const consignment = 'consignment';
    const consume = 'consume';
    const send_back = 'send_back';

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
            case self::sale:
                $result = '通路商';
                break;
            case self::inbound_del:
                $result = '刪除入庫單';
                break;
            case self::consignment:
                $result = '寄倉';
                break;
            case self::consume:
                $result = '耗材';
                break;
            case self::send_back:
                $result = '退回';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }

}

