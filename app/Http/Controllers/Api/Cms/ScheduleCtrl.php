<?php

namespace App\Http\Controllers\Api\Cms;

use App\Enums\Discount\DividendCategory;
use App\Enums\Order\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerCoupon;
use App\Models\CustomerDividend;
use App\Models\CustomerReportDaily;
use App\Models\CustomerReportMonth;
use App\Models\Order;
use App\Models\OrderReportDaily;
use App\Models\OrderReportMonth;
use App\Models\RptOrganizeReportMonthly;
use App\Models\RptUserReportMonthly;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ScheduleCtrl extends Controller
{
    //
    public function checkDividendExpired(Request $request)
    {
        foreach (Customer::get() as $customer) {
            CustomerDividend::checkExpired($customer->id);
        }

        return ['status' => '0'];
    }

    public function activeDividend(Request $request)
    {
        $order = Order::where('payment_status', OrderStatus::Received()->value)
            ->where('auto_dividend', '1')
            ->where('allotted_dividend', '0')
            ->where('dividend_active_at', '<=', now())
            ->get();

        foreach ($order as $ord) {
            CustomerDividend::activeDividend(DividendCategory::Order(), $ord->sn, now());
            CustomerCoupon::activeCoupon($ord->id, now());
        }

        return ['status' => '0'];
    }

    public function orderReportDaily()
    {
        OrderReportDaily::createData();
        return ['status' => '0'];
    }

    public function orderReportMonth(Request $request)
    {

        $query = $request->query();

        $date = Arr::get($query, 'date', null);

        // OrderReportMonth::createData($date);
        return ['status' => '0', 'data' => OrderReportMonth::createData($date)];
    }

    public function customerReportDaily()
    {
        CustomerReportDaily::createData();
        return ['status' => '0'];
    }

    public function customerReportMonth(Request $request)
    {

        $query = $request->query();

        $date = Arr::get($query, 'date', null);

        // OrderReportMonth::createData($date);
        return ['status' => '0', 'data' => CustomerReportMonth::createData($date)];
    }

    public function userReportMonth(Request $request)
    {

        $query = $request->query();

        $date = Arr::get($query, 'date', null);
        RptUserReportMonthly::report($date);
        RptOrganizeReportMonthly::report($date);
        // OrderReportMonth::createData($date);
        return ['status' => '0'];
    }

    /**
     * 將產品資料儲存成CSV檔，供臉書商店使用，臉書商店後台會定期自動抓取檔案
     * 臉書商務管理工具連結 https://business.facebook.com/commerce/catalogs
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function facebookShop()
    {
        $table = self::getFacebookShopTable();

        $fileName = 'shop.csv';
        $handle = fopen($fileName, 'w+');
        fputcsv($handle, [
            'id',
            'availability',
            'condition',
            'description',
            'image_link',
            'link',
            'title',
            'price',
            'sale_price',
            'brand',
            'custom_label_0',
        ]);

        foreach($table as $row) {
            fputcsv($handle,[
                $row['id'],
                $row['availability'],
                $row['condition'],
                $row['description'],
                $row['image_link'],
                $row['link'],
                $row['title'],
                $row['price'],
                $row['sale_price'],
                $row['brand'],
                $row['custom_label_0'],
            ]);
        }

        fclose($handle);

        $headers = array(
            'Content-Type' => 'text/csv',
        );

        return response()->download($fileName, 'shop.csv', $headers);
    }

    /**
     * @param $sku string 產品SKU
     * @param $title string 產品標題
     * 把「商品連結」加上query參數
     * @return string 商品連結
     */
    private static function getUrlLink($sku, $title)
    {
        $productUrl = 'https://www.bestselection.com.tw/product/';
        $queryArray = [
            'utm_source' => 'fb',
            'utm_medium' => 'shop',
            'utm_campaign' => 'bestselect_shop-' . urlencode($sku),
        ];

        return $productUrl . $sku . '/' . urlencode($title) . '?' . http_build_query($queryArray);
    }

    /**
     * 取得產品Array，來做資料儲存成csv格式使用
     * @return array
     */
    private static function getFacebookShopTable()
    {
        $data = DB::table('prd_products')
            ->select([
                'prd_products.id',
                'prd_products.sku',
                'prd_products.title',
                'prd_products.feature AS description',
            ])
            ->where([
                'public' => 1,
                'prd_products.online' => 1,
            ])
            ->where(function ($query) {
                $now = date('Y-m-d H:i:s');
                $query->where('active_sdate', '<=', $now)
                    ->where('active_edate', '>=', $now)
                    ->orWhereNull('active_sdate')
                    ->orWhereNull('active_edate');
            })
            ->whereNull('prd_products.deleted_at');

        $imageQuerySql = DB::table('prd_product_images')
            ->whereRaw('prd_products.id = prd_product_images.product_id')
            ->select('prd_product_images.url')
            ->limit(1)
            ->toSql();
        $data->addSelect(DB::raw("($imageQuerySql) as image_link"));

        $data = $data->leftJoin('collection_prd', 'prd_products.id', '=', 'collection_prd.product_id_fk')
            ->leftJoin('collection', 'collection.id', '=', 'collection_prd.collection_id_fk')
            ->where('is_liquor', 0);

        $dataList = $data
            ->groupBy('prd_products.id')
            ->orderBy('prd_products.id')
            ->get()
            ->toArray();
        self::getMinPriceProducts(1, null, $dataList);
        $data = self::getImgUrl($dataList);

        $productIdToCollectionNameArray = self::getProductIdToCollectionNameArray();

        $resultArray = [];
        foreach ($data as $datum) {
            $resultArray[] = [
                'id' => $datum->sku,
                'availability' => 'in stock',
                'condition' => 'new',
                'description' => $datum->description,
                'image_link' => $datum->image_link,
                'link' => self::getUrlLink($datum->sku, $datum->title),
                'title' => $datum->title,
                'price' => $datum->price . ' TWD',
                'sale_price' => $datum->sale_price . ' TWD',
                'brand' => '喜鴻購物',
                'custom_label_0' => $productIdToCollectionNameArray[$datum->id],
            ];
        }

        return $resultArray;
    }

    /**
     * @return array key 產品ID, value:產品對應到的商品群組集合（例如：群組A,群組B）
     */
    private static function getProductIdToCollectionNameArray()
    {
        $productCollection = DB::table('prd_products')
            ->leftJoin('collection_prd', 'prd_products.id', '=', 'collection_prd.product_id_fk')
            ->leftJoin('collection', 'collection.id', '=', 'collection_prd.collection_id_fk')
            ->where('is_liquor', 0)
            ->select([
                'prd_products.id',
                'collection.name',
            ])
            ->orderBy('id')
            ->get();
        $productCollect = $productCollection->groupBy('id')->toArray();

        $dataArray = [];
        $productCollectionData = array_map(function ($x) use ($dataArray){
            foreach ($x as $item) {
                if (array_key_exists($item->id, $dataArray)) {
                    $dataArray[$item->id] = $dataArray[$item->id] . ',' . $item->name;
                } else {
                    $dataArray[$item->id] = $item->name;
                }
            }
            return $dataArray;
        }, $productCollect);

        $productIdToCollectionNameArray = [];
        foreach ($productCollectionData as $key => $productCollectionDatum) {
            $productIdToCollectionNameArray[$key] = $productCollectionDatum[$key];
        }
        return $productIdToCollectionNameArray;
    }

    /**
     * @param $sale_channel_id
     * @param $product_id
     * @param $product_list
     * 更新產品價錢
     * @return array|void
     */
    private static function getMinPriceProducts($sale_channel_id, $product_id = [], &$product_list = null)
    {
        if ($product_list) {
            $product_id = array_map(function ($n) {
                return $n->id;
            }, $product_list);
        }

        $subPrice = DB::table('prd_salechannel_style_price as price')
            ->leftJoin('prd_product_styles as style', 'style.id', '=', 'price.style_id')
            ->select([
                'price.origin_price as price',
                'price.price as sale_price',
                'style.product_id',
            ])
            ->where('price.sale_channel_id', $sale_channel_id)
            ->orderBy('sale_price', 'ASC');
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

                    $pp->sale_price = $re[$pp->id]->sale_price;
                    $pp->price = $re[$pp->id]->price;

                } else {
                    $pp->sale_price = 0;
                    $pp->price = 0;
                }
            }
        }
    }

    /**
     * @param $dataList
     *  轉換圖檔連結
     * @return mixed
     */
    private static function getImgUrl($dataList)
    {
        $result = $dataList;
        if ($dataList) {
            $result = array_map(function ($n) {
                if ($n->image_link) {
                    $n->image_link = getImageUrl($n->image_link, true);
                } else {
                    $n->image_link = '';
                }

                return $n;
            }, $dataList);
        }
        return $result;
    }
}
