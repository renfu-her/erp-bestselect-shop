<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayableCash extends Model
{
    use HasFactory;

    protected $table = 'acc_payable_cash';

    /**
     * 取得現金方式對應到acc_payable table資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function pay()
    {
        return $this->morphOne(AccountPayable::class, 'payable')->withDefault();
    }

    /**
     * 取得用現金方式對應的科目類別
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function grade()
    {
        return $this->morphTo();
    }
}
