<?php

namespace App\Enums\Consignment;

use BenSampo\Enum\Enum;

/**
 * @method static static unreviewed()
 * @method static static approved()
 * @method static static veto()
 */
class AuditStatus extends Enum
{
    const unreviewed = '0'; //未審核

    const approved = '1'; //核可
    const veto = '2'; //否決

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::unreviewed:
                $result = '尚未審核';
                break;

            case self::approved:
                $result = '核可';
                break;
            case self::veto:
                $result = '否決';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }
}
