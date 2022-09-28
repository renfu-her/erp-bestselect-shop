<?php

namespace App\Enums\DlvBack;

use BenSampo\Enum\Enum;

/**
 * @method static static logistic()
 * @method static static sales_revenue()
 */
final class DlvBackType extends Enum
{
    const logistic = 1; //物流
    const sales_revenue = 2; //銷貨收入

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::logistic:
                $result = '物流';
                break;
            case self::sales_revenue:
                $result = '銷貨收入';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
