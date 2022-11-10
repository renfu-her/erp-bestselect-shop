<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Expenditure extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'exp_expenditure';
    protected $guarded = [];

    public static function dataList($option = [])
    {
        $sub = Audit::auditList('expenditure');

        $canDelSub = DB::table('pet_audit')
            ->select('source_id', 'checked_at')
            ->groupBy('source_id')->whereNotNull('checked_at');

        $re = DB::table('exp_expenditure as expenditure')
            ->leftJoinSub($sub, 'audit', 'audit.source_id', '=', 'expenditure.id')
            ->leftJoinSub($canDelSub, 'audit2', 'audit2.source_id', '=', 'expenditure.id')
            ->leftJoin('usr_users as user', 'expenditure.user_id', '=', 'user.id')
            ->leftJoin('exp_items as item', 'expenditure.item_id', '=', 'item.id')
            ->leftJoin('exp_payment as payment', 'expenditure.payment_id', '=', 'payment.id')
            ->leftJoin('usr_user_organize as org', 'expenditure.department_id', '=', 'org.id')
            ->select(['expenditure.*', 'audit.*',
                'user.name as user_name',
                'audit2.checked_at', 'item.title as item_title',
                'payment.title as payment_title',
                'org.title as department_title'])
            ->whereNull('expenditure.deleted_at');

        //  dd($re->get());
        if (isset($option['audit'])) {
            $re->joinSub(Audit::waitAuditlist($option['audit'], 'expenditure'), 'pet', 'pet.source_id', '=', 'expenditure.id');
        }

        if (isset($option['user_id']) && $option['user_id']) {
            $re->where('expenditure.user_id', $option['user_id']);
        }

        if (isset($option['sn']) && $option['sn']) {
            $re->where('expenditure.sn', 'like', "%" . $option['sn'] . "%");
        }

        if (isset($option['title']) && $option['title']) {
            $re->where('expenditure.title', 'like', "%" . $option['title'] . "%");
        }

        if (isset($option['sdate']) && $option['sdate']) {
            $sdate = date('Y-m-d 00:00:00', strtotime($option['sdate']));
            $re->where('expenditure.created_at', '>=', $sdate);
        }

        if (isset($option['edate']) && $option['edate']) {
            $edate = date('Y-m-d 23:59:59', strtotime($option['edate']));
            $re->where('expenditure.created_at', '<=', $edate);
        }

        return $re;

    }

    public static function getItems()
    {
        return DB::table('exp_items')->get();
    }

    public static function getPayment()
    {
        return DB::table('exp_payment')->get();
    }

    public static function createExpenditure($user_id, $title, $content, $department_id, $item_id, $payment_id, $amount, $orders = [])
    {
        DB::beginTransaction();

        $sn = 'EXP' . str_pad((self::withTrashed()->lockForUpdate()->get()
                ->count()) + 1, 9, '0', STR_PAD_LEFT);

        $id = self::create([
            'user_id' => $user_id,
            'title' => $title,
            'content' => $content,
            'department_id' => $department_id,
            'item_id' => $item_id,
            'payment_id' => $payment_id,
            'sn' => $sn,
            'amount' => $amount,
        ])->id;

        Audit::addAudit($user_id, $id, 'expenditure');

        // 關聯訂單
        $re = Petition::updateOrderSn($orders, $id, 'expenditure');

        if ($re['success'] != '1') {
            DB::rollBack();
            return $re;
        }

        DB::commit();
        return ['success' => '1'];
    }
}
