<?php

namespace App\Models;

use App\Enums\Delivery\BackStatus;
use App\Enums\DlvBack\DlvBackPapaType;
use App\Helpers\IttmsDBB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

    public static function changeBackStatus($bac_papa_id, BackStatus $status)
    {
        if (false == BackStatus::hasKey($status->key)) {
            throw ValidationException::withMessages(['error_msg' => '無此退貨狀態']);
        }

        DlvBacPapa::where('id', '=', $bac_papa_id)->update([
            'status' => $status->value
            , 'status_date' => date('Y-m-d H:i:s'),
        ]);
    }

}
