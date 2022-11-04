<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Petition extends Model
{
    use HasFactory;
    protected $table = 'pet_petition';
    protected $guarded = [];

    public static function dataList()
    {

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
            ->where('audit.source_type', 'petition');

        $re = DB::table('pet_petition as petition')
            ->leftJoinSub($sub, 'audit', 'audit.source_id', '=', 'petition.id')
            ->leftJoin('usr_users as user', 'petition.user_id', '=', 'user.id')
            ->select(['petition.*', 'audit.*', 'user.name as user_name']);

        return $re;

    }

    public static function waitAuditlist($user_id)
    {

        $sub = DB::table('pet_audit as audit')
            ->whereNull('audit.checked_at')
            ->select('audit.source_id')
            ->selectRaw('max(audit.step) as step')
            ->groupBy('audit.source_id')
            ->where('audit.source_type', 'petition');

        $sub2 = DB::table('pet_audit as audit')
            ->select('audit.*')
            ->joinSub($sub, 'sub_audit', function ($join) {
                $join->on('sub_audit.source_id', '=', 'audit.source_id')
                    ->on('sub_audit.step', '=', 'audit.step');
            })
            ->where('audit.user_id', $user_id);

        return $sub2;
        /*
        dd($sub2->get());
      
        $re = DB::table('pet_petition as pet')
            ->select('pet.*')
            ->joinSub($sub2, 'audit2', 'audit2.source_id', '=', 'pet.id');

        dd($re->get());
        */
    }

    public static function createPetition($user_id, $title, $content, $orders = [])
    {
        DB::beginTransaction();

        $sn = 'PET' . str_pad((self::lockForUpdate()->get()
                ->count()) + 1, 9, '0', STR_PAD_LEFT);

        $id = self::create([
            'user_id' => $user_id,
            'title' => $title,
            'content' => $content,
            'sn' => $sn,
        ])->id;

        $org = UserOrganize::auditList($user_id);

        //  dd($org);

        DB::table('pet_audit')->insert(array_map(function ($n, $idx) use ($id) {
            return [
                'step' => $idx + 1,
                'user_id' => $n->user_id,
                'source_id' => $id,
                'source_type' => 'petition',
            ];
        }, $org, array_keys($org)));

        DB::commit();

    }
}
