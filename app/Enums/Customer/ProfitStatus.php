<?php

namespace App\Enums\Customer;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class ProfitStatus extends Enum
{
    const NotApplied = "not_applied";
    const Checking = "checking";
    const Success = "success";
    const Failed = "failed";

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::NotApplied:
                $result = '未申請';
                break;
            case self::Checking:
                $result = '審核中';
                break;
            case self::Success:
                $result = '審核通過';
                break;
            case self::Failed:
                $result = '審核失敗';
                break;
        }

        return $result;
    }

}
