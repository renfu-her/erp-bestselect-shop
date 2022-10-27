<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

class OrderPayLinePay extends Model
{
    use HasFactory;

    protected $table = 'ord_payment_line_pay_log';
    protected $guarded = [];


    public static function api_send(string $action, $transaction_id = null, $data)
    {
        if(env('APP_ENV') == 'local' || env('APP_ENV') == 'dev'){
            $valid = [
                'channelId'=>'1657553869',
                'channelSecret'=>'c4291dd78821815e6af3ecd209d1db87',
                'nonce'=>Str::uuid(),
            ];

            $basic_path = 'https://sandbox-api-pay.line.me';

        } else {
            // formal env
            $valid = [
                'channelId'=>'1657553869',
                'channelSecret'=>'c4291dd78821815e6af3ecd209d1db87',
                'nonce'=>Str::uuid(),
            ];

            $basic_path = 'https://api-pay.line.me';
        }

        include app_path() . '/Helpers/line_pay_auth_mac.php';

        $transaction_id = $transaction_id ? $transaction_id : 'none';

        $uri_arr = [
            'request' => '/v3/payments/request',
            'confirm' => '/v3/payments/' . $transaction_id . '/confirm',
            'capture' => '/v3/payments/authorizations/' . $transaction_id . '/capture',
            'void' => '/v3/payments/authorizations/' . $transaction_id . '/void',
            'refund' => '/v3/payments/' . $transaction_id . '/refund',
        ];

        $uri = $uri_arr[$action];
        $url = $basic_path . $uri;

        $result = auth_mac($data, $uri, $url, $valid);

        return $result;
    }


    public static function create_log(string $source_type, int $source_id, object $response)
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

        $grade_id = 267;
        $grade_code = '11050010';
        $grade_name = '應收帳款-LINE PAY';
        $action = null;
        $transaction_id = null;
        $authamt = 0;
        $checkout_mode = 'online';
        if(property_exists($response, 'more_info') && count($response->more_info) > 0){
            if(array_key_exists('grade_id', $response->more_info)){
                $grade_id = $response->more_info['grade_id'];
            }
            if(array_key_exists('grade_code', $response->more_info)){
                $grade_code = $response->more_info['grade_code'];
            }
            if(array_key_exists('grade_name', $response->more_info)){
                $grade_name = $response->more_info['grade_name'];
            }
            if(array_key_exists('action', $response->more_info)){
                $action = $response->more_info['action'];
            }
            if(array_key_exists('authamt', $response->more_info)){
                $authamt = $response->more_info['authamt'];
            }
            if(array_key_exists('checkout_mode', $response->more_info)){
                $checkout_mode = $response->more_info['checkout_mode'];
            }
        }

        self::create([
            'source_type'=>$source_type,
            'source_id'=>$source_id,
            'grade_id'=>$grade_id,
            'grade_code'=>$grade_code,
            'grade_name'=>$grade_name,
            'action'=>$action,
            'return_code'=>property_exists($response, 'returnCode') ? $response->returnCode : null,
            'return_message'=>property_exists($response, 'returnMessage') ? $response->returnMessage : null,
            'info'=>property_exists($response, 'info') ? json_encode($response->info) : null,
            'transaction_id'=>property_exists($response, 'info') ? (property_exists($response->info, 'transactionId') ? $response->info->transactionId : $transaction_id) : null,
            'authamt'=>property_exists($response, 'info') && property_exists($response->info, 'payInfo') ? ($response->info->payInfo)[0]->amount : $authamt,
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
