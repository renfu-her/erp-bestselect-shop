<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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


    public function deleteCollectionById(int $id)
    {
        DB::table('collection_prd')
            ->where('collection_id_fk', $id)
            ->delete();
        self::where('id', $id)
            ->delete();
    }
}
