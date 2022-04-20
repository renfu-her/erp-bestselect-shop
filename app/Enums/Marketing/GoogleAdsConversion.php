<?php

namespace App\Enums\Marketing;

use BenSampo\Enum\Enum;

/**
 * @method static Pageview () 顧客造訪商店內任何頁面
 * @method static AddToCart () 顧客將產品加入購物車
 * @method static Checkout () 顧客造訪結帳頁
 * @method static Purchase () 顧客完成訂單
 * @method static Signup () 顧客註冊會員完成
 */
final class GoogleAdsConversion extends Enum
{
    const Pageview = 1;
    const AddToCart = 2;
    const Checkout = 3;
    const Purchase = 4;
    const Signup = 5;

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::Pageview:
                $result = '顧客造訪商店內任何頁面';
                break;
            case self::AddToCart:
                $result = '顧客將產品加入購物車';
                break;
            case self::Checkout:
                $result = '顧客造訪結帳頁';
                break;
            case self::Purchase:
                $result = '顧客完成訂單';
                break;
            case self::Signup:
                $result = '顧客註冊會員完成';
                break;
        }
        return $result;
    }
}
