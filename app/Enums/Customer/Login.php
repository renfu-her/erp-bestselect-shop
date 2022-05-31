<?php

namespace App\Enums\Customer;

use BenSampo\Enum\Enum;

/**
 * 消費者註冊登入方式
 * @method static static FACEBOOK() Facebook
 * @method static static LINE() Line
 */
final class Login extends Enum
{
    const FACEBOOK = 1;
    const LINE = 2;

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::FACEBOOK:
                $result = 'Facebook';
                break;
            case self::LINE:
                $result = 'Line';
                break;

            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
