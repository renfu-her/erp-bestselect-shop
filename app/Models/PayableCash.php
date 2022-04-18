<?php

namespace App\Models;

use App\Enums\Supplier\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PayableCash extends Model
{
    use HasFactory;

    protected $table = 'acc_payable_cash';
    protected $fillable = [
        'grade_type',
        'grade_id',
    ];

    /**
     * 取得現金方式對應到acc_payable table資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function pay()
    {
        return $this->morphOne(AccountPayable::class, 'payable');
    }

    /**
     * 取得用現金方式對應的科目類別
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

    public static function storePayableCash($req)
    {
        $payableData =self::create([
            'grade_type' => AllGrade::findOrFail($req['cash']['grade_id_fk'])->grade_type,
            'grade_id' => $req['cash']['grade_id_fk']
        ]);

//        $payOrder = PayingOrder::find($req['pay_order_id']);
        AccountPayable::create([
            'pay_order_type' => 'App\Models\PayingOrder',
            'pay_order_id' => $req['pay_order_id'],
            'acc_income_type_fk' => Payment::Cash,
            'payable_type' => 'App\Models\PayableCash',
            'payable_id' => $payableData->id,
            'tw_price' => $req['tw_price'],
            //            'payable_status' => $req['payable_status'],
            'payment_date' => $req['payment_date'],
            'accountant_id_fk' => Auth::user()->id,
            'note' => $req['note'],
        ]);
    }
}
