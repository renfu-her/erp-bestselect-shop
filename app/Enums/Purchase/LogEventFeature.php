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
    //採購
    const pcs_add = 'pcs_add';
    const pcs_del = 'pcs_del';
    const pcs_close = 'pcs_close';
    const pcs_change_data = 'pcs_change_data';

    //款式
    const style_add = 'style_add';
    const style_del = 'style_del';
    const style_change_price = 'style_change_price';
    const style_change_qty = 'style_change_qty';

    //入庫
    const inbound_add = 'inbound_add';
    const inbound_del = 'inbound_del';

    const order_shipping = 'order_shipping';
    const order_send_back = 'order_send_back';
    const consume_shipping = 'consume_shipping';
    const consume_send_back = 'consume_send_back';
    const consignment_shipping = 'consignment_shipping';
    const consignment_send_back = 'consignment_send_back';

    //付款
    const pay_add = 'pay_add';
    const pay_del = 'pay_del';
    const pay_change_pay_type = 'pay_change_pay_type';
    const pay_change_shipping_fee = 'pay_change_shipping_fee';

    //寄倉
    const csn_add = 'csn_add';
    const csn_del = 'csn_del';
    const csn_close = 'csn_close';
    const csn_change_data = 'csn_change_data';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::pcs_add:
                $result = '新增採購單';
                break;
            case self::pcs_del:
                $result = '刪除採購單';
                break;
            case self::pcs_close:
                $result = '採購單結單';
                break;
            case self::pcs_change_data:
                $result = '採購單修改內容';
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
            case self::order_shipping:
                $result = '訂單出貨';
                break;
            case self::order_send_back:
                $result = '訂單退回';
                break;
            case self::consignment_shipping:
                $result = '寄倉出貨';
                break;
            case self::consignment_send_back:
                $result = '寄倉退回';
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

            case self::csn_add:
                $result = '新增寄倉單';
                break;
            case self::csn_del:
                $result = '刪除寄倉單';
                break;
            case self::csn_close:
                $result = '寄倉單結單';
                break;
            case self::csn_change_data:
                $result = '寄倉單修改內容';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }

}
