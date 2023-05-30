<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FruitCollection extends Model
{
    use HasFactory;
    protected $table = 'fru_collections';
    protected $guarded = [];
    public $timestamps = false;

    public static function getFruitList()
    {

        $fruitData = [
            'fruit_id' => 'cf.fruit_id',
            'sort' => 'cf.sort',
            'fruit_title' => 'fruit.title',
        ];

        $re = DB::table('fru_collection_fruit as cf')
            ->leftJoin('fru_fruits as fruit', 'cf.fruit_id', '=', 'fruit.id')
            ->select('cf.collection_id')
            ->selectRaw(concatStr($fruitData, 'order by cf.sort asc') . " as fruits")
            ->groupBy('cf.collection_id')->get();

        foreach ($re as $value) {
            $value->fruits = json_decode($value->fruits);
        }

        return $re;

    }

    public static function getFruitListForApi()
    {

        $fruitData = [
            'id' => 'cf.fruit_id',
            'sort' => 'cf.sort',
            'title' => 'fruit.title',
            'sub_title' => 'fruit.sub_title',
            'place' => 'fruit.place',
            'season' => 'fruit.season',
            'pic' => 'fruit.pic',
            'link' => 'fruit.link',
            'text' => 'fruit.text',
            'status' => 'fruit.status',
        ];

        $sub = DB::table('fru_collection_fruit as cf')
            ->leftJoin('fru_fruits as fruit', 'cf.fruit_id', '=', 'fruit.id')
            ->select('cf.collection_id')
            ->selectRaw(concatStr($fruitData, 'order by cf.sort asc') . " as fruits")
            ->groupBy('cf.collection_id');

        $re = DB::table('fru_collections as collection')
            ->leftJoinSub($sub, 'fruit', 'collection.id', '=', 'fruit.collection_id')
            ->select(['collection.*'])
            ->selectRaw('IF(fruit.fruits IS NULL,"[]",fruit.fruits) as fruits')
            ->orderBy('collection.id')
            ->get();

        foreach ($re as $value) {
            $value->fruits = json_decode($value->fruits);
        }

        return $re;
    }

}
