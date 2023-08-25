<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class productDisaledSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $sup = ["台塑生醫", "合益", "奇華", "養泉", "耐嘉", "祥和", "大鼎", "十翼饌"
            , "八木", "匯恩", "王瓊凰", "黃源財", "李寶輝", "童林"];
/*
        $subbbb = DB::table('prd_suppliers as supplier')
            ->select(['id'])
            ->where(function ($query) use ($sup) {
                $query->where('supplier.name', 'like', "%" . $sup[0] . "%");
                for ($i = 1; $i < count($sup); $i++) {
                    $query->orWhere('supplier.name', 'like', "%" . $sup[$i] . "%");
                }
            })->get()->toArray();
*/
        $re = DB::table('prd_suppliers as supplier')
            ->leftJoin('prd_product_supplier as ps', 'supplier.id', '=', 'ps.supplier_id')
            ->select(['ps.product_id'])
            ->where(function ($query) use ($sup) {
                $query->where('supplier.name', 'like', "%" . $sup[0] . "%");
                for ($i = 1; $i < count($sup); $i++) {
                    $query->orWhere('supplier.name', 'like', "%" . $sup[$i] . "%");
                }
            })
         
            ->get()->toArray();

        $pp = DB::table('prd_products')
        //    ->select('id', 'sku', 'public')
        //  ->where('public', '0')
            ->whereIn('id', array_map(function ($n) {
                return $n->product_id;
            }, $re))->update(['public' => 1]);

        dd('done');
    }
}
