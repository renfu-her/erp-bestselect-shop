<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * 處理商品的款式 Model
 */
class ProductStyle extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'prd_product_styles';
    protected $guarded = [];

    /**
     * @param $product_id
     * @param int[] $item_ids Table prd_spec_items primary_id
     *
     * @return array
     */
    private static function _specQuery($product_id, $item_ids)
    {
        return DB::table('prd_product_spec as ps')
            ->leftJoin('prd_spec_items as item', 'ps.spec_id', '=', 'item.spec_id')
            ->where('ps.product_id', $product_id)
            ->whereIn('item.id', $item_ids)
            ->select('item.title', 'item.id')
            ->orderBy('ps.rank', 'ASC')->get()->toArray();
    }

    /**
     * @param int $product_id
     * @param int[] $item_ids Table prd_spec_items' primary_id array
     * 同一規格的spec_id只能傳入1個,
     * 例如商品A，規格「容量」有項目「100ML」,「200ML」，對應的pimary_id為1, 2
     *          規格「顏色」有項目「紅」,「藍」對應的primary_id為3, 4
     *          $item_ids只需傳入[1, 3],[1, 4],[2, 3], [2,4]
     * @param int $is_active 上下架
     *
     */
    public static function createStyle($product_id, $item_ids, $is_active = 1)
    {

        $product = Product::where('id', $product_id)->get()->first();
        if (!$product) {
            return;
        }

        $spec = self::_specQuery($product_id, $item_ids);

        $title = '';
        foreach ($spec as $key => $v) {
            $data['spec_item' . ($key + 1) . '_id'] = $v->id;
            $data['spec_item' . ($key + 1) . '_title'] = $v->title;
            $title .= $v->title . " ";
        }

        $data['product_id'] = $product_id;
        $data['type'] = 'p';

        $data['is_active'] = $is_active;
        $data['title'] = trim($title);

        return self::create($data)->id;

    }

    /**
     * subquery 範例
     */

    public static function ttt()
    {
        $usb = DB::table('prd_products')->select('title')->where('prd_products.id', DB::raw('style.id'));
        return DB::table('prd_product_styles as style')
            ->select('style.*')
            ->selectRaw(DB::raw("({$usb->toSql()}) as p_title"))
            ->mergeBindings($usb);
    }

    /**
     * 款式列表與其價格
     */
    public static function styleList($product_id)
    {
        $channelSub = self::getChannelSubList();

        $re = DB::table('prd_product_styles as style')
            ->leftJoin(DB::raw("({$channelSub->toSql()}) as price"), function ($join) {
                $join->on('style.id', '=', 'price.style_id');
            })
            ->mergeBindings($channelSub)
            ->select('style.*', 'price.*')
            ->selectRaw('IF(price.dealer_price,price.dealer_price,0) as dealer_price')
            ->selectRaw('IF(price.origin_price,price.origin_price,0) as origin_price')
            ->selectRaw('IF(price.price,price.price,0) as price')
            ->selectRaw('IF(price.bonus,price.bonus,0) as bonus')
            ->selectRaw('IF(price.dividend,price.dividend,0) as dividend')
            ->where('style.product_id', $product_id)
            ->whereNull('style.deleted_at');

        return $re;
    }

    // 取得官網價格
    public static function getChannelSubList()
    {
        $channelSub = DB::table('prd_sale_channels as sale_channel')
            ->leftJoin('prd_salechannel_style_price as price', 'sale_channel.id', '=', 'price.sale_channel_id')
            ->select('price.style_id')
            ->selectRaw('IF(price.dealer_price,price.dealer_price,0) as dealer_price')
            ->selectRaw('IF(price.origin_price,price.origin_price,0) as origin_price')
            ->selectRaw('IF(price.price,price.price,0) as price')
            ->selectRaw('IF(price.bonus,price.bonus,0) as bonus')
            ->selectRaw('IF(price.dividend,price.dividend,0) as dividend')
            ->where('is_master', '1');
        return $channelSub;
    }

    /**
     * 更新款式
     * @param int $id
     * @param int $product_id
     * @param array $item_ids 規格資料
     * @param array $otherData 其他規格
     */

    public static function updateStyle($id, $product_id, $item_ids, $otherData = [])
    {

        $data = [];

        $spec = self::_specQuery($product_id, $item_ids);

        $title = '';

        foreach ($spec as $key => $v) {
            $data['spec_item' . ($key + 1) . '_id'] = $v->id;
            $data['spec_item' . ($key + 1) . '_title'] = $v->title;
            $title .= $v->title . " ";
        }

        $data = array_merge($data, $otherData);
        $data['title'] = $title;
        self::where('id', $id)->update($data);

    }

    /**
     * 建立商品各款式SKU(商品SKU後面再加流水號）
     * @param $product_id
     * @param int $id this model's primary_id
     *
     * @return false|mixed
     */
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
            Product::where('id', $product_id)->update(['spec_locked' => 1]);
            //新增所有通路價格

            SaleChannel::addPriceForStyle($id);
            return true;
        });
    }

    public static function createSkuByProductId($product_id)
    {
        $styles = self::where('product_id', $product_id)->whereNull('sku')->select('id')->get()->toArray();

        foreach ($styles as $style) {
            self::createSku($product_id, $style['id']);
        }

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

    public static function createComboStyle($product_id, $title, $is_active = 1)
    {

        if (!Product::where('id', $product_id)->where('type', 'c')->get()->first()) {
            return;
        }

        $data['product_id'] = $product_id;
        $data['type'] = 'c';

        $data['is_active'] = $is_active;
        $data['title'] = trim($title);

        return self::create($data)->id;

    }

    public static function stockProcess($style_id, $safety_stock, $overbought, $sale_ids = [], $qtys = [])
    {

        return DB::transaction(function () use ($style_id, $safety_stock, $overbought, $sale_ids, $qtys) {
            // self::where('id', $style_id)->update(['safety_stock' => $safety_stock, $overbought => 'overbought']);
            if (isset($sale_ids) && is_array($sale_ids)) {
                $data = [];

                for ($i = 0; $i < count($sale_ids); $i++) {
                    if (isset($qtys[$i])) {
                        $data[] = ['sale_id' => $sale_ids[$i], 'qty' => $qtys[$i]];
                    }
                }

                usort($data, function ($a, $b) {
                    return $a['qty'] > $b['qty'];
                });

                foreach ($data as $value) {
                    if ($value['qty'] != 0) {

                        SaleChannel::changeStock($value['sale_id'], $style_id, $value['qty']);
                        $re = ProductStock::stockChange($style_id, $value['qty'] * -1, 'sale', $value['sale_id']);
                        if (!$re['success']) {
                            DB::rollBack();
                            return $re;
                        }
                    }
                }
            }

            self::where('id', $style_id)->update(['safety_stock' => $safety_stock,
                'overbought' => $overbought]);

            return ['success' => 1];
        });
    }

    public static function batchOverbought()
    {
        DB::beginTransaction();
        $re = DB::table('prd_products as product')
            ->leftJoin('prd_product_shipment as shipment', 'product.id', '=', 'shipment.product_id')
            ->leftJoin('shi_group as group', 'group.id', '=', 'shipment.group_id')
            ->leftJoin('shi_method as method', 'method.id', '=', 'group.method_fk')
            ->select('product.id as product_id')
            ->where('method.id', 2)
            ->where('group.category_fk', 1)->get()->toArray();

        // dd($re);
        self::query()->update([
            'overbought' => 0,
        ]);

        self::whereIn('product_id', array_map(function ($n) {
            return $n->product_id;
        }, $re))->update([
            'overbought' => 10,
        ]);

        echo "超買設定完成";
        DB::commit();
    }

    public static function getStylePrice($product_id, $salechannel = [1, 2])
    {

        $column = concatStr([
            'salechannel_title' => 'channel.title',
            'style_title' => 'style.title',
            'price' => 'sp.price',
            'bonus' => 'sp.bonus',
            'style_id' => 'style.id',
            'dealer_price' => 'sp.dealer_price',
            'dividend' => 'sp.dividend',
            'origin_price' => 'sp.origin_price',
            'sku' => 'style.sku',
        ]);

        $sub = DB::table('prd_salechannel_style_price as sp')
            ->leftJoin('prd_sale_channels as channel', 'sp.sale_channel_id', '=', 'channel.id')
            ->leftJoin('prd_product_styles as style', 'sp.style_id', '=', 'style.id')
            ->select('sp.style_id')
            ->selectRaw(('(' . $column . ') as prices'))
            ->groupBy('sp.style_id')
            ->where('style.product_id', $product_id);

        if ($salechannel && is_array($salechannel)) {
            $sub->whereIn('sp.sale_channel_id', $salechannel);
        }

        $output = array_map(function ($v) {
            $v->prices = json_decode($v->prices);
            // dd($v);
            if (isset($v->prices[0])) {
                $v->title = $v->prices[0]->style_title;
                $v->sku = $v->prices[0]->sku;
            }
            return $v;

        }, $sub->get()->toArray());

        return $output;
    }

    public static function willBeShipped($style_id, $qty)
    {
       // dd('aaa');
        self::where('id', $style_id)->update([
            'will_be_shipped' => DB::raw('will_be_shipped +' . $qty),
        ]);
    }

}
