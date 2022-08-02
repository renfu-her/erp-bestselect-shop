<?php

namespace App\Enums\SaleChannel;

use BenSampo\Enum\Enum;

/**
 * @method static static Ecommerce()
 * @method static static Erp()
 * @method static static B2b()
 * @method static static Shopee()
 * @method static static Post()
 */
final class Channel extends Enum
{
    const Ecommerce = 1;
    const Erp = 2;
    const B2b = 3;
    const Shopee = 4;
    const Post = 5;

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::Ecommerce:
                $result = '喜鴻購物2.0官網';
                break;
            case self::Erp:
                $result = '喜鴻購物2.0ERP';

        }
        return $result;
    }}
