<?php

namespace App\Models;

use App\Enums\Globals\AppEnvClass;
use App\Enums\Globals\FrontendApiUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
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
        'is_public',
        'is_liquor',
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
                ],
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

        $dataList = array();
        foreach ($queryResult as $item) {
            $dataList[] = [
                'sku' => $item[0]->sku,
                'name' => $item[0]->title,
                'img' => $item[0]->img_url,
                'amount' => $item[0]->in_stock,
                'price' => [
                    'origin' => $item[0]->origin_price,
                    'sale' => $item[0]->price,
                ],
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
        int $is_liquor,
        array $productIdArray
    ) {
        if (self::where('url', $url)->get()->first()) {
            $url = $url . '-' . time();
        }

        $collectionId = self::create([
            'name' => $collection_name,
            'url' => $url,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'is_public' => (bool) $is_public,
            'is_liquor' => $is_liquor,
        ])->id;

        for ($i = 0; $i < count($productIdArray); $i++) {
            DB::table('collection_prd')
                ->insert([
                    'collection_id_fk' => $collectionId,
                    'product_id_fk' => $productIdArray[$i],
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
                'collection.is_liquor',
                'prd_products.id',
                'prd_products.title',
                'prd_products.sku',
                'collection_prd.sort')
            ->selectraw(
                'case
                when prd_products.type = "p" then "一般商品"
                when prd_products.type = "c" then "組合包"
                end as type_title')
            ->orderBy('collection_prd.sort')
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
        int $is_liquor,
        array $prdIdArray,
        array $sort

    ) {
        if (self::where([
            ['id', '<>', $collectionId],
            ['url', '=', $url],
        ])->get()->first()
        ) {
            $url = $url . '-' . time();
        }

        self::where('id', $collectionId)
            ->update([
                'name' => $collection_name,
                'url' => $url,
                'meta_title' => $meta_title,
                'meta_description' => $meta_description,
                'is_liquor' => $is_liquor,
            ]);

        DB::table('collection_prd')
            ->where('collection_id_fk', $collectionId)
            ->delete();
        for ($i = 0; $i < count($prdIdArray); $i++) {
            DB::table('collection_prd')->insert([
                'collection_id_fk' => $collectionId,
                'product_id_fk' => $prdIdArray[$i],
                'sort' => $sort[$i],
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

    public function changeEdmStatus(int $id)
    {
        $edm = self::where('id', $id)
            ->get()
            ->first()
            ->edm;

        if ($edm) {
            self::where('id', $id)->update(['edm' => 0]);
        } else {
            self::where('id', $id)->update(['edm' => 1]);
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

    /**
     * @param $id string collection table primary id
     * @param $is_liquor string 是否是酒類商品?
     * @param $subUrl string 商品最底層的url
     * 回傳完整的商品群組給後台的「商品群組」複製連結使用
     * @return string
     */
    public static function getCollectionFullPath($id, $is_liquor, $subUrl)
    {
        # trim special character
        # reference https://secure.n-able.com/webhelp/nc_9-1-0_so_en/content/sa_docs/api_level_integration/api_integration_urlencoding.html
        $subUrl = str_replace(
            [
                '$',
                '&',
                '+',
                ',',
                '/',
                ':',
                ';',
                '=',
                '?',
                '@',
                ' ',
                '<',
                '>',
                '#',
                '%',
                '{',
                '}',
                '|',
                '\\',
                '^',
                '~',
                '[',
                ']',
                '`',
            ],
            '',
            $subUrl
        );

        $domain = frontendUrl();
        if (App::environment([
            AppEnvClass::Local,
            AppEnvClass::Development,
        ])) {
            if ($is_liquor == 1) {
                $domain = env('FRONTEND_DEV_LIQUOR_URL');
            }
        } else {
            if ($is_liquor == 1) {
                $domain = env('FRONTEND_LIQUOR_URL');
            }
        }

        return $domain .
        FrontendApiUrl::collection .
            '/' .
            $id .
            '/' .
            $subUrl;
    }

    public static function addProductToCollections($product_id, $collection_ids)
    {
        DB::table('collection_prd')->where('product_id_fk', $product_id)->delete();
        if (count($collection_ids) == 0) {
            return;
        }
        DB::table('collection_prd')->insert(array_map(function ($n) use ($product_id) {
            return [
                'product_id_fk' => $product_id,
                'collection_id_fk' => $n,
            ];
        }, $collection_ids));
    }
}
