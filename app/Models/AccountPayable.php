<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 付款管理
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

}
