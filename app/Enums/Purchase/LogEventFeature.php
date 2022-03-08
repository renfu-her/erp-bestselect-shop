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
    const style_change_memo = 'style_change_memo';
    const style_change_price = 'style_change_price';
    const style_change_qty = 'style_change_qty';

    //入庫
    const inbound_add = 'inbound_add';
    const inbound_del = 'inbound_del';
    const inbound_shipping = 'inbound_shipping';
    const inbound_send_back = 'inbound_send_back';

    //付款
    const pay_add = 'pay_add';
    const pay_del = 'pay_del';
    const pay_change_pay_type = 'pay_change_pay_type';
    const pay_change_shipping_fee = 'pay_change_shipping_fee';

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
            case self::style_change_memo:
                $result = '修改備註';
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
            case self::inbound_shipping:
                $result = '庫存出貨';
                break;
            case self::inbound_send_back:
                $result = '庫存退回';
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
