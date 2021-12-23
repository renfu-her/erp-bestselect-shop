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
            return false;
        }

        $style = self::where('id', $id)->select('sku')->get()->first();
        if ($style->sku) {
            return false;
        }

        $sku = $product->sku;

        return DB::transaction(function () use ($product_id, $sku, $id) {
            $sku = $sku . str_pad((self::where('product_id', '=', $product_id)
                    ->whereNotNull('sku')
                    ->withTrashed()
                    ->get()
                    ->count()) + 1, 2, '0', STR_PAD_LEFT);

            self::where('id', $id)->update(['sku' => $sku]);
            return true;
        });
    }

    public static function activeStyle($product_id, $ids = [])
    {
        self::where('product_id', $product_id)->update(['is_active' => 0]);
        if ($ids) {
            self::whereIn('id', $ids)->update(['is_active' => 1]);
        }
    }

    public static function createInitStyles($product_id)
    {
        $spec = DB::table('prd_product_spec')
            ->where('product_id', $product_id)
            ->orderBy('rank')
            ->get()->toArray();

        if (!$spec) {
            return [];
        }
      
        $re = DB::table('prd_spec_items as t1')
            ->select("t1.id as spec_item1_id")
            ->selectRaw('@sku:=null as sku')
            ->selectRaw('@safety_stock:=0 as safety_stock')
            ->selectRaw('@in_stock:=0 as in_stock')
            ->selectRaw('@sold_out_event:=null as sold_out_event')
            ->selectRaw('@is_active:=1 as is_active')
            ->where('t1.product_id', $product_id)
            ->where('t1.spec_id', $spec[0]->spec_id)
            ->orderBy("spec_item1_id");

        if (count($spec) > 1) {
            for ($i = 1; $i < count($spec); $i++) {
                $k = $i + 1;
             
                $re->crossJoin("prd_spec_items as t$k")
                    ->addSelect("t$k.id as spec_item${k}_id")
                    ->where("t$k.product_id", $product_id)
                    ->where("t$k.spec_id", $spec[$i]->spec_id)
                    ->orderBy("spec_item${k}_id");

                switch ($i) {
                    case 1:   
                        $re->where('t1.spec_id', '<>', 't2.spec_id');
                        break;
                    case 2:  
                        $re->where('t2.spec_id', '<>', 't3.spec_id');
                        $re->where('t3.spec_id', '<>', 't1.spec_id');
                        break;
                }

            }
        }

        return $re->get()->toArray();

    }

}
