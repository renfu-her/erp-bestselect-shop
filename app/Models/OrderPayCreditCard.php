<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPayCreditCard extends Model
{
    use HasFactory;

    protected $table = 'ord_payment_credit_card_log';
    protected $guarded = [];

    public static function create_log(int $order_id, object $response)
    {
        $agent = request()->server('HTTP_USER_AGENT');
        $platform = 'Unknown OS Platform';
        $browser = 'Unknown Browser';

        $platform_arr     = [
            '/windows nt 10/i' => 'Windows 10',
            '/windows nt 6.3/i' => 'Windows 8.1',
            '/windows nt 6.2/i' => 'Windows 8',
            '/windows nt 6.1/i' => 'Windows 7',
            '/windows nt 6.0/i' => 'Windows Vista',
            '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i' => 'Windows XP',
            '/windows xp/i' => 'Windows XP',
            '/windows nt 5.0/i' => 'Windows 2000',
            '/windows me/i' => 'Windows ME',
            '/win98/i' => 'Windows 98',
            '/win95/i' => 'Windows 95',
            '/win16/i' => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i' => 'Mac OS 9',
            '/linux/i' => 'Linux',
            '/ubuntu/i' => 'Ubuntu',
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile'
        ];

        foreach ($platform_arr as $key => $value)
            if (preg_match($key, $agent)) $platform = $value;

        $browser_arr = [
            '/msie/i' => 'Internet Explorer',
            '/firefox/i' => 'Firefox',
            '/safari/i' => 'Safari',
            '/chrome/i' => 'Chrome',
            '/edge/i' => 'Edge',
            '/opera/i' => 'Opera',
            '/netscape/i' => 'Netscape',
            '/maxthon/i' => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i' => 'Handheld Browser'
        ];

        foreach ($browser_arr as $key => $value)
            if (preg_match($key, $agent)) $browser = $value;

        self::create([
            'order_id'=>$order_id,
            'status'=>property_exists($response, 'status') ? $response->status : null,
            'errcode'=>property_exists($response, 'errcode') ? $response->errcode : null,
            'errdesc'=>property_exists($response, 'errdesc') ? mb_convert_encoding(trim($response->errdesc, "\x00..\x08"), 'UTF-8', ['BIG5', 'UTF-8']) : null,
            'outmac'=>property_exists($response, 'outmac') ? $response->outmac : null,
            'merid'=>property_exists($response, 'merid') ? $response->merid : null,
            'authcode'=>property_exists($response, 'authcode') ? $response->authcode : null,
            'authamt'=>property_exists($response, 'authamt') ? $response->authamt : null,
            'lidm'=>property_exists($response, 'lidm') ? $response->lidm : null,
            'xid'=>property_exists($response, 'xid') ? $response->xid : null,
            'termseq'=>property_exists($response, 'termseq') ? $response->termseq : null,
            'last4digitpan'=>property_exists($response, 'last4digitpan') ? $response->last4digitpan : null,
            'cardnumber'=>property_exists($response, 'cardnumber') ? $response->cardnumber : null,
            'authresurl'=>property_exists($response, 'authresurl') ? $response->authresurl : null,
            'hostname'=>request()->ip() ? request()->ip() : 'Unknown',
            'os'=>$platform,
            'browser'=>$browser,
            'full_agent_msg'=>$agent,
            'updated_at'=>null,
        ]);
    }
}
