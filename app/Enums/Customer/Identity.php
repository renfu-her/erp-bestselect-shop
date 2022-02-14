<?php

namespace App\Enums\Customer;

use BenSampo\Enum\Enum;

class Identity extends Enum
{
    const customer = 'customer';
    const staff = 'staff';
    const enterprise = 'enterprise';
    const agent = 'agent';
    const group_buyer = 'group_buyer';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::customer:
                $result = '消費者';
                break;
            case self::staff:
                $result = '員工';
                break;
            case self::enterprise:
                $result = '企業';
                break;
            case self::agent:
                $result = '同業';
                break;
            case self::group_buyer:
                $result = '團購';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
