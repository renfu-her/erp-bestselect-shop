<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ProductSpec extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'prd_spec';
    protected $guarded = [];

    public static function specList($product_id)
    {

        $sub = DB::table('prd_spec_items as item')
            ->where('product_id', $product_id)->groupBy('spec_id')
            ->select('item.spec_id')
            ->selectRaw('GROUP_CONCAT(item.title) as item')
            ->selectRaw('CONCAT("[",GROUP_CONCAT("{\\"key\\":\\"",item.id,"\\",\\"value\\":\\"",item.title,"\\"}"),"]") as items');

        $re = DB::table('prd_product_spec as ps')
            ->leftJoin('prd_spec as spec', 'ps.spec_id', '=', 'spec.id')
            ->leftJoin(DB::raw("({$sub->toSql()}) as items"), function ($join) {
                $join->on('spec.id', '=', 'items.spec_id');
            })
            ->where('ps.product_id', $product_id)
            ->select('spec.title', 'spec.id', 'items.item', 'items.items')
            ->orderBy('ps.rank', 'ASC')
            ->mergeBindings($sub);
     
        return array_map(function ($n) {
            $n->items = $n->items ? json_decode($n->items) : [];
            return $n;
        }, $re->get()->toArray());

    }
}
