<?php

namespace App\Enums\Delivery;

use BenSampo\Enum\Enum;

final class LogisticStatus extends Enum
{
    const A1000 = '尚未出貨';
    const A2000 = '檢貨中';
    const A3000 = '理貨中';
    const A4000 = '待配送';
    const A5000 = '已出貨';
    const B1000 = '配送中';
    const B2000 = '已送達';
    const B3000 = '未送達';
    const C1000 = '已回倉';
    const C2000 = '退回中';
    const C3000 = '已退回';

    const D9000 = '寄倉售出';

    public static function getDescription($value): string
    {
        $result = '';
        return $result;
    }
}
