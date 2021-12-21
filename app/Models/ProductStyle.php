<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ProductStyle extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'prd_product_styles';
    protected $guarded = [];

    public static function createStyle($product_id, $item_ids, $is_active = 1)
    {

        $product = Product::where('id', $product_id)->get()->first();
        if (!$product) {
            return;
        }

        $spec = DB::table('prd_product_spec as ps')
            ->leftJoin('prd_spec_items as item', 'ps.spec_id', '=', 'item.spec_id')
            ->where('ps.product_id', $product_id)
            ->whereIn('item.id', $item_ids)
            ->select('item.title', 'item.id')
            ->orderBy('ps.rank', 'ASC')->get()->toArray();

        foreach ($spec as $key => $v) {
            $data['spec_item' . ($key + 1) . '_id'] = $v->id;
            $data['spec_item' . ($key + 1) . '_title'] = $v->title;
        }

        $data['product_id'] = $product_id;

        $data['is_active'] = $is_active;

        self::create($data);

    }

    public static function updateStyle($product_id, $id)
    {

        /*
    $product = Product::where('id', $product_id)->get()->first();

    $spec = DB::table('prd_product_spec as ps')
    ->leftJoin('prd_spec_items as item', 'ps.spec_id', '=', 'item.spec_id')
    ->where('ps.product_id', $product_id)
    ->whereIn('item.id', $item_ids)
    ->select('item.title', 'item.id')
    ->orderBy('ps.rank', 'ASC')->get()->toArray();

    $sku = $product->sku . str_pad((self::where('product_id', '=', $product_id)
    ->withTrashed()
    ->get()
    ->count()) + 1, 2, '0', STR_PAD_LEFT);
     */
    }

    public static function createSku($product_id, $id)
    {
        $product = Product::where('id', $product_id)->get()->first();
        if (!$product) {
            return;
        }

        $style = self::where('id', $id)->select('sku')->get()->first();
        if ($style->sku) {
            return;
        }

        $sku = $product->sku;

        DB::transaction(function () use ($product_id, $sku, $id) {
            $sku = $sku . str_pad((self::where('product_id', '=', $product_id)
                    ->whereNotNull('sku')
                    ->withTrashed()
                    ->get()
                    ->count()) + 1, 2, '0', STR_PAD_LEFT);

            self::where('id', $id)->update(['sku' => $sku]);
        });
    }
}
