<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Http\Controllers\Controller;
use App\Models\AccountPayable;
use App\Models\GeneralLedger;
use App\Models\PayableRemit;
use App\Models\PayingOrder;
use App\Models\ReceivedOrder;
use Illuminate\Http\Request;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class RemittanceRecordCtrl extends Controller
{
    public function index(Request $request) {
        $query = $request->query();
        $cond = [];
        $cond['sn'] = Arr::get($query, 'sn', null);
        $cond['remit_type'] = Arr::get($query, 'remit_type', 'all');
        $cond['sdate'] = Arr::get($query, 'sdate', null);
        $cond['edate'] = Arr::get($query, 'edate', null);

        $cond['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page', 100)) > 0 ? getPageCount(Arr::get($query, 'data_per_page', 100)) : 100;;

        $data_list = self::getRemitRecord($cond);

        $data_list = $data_list->paginate($cond['data_per_page'])->appends($query);

        return view('cms.account_management.remittance_record.list', [
            'data_per_page' => $cond['data_per_page'],
            'data_list' => $data_list,
            'cond' => $cond,
        ]);
    }

    public static function getRemitRecord($cond) {
        $data_list_payable = DB::table(app(AccountPayable::class)->getTable(). ' as table')
            ->leftJoin(app(PayingOrder::class)->getTable(). ' as order', 'order.id', '=', 'table.pay_order_id')
            ->leftJoin(app(PayableRemit::class)->getTable(). ' as remit', 'remit.id', '=', 'table.payable_id')
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'grade', function($join) {
                $join->on('grade.primary_id', 'table.all_grades_id');
            })
            ->select(
                'table.id'
                , 'order.sn'
                , DB::raw('DATE_FORMAT(remit.remit_date,"%Y-%m-%d") as remit_date')
                , 'table.tw_price'
                , 'grade.code'
                , 'grade.name'
                , 'remit.created_at'
                , DB::raw('"" as memo')
                , DB::raw('"payable" as type_eng')
                , DB::raw('"匯出" as type')
            )
            ->where('table.acc_income_type_fk', '=', 3) // 3 是匯款
        ;
        $data_list_received = DB::table('acc_received'. ' as table')
            ->leftJoin(app(ReceivedOrder::class)->getTable(). ' as order', 'order.id', '=', 'table.received_order_id')
            ->leftJoin('acc_received_remit'. ' as remit', 'remit.id', '=', 'table.received_method_id')
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'grade', function($join) {
                $join->on('grade.primary_id', 'table.all_grades_id');
            })
            ->select(
                'table.id'
                , 'order.sn'
                , DB::raw('DATE_FORMAT(remit.remittance,"%Y-%m-%d") as remit_date')
                , 'table.tw_price'
                , 'grade.code'
                , 'grade.name'
                , 'remit.created_at'
                , 'remit.memo as memo'
                , DB::raw('"received" as type_eng')
                , DB::raw('"匯入" as type')
            )
            ->where('table.received_method', '=', 'remit') // remit 是匯款
        ;
        $union = $data_list_payable->union($data_list_received);
        $data_list = DB::query()->fromSub($union, 'select_list')
            ->orderByDesc('created_at');

        if (isset($cond['sn'])) {
            $data_list->where('sn', '=', $cond['sn']);
        }
        if (isset($cond['remit_type']) && 'all' != $cond['remit_type']) {
            if ('payable' == $cond['remit_type']) {
                $data_list->where('type_eng', '=', 'payable');
            } else if ('received' == $cond['remit_type']) {
                $data_list->where('type_eng', '=', 'received');
            }
        }
        if (isset($cond['sdate']) && isset($cond['edate'])) {
            $s_date = date('Y-m-d', strtotime($cond['sdate']));
            $e_date = date('Y-m-d', strtotime($cond['edate'] . ' +1 day'));
            $data_list->whereBetween('created_at', [$s_date, $e_date]);
        }
        return $data_list;
    }

    public function detail(Request $request, $sn) {
        $query = $request->query();
        $cond = [];
        $cond['sn'] = $sn ?? null;
        $data = self::getRemitRecord($cond)->first();
        if (false == isset($data)) {
            return abort(404);
        }
        return view('cms.account_management.remittance_record.edit', [
            'data' => $data,
            'breadcrumb_data' => ['sn'=>$sn],
            ])
            ->with('backUrl', old('backUrl', Session::get('backUrl', URL::previous())));
    }
}
