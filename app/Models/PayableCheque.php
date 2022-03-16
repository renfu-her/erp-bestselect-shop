<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayableCheque extends Model
{
    use HasFactory;

    protected $table = 'acc_payable_cheque';
    protected $fillable = [
            'grade_type',
            'grade_id',
            'check_num',
            'maturity_date',
            'cash_cheque_date',
            'cheque_status',
        ];

    /**
     * 取得支票方式對應到acc_payable table資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function pay()
    {
        return $this->morphOne(AccountPayable::class, 'payable');
    }

    /**
     * 取得用支票方式對應的科目類別
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function grade()
    {
        return $this->morphTo();
    }
}
