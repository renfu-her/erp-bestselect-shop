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
            ->get();
    }

    public static function getThirdGradeById(int $secondGradeId)
    {
        return DB::table('acc_third_grade')
            ->where('second_grade_fk', '=', $secondGradeId)
            ->get();
    }

    public static function getFourthGradeById(int $fourthGradeId)
    {
        return DB::table('acc_fourth_grade')
            ->where('third_grade_fk', '=', $fourthGradeId)
            ->get();
    }
}
