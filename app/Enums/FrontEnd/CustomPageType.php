<?php

namespace App\Enums\FrontEnd;

use BenSampo\Enum\Enum;

/**
 * 自訂類型
 * @method static static General() 一般頁面
 * @method static static Activity() 活動頁
 */
final class CustomPageType extends Enum
{
    const General = '1';
    const Activity = '2';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::General:
                $result = '一般頁';
                break;
            case self::Activity:
                $result = '活動頁';
                break;
        }
        return $result;
    }
}
