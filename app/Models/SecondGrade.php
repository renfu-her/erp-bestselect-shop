<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SecondGrade extends Model
{
    use HasFactory;

    protected $table = 'acc_second_grade';

    /**
     * 取得第2級科目現金資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payableCash()
    {
        return $this->morphMany(PayableCash::class, 'grade');
    }

    /**
     * 取得第2級科目支票資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payableCheque()
    {
        return $this->morphMany(PayableCheque::class, 'grade');
    }

    /**
     * 取得第2級科目匯款資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payableRemit()
    {
        return $this->morphMany(PayableRemit::class, 'grade');
    }

    /**
     * 取得第2級科目外幣資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payableForeignCurrency()
    {
        return $this->morphMany(PayableForeignCurrency::class, 'grade');
    }

    /**
     * 取得第2級科目「應付帳款」資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payableAccount()
    {
        return $this->morphMany(PayableAccount::class, 'grade');
    }

    /**
     * 取得第2級科目其它資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payableOther()
    {
        return $this->morphMany(PayableOther::class, 'grade');
    }
}
