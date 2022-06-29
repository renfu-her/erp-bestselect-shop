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
        $ipaddress = '';

        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'Unknown';

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

        $installment = 'none';
        $ckeckout_date = date("Y-m-d H:i:s");
        $card_type_code = null;
        $card_type = null;
        $card_owner_name = null;
        $all_grades_id = 0;
        $checkout_area_code = 'taipei';
        $checkout_area = '台北';
        $requested = 'n';
        $card_nat = 'local';
        $checkout_mode = 'online';
        if(property_exists($response, 'more_info') && count($response->more_info) > 0){
            $installment = $response->more_info['installment'];
            $ckeckout_date = $response->more_info['ckeckout_date'];
            $card_type_code = $response->more_info['card_type_code'];
            $card_type = $response->more_info['card_type'];
            $card_owner_name = $response->more_info['card_owner_name'];
            $all_grades_id = $response->more_info['all_grades_id'];
            $checkout_area_code = $response->more_info['checkout_area_code'];
            $checkout_area = $response->more_info['checkout_area'];
            $requested = $response->more_info['requested'];
            $card_nat = $response->more_info['card_nat'];
            $checkout_mode = $response->more_info['checkout_mode'];
        }

        self::create([
            'order_id'=>$order_id,
            'status'=>property_exists($response, 'status') ? $response->status : null,
            'errcode'=>property_exists($response, 'errcode') ? $response->errcode : null,
            'errdesc'=>property_exists($response, 'errdesc') ? ( mb_convert_encoding(trim($response->errdesc, "\x00..\x08"), 'UTF-8', ['BIG5', 'UTF-8']) !== 'null' ? mb_convert_encoding(trim($response->errdesc, "\x00..\x08"), 'UTF-8', ['BIG5', 'UTF-8']) : null ) : null,
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

            'installment'=>$installment,
            'ckeckout_date'=>$ckeckout_date,
            'card_type_code'=>$card_type_code,
            'card_type'=>$card_type,
            'card_owner_name'=>$card_owner_name,
            'all_grades_id'=>$all_grades_id,
            'checkout_area_code'=>$checkout_area_code,
            'checkout_area'=>$checkout_area,
            'requested'=>$requested,
            'card_nat'=>$card_nat,
            'checkout_mode'=>$checkout_mode,

            'hostname_external'=>$ipaddress,
            'hostname_internal'=>request()->ip() ? request()->ip() : 'Unknown',// $_SERVER['REMOTE_ADDR']
            'os'=>$platform,
            'browser'=>$browser,
            'full_agent_msg'=>$agent,
            'updated_at'=>null,
        ]);
    }
}
