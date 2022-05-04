<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

use App\Enums\Accounting\GradeModelClass;
use App\Enums\Accounting\ItemNameGradeDefault;

class PayableDefault extends Model
{
    use HasFactory;

    protected $table = 'acc_payable_default';

    protected $guarded = [];


    /**
     * 取得所有外匯收支設定選項
     *
     * @return array
     * 'selectedCurrencyResult' => 外匯已選擇的會計科目
     * 'allGradeOptions' => 所有能選擇項目
     */
    public static function getCurrencyOptionData()
    {
        $selectedCurrencyResult = DB::table('acc_payable_default')
            ->leftJoin('acc_currency', 'acc_currency.payable_default_fk', '=', 'acc_payable_default.id')
            ->where('acc_payable_default.name', 'foreign_currency')
            ->select(
                'acc_payable_default.default_grade_id',
                'acc_currency.id as currency_id',
                'acc_currency.name',
                'acc_currency.rate',
            )
            ->get();


        return [
            'selectedCurrencyResult' => $selectedCurrencyResult,
        ];
    }


    public static function updateCurrency(array $validatedReq)
    {
        $currency = DB::table('acc_payable_default')->where('name', '=', 'foreign_currency')->get();
        $currency_key = 0;

        foreach ($validatedReq['currency'] as $key => $value) {
            DB::table('acc_currency')->where('id', '=', $key)->update([
                    'rate' => $value['rate'],
                    'payable_default_fk' => $currency[$currency_key]->id,
                ]);

            self::where([
                'id'=>$currency[$currency_key]->id,
                'name'=>'foreign_currency',
            ])->update([
                'default_grade_id' => $value['gradeOption'] ?? null
            ]);
            $currency_key++;
        }
    }

    public static function updateIncomeExpenditure(array $validatedReq)
    {
        DB::table('acc_payable_default')
            ->where('name', '!=', 'foreign_currency')
            ->where('name', '!=', 'product')
            ->where('name', '!=', 'logistics')
            ->delete();

        if (isset($validatedReq['income_type'])) {
            foreach ($validatedReq['income_type'] as $acc_income_type => $selectedOptions) {
                foreach ($selectedOptions as $selectedOption) {
                    DB::table('acc_payable_default')
                        ->insert([
                            'name' => $acc_income_type,
                            'default_grade_id' => $selectedOption,
                        ]);
                }
            }
        }
    }

    /**
     * @param $type int use Enum Payment class in App\Enums\Payment
     * 取得付款選項的會計科目
     *
     * @return string grade model class name Eg,App\Models\FourthGrade
     */
    public static function getModelNameByPayableTypeId($type)
    {
        return DB::table('acc_all_grades')
            ->find($type, ['grade_type'])
            ->grade_type;
    }
}
