<?php

namespace App\Enums\Discount;

use BenSampo\Enum\Enum;

final class DividendFlag extends Enum
{
    const Active = 'active';
    const NonActive = 'non_active';
    const Invalid = 'invalid';
    const Expired = 'expired';
    const Cancel = 'cancel';
    const Discount = 'discount';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::Active:
                $result = '啟用';
                break;
            case self::NonActive:
                $result = '尚未啟用';
                break;
            case self::Expired:
                $result = '過期';
                break;
            case self::Cancel:
                $result = '取消訂單';
                break;
            case self::Invalid:
                $result = '失效';
                break;
            case self::Discount:
                $result = '紅利折抵';
                break;

        }
        return $result;
    }

}
