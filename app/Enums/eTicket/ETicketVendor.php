<?php

namespace App\Enums\eTicket;

use App\Enums\Helper;

// 廠商代碼常數
/**
 * @method static static YOUBON_CODE()
 */
final class ETicketVendor extends Helper
{
    const YOUBON_CODE = 'eYoubon';

    public static function getDescription($value): string
    {
        switch ($value) {
            case self::YOUBON_CODE:
                return '星全安';
            default:
                return parent::getDescription($value);
        }
    }
}

