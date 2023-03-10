<?php

namespace App\Enums\PcsScrap;

use BenSampo\Enum\Enum;

/**
 * @method static static scrap()
 */
final class PcsScrapType extends Enum
{
    const scrap = 'scrap';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::scrap:
                $result = '報廢';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
