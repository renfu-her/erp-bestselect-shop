<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStyleCombo extends Model
{
    use HasFactory;
    protected $table = 'prd_style_combos';
    protected $guarded = [];
    public $timestamps = false;

    public static function createCombo($style_id, $child_id, $qty)
    {
        if (self::where('product_style_id', $style_id)->where('product_style_child_id', $child_id)->get()->first()) {
            return false;
        }

        self::create(['product_style_id' => $style_id, 'product_style_child_id' => $child_id, 'qty' => $qty]);
    }

}
