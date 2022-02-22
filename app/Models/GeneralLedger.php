<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GeneralLedger extends Model
{
    use HasFactory;

    public static function getSecondGradeById($firstGradeId)
    {
        return DB::table('acc_second_grade')
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
    }

    public static function getThirdGradeById($secondGradeId)
    {
        return DB::table('acc_third_grade')
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
    }

    public static function getFourthGradeById($fourthGradeId)
    {
        return DB::table('acc_fourth_grade')
            ->where('third_grade_fk', '=', $fourthGradeId)
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
    }

    public static function getDataByGrade($id, string $grade)
    {
        $tabaleNameArray = [
            '1st' => 'acc_first_grade',
            '2nd' => 'acc_second_grade',
            '3rd' => 'acc_third_grade',
            '4th' => 'acc_fourth_grade',
        ];

        $tableName = $tabaleNameArray[$grade];

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
}
