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

    /**
     * @param string $title 商品名稱
     * @param int $user_id
     * @param int $category_id
     * @param string $type 商品類別 p:商品 c:組合包
     * @param string $feature 商品簡述
     * @param $url
     * @param string $slogan 商品標語
     * @param $active_sdate
     * @param $active_edate
     * @param $supplier
     * @param int $has_tax 應稅免稅
     *
     * @return string[]
     */
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

    public static function productStyleList($keyword = null, $type = null, $stock_status = [], $options = [])
    {

        $re = DB::table('prd_products as p')
            ->leftJoin('prd_product_styles as s', 'p.id', '=', 's.product_id')
            ->select('s.id', 's.sku', 'p.title as product_title', 'p.id as product_id', 's.title as spec', 's.in_stock', 's.safety_stock')
            ->selectRaw('CASE p.type WHEN "p" THEN "一般商品" WHEN "c" THEN "組合包商品" END as type_title')
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

        if ($type && $type != 'all') {
            $re->where('s.type', $type);
        }

        if ($stock_status) {
            $re->where(function ($_q) use ($stock_status) {
                if (in_array('warning', $stock_status)) {
                    $_q->orWhere('in_stock', '<=', DB::raw("safety_stock"));
                }

                if (in_array('out_of_stock', $stock_status)) {
                    $_q->orWhere('in_stock', '=', 0);
                }
            });
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
            if (isset($options['user']['show'])) {
                $re->leftJoin('usr_users as user', 'p.user_id', '=', 'user.id')
                    ->addSelect('user.name as user_name');
            }

            if (isset($options['user']['condition'])) {
                if (is_array($options['user']['condition'])) {
                    if (count($options['user']['condition']) > 0) {
                        $re->whereIn('p.user_id', $options['user']['condition']);
                    }
                } else {
                    if ($options['user']['condition']) {
                        $re->where('p.user_id', $options['user']['condition']);
                    }
                }
            }
        }

        if (isset($options['price'])) {
            $re->leftJoin('prd_salechannel_style_price as price', 's.id', '=', 'price.style_id')
                ->addSelect('price.price')
                ->where('price.sale_channel_id', $options['price']);
        }

        return $re;

    }

    public static function singleProduct($sku = null, $sale_channel_id = 1)
    {
        $concatString = concatStr([
            'id' => 's.id',
            'title' => 's.title',
            'sku' => 's.sku',
            'in_stock' => 's.in_stock',
            'origin_price' => 'p.origin_price',
            'price' => 'p.price']);

        $concatImg = concatStr([
            'url' => 'url',
        ]);

        $styleQuery = DB::table('prd_product_styles as s')
            ->leftJoin('prd_salechannel_style_price as p', 's.id', '=', 'p.style_id')
            ->where('p.sale_channel_id', $sale_channel_id)
            ->whereNull('s.deleted_at')
            ->whereNotNull('s.sku')
            ->select('s.product_id')
            ->selectRaw($concatString . ' as styles')
            ->groupBy('s.product_id');

        $imgQuery = DB::table('prd_product_images')
            ->select('product_id')
            ->selectRaw($concatImg . ' as imgs')
            ->groupBy('product_id');

        $re = DB::table('prd_products as p')
            ->leftJoin(DB::raw("({$styleQuery->toSql()}) as s"), function ($join) {
                $join->on('p.id', '=', 's.product_id');
            })
            ->leftJoin(DB::raw("({$imgQuery->toSql()}) as i"), function ($join) {
                $join->on('p.id', '=', 'i.product_id');
            })
            ->select('p.id', 'p.title', 'p.sku', 'p.desc', 's.styles', 'i.imgs')
            ->mergeBindings($styleQuery)
            ->mergeBindings($imgQuery)
            ->where('sku', $sku)
            ->whereNull('p.deleted_at')
            ->whereNotNull('s.styles')
            ->get()->first();

        if (!$re) {
            return;
        }

        $re->styles = json_decode($re->styles);
        $re->imgs = array_map(function ($n) {
            $n->url = asset($n->url);
            return $n;
        }, json_decode($re->imgs));

        return $re;
    }

    public static function changeShipment($product_id, $category_id, $group_id)
    {

        DB::table('prd_product_shipment')->where('product_id', $product_id)
            ->where('category_id', $category_id)
            ->delete();
        if ($group_id != 0) {
            DB::table('prd_product_shipment')->insert([
                'product_id' => $product_id,
                'category_id' => $category_id,
                'group_id' => $group_id,
            ]);
        }

    }

    public static function changePickup(int $product_id, array $depot_id_array)
    {
        if (DB::table('prd_pickup')->where('product_id_fk', $product_id)->get()->first()) {
            DB::table('prd_pickup')
                ->where('product_id_fk', $product_id)
                ->delete();
        }

        if (count($depot_id_array) > 0) {
            foreach ($depot_id_array as $depot_id) {
                DB::table('prd_pickup')
                    ->insert([
                        'product_id_fk' => $product_id,
                        'depot_id_fk' => $depot_id,
                    ]);
            }
        }
    }

    public static function shipmentList($product_id)
    {
        return DB::table('prd_product_shipment')->where('product_id', $product_id);
    }

    public static function pickupList($product_id)
    {
        return DB::table('prd_pickup')->where('product_id_fk', $product_id);
    }

    public static function getShipment($product_id, $code = "deliver")
    {
        $concatString = concatStr([
            'id' => 'rule.id',
            'min_price' => 'rule.min_price',
            'max_price' => 'rule.max_price',
            'dlv_fee' => 'rule.dlv_fee',
            'at_most' => 'rule.at_most',
            'is_above' => 'rule.is_above']);

        $ruleSubQuery = DB::table('shi_rule as rule')
            ->select('rule.group_id_fk as group_id')
            ->selectRaw($concatString . ' as rules')
            ->groupBy('rule.group_id_fk');

        $re = DB::table('prd_product_shipment as ps')
            ->leftJoin('shi_category as category', 'ps.category_id', '=', 'category.id')
            ->leftJoin('shi_group as g', 'ps.group_id', '=', 'g.id')
            ->leftJoin(DB::raw("({$ruleSubQuery->toSql()}) as rule"), function ($join) {
                $join->on('ps.group_id', '=', 'rule.group_id');
            })
            ->select('category.code as event', 'category.category', 'g.id as group_id', 'g.name as group_name', 'rule.rules')
            ->mergeBindings($ruleSubQuery)
            ->where('ps.product_id', $product_id)
            ->where('code', $code);

        return $re;

    }

    public static function getPickup($product_id)
    {
        $pick_up = DB::table('prd_pickup as pick_up')
            ->leftJoin('depot', 'depot.id', '=', 'pick_up.depot_id_fk')
            ->select('pick_up.id', 'depot.name as depot_name', )
            ->whereNull('depot.deleted_at')
            ->where('pick_up.product_id_fk', $product_id);

        return $pick_up;
    }

    public static function getProductShipments($product_id)
    {
        $delivery = self::getShipment($product_id)->get()->first();
        $arr = [];
        if ($delivery) {
            $delivery->rules = json_decode($delivery->rules);
            $arr[$delivery->event] = $delivery;
        }
        
        $pickup = self::getPickup($product_id)->get()->toArray();
        if ($pickup) {
            $arr['pickup'] = [
                'event' => 'pickup',
                'category' => '自取',
                'depots' => $pickup,
            ];
        }

        return $arr;

    }
}
