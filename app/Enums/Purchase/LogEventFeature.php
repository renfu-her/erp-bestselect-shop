<?php

namespace App\Enums\Purchase;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class LogEventFeature extends Enum
{
    const add = 'add';
    const del = 'del';
    const close = 'close';
    const change_data = 'change_data';

    //款式
    const style_add = 'style_add';
    const style_del = 'style_del';
    const style_change_price = 'style_change_price';
    const style_change_qty = 'style_change_qty';

    //入庫
    const inbound_add = 'inbound_add';
    const inbound_del = 'inbound_del';
    const inbound_update = 'inbound_update';

    const combo = 'combo'; //組成組合包
    const decompose = 'decompose'; //組合包分解
    const scrapped = 'scrapped'; //報廢

    const delivery = 'delivery';
    const delivery_cancle = 'delivery_cancle';
    const send_back = 'send_back';
    const send_back_cancle = 'send_back_cancle';
    const consume_delivery = 'consume_delivery';
    const consume_cancle = 'consume_cancle';
    const consume_send_back = 'consume_send_back';

    //付款
    const pay_add = 'pay_add';
    const pay_del = 'pay_del';
    const pay_change_pay_type = 'pay_change_pay_type';
    const pay_change_shipping_fee = 'pay_change_shipping_fee';


    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::add:
                $result = '新單';
                break;
            case self::del:
                $result = '刪單';
                break;
            case self::close:
                $result = '結單';
                break;
            case self::change_data:
                $result = '修改內容';
                break;

            case self::style_add:
                $result = '新增商品';
                break;
            case self::style_del:
                $result = '刪除商品';
                break;
            case self::style_change_qty:
                $result = '修改數量';
                break;
            case self::style_change_price:
                $result = '修改價錢';
                break;

            case self::inbound_add:
                $result = '新增入庫';
                break;
            case self::inbound_del:
                $result = '刪除入庫';
                break;
            case self::inbound_update:
                $result = '入庫單庫存調整';
                break;

            case self::combo:
                $result = '組成組合包';
                break;
            case self::decompose:
                $result = '組合包分解';
                break;
            case self::scrapped:
                $result = '報廢';
                break;

            case self::delivery:
                $result = '出貨';
                break;
            case self::delivery_cancle:
                $result = '出貨取消';
                break;

            case self::consume_delivery:
                $result = '耗材出貨';
                break;
            case self::consume_cancle:
                $result = '耗材取消出貨';
                break;
            case self::send_back:
                $result = '退回';
                break;
            case self::send_back_cancle:
                $result = '取消退回';
                break;
            case self::consume_send_back:
                $result = '耗材退回';
                break;

            case self::pay_add:
                $result = '新增付款單';
                break;
            case self::pay_del:
                $result = '刪除付款單';
                break;
            case self::pay_change_pay_type:
                $result = '變更付款方式';
                break;
            case self::pay_change_shipping_fee:
                $result = '變更運費';
                break;


            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }

}
