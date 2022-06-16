<?php

namespace App\Models;

use App\Enums\ProjLogisticLog\Feature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LogisticProjLogisticLog extends Model
{
    use HasFactory;
    protected $table = 'dlv_logistic_proj_logistic_log';
    protected $guarded = [];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    public function setUpdatedAt($value)
    {
        // do nothing
    }

    public static function createData($feature, $logistic_id, $order_sn = null, $status, $text_request, $item_request, $text_response, $user) {
        if (null != $item_request && true == is_array($item_request)) {
            $text_request['items'] = ($item_request);
        }
        self::create([
            'logistic_fk' => $logistic_id
            , 'feature' => $feature
            , 'order_sn' => $order_sn
            , 'status' => $status
            , 'text_request' => json_encode($text_request, JSON_UNESCAPED_UNICODE)
            , 'text_response' => json_encode($text_response)
            , 'create_user_fk' => $user->id
            , 'create_user_name' => $user->name
        ]);
    }

    public static function getDataWithLogisticId($logistic_id) {
        $raw_feature = '';
        foreach (Feature::asArray() as $key => $val) {
            $raw_feature = $raw_feature. ' when feature = '. $val. ' then "'. Feature::getDescription($val). '"';
        }

        $query = DB::table('dlv_logistic_proj_logistic_log as lgt_log')
            ->where('lgt_log.logistic_fk', '=', $logistic_id)
            ->select('id as id'
                , 'logistic_fk as logistic_fk'
                , DB::raw('(case '. $raw_feature. ' else feature end) as feature')
                , 'order_sn as order_sn'
                , 'status as status'
                , 'text_request as text_request'
                , 'text_response as text_response'
                , 'create_user_fk as create_user_fk'
                , 'create_user_name as create_user_name'
                , 'created_at as created_at')
            ->get()->toArray();
        return $query;
    }
}
