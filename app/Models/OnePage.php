<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class OnePage extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'opg_one_page';
    protected $guarded = [];

    public static function dataList($title = null)
    {

        $re = DB::table('opg_one_page as one')
            ->join('collection', 'one.collection_id', '=', 'collection.id')
            ->join('prd_sale_channels as channel', 'one.sale_channel_id', '=', 'channel.id')
            ->select(['one.*', 'collection.name as collection_title', 'channel.title as salechannel_title'])
            ->whereNull('one.deleted_at');

        if ($title) {
            $re->where('one.title', 'like', "%$title%");
        }

        return $re;

    }

    public static function changeActiveStatus($id)
    {
        $active = self::where('id', $id)
            ->get()
            ->first()
            ->active;

        if ($active) {
            self::where('id', $id)->update(['active' => 0]);
        } else {
            self::where('id', $id)->update(['active' => 1]);
        }
    }

    public static function changeAppStatus($id)
    {
        $active = self::where('id', $id)
            ->get()
            ->first()
            ->active;

        if ($active) {
            self::where('id', $id)->update(['app' => 0]);
        } else {
            self::where('id', $id)->update(['app' => 1]);
        }
    }

    public static function getProducts($collection_id, $sale_channel_id)
    {

        $dataList = DB::table('prd_products as product')
            ->join('collection_prd as cp', 'cp.product_id_fk', '=', 'product.id')
            ->select('product.id as id',
                'product.title as title',
                'product.sku as sku',
                'product.type as type',
                'product.consume as consume',
                'product.online as online',
                'product.offline as offline',
                'product.public as public')
            ->selectRaw('CASE product.type WHEN "p" THEN "一般商品" WHEN "c" THEN "組合包商品" END as type_title')
            ->selectRaw('IF(product.desc IS NULL,"",product.desc) as _desc')
            ->selectRaw('IF(product.feature IS NULL,"",product.feature) as feature')
            ->selectRaw('IF(product.logistic_desc IS NULL,"",product.logistic_desc) as logistic_desc')
            ->selectRaw('IF(product.slogan IS NULL,"",product.slogan) as slogan')
            ->where('product.public', 1)
            ->where('online', '1')
            ->where('product.online', 1)
            ->where(function ($query) {
                $now = date('Y-m-d H:i:s');
                $query->where(function ($query) use ($now) {
                    $query->where('product.active_sdate', '<=', $now)
                        ->orWhereNull('product.active_sdate');
                })
                    ->where(function ($query) use ($now) {
                        $query->where('product.active_edate', '>=', $now)
                            ->orWhereNull('product.active_edate');
                    });
            })
            ->whereNull('product.deleted_at')
            ->where('cp.collection_id_fk', '=', $collection_id);

        $subImg = DB::table('prd_product_images as img')
            ->select('img.url')
            ->whereRaw('img.product_id = product.id')
            ->limit(1);

        $subStyle = DB::table('prd_product_styles as style')
            ->join('prd_salechannel_style_price as price', 'style.id', '=', 'price.style_id')
            ->select('style.product_id')
            ->selectRaw(concatStr([
                'id' => 'style.id',
                'title' => 'style.title',
                'in_stock' => DB::raw('style.in_stock + style.overbought'),
                'price' => 'price.price',
                'origin_price' => 'price.origin_price',
                'dividend' => 'price.dividend',
                'sku' => 'style.sku',
                'overbought' => 'style.overbought',
            ]) . " as styles")
            ->where('price.sale_channel_id', $sale_channel_id)
            ->groupBy('style.product_id');

        $dataList->joinSub($subStyle, 'style', 'product.id', '=', 'style.product_id')
            ->addSelect('style.styles');

        $dataList = $dataList->addSelect(DB::raw("({$subImg->toSql()}) as img_url"))->get()->toArray();

        if ($dataList) {

            foreach ($dataList as $key => $value) {
                if ($value->img_url) {
                    $dataList[$key]->img_url = getImageUrl($value->img_url, true);
                    $dataList[$key]->styles = json_decode($value->styles);
                }
                $shipment = Product::getProductShipments($value->id);
                $dataList[$key]->shipment = $shipment ? $shipment : '';
            }

            Product::getMinPriceProducts($sale_channel_id, null, $dataList);

        }

        return $dataList;

    }

}
