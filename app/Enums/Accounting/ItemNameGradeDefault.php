<?php

namespace App\Enums\Accounting;

use BenSampo\Enum\Enum;

/**
 * 用來設定「會計科目」預設值的項目
 * @method static static Product() 商品支出
 * @method static static Logistics() 物流費用
 */
final class ItemNameGradeDefault extends Enum
{
    const Product = 'product';
    const Logistics = 'logistics';

    public static function getDescription($value): string
    {
        switch ($value) {
            case self::Product:
                return '商品支出';
            case self::Logistics:
                return '物流費用';
            default:
                return parent::getDescription($value);
        }
    }
}
