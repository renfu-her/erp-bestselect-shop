<?php

namespace App\Models;

use App\Enums\Supplier\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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

    public function all_grade()
    {
        return $this->belongsTo(AllGrade::class, 'grade_id', 'id');
    }


    public static function storePayableCurrency($req)
    {
        $payableData =self::create([
            'foreign_currency' => $req['foreign_currency']['foreign_price'],
            'rate' => $req['foreign_currency']['rate'],
            'grade_type' => AllGrade::findOrFail($req['foreign_currency']['grade_id_fk'])->grade_type,
            'grade_id' => $req['foreign_currency']['grade_id_fk'],
            'acc_currency_fk' => $req['foreign_currency']['currency'],
        ]);

        AccountPayable::create([
            'pay_order_type' => 'App\Models\PayingOrder',
            'pay_order_id' => $req['pay_order_id'],
            'acc_income_type_fk' => Payment::ForeignCurrency,
            'payable_type' => 'App\Models\PayableForeignCurrency',
            'payable_id' => $payableData->id,
            'tw_price' => $req['tw_price'],
            //            'payable_status' => $req['payable_status'],
            'payment_date' => $req['payment_date'],
            'accountant_id_fk' => Auth::user()->id,
            'note' => $req['note'],
        ]);
    }
}
