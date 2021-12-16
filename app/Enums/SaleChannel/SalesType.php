<?php

namespace App\Enums\SaleChannel;

use BenSampo\Enum\Enum;

class SalesType extends Enum
{
    const offline = 0;
    const online = 1;

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::offline:
                $result = '線下';
                break;
            case self::online:
                $result = '線上';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }

    public static function sample() {
        var_dump(SalesType::online()->key);
        var_dump(SalesType::online()->value);
        var_dump(SalesType::online()->description);
        var_dump(SalesType::hasKey('online'));
        var_dump(SalesType::getDescription(SalesType::fromKey('online')->value));
        var_dump(SalesType::getKeys());
        var_dump(SalesType::getKeys()[1]);
        var_dump(SalesType::asArray());
    }
}
