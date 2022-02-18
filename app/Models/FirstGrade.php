<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 會計分類(一級科目）
 */
class FirstGrade extends Model
{
    use HasFactory;

    protected $table = 'acc_first_grade';
    protected $fillable = [
        'name'
    ];
}
