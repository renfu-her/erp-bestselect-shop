<?php

namespace App\Models;

use App\Enums\Supplier\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PayableOther extends Model
{
    use HasFactory;

    protected $table = 'acc_payable_other';
    protected $fillable = [
        'grade_type',
        'grade_id',
    ];

    /**
     * 取得「其它」方式對應到acc_payable table資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function pay()
    {
        return $this->morphOne(AccountPayable::class, 'payable');
    }

    /**
     * 取得用「其它」方式對應的科目類別
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

    public static function storePayableOther($req)
    {
        $payableData =self::create([
            'grade_type' => AllGrade::findOrFail($req['other']['grade_id_fk'])->grade_type,
            'grade_id' => $req['other']['grade_id_fk']
        ]);

        AccountPayable::create([
            'pay_order_type' => 'App\Models\PayingOrder',
            'pay_order_id' => $req['pay_order_id'],
            'acc_income_type_fk' => Payment::Other,
            'payable_type' => 'App\Models\PayableOther',
            'payable_id' => $payableData->id,
            'all_grades_id' => $req['other']['grade_id_fk'],
            'tw_price' => $req['tw_price'],
            //            'payable_status' => $req['payable_status'],
            'payment_date' => $req['payment_date'],
            'accountant_id_fk' => Auth::user()->id,
            'summary' => $req['summary'] ?? '',
            'note' => $req['note'] ?? '',
        ]);
    }
}
