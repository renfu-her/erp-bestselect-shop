<?php

namespace App\Enums\Globals;

use BenSampo\Enum\Enum;

/**
 * @method static static CDN() 正式機CDN網域
 * @method static static FTP() 使用FTP（202.168.206.100）手動上傳的網域
 * 圖檔網域
 */
final class ImageDomain extends Enum
{
    //遠傳CDN伺服器壞掉，暫時使用FTP 202.168.206.100
//    const CDN = 'https://besttour-img.ittms.com.tw/';
    const CDN = 'https://img.bestselection.com.tw/';
    const FTP = 'https://img.bestselection.com.tw/';
}
