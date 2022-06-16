<?php

namespace App\Models;

use App\Enums\Customer\Login;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 消費者註冊登入方式
 */
class CustomerLogin extends Model
{
    use HasFactory;

    protected $table = 'usr_customers_login';

    protected $fillable = [
        'usr_customers_id_fk',
        'login_method',
    ];

    /**
     * 新增消費者註冊登入方式
     * @param int $usr_customers_id_fk table usr_customer id
     * @param  array|string|null  $loginMethods
     *
     * @return void
     */
    public static function addLoginMethod($usr_customers_id_fk, $loginMethods)
    {
        if (is_null($loginMethods)) {
            return;
        } elseif (is_string($loginMethods)) {
            self::create([
                'usr_customers_id_fk' => $usr_customers_id_fk,
                'login_method'        => Login::getValue(strtoupper($loginMethods)),
            ]);
        } elseif (is_array($loginMethods)) {
            foreach ($loginMethods as $loginMethod) {
                self::create([
                    'usr_customers_id_fk' => $usr_customers_id_fk,
                    'login_method'        => Login::getValue(strtoupper($loginMethod)),
                ]);
            }
        }
    }
}
