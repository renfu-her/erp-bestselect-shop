<?php

namespace App\Enums\DlvBack;

use BenSampo\Enum\Enum;

/**
 * @method static static back()
 * @method static static out()
 */
final class DlvBackPapaType extends Enum
{
    const back = 'back';
    const out = 'out';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::back:
                $result = '退貨';
                break;
            case self::out:
                $result = '缺貨';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
