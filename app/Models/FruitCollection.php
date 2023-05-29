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

        foreach($re as $value ){
            $value->fruits = json_decode($value->fruits);
        }

       

        return $re;

    }

}
