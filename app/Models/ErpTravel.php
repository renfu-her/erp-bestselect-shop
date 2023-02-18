<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class ErpTravel extends Model
{
    use HasFactory;

    public static function getUsers($login_name = null)
    {
        $url = 'https://travel-api.ittms.com.tw/auth/getUsers';
        $body = [];
        if ($login_name) {
            $body['login_name'] = $login_name;
        }

        $response = Http::withoutVerifying()->post($url, $body);

        if ($response->successful()) {
            return $response->json();
        } else {
            return [];
        }

    }

    public static function updateData($login_name, $options = [])
    {
        $fields = ['login_pw', 'status', 'cf', 'ittms_code',
            'agt_flag', 'flag_package', 'flag_ship', 'flag_tax', 'sales_type',
        ];

        $url = 'https://travel-api.ittms.com.tw/auth/update';
        $body = [];

        $body['login_name'] = $login_name;

        foreach($fields as $f){
            $body[$f] = Arr::get($options,$f,'');
        }

        $response = Http::withoutVerifying()->post($url, $body);

        if ($response->successful()) {
            return $response->json();
        } else {
            return null;
        }

    }

}
