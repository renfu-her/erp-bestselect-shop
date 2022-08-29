<?php

namespace App\Models;

use App\Enums\Customer\Login;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 消費者註冊登入方式
 */
class CustomerLoginMethod extends Model
{
    use HasFactory;

    protected $table = 'usr_customer_login_method';
    protected $guarded = [];


    //綁定消費者與第三方登入
    public static function createData($usr_customers_id_fk, $login_method, $uid) {
        if (isset($usr_customers_id_fk) && isset($login_method) && isset($uid)) {
            $data = self::create([
                'usr_customer_id_fk' => $usr_customers_id_fk,
                'method'        => $login_method,
                'uid'        => $uid,
            ]);
            return $data->id;
        }
    }
}
