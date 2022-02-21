<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GeneralLedger extends Model
{
    use HasFactory;

    public static function getSecondGradeById(int $firstGradeId)
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

    public static function getThirdGradeById(int $secondGradeId)
    {
        return DB::table('acc_third_grade')
            ->where('second_grade_fk', '=', $secondGradeId)
            ->leftJoin('acc_company', 'acc_company_fk', '=', 'acc_company.id')
            ->leftJoin('acc_income_statement', 'acc_income_statement_fk', '=', 'acc_income_statement.id')
            ->select(
                'acc_third_grade.id',
                'acc_third_grade.code',
                'acc_third_grade.name',
                'acc_third_grade.note_1',
                'acc_third_grade.note_2',
                'acc_company.company',
                'acc_income_statement.name as category'
            )
            ->get();
    }

    public static function getFourthGradeById(int $fourthGradeId)
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
}
