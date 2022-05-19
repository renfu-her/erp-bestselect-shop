<?php

namespace App\Models;

use App\Enums\Supplier\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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

    public function all_grade()
    {
        return $this->belongsTo(AllGrade::class, 'grade_id', 'id');
    }

    public static function storePayableCheque($req)
    {
        $payableData =self::create([
            'check_num' => $req['cheque']['check_num'],
            'grade_type' => AllGrade::findOrFail($req['cheque']['grade_id_fk'])->grade_type,
            'grade_id' => $req['cheque']['grade_id_fk'],
            'maturity_date' => $req['cheque']['maturity_date'],
            'cash_cheque_date' => $req['cheque']['cash_cheque_date'],
            'cheque_status' => $req['cheque']['cheque_status'],
        ]);

        AccountPayable::create([
            'pay_order_type' => 'App\Models\PayingOrder',
            'pay_order_id' => $req['pay_order_id'],
            'acc_income_type_fk' => Payment::Cheque,
            'payable_type' => 'App\Models\PayableCheque',
            'payable_id' => $payableData->id,
            'all_grades_id' => $req['cheque']['grade_id_fk'],
            'tw_price' => $req['tw_price'],
            //            'payable_status' => $req['payable_status'],
            'payment_date' => $req['payment_date'],
            'accountant_id_fk' => Auth::user()->id,
            'note' => $req['note'],
        ]);
    }
}
