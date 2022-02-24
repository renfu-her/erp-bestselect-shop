<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GeneralLedger extends Model
{
    use HasFactory;

    private const GRADE_TABALE_NAME_ARRAY
        = [
            '1' => 'acc_first_grade',
            '2' => 'acc_second_grade',
            '3' => 'acc_third_grade',
            '4' => 'acc_fourth_grade',
        ];

    public static function getAllFirstGrade()
    {
        $stdResult = DB::table('acc_first_grade')
            ->leftJoin('acc_company', 'acc_company_fk', '=', 'acc_company.id')
            ->leftJoin('acc_income_statement', 'acc_income_statement_fk', '=', 'acc_income_statement.id')
            ->select(
                'acc_first_grade.id',
                'acc_first_grade.code',
                'acc_first_grade.has_next_grade',
                'acc_first_grade.name',
                'acc_first_grade.note_1',
                'acc_first_grade.note_2',
                'acc_company.company',
                'acc_income_statement.name as category'
            )
            ->get();

        if (!$stdResult) {
            return array();
        }

        return json_decode(json_encode($stdResult), true);
    }

    public static function getSecondGradeById($firstGradeId)
    {
        $stdResult = DB::table('acc_second_grade')
            ->where('first_grade_fk', '=', $firstGradeId)
            ->leftJoin('acc_company', 'acc_company_fk', '=', 'acc_company.id')
            ->leftJoin('acc_income_statement', 'acc_income_statement_fk', '=', 'acc_income_statement.id')
            ->select(
                'acc_second_grade.id',
                'acc_second_grade.code',
                'acc_second_grade.name',
                'acc_second_grade.note_1',
                'acc_second_grade.note_2',
                'acc_company.company',
                'acc_income_statement.name as category'
            )
            ->get();

        if (!$stdResult) {
            return array();
        }

        return json_decode(json_encode($stdResult), true);
    }

    public static function getThirdGradeById($secondGradeId)
    {
        $stdResult = DB::table('acc_third_grade')
            ->where('second_grade_fk', '=', $secondGradeId)
            ->leftJoin('acc_company', 'acc_company_fk', '=', 'acc_company.id')
            ->leftJoin('acc_income_statement', 'acc_income_statement_fk', '=', 'acc_income_statement.id')
            ->select(
                'acc_third_grade.id',
                'acc_third_grade.code',
                'acc_third_grade.name',
                'acc_third_grade.has_next_grade',
                'acc_third_grade.note_1',
                'acc_third_grade.note_2',
                'acc_company.company',
                'acc_income_statement.name as category'
            )
            ->get();

        if (!$stdResult) {
            return array();
        }
        return json_decode(json_encode($stdResult), true);
    }

    public static function getFourthGradeById($thirdGradeId)
    {
         $stdResult = DB::table('acc_fourth_grade')
            ->where('third_grade_fk', '=', $thirdGradeId)
            ->leftJoin('acc_company', 'acc_company_fk', '=', 'acc_company.id')
            ->leftJoin('acc_income_statement', 'acc_income_statement_fk', '=', 'acc_income_statement.id')
            ->select(
                'acc_fourth_grade.id',
                'acc_fourth_grade.code',
                'acc_fourth_grade.name',
                'acc_fourth_grade.note_1',
                'acc_fourth_grade.note_2',
                'acc_company.company',
                'acc_income_statement.name as category'
            )
            ->get();

        if (!$stdResult) {
            return array();
        }
        return json_decode(json_encode($stdResult), true);
    }

    public static function getDataByGrade($id, string $grade)
    {
        $tableName = self::GRADE_TABALE_NAME_ARRAY[$grade];

        return DB::table($tableName)
            ->where($tableName . '.id', '=', $id)
            ->leftJoin('acc_company', 'acc_company_fk', '=', 'acc_company.id')
            ->leftJoin('acc_income_statement', 'acc_income_statement_fk', '=', 'acc_income_statement.id')
            ->select(
                $tableName . '.id',
                $tableName . '.code',
                $tableName . '.name',
                $tableName . '.has_next_grade',
                $tableName . '.note_1',
                $tableName . '.note_2',
                'acc_company.company',
                'acc_income_statement.name as category'
            )
            ->get();
    }

    /**
     * @
     * @param  string  $currentCode 現有的科目代碼
     * @param  string  $newGrade  新的科目代碼是第幾級？ [1, 2, 3, 4]
     * 產生新的科目代碼
     *
     * @return int 回傳新的科目代碼
     */
    public static function generateCode(string $currentCode, string $newGrade)
    {
        $newGradeNum = $newGrade;
        $isGenerateInSameGrade = self::getGradeByCode($currentCode) === $newGradeNum;

        $result = '';
        if ($isGenerateInSameGrade) {
            if ($newGradeNum === '1') {
                $result = DB::table(self::GRADE_TABALE_NAME_ARRAY[$newGradeNum])
                    ->select('code')
                    ->orderByRaw('CONVERT(code, SIGNED) DESC')
                    ->first();
            } elseif ($newGradeNum === '2') {
                $result = DB::table(self::GRADE_TABALE_NAME_ARRAY[$newGradeNum])
                    ->where('code', 'like', substr($currentCode, 0, 1) . '%')
                    ->select('code')
                    ->orderByRaw('CONVERT(code, SIGNED) DESC')
                    ->first();
            } elseif ($newGradeNum === '3') {
                $result = DB::table(self::GRADE_TABALE_NAME_ARRAY[$newGradeNum])
                    ->where('code', 'like', substr($currentCode, 0, 2) . '%')
                    ->select('code')
                    ->orderByRaw('CONVERT(code, SIGNED) DESC')
                    ->first();
            } elseif ($newGradeNum === '4') {
                $result = DB::table(self::GRADE_TABALE_NAME_ARRAY[$newGradeNum])
                    ->where('code', 'like', substr($currentCode, 0, 4) .'%')
                    ->select('code')
                    ->orderByRaw('CONVERT(code, SIGNED) DESC')
                    ->first();
            }
        } else {
            if ($newGradeNum === '2') {
                $result = DB::table(self::GRADE_TABALE_NAME_ARRAY[$newGradeNum])
                    ->where('code', 'like', $currentCode . '%')
                    ->select('code')
                    ->orderByRaw('CONVERT(code, SIGNED) DESC')
                    ->first();
            } elseif ($newGradeNum === '3') {
                $result = DB::table(self::GRADE_TABALE_NAME_ARRAY[$newGradeNum])
                    ->where('code', 'like', $currentCode . '%')
                    ->select('code')
                    ->orderByRaw('CONVERT(code, SIGNED) DESC')
                    ->first();
            } elseif ($newGradeNum === '4') {
                $result = DB::table(self::GRADE_TABALE_NAME_ARRAY[$newGradeNum])
                    ->where('code', 'like', $currentCode . '%')
                    ->select('code')
                    ->orderByRaw('CONVERT(code, SIGNED) DESC')
                    ->first();
            }
        }

        if (is_null($result)) {
            if ($newGradeNum === '1') {
                return intval('1');
            } elseif ($newGradeNum === '2') {
                return intval($currentCode . '1');
            } elseif ($newGradeNum === '3') {
                return intval($currentCode . '01');
            } elseif ($newGradeNum === '4') {
                return intval($currentCode . '0001');
            }
        }

        return intval($result->code) + 1;
    }

    public static function getGradeByCode(string $code)
    {
        $codeLength = strlen($code);
        $currentGrade = '';
        if ($codeLength === 1) {
            $currentGrade = '1';
        } elseif ($codeLength === 2) {
            $currentGrade = '2';
        } elseif ($codeLength === 4) {
            $currentGrade = '3';
        } elseif ($codeLength === 8) {
            $currentGrade = '4';
        }
        return $currentGrade;
    }

    public static function storeGradeData(array $req, string $grade)
    {
        $newCode = self::generateCode($req['code'], $grade);
        $tableName = self::GRADE_TABALE_NAME_ARRAY[$grade];

        if (strlen($newCode) > 1) {
            $prevGrade  = strval(intval($grade) - 1);
            $prevTableName = self::GRADE_TABALE_NAME_ARRAY[$prevGrade];
        }
        $FOREIGN_KEY_ARRAY = [
            '1' => 'first_grade_fk',
            '2' => 'second_grade_fk',
            '3' => 'third_grade_fk'
        ];

        $prevCode = '';
        if (strlen($newCode) === 2) {
            $prevCode = substr($newCode, 0, 1);
        } elseif (strlen($newCode) === 4) {
            $prevCode = substr($newCode, 0, 2);
        } elseif (strlen($newCode) === 8) {
            $prevCode = substr($newCode, 0, 4);
        }

        $insertData = [
            'name' => $req['name'],
            'code' => $newCode,
            'has_next_grade' => $req['has_next_grade'],
            'acc_company_fk' => $req['acc_company_fk'],
            'acc_income_statement_fk' => $req['acc_income_statement_fk'],
            'note_1' => $req['note_1'],
            'note_2' => $req['note_2'],
        ];

        if (strlen($newCode) > 1) {
            $prevGradeFk = DB::table($prevTableName)
                ->where('code', '=', $prevCode)
                ->select('id')
                ->get();
            $insertData[$FOREIGN_KEY_ARRAY[$prevGrade]] = $prevGradeFk[0]->id;
        }

        DB::table($tableName)
            ->insert($insertData);
    }
}
