<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 科目類別（損益表income statement的分類方式)
 */
class IncomeStatement extends Model
{
    use HasFactory;

    protected $table = 'acc_income_statement';
    protected $fillable = [
        'name'
    ];
}
