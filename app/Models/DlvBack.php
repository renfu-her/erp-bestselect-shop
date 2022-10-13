<?php

namespace App\Models;

use App\Enums\DlvBack\DlvBackType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DlvBack extends Model
{
    use HasFactory;
    protected $table = 'dlv_back';
    protected $guarded = [];
    public $timestamps = true;

    public static function getDataWithDeliveryID($delivery_id) {
        $result = DB::table(app(DlvBack::class)->getTable(). ' as dlv_back')
            ->where('dlv_back.delivery_id', $delivery_id)
            ->where('dlv_back.type', DlvBackType::product()->value)
            ->select(
                'dlv_back.id'
                , 'dlv_back.event_item_id'
                , 'dlv_back.product_style_id'
                , 'dlv_back.sku'
                , 'dlv_back.product_title'
                , 'dlv_back.price'
                , 'dlv_back.origin_qty'
                , 'dlv_back.qty as back_qty'
                , DB::raw('ifnull(dlv_back.bonus, "") as bonus')
                , 'dlv_back.memo'
                , 'dlv_back.show'
            );
        return $result;
    }

    public static function getOtherDataWithDeliveryID($delivery_id) {
        $result = DB::table(app(DlvBack::class)->getTable(). ' as dlv_backoth')
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'l_grade', function($join) {
                $join->on('l_grade.primary_id', 'dlv_backoth.grade_id');
            })
            ->where('dlv_backoth.type', '<>', DlvBackType::product()->value)
            ->where('dlv_backoth.delivery_id', '=', $delivery_id)
            ->select('dlv_backoth.id'
                , 'l_grade.code as grade_code'
                , 'l_grade.name as grade_name'
                , 'dlv_backoth.grade_id'
                , 'dlv_backoth.type'
                , 'dlv_backoth.product_title'
                , 'dlv_backoth.price'
                , 'dlv_backoth.qty'
                , 'dlv_backoth.memo'
            );
        return $result;
    }

}
