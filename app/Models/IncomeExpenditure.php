<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class IncomeExpenditure extends Model
{
    use HasFactory;

    /**
     * 取得所有外匯收支設定選項
     *
     * @return array
     * 'selectedCurrencyResult' => 外匯已選擇的會計科目
     * 'allGradeOptions' => 所有能選擇項目
     */
    public static function getCurrencyOptionData()
    {
        $currencyGrade = DB::table('acc_income_expenditure')
            ->whereNotNull('acc_currency_fk')
            ->limit(1)
            ->leftJoin('acc_income_type', 'acc_income_type_fk', '=', 'acc_income_type.id')
            ->select('grade')
            ->get()
            ->first()
            ->grade;

        $selectedCurrencyResult = DB::table('acc_income_expenditure')
            ->whereNotNull('acc_currency_fk')
            ->leftJoin('acc_currency', 'acc_currency_fk', '=', 'acc_currency.id')
            ->select(
                'acc_income_expenditure.grade_id_fk',
                'acc_income_expenditure.acc_currency_fk',
                'acc_currency.name',
                'acc_currency.rate',
            )
            ->get();

        $allGradeOptions = GeneralLedger::getGradeData($currencyGrade);

        return [
            'selectedCurrencyResult' => $selectedCurrencyResult,
            'allGradeOptions'        => $allGradeOptions,
        ];
    }

    /**
     * 取得所有收支設定選項
     *
     * @param  int  $grade  科目代碼是第幾級？ [1, 2, 3, 4]
     *
     * @return array
     * 'selectedResult' => 已選擇的會計科目
     * 'allGradeOptions' => 所有能選擇項目
     */
    public static function getOptionDataByGrade(int $grade)
    {
        $gradeNum = DB::table('acc_income_expenditure')
            ->leftJoin('acc_income_type', 'acc_income_expenditure.acc_income_type_fk', '=', 'acc_income_type.id')
            ->where('acc_income_type.grade', '=', $grade)
            ->select('acc_income_type.grade')
            ->get()
            ->first()
            ->grade;

        $gradeTableName = GeneralLedger::GRADE_TABALE_NAME_ARRAY[$gradeNum];
        $query = DB::table('acc_income_expenditure')
            ->leftJoin($gradeTableName, $gradeTableName.'.id', '=', 'acc_income_expenditure.grade_id_fk')
            ->leftJoin('acc_income_type', 'acc_income_type_fk', '=', 'acc_income_type.id')
            ->where('acc_income_type.type', '<>', '外幣')
            ->select(
                'acc_income_expenditure.acc_income_type_fk',
                'acc_income_expenditure.grade_id_fk',
                'acc_income_type.type',
            )
            ->get()
            ->groupBy('type');

        $allGradeOptions = GeneralLedger::getGradeData($gradeNum);

        $selectedResult = [];
        foreach ($query as $typeName => $dataItem) {
            $temp = [];
            foreach ($dataItem as $data) {
                $temp[] = $data->grade_id_fk;
            }
            $selectedResult[$typeName] = [
                'grade_id_fk_arr' => $temp,
                'acc_income_type_fk' => $dataItem[0]->acc_income_type_fk
            ];
        }

        return [
            'selectedResult'  => $selectedResult,
            'allGradeOptions' => $allGradeOptions,
        ];
    }

    public static function updateCurrency(array $validatedReq)
    {
        foreach ($validatedReq['currency'] as $acc_currency_id => $currency) {
            DB::table('acc_currency')
                ->where('id', '=', $acc_currency_id)
                ->update(['rate' => $currency['rate']]);

            DB::table('acc_income_expenditure')
                ->where('acc_currency_fk', '=', $acc_currency_id)
                ->update(['grade_id_fk' => $currency['gradeOption'] ?? null]);
        }
    }

    public static function updateIncomeExpenditure(array $validatedReq)
    {
        DB::table('acc_income_expenditure')
            ->whereNull('acc_currency_fk')
            ->delete();

        if (isset($validatedReq['income_type'])) {
            foreach ($validatedReq['income_type'] as $acc_income_type => $selectedOptions) {
                foreach ($selectedOptions as $selectedOption) {
                    DB::table('acc_income_expenditure')
                        ->insert([
                            'acc_income_type_fk' => $acc_income_type,
                            'grade_id_fk'        => $selectedOption,
                        ]);
                }
            }
        }

        $emptyIncomeType = DB::table('acc_income_type')
            ->whereNotIn('id', function ($query) {
                $query->select('acc_income_type_fk')
                    ->from('acc_income_expenditure');
            })
            ->select('acc_income_type.id')
            ->get();

        foreach ($emptyIncomeType as $emptyItem) {
            DB::table('acc_income_expenditure')
                ->insert([
                    'acc_income_type_fk' => $emptyItem->id,
                ]);
        }
    }
}
