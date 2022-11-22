<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static order()
 * @method static static combo()
 * @method static static inbound()
 * @method static static sale()
 * @method static static inbound_del()
 * @method static static consignment()
 * @method static static consume()
 * @method static static consume_cancle()
 * @method static static send_back()
 * @method static static send_back_cancle()
 * @method static static out_stock()
 * @method static static out_stock_cancle()
 * @method static static combo_to_element()
 * @method static static purchase_del()
 * @method static static purchase_recovery()
 */
final class StockEvent extends Enum
{
    const order = 'order';
    const combo = 'combo';
    const inbound = 'inbound';
    const sale = 'sale';
    const inbound_del = 'inbound_del';
    const consignment = 'consignment';
    const consume = 'consume';
    const consume_cancle = 'consume_cancle';
    const send_back = 'send_back';
    const send_back_cancle = 'send_back_cancle';
    const out_stock = 'out_stock';
    const out_stock_cancle = 'out_stock_cancle';
    const combo_to_element = 'combo_to_element';
    const purchase_del = 'purchase_del';
    const purchase_recovery = 'purchase_recovery';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::order:
                $result = '訂單';
                break;
            case self::combo:
                $result = '組合包';
                break;
            case self::inbound:
                $result = '入庫';
                break;
            case self::sale:
                $result = '通路商';
                break;
            case self::inbound_del:
                $result = '刪除入庫單';
                break;
            case self::consignment:
                $result = '寄倉';
                break;
            case self::consume:
                $result = '耗材';
                break;
            case self::consume_cancle:
                $result = '耗材取消';
                break;
            case self::send_back:
                $result = '退回';
                break;
            case self::send_back_cancle:
                $result = '刪除退回';
                break;
            case self::out_stock:
                $result = '缺貨';
                break;
            case self::out_stock_cancle:
                $result = '刪除缺貨';
                break;
            case self::combo_to_element:
                $result = '分解組合包';
                break;
            case self::purchase_del:
                $result = '刪除採購單';
                break;
            case self::purchase_recovery:
                $result = '恢復採購單';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }

}

