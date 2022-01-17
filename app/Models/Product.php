<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'prd_products';
    protected $guarded = [];

    public static function productList($title = null, $id = null, $options = [])
    {

        $re = DB::table('prd_products as product')
            ->select('product.id as id', 'product.title as title', 'product.sku as sku', 'product.type as type')
            ->selectRaw('CASE product.type WHEN "p" THEN "一般商品" WHEN "c" THEN "組合包商品" END as type_title')
            ->whereNull('product.deleted_at');

        if ($title) {
            $re->where('product.title', 'like', "%$title%");
        }

        if ($id) {
            $re->where('product.id', $id);
        }

        if (isset($options['user'])) {
            $re->leftJoin('usr_users as user', 'product.user_id', '=', 'user.id')
                ->addSelect('user.name as user_name');
        }

        if (isset($options['sku'])) {
            $re->orWhere('product.sku', 'like', '%' . $options['sku'] . '%');
        }

        if (isset($options['supplier'])) {
            $subSupplier = DB::table('prd_product_supplier as ps')
                ->select('ps.product_id')
                ->selectRaw('CONCAT("[",GROUP_CONCAT("{\\"id\\":",s.id,",\\"name\\":","\\"",s.name,"\\"","}"),"]") as suppliers')
                ->leftJoin('prd_suppliers as s', 'ps.supplier_id', '=', 's.id')
                ->groupBy('ps.product_id');

            $re->leftJoin(DB::raw("({$subSupplier->toSql()}) as supplier"), function ($join) {
                $join->on('product.id', '=', 'supplier.product_id');
            });

            $re->selectRaw('IF(ISNULL(supplier.suppliers),"[]",supplier.suppliers) as suppliers');

            $re->mergeBindings($subSupplier);

        }

        return $re;
    }

    public static function createProduct($title, $user_id, $category_id, $type = 'p', $feature = null, $url = null, $slogan = null, $active_sdate = null, $active_edate = null, $supplier = null, $has_tax = 0)
    {
        return DB::transaction(function () use ($title,
            $user_id,
            $category_id,
            $type,
            $feature,
            $url,
            $slogan,
            $active_sdate,
            $active_edate,
            $supplier,
            $has_tax) {

            switch ($type) {
                case 'p':
                    $prefix = "P";
                    break;
                case 'c':
                    $prefix = "C";
                    break;

            }

            $url = $url ? $url : $title;

            if (self::where('url', $url)->get()->first()) {
                $url = $url . "-" . time();
            }

            $sku = $prefix . date("ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))
                    ->withTrashed()
                    ->get()
                    ->count()) + 1, 3, '0', STR_PAD_LEFT);

            $id = self::create([
                "title" => $title,
                'type' => $type,
                "sku" => $sku,
                "user_id" => $user_id,
                "category_id" => $category_id,
                "feature" => $feature,
                "url" => $url,
                "slogan" => $slogan,
                "active_sdate" => $active_sdate,
                "active_edate" => $active_edate ? $active_edate . " 23:59:59" : null,
                "has_tax" => $has_tax,
            ])->id;

            if ($supplier) {
                Supplier::updateProductSupplier($id, $supplier);
            }

            return [
                'sku' => $sku,
                'id' => $id,
            ];

        });
    }

    public static function updateProduct($id,
        $title,
        $user_id,
        $category_id,
        $feature = null,
        $url = null,
        $slogan = null,
        $active_sdate = null,
        $active_edate = null,
        $supplier,
        $has_tax = 0) {

        $url = $url ? $url : $title;

        if (self::where('url', $url)->where('id', '<>', $id)->get()->first()) {
            $url = $url . "-" . time();
        }

        self::where('id', $id)->update([
            "title" => $title,
            "user_id" => $user_id,
            "category_id" => $category_id,
            "feature" => $feature,
            "url" => $url,
            "slogan" => $slogan,
            "active_sdate" => $active_sdate,
            "active_edate" => $active_edate,
            "has_tax" => $has_tax,
        ]);

        Supplier::updateProductSupplier($id, $supplier);
    }

    public static function setProductSpec($product_id, $spec_id)
    {
        $db = DB::table('prd_product_spec')->where('product_id', $product_id);
        if ($db->count() > 2) {
            return '超過上限';
        }

        if ($db->where('spec_id', $spec_id)->get()->first()) {
            return '重複設定';
        }

        return DB::transaction(function () use ($product_id, $spec_id) {
            $count = DB::table('prd_product_spec')->where('product_id', $product_id)->count();
            return DB::table('prd_product_spec')->insert(['product_id' => $product_id, 'spec_id' => $spec_id, 'rank' => $count]);
        });

    }

    public static function productStyleList($keyword = null, $type = null, $options = [])
    {

        $re = DB::table('prd_products as p')
            ->leftJoin('prd_product_styles as s', 'p.id', '=', 's.product_id')
            ->select('s.id', 's.sku', 'p.title as product_title', 's.title as spec', 's.in_stock', 's.safety_stock')
        // ->selectRaw('IF(s.title,p.title,CONCAT(p.title," ",COALESCE(s.title,""))) as title')
            ->whereNotNull('s.sku')
            ->where(function ($q) use ($keyword) {
                if ($keyword) {
                    $q->where('p.title', 'like', "%$keyword%");
                    $q->orWhere('s.title', 'like', "%$keyword%");
                    $q->orWhere('s.sku', 'like', "%$keyword%");
                }
            })
            ->whereNotNull('s.sku')
            ->whereNull('s.deleted_at');

        if ($type) {
            $re->where('s.type', $type);
        }

        if (isset($options['supplier'])) {
            if (isset($options['supplier']['show'])) {
                $supplierSub = DB::table('prd_product_supplier as ps')
                    ->leftJoin('prd_suppliers as sup', 'ps.supplier_id', '=', 'sup.id')
                    ->select('ps.product_id as product_id')
                    ->selectRaw('GROUP_CONCAT(sup.name) as suppliers_name')
                    ->groupBy('ps.product_id');

                $re->leftJoin(DB::raw("({$supplierSub->toSql()}) as sup"), 'p.id', '=', 'sup.product_id')
                    ->addSelect('sup.suppliers_name as suppliers_name');

                $re->mergeBindings($supplierSub);
            }

            if (isset($options['supplier']['condition'])) {
                $re->leftJoin('prd_product_supplier as ps', 'p.id', '=', 'ps.product_id');
                if (is_array($options['supplier']['condition'])) {
                    if (count($options['supplier']['condition']) > 0) {
                        $re->whereIn('ps.supplier_id', $options['supplier']['condition']);
                    }
                } else {
                    if ($options['supplier']['condition']) {
                        $re->where('ps.supplier_id', $options['supplier']['condition']);
                    }
                }
            }

        }

        if (isset($options['user'])) {
            $re->leftJoin('usr_users as user', 'p.user_id', '=', 'user.id')
                ->addSelect('user.name as user_name');

            if ($options['user']) {
                if (is_array($options['user'])) {
                    if (count($options['user']) > 0) {
                        $re->whereIn('p.user_id', $options['user']);
                    }
                } else {
                    if ($options['user']) {
                        $re->where('p.user_id', $options['user']);
                    }
                }
            }
        }

        return $re->distinct();

    }

}
