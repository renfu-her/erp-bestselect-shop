<?php

namespace App\Enums\Purchase;

use BenSampo\Enum\Enum;

final class ReturnStatus extends Enum
{
    const add_return = 'add_return'; //新增退出
    const del_return = 'del_return'; //刪除退出

    const add_return_inbound = 'add_return_inbound'; //退出入庫
    const del_return_inbound = 'del_return_inbound'; //取消退出入庫

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::add_return:
                $result = '新增退出';
                break;
            case self::del_return:
                $result = '刪除退出';
                break;
            case self::add_return_inbound:
                $result = '退出入庫';
                break;
            case self::del_return_inbound:
                $result = '取消退出入庫';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
