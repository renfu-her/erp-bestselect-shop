<?php

namespace App\Models;

use App\Enums\Customer\Identity;
use App\Enums\Globals\ApiStatusMessage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'prd_products';
    protected $guarded = [];

    public static function productList($keyword = null, $id = null, $options = [])
    {

        $re = DB::table('prd_products as product')
            ->select('product.id as id',
                'product.title as title',
                'product.sku as sku',
                'product.type as type',
                'product.consume as consume',
                'product.online as online',
                'product.offline as offline',
                'product.public as public')
            ->selectRaw('CASE product.type WHEN "p" THEN "一般商品" WHEN "c" THEN "組合包商品" END as type_title')
            ->orderBy('id')

            ->whereNull('product.deleted_at');

        if ($keyword) {
            $re->where(function ($q) use ($keyword) {
                $q->where('product.title', 'like', "%$keyword%")
                    ->orWhere('product.sku', 'like', "%$keyword%");
            });
        }

        if ($id) {
            $re->where('product.id', $id);
        }

        if (isset($options['user'])) {
            $re->leftJoin('usr_users as user', 'product.user_id', '=', 'user.id')
                ->addSelect('user.name as user_name');

            if (is_array($options['user'])) {
                $re->whereIn('user.id', $options['user']);
            } else if (is_string($options['user']) || is_numeric($options['user'])) {
                $re->where('user.id', $options['user']);
            }
        }

        if (isset($options['sku'])) {
            $re->orWhere('product.sku', 'like', '%' . $options['sku'] . '%');
        }

        if (isset($options['consume']) && !is_null($options['consume'])) {

            $re->where('product.consume', $options['consume']);
        }

        if (isset($options['public']) && !is_null($options['public'])) {
            $re->where('product.public', $options['public']);
        }

        if (isset($options['product_type']) && in_array($options['product_type'], ['c', 'p'])) {
            $re->where('product.type', $options['product_type']);
        }

        //商品管理-搜尋廠商條件
        if (!empty($options['search_supplier'])) {
            $re->leftJoin('prd_product_supplier', 'product.id', '=', 'prd_product_supplier.product_id')
                ->join('prd_suppliers', function ($join) use ($options) {
                    $join->on('prd_product_supplier.supplier_id', '=', 'prd_suppliers.id')
                        ->where('prd_suppliers.id', '=', $options['search_supplier']);
                })
                ->addSelect([
                    'prd_suppliers.id as supplier_id',
                    'prd_suppliers.name as supplier_name',
                ]);
        }

        //銷售控管 - 價格管理
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

        if (isset($options['collection']) && $options['collection']) {

            $re->leftJoin('collection_prd as cprd', 'product.id', '=', 'cprd.product_id_fk')
                ->leftJoin('collection as colc', 'colc.id', '=', 'cprd.collection_id_fk');

            //使用在酒類商品搜尋
//            if (!isset($options['is_liquor'])) {
                $re->where('cprd.collection_id_fk', '=', $options['collection'])
                    ->addSelect(['colc.name as collection_name', 'collection_id_fk']);
//            } else {
//                if ($options['is_liquor'] == 1) {
//                    $re->where('colc.is_liquor', '=', 1)
//                        ->where('colc.is_public', '=', 1);
//                } elseif ($options['is_liquor'] == 0) {
//                    $re->where('colc.is_liquor', '=', 0);
//                }
//            }
        }

        if (isset($options['category_id']) && $options['category_id']) {
            $re->where('product.category_id', '=', $options['category_id']);
        }
        if (isset($options['product_ids'])) {
            $re->whereIn('product.id', $options['product_ids']);
        }

        if (isset($options['img'])) {
            $subImg = DB::table('prd_product_images as img')
                ->select('img.url')
                ->whereRaw('img.product_id = product.id')
                ->limit(1);

            $re->addSelect(DB::raw("({$subImg->toSql()}) as img_url"));

        }

        if (isset($options['active_date'])) {

            $re->where(function ($query) {
                $now = date('Y-m-d H:i:s');
                $query->where('product.active_sdate', '<=', $now)
                    ->where('product.active_edate', '>=', $now)
                    ->orWhereNull('product.active_sdate')
                    ->orWhereNull('product.active_edate');
            });

        }

        if (isset($options['sale_channel_id'])) {

            $sales_type = SaleChannel::where('id', $options['sale_channel_id'])->get()->first()->sales_type;

            if ($sales_type == '1') {
                $re->where('product.online', 1);
            } else {
                $re->where('product.offline', 1);
            }

        }

        if (isset($options['online'])) {
            if ($options['online'] != 'all') {
                if ($options['online'] == 'online') {
                    $re->where('online', '1');
                } else {
                    $re->where('offline', '1');
                }
            }

        }

        if (isset($options['hasDelivery']) && $options['hasDelivery'] != 'all') {
            if ($options['hasDelivery'] == '1') {
                $re->join('prd_product_shipment', 'product.id', '=', 'prd_product_shipment.product_id')
                    ->join('shi_category', function ($join) {
                        $join->on('prd_product_shipment.category_id', '=', 'shi_category.id')
                            ->where('shi_category.code', '=', 'deliver');
                    })
                    ->addSelect(
                        'shi_category.code as hasDelivery',
                    );
            } else {
                $re->leftJoin('prd_product_shipment', 'product.id', '=',
                    'prd_product_shipment.product_id')
                    ->whereNotIn('product.id', function ($q) {
                        $q->select('prd_product_shipment.product_id')
                            ->from('prd_product_shipment')
                        //category_id = 1 宅配
                            ->where('prd_product_shipment.category_id', '=', 1);
                    })
                    ->addSelect(
                        'prd_product_shipment.category_id as hasDelivery',
                    );
            }
        }
//        else {
            //不限是否設定宅配
//            $re->leftJoin('prd_product_shipment', 'product.id', '=', 'prd_product_shipment.product_id')
//                ->addSelect(['prd_product_shipment.product_id AS hasDelivery']);
//        }

        if (isset($options['hasSpecList']) && $options['hasSpecList'] != 'all') {
            if ($options['hasSpecList'] == '1') {
                $re->join('prd_speclists', 'product.id', '=',
                    'prd_speclists.product_id')
                    ->distinct('prd_speclists.product_id')
                    ->addSelect(
                        'prd_speclists.product_id as hasSpecList',
                    );
            } else {
                $re->leftJoin('prd_speclists', 'product.id', '=', 'prd_speclists.product_id')
                    ->whereNotIn('product.id', function ($q) {
                        $q->select('prd_speclists.product_id')
                            ->from('prd_speclists');
                    })
                    ->addSelect(
                        'prd_speclists.product_id as hasSpecList',
                    );
            }
        }

        return $re;
    }

    public static function getMinPriceProducts($sale_channel_id, $product_id = [], &$product_list = null)
    {

        if ($product_list) {
            $product_id = array_map(function ($n) {
                return $n->id;
            }, $product_list);
        }

        $subPrice = DB::table('prd_salechannel_style_price as price')
            ->leftJoin('prd_product_styles as style', 'style.id', '=', 'price.style_id')
            ->select(['price.*', 'style.product_id'])
            ->where('price.sale_channel_id', $sale_channel_id)
            ->orderBy('price.price', 'ASC');
        if ($product_id) {
            $subPrice->whereIn('product_id', $product_id);
        }
        $price = $subPrice->get()->toArray();
        $re = [];
        foreach ($price as $p) {
            if (!isset($re[$p->product_id])) {
                $re[$p->product_id] = $p;
            }
        }

        if (!$product_list) {
            return $re;
        } else {
            foreach ($product_list as $pp) {
                if (isset($re[$pp->id])) {

                    $pp->price = $re[$pp->id]->price;
                    $pp->origin_price = $re[$pp->id]->origin_price;

                } else {
                    $pp->price = 0;
                    $pp->origin_price = 0;
                }
            }
        }

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
     * @param int $consume 是否耗材
     * @param  int $public 公開
     * @param  int $online 開放通路 *線上 (對外網站)
     * @param  int $offline 開放通路 *線下 (ERP)
     *
     * @return string[]
     */
    public static function createProduct($title,
        $user_id, $category_id, $type = 'p',
        $feature = null, $url = null, $slogan = null, $active_sdate = null,
        $active_edate = null, $supplier = null, $has_tax = 0, $consume = 0, $public = 1, $online = 0, $offline = 0) {
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
            $has_tax,
            $consume,
            $public,
            $online,
            $offline) {

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
                'consume' => $consume,
                'public' => $public,
                'online' => $online,
                'offline' => $offline,
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
        $has_tax = 0,
        $consume = 0,
        $public = 1,
        $online = null,
        $offline = null) {

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
            'consume' => $consume,
            'public' => $public,
            'online' => $online,
            'offline' => $offline,
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
            ->select('s.id', 's.sku', 's.estimated_cost', 'p.title as product_title', 'p.id as product_id', 's.title as spec', 's.safety_stock', 's.total_inbound')
            ->selectRaw('CASE p.type WHEN "p" THEN "一般商品" WHEN "c" THEN "組合包商品" END as type_title')
            ->selectRaw('s.in_stock + s.overbought as in_stock')
            ->selectRaw('(SELECT `url` FROM prd_product_images WHERE product_id=p.id LIMIT 1) as img_url')
            ->whereNotNull('s.sku')
            ->whereNull('s.deleted_at');

        if ($keyword) {
            $re->where(function ($q) use ($keyword) {

                $q->where('p.title', 'like', "%$keyword%");
                $q->orWhere('s.title', 'like', "%$keyword%");
                $q->orWhere('s.sku', 'like', "%$keyword%");

            });

        }

        if ($type && $type != 'all') {
            $re->where('s.type', $type);
        }

        if ($stock_status) {
            $re->where(function ($_q) use ($stock_status) {
                if (in_array('warning', $stock_status)) {
                    $_q->orWhere('s.in_stock', '<=', DB::raw("safety_stock"));
                }

                if (in_array('out_of_stock', $stock_status)) {
                    $_q->orWhere('s.in_stock', '=', 0);
                }

                if (in_array('in_stock', $stock_status)) {
                    $_q->orWhere(DB::raw('s.in_stock + s.overbought'), '>', 0);
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
                ->addSelect('price.bonus')
                ->addSelect('price.dividend')
                ->addSelect('price.bonus')
                ->addSelect('price.dealer_price')
                ->where('price.sale_channel_id', $options['price']);
        }

        if (isset($options['consume']) && !is_null($options['consume'])) {
            $re->where('p.consume', $options['consume']);
        }

        if (isset($options['public']) && !is_null($options['public'])) {
            $re->where('p.public', $options['public']);
        }

        if (isset($options['salechannel']) && !is_null($options['salechannel'])) {
            $sales_type = SaleChannel::where('id', $options['salechannel'])->get()->first()->sales_type;
            if ($sales_type == '1') {
                $re->where('p.online', 1);
            } else {
                $re->where('p.offline', 1);
            }
        }
        // 僅顯示有物流的商品
        if (isset($options['shipment']) && $options['shipment'] == '1') {
            $re->leftJoin('prd_product_shipment as shipment', 'p.id', '=', 'shipment.product_id')
                ->leftJoinSub(DB::table('prd_pickup')->select('product_id_fk')->selectRaw('count(*) as pickup_count')->groupBy('product_id_fk'), 'pickup', 'pickup.product_id_fk', '=', 'p.id')
                ->where(function ($query) {
                    $query->whereNotNull('shipment.product_id')
                        ->orWhere('pickup.pickup_count', '>', 0);
                });
        }

        return $re;

    }

    /**
     * @param $sku
     * 檢查是否是酒類商品?
     * @return bool
     */
    public static function isLiquor($sku)
    {
        $collectionExist = DB::table('collection')
            ->where([
                ['is_public', '=', '1'],
                ['is_liquor', '=', '1'],
            ])
            ->select('id')
            ->get()
            ->first();

        if (!$collectionExist) {
            return false;
        }

        $collections = DB::table('collection')
            ->where([
                ['is_public', '=', '1'],
                ['is_liquor', '=', '1'],
            ])
            ->select('id')
            ->get();

        $skuData = [];
        foreach ($collections as $collection) {
            $temp = DB::table('prd_products as product')
                ->select('product.id as id',
                    'product.title as title',
                    'product.sku as sku',
                    'product.type as type',
                    'product.consume as consume',
                    'product.online as online',
                    'product.offline as offline',
                    'product.public as public')
                ->selectRaw('CASE product.type WHEN "p" THEN "一般商品" WHEN "c" THEN "組合包商品" END as type_title')
                ->where('product.public', 1)
                ->where('online', '1')
                ->where('product.online', 1)
                ->where(function ($query) {
                    $now = date('Y-m-d H:i:s');
                    $query->where('product.active_sdate', '<=', $now)
                        ->where('product.active_edate', '>=', $now)
                        ->orWhereNull('product.active_sdate')
                        ->orWhereNull('product.active_edate');
                })
                ->orderBy('id')
                ->whereNull('product.deleted_at')
                ->leftJoin('collection_prd as cprd', 'product.id', '=', 'cprd.product_id_fk')
                ->leftJoin('collection as colc', 'colc.id', '=', 'cprd.collection_id_fk')
                ->where('cprd.collection_id_fk', '=', $collection->id)
                ->addSelect(['colc.name as collection_name', 'collection_id_fk'])
                ->get();
            if ($temp) {
                foreach ($temp as $data) {
                    $skuData[] = $data->sku;
                }
            }
        }

        return in_array($sku, array_unique($skuData));
    }

    public static function singleProduct($sku = null, $sale_channel_id = 1)
    {

        $concatString = concatStr([
            'id' => 's.id',
            'title' => 's.title',
            'sku' => 's.sku',
            'in_stock' => 's.in_stock',
            'overbought' => 's.overbought',
            'origin_price' => 'p.origin_price',
            'price' => 'p.price',
            'dividend' => 'p.dividend']);

        $concatImg = concatStr([
            'url' => 'url',
        ]);

        $styleQuery = DB::table('prd_product_styles as s')
            ->leftJoin('prd_salechannel_style_price as p', 's.id', '=', 'p.style_id')
            ->where('p.sale_channel_id', $sale_channel_id)
            ->whereNull('s.deleted_at')
            ->whereNotNull('s.sku')
            ->where('s.is_active', '1')
            ->select('s.product_id')
            ->selectRaw($concatString . ' as styles')
            ->groupBy('s.product_id');

        $imgQuery = DB::table('prd_product_images')
            ->select('product_id')
            ->selectRaw($concatImg . ' as imgs')
            ->groupBy('product_id');

        $sales_type = SaleChannel::where('id', $sale_channel_id)->get()->first()->sales_type;

        if ($sales_type == '1') {
            $sales_type = 'p.online';
        } else {
            $sales_type = 'p.offline';
        }

        $re = DB::table('prd_products as p')
            ->leftJoin(DB::raw("({$styleQuery->toSql()}) as s"), function ($join) {
                $join->on('p.id', '=', 's.product_id');
            })
            ->leftJoin(DB::raw("({$imgQuery->toSql()}) as i"), function ($join) {
                $join->on('p.id', '=', 'i.product_id');
            })
            ->select(['p.id', 'p.title',
                'p.sku', 's.styles', 'i.imgs',
            ])
            ->selectRaw('IF(p.desc IS NULL,"",p.desc) as _desc')
            ->selectRaw('IF(p.feature IS NULL,"",p.feature) as _feature')
            ->selectRaw('IF(p.logistic_desc IS NULL,"",p.logistic_desc) as _logistic_desc')
            ->selectRaw('IF(p.slogan IS NULL,"",p.slogan) as _slogan')

            ->mergeBindings($styleQuery)
            ->mergeBindings($imgQuery)
            ->where('sku', $sku)
        // ->where('s.is_active','1')
            ->whereNull('p.deleted_at')
            ->whereNotNull('s.styles')
            ->where(function ($query) {
                $now = date('Y-m-d H:i:s');
                $query->where('p.active_sdate', '<=', $now)
                    ->where('p.active_edate', '>=', $now)
                    ->orWhereNull('p.active_sdate')
                    ->orWhereNull('p.active_edate');
            })
            ->where('p.public', '1')
            ->where($sales_type, 1)
            ->get()->first();

        if (!$re) {
            return;
        }

        $output = [
            "info" => [
                "title" => $re->title,
                "slogan" => $re->_slogan,
                "feature" => $re->_feature,
                'id' => $re->id,
                "image" => [],
            ],
            "desc" => $re->_desc,
            "spec" => [],
            "logistic_desc" => $re->_logistic_desc,
            "styles" => json_decode($re->styles),
            "shipment" => '',

        ];
        //  $re->styles = json_decode($re->styles);

        if ($re->imgs) {
            $output['info']['image'] = array_map(function ($n) {
                $n->url = getImageUrl($n->url, true);
                return $n;
            }, json_decode($re->imgs));
        }

        $shipment = self::getProductShipments($re->id);
        $output['shipment'] = $shipment ? $shipment : '';
        $output['spec'] = ProductSpecList::where('product_id', $re->id)
            ->select('title', 'content')->get()->toArray();

        return $output;
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
            ->leftJoin('shi_temps as temp', 'g.temps_fk', '=', 'temp.id')
            ->leftJoin(DB::raw("({$ruleSubQuery->toSql()}) as rule"), function ($join) {
                $join->on('ps.group_id', '=', 'rule.group_id');
            })
            ->select(['category.code as category',
                'category.category as category_name',
                'g.id as group_id',
                'g.name as group_name',
                'g.method_fk as method',
                'g.note as note',
                'temp.temps',
                'temp.id as temp_id',
                'rule.rules'])
            ->mergeBindings($ruleSubQuery)
            ->where('ps.product_id', $product_id)
            ->where('code', $code);

        return $re;

    }

    public static function getPickup($product_id)
    {
        $pick_up = DB::table('prd_pickup as pick_up')
            ->leftJoin('depot', 'depot.id', '=', 'pick_up.depot_id_fk')
            ->select('depot.id as id', 'depot.id as depot_id', 'depot.name as depot_name', 'depot.address as depot_address', 'depot.tel as depot_tel', )
            ->whereNull('depot.deleted_at')
            ->where('pick_up.product_id_fk', $product_id);

        return $pick_up;
    }

    public static function getPickupWithPickUpId($pickup_id)
    {
        $pick_up = DB::table('prd_pickup as pick_up')
            ->leftJoin('depot', 'depot.id', '=', 'pick_up.depot_id_fk')
            ->select('pick_up.id', 'depot.id as depot_id', 'depot.name as depot_name', 'depot.address as depot_address', 'depot.tel as depot_tel', )
            ->whereNull('depot.deleted_at')
            ->where('pick_up.id', $pickup_id);

        return $pick_up;
    }

    public static function getProductShipments($product_id)
    {
        $delivery = self::getShipment($product_id)->get()->first();
        $arr = [];
        if ($delivery) {
            $delivery->rules = json_decode($delivery->rules);
            $arr[$delivery->category] = $delivery;
        }

        $pickup = self::getPickup($product_id)->get()->toArray();
        if ($pickup) {
            $arr['pickup'] = [
                'category' => 'pickup',
                'category_name' => '自取',
                'depots' => $pickup,
            ];
        }

        return $arr;

    }

    public static function getCollectionsOfProduct($product_id)
    {
        return DB::table('collection_prd')->where('product_id_fk', $product_id)
            ->select('collection_id_fk as collection_id');

    }

    /**
     * @param $sku string 產品SKU（不是款式SKU）
     * @param $m_class string ENUM Identity 會員類別
     * @param $m_id string 加密會員編號
     * 前端商品資訊API
     */
    public static function getProductInfo($sku, string $m_class = Identity::customer, string $m_id = null)
    {
        // start to check 產品的公開、上下架時間
        $conditionQuery = DB::table('prd_products as product')
            ->where([
                ['product.sku', '=', $sku],
                ['product.public', '=', 1],
            ]);

        $isPublic = $conditionQuery->exists();
        if (!$isPublic) {
            return response()->json([
                'status' => ApiStatusMessage::Fail,
                'msg' => '不公開',
                'data' => [],
            ]);
        }

        $isActive = $conditionQuery
            ->leftJoin('prd_product_styles', 'product.id', '=', 'prd_product_styles.product_id')
            ->where('prd_product_styles.is_active', '=', 1)
            ->exists();
        if (!$isActive) {
            return response()->json([
                'status' => ApiStatusMessage::Fail,
                'msg' => '已下架',
                'data' => [],
            ]);
        }

        $activeDateQuery = $conditionQuery
            ->select(
                'active_sdate',
                'active_edate',
            )
            ->get()
            ->first();
        $startDate = $activeDateQuery->active_sdate ?? null;
        $endDate = $activeDateQuery->active_edate ?? null;
        date_default_timezone_set('Asia/Taipei');
        $now = date('Y-m-d H:i:s');

        if (is_null($startDate)
            && !is_null($endDate)
            && $now > $endDate
        ) {
            return response()->json([
                'status' => ApiStatusMessage::Fail,
                'msg' => '已過下架時間',
                'data' => [],
            ]);
        } elseif (!is_null($startDate)
            && is_null($endDate)
            && $now < $startDate
        ) {
            return response()->json([
                'status' => ApiStatusMessage::Fail,
                'msg' => '未到上架時間',
                'data' => [],
            ]);
        } elseif (!is_null($startDate)
            && !is_null($endDate)
        ) {
            if ($now < $startDate) {
                return response()->json([
                    'status' => ApiStatusMessage::Fail,
                    'msg' => '還未上架',
                    'data' => [],
                ]);
            } elseif ($now > $endDate) {
                return response()->json([
                    'status' => ApiStatusMessage::Fail,
                    'msg' => '已經下架',
                    'data' => [],
                ]);
            }
        }
        // end to check 產品的公開、上下架時間

        // 產品已上架、公開，開始做query
        $query = DB::table('prd_products as product')
            ->where([
                ['product.sku', '=', $sku],
                ['product.public', '=', 1],
            ])
            ->whereNull('product.deleted_at');

        $productQuery = $query
            ->select(
                'product.id as id',
                'product.title as title',
                'product.feature',
                'product.slogan',
                'product.desc as introduction',
                'product.logistic_desc as logist_desc',
                'product.type as type',
                'product.active_sdate',
                'product.desc as introduction',
            )
            ->get();

        $imageBuilder = $query->leftJoin('prd_product_images as images', 'images.product_id', '=', 'product.id')
            ->select('images.url as src')
            ->get();
        $imageArray = [];
        foreach ($imageBuilder as $image) {
            if (!empty($image->src)) {
                $imageArray[] = [
                    'src' => $image->src,
                ];
            }
        }

        $transport = $query->leftJoin('prd_product_shipment as ship', 'product.id', '=', 'ship.product_id')
            ->leftJoin('shi_group', 'ship.group_id', '=', 'shi_group.id')
            ->select('shi_group.name as transport')
            ->get()
            ->first()
            ->transport;

        // get sales_id
        $sale_channel_id = DB::table('usr_identity')
            ->where('code', '=', $m_class)
            ->leftJoin('usr_identity_salechannel', 'usr_identity.id', '=', 'usr_identity_salechannel.identity_id')
            ->select('sale_channel_id')
            ->get()
            ->first()
            ->sale_channel_id;

        $productStyleProduct = $query
            ->leftJoin('prd_product_styles as product_style', function ($join) {
                $join->on('product_style.product_id', '=', 'product.id')
                    ->where('product_style.is_active', '=', 1);
            })
            ->leftJoin('prd_salechannel_style_price as sale_channel', function ($join) use ($sale_channel_id) {
                $join->on('sale_channel.style_id', '=', 'product_style.id')
                    ->where('sale_channel.sale_channel_id', '=', $sale_channel_id);
            })
            ->select(
                'product_style.sku',
                'product_style.title as name',
                'product_style.in_stock as amount',
                'sale_channel.origin_price as origin',
                'sale_channel.price as sale',
            )
            ->selectRaw(
                'product_style.total_inbound - product_style.in_stock as sell',
            )
            ->get();

        $pickupBuilder = $query->leftJoin('prd_pickup', 'product.id', '=', 'prd_pickup.product_id_fk')
            ->leftJoin('depot', function ($join) {
                $join->on('prd_pickup.depot_id_fk', '=', 'depot.id')
                    ->where('can_pickup', '=', 1);
            })
            ->select('depot.name as pickup')
            ->get()
            ->unique();

        $pickupArray = [];
        foreach ($pickupBuilder as $key => $pickup) {
            $pickupArray[$key] = $pickup->pickup;
        }

        $spec = DB::table('prd_speclists')
            ->where('product_id', '=', $productQuery->first()->id)
            ->select(
                'title',
                'content'
            )
            ->get();

        return response()->json([
            'status' => ApiStatusMessage::Succeed,
            'msg' => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
            'data' => [
                'info' => [
                    'name' => $productQuery->first()->title,
                    'slogan' => $productQuery->first()->slogan,
                    'feature' => $productQuery->first()->feature,
                    'image' => $imageArray,
                ],
                'introduction' => $productQuery->first()->introduction,
                'transport' => $transport,
                'spec' => $spec,
                'logist_desc' => $productQuery->first()->logist_desc,
                'pickup' => $pickupArray,
                'item' => $productStyleProduct,
            ],
        ]);
    }

    /**
     * @param  string  $type type預設0會回傳，所有「非一般商品」的SKU
     * 取得「商品群組」的類型中，所有的SKU代碼
     * @return array
     */
    public static function getSkuByType(string $type = '0')
    {
        $productQueries = DB::table('prd_products as prd')
            ->join('collection_prd', function ($join) {
                $join->on('collection_prd.product_id_fk', '=', 'prd.id');
            });
        if ($type === '0') {
            $productQueries->join('collection', function ($join_x) use ($type) {
                $join_x->on('collection.id', '=', 'collection_prd.collection_id_fk')
                    ->where('collection.is_liquor', '<>', '0');
            });
        } else {
            $productQueries->join('collection', function ($join_x) use ($type) {
                $join_x->on('collection.id', '=', 'collection_prd.collection_id_fk')
                    ->where('collection.is_liquor', '=', $type);
            });
        }

        $dataList = $productQueries->select(['prd.sku'])->get();
        $dataArray = [];
        foreach ($dataList as $data) {
            $dataArray[] = $data->sku;
        }

        return $dataArray;
    }

    /**
     * @param $data
     * @param $type string 商品群組的類別,  一般商品0、酒類1
     * model for search 商品編號、商品名稱
     * @return \Illuminate\Http\JsonResponse
     */
    public static function searchProduct(
        string $data,
        $pageSize,
        int $currentPageNumber = 1,
        bool $isPriceDescend = true,
        string $m_class = 'customer',
        string $type = '0'
    ) {
        $sale_channel = DB::table('usr_identity')
            ->where('usr_identity.code', '=', $m_class)
            ->leftJoin('usr_identity_salechannel', 'usr_identity.id', '=', 'usr_identity_salechannel.identity_id')
            ->leftJoin('prd_sale_channels as sale_channel', 'usr_identity_salechannel.sale_channel_id', '=', 'sale_channel.id')
            ->select('sale_channel_id', 'sale_channel.sales_type')
            ->get()
            ->first();

        if ($sale_channel->sales_type == '1') {
            $sales_type = 'prd.online';
        } else {
            $sales_type = 'prd.offline';
        }

        // start to check 產品的上下架時間
        $activeDateQuery = DB::table('prd_products as prd')
            ->where('prd.title', 'LIKE', "%$data%")
            ->orWhere('prd.sku', 'LIKE', "%$data%")
            ->where('prd.public', '=', 1)
            ->select(
                'active_sdate',
                'active_edate',
            )
            ->get()
            ->first();
        $startDate = $activeDateQuery->active_sdate ?? null;
        $endDate = $activeDateQuery->active_edate ?? null;
        date_default_timezone_set('Asia/Taipei');
        $now = date('Y-m-d H:i:s');

        if (is_null($startDate)
            && !is_null($endDate)
            && $now > $endDate
        ) {
            return response()->json([
                'status' => ApiStatusMessage::NotFound,
                'msg' => ApiStatusMessage::getDescription(ApiStatusMessage::NotFound),
                'data' => [],
            ]);
        } elseif (!is_null($startDate)
            && is_null($endDate)
            && $now < $startDate
        ) {
            return response()->json([
                'status' => ApiStatusMessage::NotFound,
                'msg' => ApiStatusMessage::getDescription(ApiStatusMessage::NotFound),
                'data' => [],
            ]);
        } elseif (!is_null($startDate)
            && !is_null($endDate)
        ) {
            if ($now < $startDate) {
                return response()->json([
                    'status' => ApiStatusMessage::NotFound,
                    'msg' => ApiStatusMessage::getDescription(ApiStatusMessage::NotFound),
                    'data' => [],
                ]);
            } elseif ($now > $endDate) {
                return response()->json([
                    'status' => ApiStatusMessage::NotFound,
                    'msg' => ApiStatusMessage::getDescription(ApiStatusMessage::NotFound),
                    'data' => [],
                ]);
            }
        }
        // end to check 產品的上下架時間

        $productQueries = DB::table('prd_products as prd')
            ->where('prd.online', '=', 1)
            ->where('prd.public', '=', 1);

        if ($type === '0') {
            //只有「一般商品」，排除掉其它類型商品（例如：酒類）
            $productQueries->whereNotIn('prd.sku', self::getSkuByType($type));
        } else {
            //只有「特殊商品」，例如酒類
            $productQueries->whereIn('prd.sku', self::getSkuByType($type));
        }

        $productQueries = $productQueries
            ->where('prd.title', 'LIKE', "%$data%")
            ->orWhere('prd.sku', 'LIKE', "%$data%")
            ->leftJoin('prd_product_styles as product_style', function ($join) {
                $join->on('product_style.product_id', '=', 'prd.id')
                    ->where('product_style.is_active', '=', 1);
            })
            ->leftJoin('prd_salechannel_style_price
             as sale_channel', function ($join) use ($sale_channel) {
                $join->on('sale_channel.style_id', '=', 'product_style.id')
                    ->where('sale_channel.sale_channel_id', '=', $sale_channel->sale_channel_id);
            })
            ->leftJoin('prd_product_images as images', 'images.product_id', '=', 'prd.id')
            ->whereNull('prd.deleted_at')
            ->where($sales_type, '1')
            ->whereNotNull('sale_channel.price')
            ->select(
                'prd.id as id',
                'prd.sku as sku',
                'prd.title as title',
                'sale_channel.price as price',
                'sale_channel.origin_price as origin_price',
                'images.url as img_url',
            )
            ->orderBy('price', $isPriceDescend ? 'desc' : 'asc')
            ->get()
            //用groupBy(product_id)及transform min(price, origin_price) 取得product_id的不同款式中價錢最小
            ->groupBy('id')
            ->transform(function ($item) {
                return [
                    'id' => $item[0]->id,
                    'sku' => $item[0]->sku,
                    'title' => $item[0]->title,
                    'price' => $item->min('price'),
                    'origin_price' => $item->min('origin_price'),
                    'img_url' => $item[0]->img_url,
                ];
            })
        ;

        $totalCounts = $productQueries->count();
        if ($totalCounts === 0) {
            return response()->json([
                'status' => ApiStatusMessage::NotFound,
                'msg' => ApiStatusMessage::getDescription(ApiStatusMessage::NotFound),
                'data' => [],
            ]);
        }
        if (empty($pageSize)) {
            $productQueries = $productQueries->forPage(1, $totalCounts);
            $totalPages = 1;
        } else {
            $productQueries = $productQueries->forPage($currentPageNumber, $pageSize);
            $totalPages = ceil($totalCounts / $pageSize);
        }

        $productData = [];
        foreach ($productQueries as $productQuery) {
            if (!is_null($productQuery['img_url'])) {
                $imageUrl = getImageUrl($productQuery['img_url'], true);
            } else {
                $imageUrl = '';
            }

            $productData[] = [
                'id' => $productQuery['id'],
                'sku' => $productQuery['sku'],
                'title' => $productQuery['title'],
                'img_url' => $imageUrl,
                'price' => $productQuery['price'],
                'origin_price' => $productQuery['origin_price'],
            ];
        }
        if ($isPriceDescend) {
            $listData = collect($productData)->sortByDesc('price')->values()->all();
        } else {
            $listData = collect($productData)->sortBy('price')->values()->all();
        }

        return response()->json([
            'status' => ApiStatusMessage::Succeed,
            'msg' => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
            'data' => [
                'page' => $totalPages,
                'list' => $listData,
            ],
        ]);
    }

    public static function update_product_taxation($parm)
    {
        self::where('id', $parm['product_id'])->update([
            'has_tax' => $parm['taxation'],
        ]);
    }

    public static function cloneInfo($product_id, $from_id)
    {
        $fromProduct = self::where('id', $from_id)->get()->first();
        $product = self::where('id', $product_id)->get()->first();
        if (!$fromProduct || !$product) {
            return;
        }

        DB::beginTransaction();

        self::where('id', $product_id)->update([
            'feature' => $fromProduct->feature,
            'slogan' => $fromProduct->slogan,
            'desc' => $fromProduct->desc,
        ]);

        // img

        $fromImg = ProductImg::where('product_id', $from_id)->get()->toArray();
        ProductImg::where('product_id', $product_id)->delete();
        if (count($fromImg) > 0) {
            ProductImg::insert(array_map(function ($n) use ($product_id) {
                return [
                    'product_id' => $product_id,
                    'url' => $n['url'],
                    'sort' => $n['sort'],
                ];
            }, $fromImg));
        }

        // spec list
        $fromList = ProductSpecList::where('product_id', $from_id)->get()->toArray();
        ProductSpecList::where('product_id', $product_id)->delete();
        if (count($fromList) > 0) {
            ProductSpecList::insert(array_map(function ($n) use ($product_id) {
                return [
                    'product_id' => $product_id,
                    'title' => $n['title'],
                    'content' => $n['content'],
                    'sort' => $n['sort'],
                ];
            }, $fromList));
        }

        // shipment

        $shipList = DB::table('prd_product_shipment')->where('product_id', $from_id)->get()->toArray();
        DB::table('prd_product_shipment')->where('product_id', $product_id)->delete();
        if (count($shipList) > 0) {
            DB::table('prd_product_shipment')->insert(array_map(function ($n) use ($product_id) {
                return [
                    'product_id' => $product_id,
                    'category_id' => $n->category_id,
                    'group_id' => $n->group_id,
                ];
            }, $shipList));
        }

        DB::commit();

    }

}
