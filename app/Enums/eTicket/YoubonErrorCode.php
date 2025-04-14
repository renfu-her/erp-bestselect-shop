<?php

namespace App\Enums\eTicket;

use App\Enums\Helper;

// 錯誤代碼常數
/**
 * @method static static SUCCESS()
 * @method static static ORDER_DUPLICATE()
 * @method static static SHIPPING_ISSUE()
 * @method static static ORDER_ALREADY_SHIPPED()
 * @method static static ORDER_NOT_FOUND()
 * @method static static PAYMENT_METHOD_ERROR()
 * @method static static DEPARTMENT_NUMBER_ERROR()
 * @method static static SALES_AMOUNT_ERROR()
 * @method static static INSUFFICIENT_CREDIT()
 * @method static static ORDER_RETURNED()
 * @method static static TRANSACTION_TYPE_ERROR()
 * @method static static ONLINE_PLATFORM_ERROR()
 * @method static static OPERATOR_NUMBER_ERROR()
 * @method static static OPERATOR_NAME_ERROR()
 * @method static static EMPTY_ORDER_NUMBER()
 * @method static static PRODUCT_NOT_FOUND()
 * @method static static EMPTY_EMAIL()
 * @method static static ORDER_EXCEPTION()
 */
final class YoubonErrorCode extends Helper
{
    const SUCCESS = '0000';
    const ORDER_DUPLICATE = '9900';
    const SHIPPING_ISSUE = '0001';
    const ORDER_ALREADY_SHIPPED = '0002';
    const ORDER_NOT_FOUND = '0003';
    const PAYMENT_METHOD_ERROR = '0004';
    const DEPARTMENT_NUMBER_ERROR = '0005';
    const SALES_AMOUNT_ERROR = '0006';
    const INSUFFICIENT_CREDIT = '0007';
    const ORDER_RETURNED = '0008';
    const TRANSACTION_TYPE_ERROR = '0009';
    const ONLINE_PLATFORM_ERROR = '0010';
    const OPERATOR_NUMBER_ERROR = '0011';
    const OPERATOR_NAME_ERROR = '0012';
    const EMPTY_ORDER_NUMBER = '0013';
    const PRODUCT_NOT_FOUND = '0014';
    const EMPTY_EMAIL = '0015';
    const ORDER_EXCEPTION = '9999';

    public static function getDescription($value): string
    {
        switch ($value) {
            case self::SUCCESS:
                return '成功';
            case self::ORDER_DUPLICATE:
                return '訂單重複發送會回傳成功';
            case self::SHIPPING_ISSUE:
                return '出貨有狀況，請洽我方人員';
            case self::ORDER_ALREADY_SHIPPED:
                return '此筆訂單已經出貨過，請勿重複送資料';
            case self::ORDER_NOT_FOUND:
                return '查無此訂單';
            case self::PAYMENT_METHOD_ERROR:
                return '付款方式錯誤';
            case self::DEPARTMENT_NUMBER_ERROR:
                return '部門編號錯誤';
            case self::SALES_AMOUNT_ERROR:
                return '售出金額有誤，請洽我方人員';
            case self::INSUFFICIENT_CREDIT:
                return '額度不足無法出貨，請補充額度後再送資料出貨';
            case self::ORDER_RETURNED:
                return '此訂單已退貨';
            case self::TRANSACTION_TYPE_ERROR:
                return '交易類型錯誤';
            case self::ONLINE_PLATFORM_ERROR:
                return '網購平臺編號錯誤';
            case self::OPERATOR_NUMBER_ERROR:
                return '操作人員編號錯誤';
            case self::OPERATOR_NAME_ERROR:
                return '操作人員姓名錯誤';
            case self::EMPTY_ORDER_NUMBER:
                return '訂單編號空白';
            case self::PRODUCT_NOT_FOUND:
                return '有商品編號查詢不到資料';
            case self::EMPTY_EMAIL:
                return 'E-mail空白';
            case self::ORDER_EXCEPTION:
                return '訂單異常';
            default:
                return parent::getDescription($value);
        }
    }
}

