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
    const pcs_add = 'add';
    const pcs_del = 'del';
    const pcs_close = 'close';
    const pcs_change_data = 'change_data';

    //款式
    const style_add = 'style_add';
    const style_del = 'style_del';
    const style_change_price = 'style_change_price';
    const style_change_qty = 'style_change_qty';

    //入庫
    const inbound_add = 'inbound_add';
    const inbound_del = 'inbound_del';

    const delivery = 'delivery';
    const order_send_back = 'send_back';
    const consume_shipping = 'consume';
    const consume_send_back = 'send_back';
    const consignment_send_back = 'send_back';

    //付款
    const pay_add = 'pay_add';
    const pay_del = 'pay_del';
    const pay_change_pay_type = 'pay_change_pay_type';
    const pay_change_shipping_fee = 'pay_change_shipping_fee';

    //寄倉
    const csn_add = 'add';
    const csn_del = 'del';
    const csn_close = 'close';
    const csn_change_data = 'change_data';

    //寄倉訂購
    const csn_order_add = 'add';
    const csn_order_del = 'del';
    const csn_order_close = 'close';
    const csn_order_change_data = 'change_data';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::pcs_add:
            case self::csn_add:
            case self::csn_order_add:
                $result = '新單';
                break;
            case self::pcs_del:
            case self::csn_del:
            case self::csn_order_del:
                $result = '刪單';
                break;
            case self::pcs_close:
            case self::csn_close:
            case self::csn_order_close:
                $result = '結單';
                break;
            case self::pcs_change_data:
            case self::csn_change_data:
            case self::csn_order_change_data:
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
            case self::delivery:
                $result = '出貨';
                break;
            case self::consume_shipping:
                $result = '耗材出貨';
                break;
            case self::order_send_back:
            case self::consume_send_back:
            case self::consignment_send_back:
                $result = '退回';
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
