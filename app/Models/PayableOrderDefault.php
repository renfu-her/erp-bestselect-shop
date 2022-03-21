<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayableOrderDefault extends Model
{
    use HasFactory;

    protected $table = 'acc_payable_default';

    public static function updatePayableOrderDefault($grade_ids)
    {
        self::where('id', '=', 1)
            ->update([
                'product_default_grade_id' => $grade_ids['product'],
                'logistics_default_grade_id' => $grade_ids['logistics'],
            ]);
    }
}
