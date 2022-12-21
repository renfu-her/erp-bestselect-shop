<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public static function comboList($style_id)
    {
        return DB::table('prd_style_combos as combo')
            ->leftJoin('prd_product_styles as style', 'combo.product_style_child_id', '=', 'style.id')
            ->leftJoin('prd_products as product', 'product.id', '=', 'style.product_id')
            ->select('combo.id', 'combo.qty', 'style.sku', 'style.title as spec', 'style.in_stock', 'product.title as title')
            ->where('combo.product_style_id', $style_id);
    }

    public static function estimatedCost($style_id)
    {
        $re = DB::table('prd_style_combos as combo')
            ->leftJoin('prd_product_styles as style', 'combo.product_style_child_id', '=', 'style.id')
            ->select(['combo.product_style_id as style_id',
                'style.estimated_cost',
                'combo.qty',
            ])
            ->selectRaw('SUM(style.estimated_cost * combo.qty) as total')
            ->where('combo.product_style_id', $style_id)
            ->groupBy('combo.product_style_id')->get()->first();

        if (!$re) {
            return false;
        }

        DB::table('prd_product_styles')->where('id', $style_id)->update([
            'estimated_cost' => $re->total,
        ]);

        return true;

    }

    public static function correction()
    {
        $s = concatStr([
            'qty' => 'combo.qty',
            'in_stock' => 'style.in_stock',
            'style_id' => 'style.id',
        ]);

        $sub = DB::table('prd_style_combos as combo')
            ->leftJoin('prd_product_styles as style', 'combo.product_style_child_id', '=', 'style.id')
            ->select('combo.product_style_id')
            ->selectRaw('(' . $s . ') as element')
            ->groupBy('combo.product_style_id');

        $styles = DB::table('prd_product_styles as style')
            ->leftJoinSub($sub, 'style2', 'style2.product_style_id', '=', 'style.id')
            ->select(['style.id', 'in_stock', 'style2.element'])
            ->where('style.type', 'c')
            ->where('style.in_stock', '<', 0)->get();

        foreach ($styles as $value) {
            $value->element = json_decode($value->element);
            $value->in_stock = abs($value->in_stock);
            $arrElemt = [];
            foreach ($value->element as $element) {
                $arrElemt[] = floor($element->in_stock / $element->qty);
            }
            print_r($value);
            echo "<br/>";
            print_r($arrElemt);
            $min = min($arrElemt);
            $s = $min > $value->in_stock ? $value->in_stock : $min;
            echo "s:" . $s;
            echo "<hr/>";

        }
        exit;

        //  ProductStock::comboProcess($id, $qty, $check_stock)
    }

}
