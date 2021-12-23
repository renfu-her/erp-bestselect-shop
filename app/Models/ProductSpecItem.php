<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSpecItem extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'prd_spec_items';
    protected $guarded = [];

    public static function createItems($product_id, $spec_id, $title)
    {

        if (gettype($title) == 'array') {
            self::insert(array_map(function ($v) use ($product_id, $spec_id) {      
                return ['product_id' => $product_id, 'spec_id' => $spec_id, 'title' => trim($v)];
            }, $title));
        } else {
            self::create(['product_id' => $product_id, 'spec_id' => $spec_id, 'title' => trim($title)]);
        }
    }
}
