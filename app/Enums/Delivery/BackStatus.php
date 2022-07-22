<?php

namespace App\Enums\Delivery;

use BenSampo\Enum\Enum;

final class BackStatus extends Enum
{
    const add_back = 'add_back'; //新增退貨
    const del_back = 'del_back'; //刪除退回入庫

    const add_back_inbound = 'add_back_inbound'; //退回入庫
    const del_back_inbound = 'del_back_inbound'; //取消退回入庫

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::add_back:
                $result = '新增退貨';
                break;
            case self::del_back:
                $result = '刪除退回入庫';
                break;
            case self::add_back_inbound:
                $result = '退回入庫';
                break;
            case self::del_back_inbound:
                $result = '取消退回入庫';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
