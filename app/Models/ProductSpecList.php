<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSpecList extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'prd_speclists';
    protected $guarded = [];

    public static function updateItems($product_id, $items = [])
    {
        self::where('product_id', $product_id)->delete();
       
        self::insert($items);
    }

}
