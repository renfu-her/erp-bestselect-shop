<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 會計分類(一級科目）
 */
class FirstGrade extends Model
{
    use HasFactory;

    protected $table = 'acc_first_grade';
    protected $fillable = [
        'code',
        'name',
        'has_next_grade',
        'acc_company_fk',
        'income_statement_fk'
    ];

    /**
     * 取得第1級科目現金資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payableCash()
    {
        return $this->morphMany(PayableCash::class, 'grade');
    }

    /**
     * 取得第1級科目支票資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payableCheque()
    {
        return $this->morphMany(PayableCheque::class, 'grade');
    }

    /**
     * 取得第1級科目匯款資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payableRemit()
    {
        return $this->morphMany(PayableRemit::class, 'grade');
    }

    /**
     * 取得第1級科目外幣資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payableForeignCurrency()
    {
        return $this->morphMany(PayableForeignCurrency::class, 'grade');
    }

    /**
     * 取得第1級科目「應付帳款」資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payableAccount()
    {
        return $this->morphMany(PayableAccount::class, 'grade');
    }

    /**
     * 取得第1級科目其它資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payableOther()
    {
        return $this->morphMany(PayableOther::class, 'grade');
    }
}
