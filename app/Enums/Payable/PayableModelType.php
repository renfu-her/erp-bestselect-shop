<?php

namespace App\Enums\Payable;

use App\Enums\Supplier\Payment;
use BenSampo\Enum\Enum;

/**
 * 主要用來回傳: 每個付款方式對應的Model class name
 */
final class PayableModelType extends Enum
{
    const CashModel = Payment::Cash;
    const ChequeModel = Payment::Cheque;
    const RemittanceModel = Payment::Remittance;
    const ForeignCurrencyModel = Payment::ForeignCurrency;
    const AccountsPayableModel = Payment::AccountsPayable;
    const OtherModel = Payment::Other;

    /**
     * @param $value int 每個付款方式的 primary ID
     *
     * @return string 每個付款方式對應的Model class name
     */
    public static function getDescription($value): string
    {
        switch ($value) {
            case self::CashModel:
                return 'App\Models\PayableCash';
            case self::ChequeModel:
                return 'App\Models\PayableCheque';
            case self::RemittanceModel:
                return 'App\Models\PayableRemit';
            case self::ForeignCurrencyModel:
                return 'App\Models\PayableForeignCurrency';
            case self::AccountsPayableModel:
                return 'App\Models\PayableAccount';
            case self::OtherModel:
                return 'App\Models\PayableOther';
            default:
                return parent::getDescription($value);
        }
    }
}
