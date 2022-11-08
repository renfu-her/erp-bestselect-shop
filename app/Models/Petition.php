<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Petition extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'pet_petition';
    protected $guarded = [];

    public static function dataList($option = [])
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

        $canDelSub = DB::table('pet_audit')
            ->select('source_id', 'checked_at')
            ->groupBy('source_id')->whereNotNull('checked_at');

        $re = DB::table('pet_petition as petition')
            ->leftJoinSub($sub, 'audit', 'audit.source_id', '=', 'petition.id')
            ->leftJoinSub($canDelSub, 'audit2', 'audit2.source_id', '=', 'petition.id')
            ->leftJoin('usr_users as user', 'petition.user_id', '=', 'user.id')
            ->select(['petition.*', 'audit.*', 'user.name as user_name', 'audit2.checked_at'])
            ->whereNull('petition.deleted_at');

        //  dd($re->get());
        if (isset($option['audit'])) {
            $re->joinSub(self::waitAuditlist($option['audit']), 'pet', 'pet.source_id', '=', 'petition.id');
        }

        if (isset($option['user_id']) && $option['user_id']) {
            $re->where('petition.user_id', $option['user_id']);
        }

        if (isset($option['sn']) && $option['sn']) {
            $re->where('petition.sn', 'like', "%" . $option['sn'] . "%");
        }

        if (isset($option['title']) && $option['title']) {
            $re->where('petition.title', 'like', "%" . $option['title'] . "%");
        }

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

    public static function getPetitionOrders($petition_id)
    {
        return DB::table('pet_petition_order')->where('petition_id', $petition_id);
    }

    public static function createPetition($user_id, $title, $content, $orders = [])
    {
        DB::beginTransaction();

        $sn = 'PET' . str_pad((self::withTrashed()->lockForUpdate()->get()
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

        // 關聯訂單
        $re = self::updatePetitionOrder($orders, $id);

        if ($re['success'] != '1') {
            DB::rollBack();
            return $re;
        }

        DB::commit();
        return ['success' => '1'];
    }

    public static function updatePetitionOrder($orders, $id)
    {
        DB::beginTransaction();

        DB::table('pet_petition_order')->where('petition_id', $id)->delete();
        if ($orders) {
            $re = self::checkOrderSn($orders, $id);
            if ($re['success'] != '1') {
                DB::rollBack();
                return $re;
            }

            DB::table('pet_petition_order')->insert($re['data']);
        }

        DB::commit();
        return ['success' => '1'];

    }

    public static function checkOrderSn($orders, $pid = null)
    {
        $err_order = []; //key
        $order_sn = [];

        foreach ($orders as $key => $order) {
            $order = strtoupper($order);
            $insert_data = null;
            preg_match('/^([A-Za-z])*/u', $order, $matches);

            if ($matches) {
                switch ($matches[0]) {
                    case "O":
                        $o = Order::where('sn', $order)->get()->first();
                        if ($o) {
                            $insert_data = ['source_id' => $o->id,
                                'source_sn' => $o->sn,
                                'source_type' => 'O'];
                        } else {
                            $err_order[] = $key;
                        }
                        break;
                    case "PSG":
                        $o = StituteOrder::where('sn', $order)->get()->first();
                        if ($o) {
                            $insert_data = ['source_id' => $o->id,
                                'source_sn' => $o->sn,
                                'source_type' => 'PSG'];
                        } else {
                            $err_order[] = $key;
                        }
                        break;
                    case "ISG":
                        $o = PayingOrder::where('sn', $order)->get()->first();
                        if ($o) {
                            $insert_data = ['source_id' => $o->id,
                                'source_sn' => $o->sn,
                                'source_type' => 'ISG'];
                        } else {
                            $err_order[] = $key;
                        }
                        break;
                    case "B":
                        $o = Purchase::where('sn', $order)->get()->first();
                        if ($o) {
                            $insert_data = ['source_id' => $o->id,
                                'source_sn' => $o->sn,
                                'source_type' => 'B'];
                        } else {
                            $err_order[] = $key;
                        }
                        break;
                }

                if ($insert_data) {
                    if ($pid) {
                        $insert_data['petition_id'] = $pid;
                    }
                    $order_sn[] = $insert_data;
                } else {
                    $err_order[] = $key;
                }
            } else {
                $err_order[] = $key;
            }
        }

        if ($err_order) {
            return ['success' => '0', 'type' => 'order_sn', 'data' => $err_order];
        }

        return ['success' => '1', 'data' => $order_sn];

    }
    public static function confirm($pid, $user_id)
    {

        DB::table('pet_audit')->where('source_id', $pid)
            ->where('user_id', $user_id)->update(['checked_at' => now()]);
    }
}
