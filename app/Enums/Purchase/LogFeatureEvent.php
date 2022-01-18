<?php

namespace App\Enums\Purchase;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class LogFeatureEvent extends Enum
{
    //採購
    const pcs_add = 'pcs_add';
    const pcs_del = 'pcs_del';
    const pcs_close = 'pcs_close';
    const pcs_change_data = 'pcs_change_data';
    const pcs_style_add = 'pcs_style_add';
    const pcs_style_del = 'pcs_style_del';
    const pcs_style_change_data = 'pcs_style_change_data';
    const pcs_change_price = 'pcs_change_price';
    const pcs_change_qty = 'pcs_change_qty';

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
            case self::pcs_style_add:
                $result = '刪除商品';
                break;
            case self::pcs_style_del:
                $result = '刪除商品';
                break;
            case self::pcs_style_change_data:
                $result = '採購單修改商品內容';
                break;
            case self::pcs_change_qty:
                $result = '修改數量';
                break;
            case self::pcs_change_price:
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
