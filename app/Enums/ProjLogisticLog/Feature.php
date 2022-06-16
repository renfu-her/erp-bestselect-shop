<?php

namespace App\Enums\ProjLogisticLog;

use BenSampo\Enum\Enum;

/**
 * @method static static create() 建立托運單
 * @method static static is_enable_del() 託運單可否刪除
 * @method static static del_order() 刪除託運單
 */
final class Feature extends Enum
{
    const create = 237;
    const is_enable_del = 238;
    const del_order = 239;

    public static function getDescription($value): string
    {
        switch ($value) {
            case self::create:
                return '建立托運單';
            case self::is_enable_del:
                return '託運單可否刪除';
            case self::del_order:
                return '刪除託運單';
            default:
                return parent::getDescription($value);
        }
    }
}
