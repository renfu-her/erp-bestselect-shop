<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 會計分類（資產負債表balance sheet的分類方式）
 */
class BalanceSheet extends Model
{
    use HasFactory;

    protected $table = 'acc_balance_sheet';
    protected $fillable = [
        'name'
    ];
}
