<?php

namespace App\Models;

use App\Enums\Accounting\GradeModelClass;
use App\Enums\Accounting\ItemNameGradeDefault;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeDefault extends Model
{
    use HasFactory;

    protected $table = 'acc_grade_default';


    /**
     * @param string \App\Enums\Accounting\ItemGradeDefault constant $name
     * @param int $each_grade_id 各會計層級的grade_id
     *
     * @return void
     */
    public static function updateGradeDefault($name, $each_grade_id)
    {
        $GRADE_DEFAULT = [
            ItemNameGradeDefault::Product => GradeModelClass::getDescription(GradeModelClass::ThirdGrade),
        ];

        $allGrade = $GRADE_DEFAULT[$name]::find($each_grade_id)->allGrade;

        self::where('name', '=', $name)
            ->update([
                'default_grade_id' => $allGrade->id,
            ]);
    }
}
