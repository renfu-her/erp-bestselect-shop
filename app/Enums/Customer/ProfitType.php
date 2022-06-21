<?php

namespace App\Enums\Customer;

use App\Enums\Helper;
use BenSampo\Enum\Enum;

final class ProfitType extends Helper
{
    const Dividend = 'dividend';
    const Cash = 'cash';
    
    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {   
            case self::Dividend:
                $result = '鴻利';
                break;
            case self::Cash:
                $result = '現金';
                break;
            
        }

        return $result;
    }


}
