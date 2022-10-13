<?php

namespace App\Enums\DlvBack;

use BenSampo\Enum\Enum;

/**
 * @method static static product()
 * @method static static other()
 */
final class DlvBackType extends Enum
{
    const product = 0;
    const other = 1;

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::product:
                $result = '商品';
                break;
            case self::other:
                $result = '其他';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
