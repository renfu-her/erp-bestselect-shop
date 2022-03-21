<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 用來對應第1～4級會計科目table資料
 * grade_type對應的model class name
 * grade_id對應到該會計科目的Id
 */
class AllGrade extends Model
{
    use HasFactory;
    protected $table = 'acc_all_grades';
    protected $fillable = [
        'grade_type',
        'grade_id',
    ];

    /**
     * 取得第1～4級會計科目的資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function eachGrade()
    {
        return $this->morphTo(__FUNCTION__, 'grade_type', 'grade_id');
    }
}
