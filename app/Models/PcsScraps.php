<?php

namespace App\Models;

use App\Enums\Consignment\AuditStatus;
use App\Enums\PcsScrap\PcsScrapType;
use App\Helpers\IttmsDBB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class PcsScraps extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'pcs_scraps';
    protected $guarded = [];

    public static function createData($type, $memo = null) {
        return IttmsDBB::transaction(function () use ($type, $memo) {
            if (!PcsScrapType::hasKey($type)) {
                return ['success' => 0, 'error_msg' => 'type error'];
            }
            $sn = Sn::createSn(app(PcsScraps::class)->getTable(), 'SCP', 'ymd', 4);
            $id = self::create([
                "type" => $type,
                "sn" => $sn,
                'memo' => $memo,
                'user_id' => Auth::user()->id,
                'user_name' => Auth::user()->name,
                'audit_status' => AuditStatus::unreviewed()->value,
            ])->id;
            return ['success' => 1, 'error_msg' => "", 'id' => $id];
        });
    }
}
