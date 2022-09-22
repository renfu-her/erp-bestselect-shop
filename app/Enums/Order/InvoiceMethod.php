<?php

namespace App\Enums\Order;

use BenSampo\Enum\Enum;

final class InvoiceMethod extends Enum
{
    const print = 'print';
    const give = 'give';
    const e_inv = 'e_inv';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::print:
                $result = '紙本發票';
                break;
            case self::give:
                $result = '捐贈';
                break;
            case self::e_inv:
                $result = '電子發票';
                break;
        }
        return $result;
    }
}
