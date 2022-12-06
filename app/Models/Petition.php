<?php

namespace App\Models;

use App\Models\Expenditure;
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

        $sub = Audit::auditList('petition');

        $canDelSub = DB::table('pet_audit')
            ->select('source_id', 'checked_at')
            ->where('source_type', 'petition')
            ->groupBy('source_id')->whereNotNull('checked_at');

        $re = DB::table('pet_petition as petition')
            ->leftJoinSub($sub, 'audit', 'audit.source_id', '=', 'petition.id')
            ->leftJoinSub($canDelSub, 'audit2', 'audit2.source_id', '=', 'petition.id')
            ->leftJoin('usr_users as user', 'petition.user_id', '=', 'user.id')
            ->select(['petition.*', 'audit.*', 'user.name as user_name', 'audit2.checked_at'])
            ->whereNull('petition.deleted_at')
            ->distinct();

        //  dd($re->get());
        if (isset($option['audit'])) {
            $re->joinSub(Audit::waitAuditlist($option['audit'], 'petition'), 'pet', 'pet.source_id', '=', 'petition.id');
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

        if (isset($option['sdate']) && $option['sdate']) {
            $sdate = date('Y-m-d 00:00:00', strtotime($option['sdate']));
            $re->where('petition.created_at', '>=', $sdate);
        }

        if (isset($option['edate']) && $option['edate']) {
            $edate = date('Y-m-d 23:59:59', strtotime($option['edate']));
            $re->where('petition.created_at', '<=', $edate);
        }

        return $re;

    }

    public static function getOrderSn($petition_id, $type)
    {
        return DB::table('pet_order_sn')->where('source_id', $petition_id)
            ->where('source_type', $type);
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

        Audit::addAudit($user_id, $id, 'petition');

        // 關聯訂單
        $re = self::updateOrderSn($orders, $id, 'petition');

        if ($re['success'] != '1') {
            DB::rollBack();
            return $re;
        }

        DB::commit();
        return ['success' => '1'];
    }
    // 新增關聯訂單
    public static function updateOrderSn($orders, $id, $type)
    {
        DB::beginTransaction();

        DB::table('pet_order_sn')->where('source_id', $id)
            ->where('source_type', $type)->delete();
        if ($orders) {
            $re = self::checkOrderSn($orders, $id, $type);
            if ($re['success'] != '1') {
                DB::rollBack();
                return $re;
            }

            DB::table('pet_order_sn')->insert($re['data']);
        }

        DB::commit();
        return ['success' => '1'];

    }
    //  trashed 尚未實裝
    public static function checkOrderSn($orders, $pid = null, $type = null, $trashed = false)
    {
        $err_order = []; //key
        $order_sn = [];

        foreach ($orders as $key => $order) {
            $order = str_replace(' ', '', strtoupper($order));

            // dd($order);
            $insert_data = null;
            preg_match('/^([A-Za-z])*/u', $order, $matches);

            if ($matches) {
                switch ($matches[0]) {
                    case "O":
                        $o = Order::where('sn', $order)->get()->first();
                        if ($o) {
                            $insert_data = ['order_id' => $o->id,
                                'order_sn' => $o->sn,
                                'order_type' => 'O'];
                        } else {
                            $err_order[] = $key;
                        }
                        break;
                    case "KSG":
                        $o = RequestOrder::where('sn', $order)->get()->first();
                        if ($o) {
                            $insert_data = ['order_id' => $o->id,
                                'order_sn' => $o->sn,
                                'order_type' => 'KSG'];
                        } else {
                            $err_order[] = $key;
                        }
                        break;
                    case "MSG":
                        $o = ReceivedOrder::where('sn', $order)->get()->first();
                        if ($o) {
                            $insert_data = ['order_id' => $o->id,
                                'order_sn' => $o->sn,
                                'order_type' => 'MSG'];
                        } else {
                            $err_order[] = $key;
                        }
                        break;
                    case "PSG":
                        $o = StituteOrder::where('sn', $order)->get()->first();
                        if ($o) {
                            $insert_data = ['order_id' => $o->id,
                                'order_sn' => $o->sn,
                                'order_type' => 'PSG'];
                        } else {
                            $err_order[] = $key;
                        }
                        break;
                    case "ISG":
                        $o = PayingOrder::where('sn', $order)->get()->first();
                        if ($o) {
                            $insert_data = ['order_id' => $o->id,
                                'order_sn' => $o->sn,
                                'order_type' => 'ISG'];
                        } else {
                            $err_order[] = $key;
                        }
                        break;
                    case "B":
                        $o = Purchase::where('sn', $order)->get()->first();
                        if ($o) {
                            $insert_data = ['order_id' => $o->id,
                                'order_sn' => $o->sn,
                                'order_type' => 'B'];
                        } else {
                            $err_order[] = $key;
                        }
                        break;

                    case "EXP":
                        $o = Expenditure::where('sn', $order)->get()->first();

                        if ($o) {
                            $insert_data = ['order_id' => $o->id,
                                'order_sn' => $o->sn,
                                'order_type' => 'EXP'];
                        } else {
                            $err_order[] = $key;
                        }
                        break;
                    case "PET":
                        $o = Petition::where('sn', $order)->get()->first();
                        if ($o) {
                            $insert_data = ['order_id' => $o->id,
                                'order_sn' => $o->sn,
                                'order_type' => 'PET'];
                        } else {
                            $err_order[] = $key;
                        }
                        break;
                }

                if ($insert_data) {
                    if ($pid) {
                        $insert_data['source_id'] = $pid;
                        $insert_data['source_type'] = $type;
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

    public static function getBindedOrder($order_id, $order_type)
    {

        $select = ['source.id', 'source.sn', 'order.order_id', 'order.order_sn', 'order.order_type'];

        $petition = DB::table('pet_order_sn as order')
            ->select($select)->leftJoin('pet_petition as source', 'source.id', '=', 'order.source_id')
            ->where('order.source_type', 'petition')
            ->whereNull('source.deleted_at');

        $expenditure = DB::table('pet_order_sn as order')
            ->select($select)->leftJoin('exp_expenditure as source', 'source.id', '=', 'order.source_id')
            ->where('order.source_type', 'expenditure')
            ->whereNull('source.deleted_at');

        $re = DB::table(DB::raw("({$petition->toSql()}) as sub"))->mergeBindings($petition)
            ->union(DB::table(DB::raw("({$expenditure->toSql()}) as sub2"))->mergeBindings($expenditure));

        $re2 = DB::table(DB::raw("({$re->toSql()}) as sub"))->mergeBindings($re)
            ->select(['id', 'sn'])
            ->where('order_id', $order_id)
            ->where('order_type', $order_type);

        $re2->bindings['union'][] = $order_id;
        $re2->bindings['union'][] = $order_type;

        unset($re2->bindings['where'][1]);
        unset($re2->bindings['where'][2]);

        $re2 = $re2->get();
        //    dd($re2);
        foreach ($re2 as $d) {
            $matches = null;
            preg_match('/^([A-Za-z])*/u', $d->sn, $matches);
            if ($matches) {
                $d->url = getErpOrderUrl((object) ['order_id' => $d->id, 'order_type' => $matches[0]])->url;
            }
        }

        return $re2;

    }
    // 反向綁定
    public static function reverseBind($current_sn, $target_sn)
    {
        $target = self::checkOrderSn([$target_sn]);
        $current = self::checkOrderSn([$current_sn]);

        if ($target['success'] != '1' || $current['success'] != '1') {
            return ['success' => '0', 'message' => '查無單號'];
        }

        $target = $target['data'][0];
        $current = $current['data'][0];

        switch ($target['order_type']) {
            case 'PET':
                $type = 'petition';
                break;
            case 'EXP':
                $type = 'expenditure';
                break;
            default:
                return ['success' => '0', 'message' => '綁定對象錯誤'];
        }

        if (DB::table('pet_order_sn')
            ->where('source_type', $type)
            ->where('source_id', $target['order_id'])
            ->where('order_sn', $current_sn)->get()->first()) {
            return ['success' => '1', 'message' => '已綁定'];
        }

        DB::table('pet_order_sn')->insert([
            'source_type' => $type,
            'source_id' => $target['order_id'],
            'order_id' => $current['order_id'],
            'order_sn' => $current_sn,
            'order_type' => $current['order_type'],
        ]);

        return ['success' => '1'];
    }

    //
    public static function reverseBindProcess($current_sn, array $target_sn = [], array $delete_sn = [])
    {
        DB::beginTransaction();
        $current = self::checkOrderSn([$current_sn]);

        if ($current['success'] != '1') {
            return ['success' => '0', 'message' => '查無單號'];
        }
        $current = $current['data'][0];

        foreach ($delete_sn as $value) {
            $t = self::checkOrderSn([$value]);
            if ($t['success'] == '1') {
                $t = $t['data'][0];
                $o_type = '';
                $o_type_list = ['expenditure', 'petition'];
                switch ($t['order_type']) {
                    case 'EXP':
                        $o_type = $o_type_list[0];
                        break;
                    case 'PET':
                        $o_type = $o_type_list[1];
                        break;

                }
                if ($o_type) {
                    DB::table('pet_order_sn')
                        ->where('source_type', $o_type)
                        ->where('source_id', $t['order_id'])
                        ->where('order_sn', $current_sn)->delete();
                }
            }
        }

        $errs = [];
        foreach ($target_sn as $key => $value) {
            $processRe = self::reverseBind($current_sn, $value);

            if ($processRe['success'] != '1') {
                $processRe['index'] = $key;
                $errs[] = $processRe;
            }
        }

        if (count($errs) > 0) {
            DB::rollBack();
            return ['success' => 0, 'message' => '錯誤', 'type' => 'multi', 'data' => $errs];
        }

        DB::commit();

        return ['success' => '1'];
    }

}
