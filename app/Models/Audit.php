<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Audit extends Model
{
    use HasFactory;
    protected $table = 'pet_audit';
    protected $guarded = [];
    public $timestamps = false;

    public static function waitAuditlist($user_id, $type)
    {

        $sub = DB::table('pet_audit as audit')
            ->whereNull('audit.checked_at')
            ->select('audit.source_id')
            ->selectRaw('max(audit.step) as step')
            ->groupBy('audit.source_id')
            ->where('audit.source_type', $type);

        $sub2 = DB::table('pet_audit as audit')
            ->select('audit.*')
            ->joinSub($sub, 'sub_audit', function ($join) {
                $join->on('sub_audit.source_id', '=', 'audit.source_id')
                    ->on('sub_audit.step', '=', 'audit.step');
            })
            ->where('audit.user_id', $user_id);

        return $sub2;
    }

    public static function auditList($type){
        $concatString = concatStr([
            'user_id' => 'audit.user_id',
            'user_name' => 'user.name',
            'user_title' => 'user.title',
            'checked_at' => 'IFNULL(audit.checked_at,"")',
        ]);

        $sub = DB::table('pet_audit as audit')
            ->leftJoin('usr_users as user', 'user.id', '=', 'audit.user_id')
            ->select('audit.source_id')
            ->selectRaw('(' . $concatString . ') as users')
            ->orderBy('audit.step')
            ->groupBy('audit.source_id')
            ->where('audit.source_type', $type);

        return $sub;
    }

    public static function addAudit($user_id, $source_id, $type)
    {
        $org = UserOrganize::auditList($user_id);

        //  dd($org);

        self::insert(array_map(function ($n, $idx) use ($source_id, $type) {
            return [
                'step' => $idx + 1,
                'user_id' => $n->user_id,
                'source_id' => $source_id,
                'source_type' => $type,
            ];
        }, $org, array_keys($org)));
    }

    public static function confirm($pid, $user_id, $type)
    {

        self::where('source_id', $pid)
            ->where('source_type', $type)
            ->where('user_id', $user_id)->update(['checked_at' => now()]);
    }
}
