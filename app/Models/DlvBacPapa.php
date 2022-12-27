<?php

namespace App\Models;

use App\Enums\DlvBack\DlvBackPapaType;
use App\Helpers\IttmsDBB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DlvBacPapa extends Model
{
    use HasFactory;
    protected $table = 'dlv_bac_papa';
    protected $guarded = [];
    public $timestamps = true;

    public static function createData($type, $delivery_id, $memo = null) {
        return IttmsDBB::transaction(function () use ($type, $delivery_id, $memo) {
            if (!DlvBackPapaType::hasKey($type)) {
                return ['success' => 0, 'error_msg' => 'type error'];
            }
            $sn = Sn::createSn('dlv_bac_papa', 'BK', 'ymd', 4);
            $id = self::create([
                "type" => $type,
                "sn" => $sn,
                'delivery_id' => $delivery_id,
                'user_id' => Auth::user()->id,
                'user_name' => Auth::user()->name,
                'memo' => $memo,
            ])->id;
            return ['success' => 1, 'error_msg' => "", 'id' => $id];
        });
    }

    public static function getData($delivery_id)
    {
        $data = null;
        if (null != $delivery_id) {
            $data = DlvBacPapa::where('delivery_id', $delivery_id)->get();
        } else {
            $data = DlvBacPapa::all();
        }
        return $data;
    }

    public static function getDataWithDelivery($delivery_id)
    {
        $re = DB::table(app(DlvBacPapa::class)->getTable(). ' as backpa')
            ->leftJoin(app(Delivery::class)->getTable(). ' as dlv', 'dlv.id', '=', 'backpa.delivery_id')
            ->whereNull('dlv.deleted_at')
            ->select('backpa.id'
                , 'backpa.type'
                , 'backpa.sn'
                , 'backpa.delivery_id'
                , 'backpa.user_id'
                , 'backpa.user_name'
                , 'backpa.inbound_date'
                , 'backpa.inbound_user_id'
                , 'backpa.inbound_user_name'
                , 'backpa.memo'
                , 'backpa.created_at'
                , 'backpa.updated_at'
                , 'dlv.event'
                , 'dlv.event_id'
                , 'dlv.event_sn'
            );

        if (isset($delivery_id)) {
            $re->where('dlv.id', '=', $delivery_id);
        }
        return $re;
    }
}
