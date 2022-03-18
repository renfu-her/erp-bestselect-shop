<?php

namespace App\Models;

use App\Enums\Payable\ChequeStatus;
use App\Enums\Payable\PayableModelType;
use App\Enums\Supplier\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * 付款管理、應付帳款
 */
class AccountPayable extends Model
{
    use HasFactory;

    protected $table = 'acc_payable';
    protected $fillable = [
        'pay_order_type',
        'pay_order_id',
        'acc_income_type_fk',
        'payable_type',
        'payable_id',
        'tw_price',
//        'payable_status',
        'payment_date',
        'accountant_id_fk',
    ];

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

    public static function getChequeStatus()
    {
        $dataArray = [];
        foreach (ChequeStatus::getValues() as $keyValue) {
            $dataArray[] = [
                'id' => $keyValue,
                'status' => ChequeStatus::getDescription($keyValue)
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

    /**
     * @param  $id 2:支票, 3:匯款, 4:外幣 , 5:應付帳款
     * 取得收支科目預設值
     * @return
     */
    public static function getFourthGradeDefaultById($id)
    {
        $result = DB::table('acc_income_expenditure')
            ->where('acc_income_type_fk', '=', $id)
            ->leftJoin('acc_income_type', 'acc_income_expenditure.acc_income_type_fk', '=', 'acc_income_type.id')
            ->leftJoin('acc_fourth_grade', 'acc_income_expenditure.grade_id_fk', '=', 'acc_fourth_grade.id')
            ->leftJoin('acc_currency', 'acc_income_expenditure.acc_currency_fk', '=', 'acc_currency.id')
            ->select(
                'grade_id_fk',
                'acc_currency_fk',
                'type',
                'grade',
                'code',
                'acc_fourth_grade.name',
                'acc_currency.name as currency',
                'acc_currency.id as currency_id',
                'rate'
            )
        ->get();

        $arrayResult = [];
        foreach ($result as $queryData) {
            $arrayResult[] = [
                'grade_id_fk' => $queryData->grade_id_fk,
                'acc_currency_fk' => $queryData->acc_currency_fk,
                'type' => $queryData->type,
                'grade' => $queryData->grade,
                'code' => $queryData->code,
                'name' => $queryData->name,
                'currency' => $queryData->currency,
                'currency_id' => $queryData->currency_id,
                'rate' => $queryData->rate,
            ];
        }

        return $arrayResult;
    }

//    TODO design
    public function getDefaultByTypeId($type_id)
    {
    }

    /**
     * @param  $id 1:現金, 6 其它
     * 取得收支科目預設值
     * @return
     */
    public static function getThirdGradeDefaultById($id)
    {
        $result = DB::table('acc_income_expenditure')
            ->where('acc_income_type_fk', '=', $id)
            ->leftJoin('acc_income_type', 'acc_income_expenditure.acc_income_type_fk', '=', 'acc_income_type.id')
            ->leftJoin('acc_third_grade', 'acc_income_expenditure.grade_id_fk', '=', 'acc_third_grade.id')
            ->select(
                'grade_id_fk',
                'acc_currency_fk',
                'type',
                'grade',
                'code',
                'name'
            )
            ->get();

        $arrayResult = [];
        foreach ($result as $queryData) {
            $arrayResult[] = [
                'grade_id_fk' => $queryData->grade_id_fk,
                'acc_currency_fk' => $queryData->acc_currency_fk,
                'type' => $queryData->type,
                'grade' => $queryData->grade,
                'code' => $queryData->code,
                'name' => $queryData->name,
            ];
        }

        return $arrayResult;
    }
}
