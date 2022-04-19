<?php

namespace App\Enums\Globals;

use BenSampo\Enum\Enum;

/**
 * API返回值 status及message
 * @method static static Succeed() 成功
 * @method static static Fail() 失敗
 * @method static static NotFound() 搜尋不到相關商品
 */
final class ApiStatusMessage extends Enum
{
    const Succeed = '0';
    const Fail = '1';
    const NotFound = '2';

    /**
     * @param $value string API代碼
     * @return string 回傳訊息
     */
    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::Succeed:
                //成功回傳「空字串」
                $result = '';
                break;
            case self::Fail:
                $result = '失敗';
                break;
            case self::NotFound:
                $result = '搜尋不到相關商品';
                break;
            default:
                $result = '無設定status狀態';
                break;
        }
        return $result;
    }
}
