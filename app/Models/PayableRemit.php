<?php

namespace App\Models;

use App\Enums\Supplier\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PayableRemit extends Model
{
    use HasFactory;

    protected $table = 'acc_payable_remit';
    protected $fillable = [
        'grade_type',
        'grade_id',
        'remit_date',
    ];

    /**
     * 取得匯款方式對應到acc_payable table資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function pay()
    {
        return $this->morphOne(AccountPayable::class, 'payable');
    }

    /**
     * 取得用匯款方式對應的科目類別
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function grade()
    {
        return $this->morphTo();
    }

    public static function storePayableRemit($req)
    {
        $payableData =self::create([
            'grade_type' => IncomeExpenditure::getModelNameByPayableTypeId(Payment::Remittance),
            'grade_id' => $req['remit']['grade_id_fk'],
            'remit_date' => $req['remit']['remit_date']
        ]);

        AccountPayable::create([
            'pay_order_type' => 'App\Models\PayingOrder',
            'pay_order_id' => $req['pay_order_id'],
            'acc_income_type_fk' => Payment::Remittance,
            'payable_type' => 'App\Models\PayableRemit',
            'payable_id' => $payableData->id,
            'tw_price' => $req['tw_price'],
            //            'payable_status' => $req['payable_status'],
            'payment_date' => $req['payment_date'],
            'accountant_id_fk' => Auth::user()->id,
            'note' => $req['note'],
        ]);
    }
}
