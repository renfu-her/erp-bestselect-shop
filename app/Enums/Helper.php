<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
class Helper extends Enum
{
    public static function getValueWithDesc($keys = null)
    {
        $output = [];
        if ($keys && is_array($keys)) {
            foreach ($keys as $value) {
                $output[$value] = self::$value()->description;
            }
        } else {
            foreach (self::asArray() as $key => $value) {
                $output[$value] = self::$key()->description;
            }
        }
        return $output;

    }
}
