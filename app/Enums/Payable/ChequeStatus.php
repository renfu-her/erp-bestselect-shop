<?php

namespace App\Enums\Payable;

use BenSampo\Enum\Enum;

/**
 * @method static static Paid() 付款
 * @method static static Cashed() 兌現
 * @method static static OnHold() 押票
 * @method static static Returned() 退票
 * @method static static Issued() 開票
 */
final class ChequeStatus extends Enum
{
    const Paid = 1;
    const Cashed = 2;
    const OnHold = 3;
    const Returned = 4;
    const Issued = 5;
}
