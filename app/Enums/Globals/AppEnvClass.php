<?php

namespace App\Enums\Globals;

use BenSampo\Enum\Enum;

/**
 * @method static static Local() 本機端阪本
 * @method static static Development() dev頒布
 * @method static static Release() release版本
 *
 */
final class AppEnvClass extends Enum
{
    const Local = 'local';
    const Development ='dev';
    const Release = 'rel';
}
