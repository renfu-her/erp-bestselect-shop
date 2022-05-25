<?php

namespace App\Enums\Received;

use BenSampo\Enum\Enum;

/**
 * 收款方式
 * @method static static Cash() 現金
 * @method static static Cheque() 支票
 * @method static static CreditCard() 信用卡
 * @method static static Remittance() 匯款
 * @method static static ForeignCurrency() 外幣
 * @method static static AccountsReceivable() 應收帳款
 * @method static static Other() 其它
 * @method static static Refund() 退還
 */
final class ReceivedMethod extends Enum
{
    const Cash = 'cash';
    const Cheque = 'cheque';
    const CreditCard = 'credit_card';
    // const CreditCard3 = 'credit_card_3';
    const Remittance = 'remit';
    const ForeignCurrency = 'foreign_currency';
    const AccountsReceivable = 'account_received';
    const Other = 'other';
    const Refund = 'refund';

    public static function getDescription($value): string
    {
        switch ($value) {
            case self::Cash:
                return '現金';
            case self::Cheque:
                return '支票';
            case self::CreditCard:
                return '信用卡';
            // case self::CreditCard3:
            //     return '信用卡（3期）';
            case self::Remittance:
                return '匯款';
            case self::ForeignCurrency:
                return '外幣';
            case self::AccountsReceivable:
                return '應收帳款';
            case self::Other:
                return '其它';
            case self::Refund:
                return '退還';
            default:
                return parent::getDescription($value);
        }
    }
}
