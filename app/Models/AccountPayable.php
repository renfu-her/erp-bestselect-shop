<?php

namespace App\Models;

use App\Enums\Payable\PayableModelType;
use App\Enums\Supplier\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 付款管理、應付帳款
 */
class AccountPayable extends Model
{
    use HasFactory;

    protected $table = 'acc_payable';
    protected $guarded = [];


    /**
     * 取得不同付款方式對應的table資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function payable()
    {
        return $this->morphTo();
    }

    /**
     * 取得「應付帳款」對應到不同類型的付款單（例如：採購、出貨）
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function payingOrder()
    {
        return $this->morphTo(__FUNCTION__, 'pay_order_type', 'pay_order_id');
    }

    /**
     * 取得所有付款方式
     * @return array key變數名稱  , name:中文描述 , value: primary_id
     */
    public static function getTransactTypeList()
    {
        $dataArray = [];
        foreach (Payment::asArray() as $key => $value) {
            $dataArray[] = [
                'value' => $value,
                'key' => $key,
                'name' => Payment::getDescription($value)
            ];
        }

        return $dataArray;
    }


    /**
     * @param $modelName \App\Models\PayingOrder
     *
     * @return string 採購、出貨、物流
     */
    public static function getPayableNameByModelName($modelName)
    {
        $dataArray = [];
        foreach (PayableModelType::asArray() as $value) {
            $dataArray[$value] = PayableModelType::getDescription($value);
        }
        $name = Payment::getDescription(array_search($modelName, $dataArray, true));

        return $name;
    }
}
