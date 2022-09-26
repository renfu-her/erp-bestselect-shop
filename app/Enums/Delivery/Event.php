<?php

namespace App\Enums\Delivery;

use BenSampo\Enum\Enum;

class Event extends Enum
{
    const purchase = 'purchase'; //採購

    const order = 'order'; //訂單
    const ord_pickup = 'ord_pickup'; //訂單自取 //只在pcs_purchase_inbound用到 因只有event欄位紀錄 但要區隔訂單和訂單自取
    const consignment = 'consignment'; //寄倉
    const csn_order = 'csn_order'; //寄倉訂購

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::purchase:
                $result = '採購';
                break;
            case self::order:
                $result = '訂單';
                break;
            case self::ord_pickup:
                $result = '訂單自取';
                break;
            case self::consignment:
                $result = '寄倉';
                break;
            case self::csn_order:
                $result = '寄倉訂購';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
