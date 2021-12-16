<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImg extends Model
{
    use HasFactory;
    protected $table = 'prd_product_images';
    protected $guarded = [];

    /**
     * @param $product_id
     * @param String/Array[String] $images [String/Array]
     */

    public static function createImgs($product_id, $images)
    {
        if (!$images) {
            return;
        }

        if (gettype($images) == 'array') {
            self::insert(array_map(function ($v) use ($product_id) {
                return ['product_id' => $product_id, 'url' => $v];
            }, $images));
        } else {
            self::create(['product_id' => $product_id, 'url' => $images]);
        }
    }
}
