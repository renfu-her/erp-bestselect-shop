<?php

namespace App\Enums\SaleChannel;

use BenSampo\Enum\Enum;

/**
 * @method static static Ecommerce()
 * @method static static Erp()
 * @method static static B2b()
 * @method static static Shopee()
 * @method static static Post()
 * @method static static DepartureLounge() 喜鴻餐飲
 * @method static static Besttour() 喜鴻旅行社
 * @method static static DealerPriceNoBonus() 經銷價販售-無獎金
 * @method static static MultipleOrders() 大量訂購通路
 */
final class Channel extends Enum
{
    const Ecommerce = 1;
    const Erp = 2;
    const B2b = 3;
    const Shopee = 4;
    const Post = 5;
    const DepartureLounge = 8;
    const Besttour = 9;
    const DealerPriceNoBonus = 10;
    const MultipleOrders = 16;
    const TourGuide = 18;

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
