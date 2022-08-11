<?php

namespace App\Enums\Received;

use BenSampo\Enum\Enum;

final class ChequeStatus extends Enum
{
    const Received = 'received';
    const Nd = 'nd';
    const Cashed = 'cashed';
    const Demand = 'demand';
    const Draw = 'draw';
    const Collection = 'collection';
    const Returned = 'returned';

    public static function getDescription($value): string
    {
        switch ($value) {
            case self::Received:
                return '收票';
            case self::Nd:
                return '次交票';
            case self::Cashed:
                return '兌現';
            case self::Demand:
                return '即兌';
            case self::Draw:
                return '抽票';
            case self::Collection:
                return '託收';
            case self::Returned:
                return '退票';
            default:
                return parent::getDescription($value);
        }
    }
}
