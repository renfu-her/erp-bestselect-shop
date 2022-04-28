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
            $ipaddress = 'UNKNOWN';

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
            'hostname'=>$ipaddress,
            'updated_at'=>null,
        ]);
    }
}
