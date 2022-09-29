<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DlvBackOther extends Model
{
    use HasFactory;
    protected $table = 'dlv_back_others';
    protected $guarded = [];
    public $timestamps = true;

    public static function getDataWithDeliveryID($delivery_id) {
        $result = DB::table(app(DlvBackOther::class)->getTable(). ' as dlv_backoth')
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'l_grade', function($join) {
                $join->on('l_grade.primary_id', 'dlv_backoth.grade_id');
            })
            ->where('dlv_backoth.delivery_id', '=', $delivery_id)
            ->select('dlv_backoth.id'
                , 'l_grade.code as grade_code'
                , 'l_grade.name as grade_name'
                , 'dlv_backoth.grade_id'
                , 'dlv_backoth.type'
                , 'dlv_backoth.title'
                , 'dlv_backoth.price'
                , 'dlv_backoth.qty'
                , 'dlv_backoth.memo'
            );
        return $result;
    }

}
