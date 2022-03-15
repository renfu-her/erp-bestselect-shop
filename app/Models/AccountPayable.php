<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 付款管理、應付帳款
 */
class AccountPayable extends Model
{
    use HasFactory;

    protected $table = 'acc_payable';

    /**
     * 取得不同付款方式對應的table資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function payable()
    {
        return $this->morphTo();
    }

    /**
     * 取得「應付帳款」對應到不同類型的付款單（例如：採購、出貨）
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function payingOrder()
    {
        return $this->morphTo(__FUNCTION__, 'pay_order_type', 'pay_order_id');
    }

}
