<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Collection extends Model
{
    use HasFactory;

    protected $table = 'collection';
    protected $fillable = [
            'name',
            'url',
            'meta_title',
            'meta_description',
            'is_public'
        ];

    /**
     * @param  string  $id collection primary_id
//     * @param  int  $amount 分頁數、限制回傳數量（待討論）
     *
     * @return array|mixed
     */
    public static function getApiCollectionData(string $id)
    {
        // data not exist
        if (!self::where('id', $id)->get()->first()) {
            return [
                'status' => 1,
                'msg' => '查無此商品群組',
                'data' => [
                ]
            ];
        }

        $queryResult =
            DB::table('collection')
                ->where('collection.id', '=', $id)
            ->leftJoin('collection_prd', 'collection_id_fk', 'collection.id')
            ->leftJoin('prd_products', 'product_id_fk', '=', 'prd_products.id')
            ->leftJoin('prd_product_styles', 'prd_products.id', '=', 'prd_product_styles.product_id')
            ->leftJoin('prd_product_images', 'prd_products.id', '=', 'prd_product_images.product_id')
            ->where('prd_product_styles.is_active', '=', '1')
            ->leftJoin('prd_salechannel_style_price', 'prd_salechannel_style_price.style_id', '=', 'prd_product_styles.id')
            ->whereNotNull('prd_salechannel_style_price.price')
            ->orderBy('prd_salechannel_style_price.price')
            ->select(
                'prd_product_styles.sku',
                'prd_products.title',
                'prd_products.id as prd_id',
                'prd_product_images.url as img_url',
                'prd_product_styles.in_stock',
                'prd_salechannel_style_price.origin_price',
                'prd_salechannel_style_price.price',
            )
            ->get()
            ->groupBy('prd_id')
            ->toArray();

        $dataList  = array();
        foreach ($queryResult as $item) {
            $dataList[] = [
                'sku'    => $item[0]->sku,
                'name'   => $item[0]->title,
                'img'    => $item[0]->img_url,
                'amount' => $item[0]->in_stock,
                'price'  => [
                    'origin' => $item[0]->origin_price,
                    'sale'   => $item[0]->price
                ]
            ];
        }

        return $dataList;
    }

    public function storeCollectionData(
        string $collection_name,
        string $url,
        $meta_title,
        $meta_description,
        string $is_public,
        array $productIdArray
    ) {
        if (self::where('url', $url)->get()->first()) {
            $url = $url.'-'.time();
        }

        $collectionId = self::create([
            'name'             => $collection_name,
            'url'              => $url,
            'meta_title'       => $meta_title,
            'meta_description' => $meta_description,
            'is_public'        => (bool) $is_public,
        ])->id;


        for ($i = 0; $i < count($productIdArray); $i++) {
            DB::table('collection_prd')
                ->insert([
                    'collection_id_fk' => $collectionId,
                    'product_id_fk'    => $productIdArray[$i]
                ]);
        }
    }

    public function getCollectionDataById(int $id)
    {
        return db::table('collection')
            ->where('collection.id', $id)
            ->leftjoin('collection_prd', 'collection.id', '=',
                'collection_prd.collection_id_fk')
            ->leftjoin('prd_products', 'collection_prd.product_id_fk', '=',
                'prd_products.id')
            ->select('collection.name',
                'collection.url',
                'collection.meta_title',
                'collection.meta_description',
                'prd_products.id',
                'prd_products.title',
                'prd_products.sku')
            ->selectraw(
                'case
                when prd_products.type = "p" then "一般商品"
                when prd_products.type = "c" then "組合包"
                end as type_title')
            ->get();
    }

    public function deleteCollectionById(int $id)
    {
        DB::table('collection_prd')
            ->where('collection_id_fk', $id)
            ->delete();
        self::where('id', $id)
            ->delete();
    }

    public function updateCollectionData(
        int $collectionId,
        string $collection_name,
        string $url,
        string $meta_title,
        string $meta_description,
        array $prdIdArray
    ) {
        if (self::where([
            ['id', '<>', $collectionId],
            ['url', '=', $url]
        ])->get()->first()
        ) {
            $url = $url.'-'.time();
        }

        self::where('id', $collectionId)
            ->update([
                'name'             => $collection_name,
                'url'              => $url,
                'meta_title'       => $meta_title,
                'meta_description' => $meta_description,
            ]);

        DB::table('collection_prd')
            ->where('collection_id_fk', $collectionId)
            ->delete();
        for ($i = 0; $i < count($prdIdArray); $i++) {
            DB::table('collection_prd')->insert([
                'collection_id_fk' => $collectionId,
                'product_id_fk'    => $prdIdArray[$i]
            ]);
        }
    }

    public function changePublicStatus(int $id)
    {
        $isPublic = self::where('id', $id)
            ->get()
            ->first()
            ->is_public;

        if ($isPublic) {
            self::where('id', $id)->update(['is_public' => 0]);
        } else {
            self::where('id', $id)->update(['is_public' => 1]);
        }
    }

    public function getDataList(array $query)
    {
        $result = DB::table('collection');

        $name = Arr::get($query, 'name', '');
        if ($name) {
           $result->where('name', 'like', "%$name%");
        }

        return $result->paginate(10)
                    ->appends($query);
    }
}
