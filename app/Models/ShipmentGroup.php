<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ShipmentGroup extends Model
{
    use HasFactory;

    protected $table = 'shi_group';

    protected $fillable = [
        'category_fk',
        'name',
        'temps_fk',
        'method_fk',
        'note',
    ];

    //取得group with 成本 for物流資料使用
    public static function getDataWithCost() {
        $query = DB::table('shi_group')
            ->leftJoin('shi_rule', function($join) {
                $join->on('shi_rule.group_id_fk', '=', 'shi_group.id');
            })
            ->groupBy('shi_rule.group_id_fk')
            ->groupBy('shi_rule.dlv_cost')
            ->groupBy('shi_rule.at_most')
            ->groupBy('shi_group.name')
            ->groupBy('shi_group.method_fk')
            ->groupBy('shi_group.temps_fk')
            ->select('shi_rule.group_id_fk'
                , 'shi_rule.dlv_cost'
                , 'shi_rule.at_most'
                , 'shi_group.name'
                , 'shi_group.method_fk'
                , 'shi_group.temps_fk'
            )
            ->selectRaw('max(shi_rule.dlv_fee) as dlv_fee');

        return $query;
    }

}
