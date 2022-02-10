<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ShipmentCategory extends Model
{
    use HasFactory;

    protected $table = 'shi_category';

    protected $fillable = [
        'category',
    ];

    public static function findCategoryIdByName(string $category)
    {
        return self::where('category', '=', $category)
            ->select('id')
            ->get()
            ->first()->id;
    }

    public static function categoryWithGroup()
    {
        $concatString = concatStr([
            'id' => 'g.id',
            'name' => 'g.name',
            'category_fk' => 'g.category_fk',
        ]);

        $groupQuery = DB::table('shi_group as g')
            ->select('g.category_fk')
            ->selectRaw($concatString . ' as groupConcat')
            ->groupBy('g.category_fk');

        $categoryQuery = DB::table('shi_category as ca')
            ->leftJoin(DB::raw("({$groupQuery->toSql()}) as g"), function ($join) {
                $join->on('ca.id', '=', 'g.category_fk');
            })
            ->select('id', 'category', 'groupConcat')
            ->mergeBindings($groupQuery)
            ->orderBy('ca.id');

        $re = $categoryQuery->get()->toArray();

        foreach ($re as $key => $value) {
            $re[$key]->groupConcat = json_decode($value->groupConcat);
        }

        return $re;

    }

}
