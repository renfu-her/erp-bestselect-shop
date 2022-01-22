<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 規格「項目」 Model
 */
class ProductSpecItem extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'prd_spec_items';
    protected $guarded = [];

    /**
     *
     * 建立單一產品所有的規格、項目
     * @param int $product_id
     * @param int $spec_id Table prd_spec的 primary key
     * @param array|string $title 項目名稱
     * @return void
     */
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
