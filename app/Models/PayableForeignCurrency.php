<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayableForeignCurrency extends Model
{
    use HasFactory;

    protected $table = 'acc_payable_currency';
    protected $fillable = [
        'grade_type',
        'grade_id',
        'foreign_currency',
        'rate',
        'acc_currency_fk',
    ];

    /**
     * 取得外幣方式對應到acc_payable table資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function pay()
    {
        return $this->morphOne(AccountPayable::class, 'payable');
    }

    /**
     * 取得用外幣方式對應的科目類別
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function grade()
    {
        return $this->morphTo();
    }
}
