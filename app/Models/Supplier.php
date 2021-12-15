<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Supplier extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'prd_suppliers';
    protected $guarded = [];

    public static function getProductSupplier($product_id, $just_id = null)
    {
        $re = DB::table('prd_product_supplier as ps')
            ->leftJoin('prd_supplier as supplier', 'ps.supplier_id', '=', 'supplier.id')
            ->where('ps.product_id', $product_id);

        if (!$just_id) {
            return $re->select('supplier.*')->get()->toArray();
        } else {
            $re = $re->select('supplier.id')->get()->toArray();
            return array_map(function ($n) {
                return $n->id;
            }, $re);
        }

    }

    public static function updateProductSupplier($product_id, $supplier_ids = [])
    {
       
        DB::table('prd_product_supplier')->where('product_id', $product_id)->delete();
        DB::table('prd_product_supplier')->insert(array_map(function ($n) use ($product_id) {
            return ['product_id' => $product_id, 'supplier_id' => $n];
        }, $supplier_ids));
    }

}
