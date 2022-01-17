<?php

namespace App\Enums\Purchase;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class LogFeatureEvent extends Enum
{

    //入庫
    const inbound = 'inbound';
    const delete = 'delete';
    const shipping = 'shipping';
    const send_back = 'send_back';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::inbound:
                $result = '入庫';
                break;
            case self::delete:
                $result = '刪除';
                break;
            case self::shipping:
                $result = '出貨';
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
