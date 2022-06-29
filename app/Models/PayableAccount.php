<?php

namespace App\Models;

use App\Enums\Supplier\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PayableAccount extends Model
{
    use HasFactory;

    protected $table = 'acc_payable_account';
    protected $fillable = [
        'grade_type',
        'grade_id',
    ];

    /**
     * 取得「應付帳款」方式對應到acc_payable table資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function pay()
    {
        return $this->morphOne(AccountPayable::class, 'payable');
    }

    /**
     * 取得用「應付帳款」方式對應的科目類別
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

    public static function storePayablePayableAccount($req)
    {
        $payableData =self::create([
            'grade_type' => AllGrade::findOrFail($req['payable_account']['grade_id_fk'])->grade_type,
            'grade_id' => $req['payable_account']['grade_id_fk'],
        ]);

        AccountPayable::create([
            'pay_order_type' => 'App\Models\PayingOrder',
            'pay_order_id' => $req['pay_order_id'],
            'acc_income_type_fk' => Payment::AccountsPayable,
            'payable_type' => 'App\Models\PayableAccount',
            'payable_id' => $payableData->id,
            'all_grades_id' => $req['payable_account']['grade_id_fk'],
            'tw_price' => $req['tw_price'],
            //            'payable_status' => $req['payable_status'],
            'payment_date' => $req['payment_date'],
            'accountant_id_fk' => Auth::user()->id,
            'summary' => $req['summary'] ?? '',
            'note' => $req['note'] ?? '',
        ]);
    }
}
