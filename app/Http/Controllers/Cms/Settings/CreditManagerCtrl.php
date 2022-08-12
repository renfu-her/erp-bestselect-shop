<?php

namespace App\Http\Controllers\Cms\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CrdBank;
use App\Models\CrdCreditCard;
use App\Models\GeneralLedger;
use App\Models\IncomeOrder;
use App\Models\ReceivedOrder;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreditManagerCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();
        $page = getPageCount(Arr::get($query, 'data_per_page', 100)) > 0 ? getPageCount(Arr::get($query, 'data_per_page', 100)) : 100;

        $cond = [];

        $cond['bank_id'] = Arr::get($query, 'bank_id', []);
        if (gettype($cond['bank_id']) == 'string') {
            $cond['bank_id'] = explode(',', $cond['bank_id']);
        } else {
            $cond['bank_id'] = [];
        }

        $cond['area_id'] = Arr::get($query, 'area_id', []);
        if (gettype($cond['area_id']) == 'string') {
            $cond['area_id'] = explode(',', $cond['area_id']);
        } else {
            $cond['area_id'] = [];
        }

        $cond['card_type_id'] = Arr::get($query, 'card_type_id', []);
        if (gettype($cond['card_type_id']) == 'string') {
            $cond['card_type_id'] = explode(',', $cond['card_type_id']);
        } else {
            $cond['card_type_id'] = [];
        }

        $cond['card_number'] = Arr::get($query, 'card_number', null);
        $cond['card_owner'] = Arr::get($query, 'card_owner', null);

        $cond['authamt_min_price'] = Arr::get($query, 'authamt_min_price', null);
        $cond['authamt_max_price'] = Arr::get($query, 'authamt_max_price', null);
        $authamt_price = [
            $cond['authamt_min_price'],
            $cond['authamt_max_price']
        ];

        $cond['mode'] = Arr::get($query, 'mode', null);

        $cond['checkout_sdate'] = Arr::get($query, 'checkout_sdate', null);
        $cond['checkout_edate'] = Arr::get($query, 'checkout_edate', null);
        $checkout_date = [
            $cond['checkout_sdate'],
            $cond['checkout_edate']
        ];

        $cond['posting_sdate'] = Arr::get($query, 'posting_sdate', null);
        $cond['posting_edate'] = Arr::get($query, 'posting_edate', null);
        $posting_date = [
            $cond['posting_sdate'],
            $cond['posting_edate']
        ];

        $cond['status_code'] = Arr::get($query, 'status_code', null);

        $data_list = IncomeOrder::get_credit_card_received_list(
                [],
                $cond['status_code'],
                null,
                $cond['bank_id'],
                $cond['area_id'],
                $cond['card_type_id'],
                $cond['card_number'],
                $cond['card_owner'],
                $authamt_price,
                $cond['mode'],
                $checkout_date,
                $posting_date
            )->paginate($page)->appends($query);

        $bank = CrdBank::orderBy('id', 'asc')->pluck('title', 'id')->toArray();
        $checkout_area = [
            'taipei'=>'台北',
            'hsinchu'=>'新竹',
        ];
        $card_type = CrdCreditCard::distinct('title')->groupBy('title')->orderBy('id', 'asc')->pluck('title', 'id')->toArray();

        return view('cms.settings.credit_manager.list', [
            'data_per_page' => $page,
            'data_list' => $data_list,
            'cond' => $cond,
            'bank' => $bank,
            'checkout_area' => $checkout_area,
            'card_type' => $card_type,
        ]);
    }


    public function record(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:acc_received_credit,id',
        ]);

        $record = IncomeOrder::get_credit_card_received_list([$id])->first();

        if($request->isMethod('post')){
            if($record->credit_card_status_code == 0){
                return abort(404);
            } else if($record->credit_card_status_code == 1){
                $parm = [
                    'credit_card_received_id'=>[$id],
                    'status_code'=>0,
                ];

                ReceivedOrder::update_credit_received_method($parm);

            } else if($record->credit_card_status_code == 2){
                $parm = [
                    'credit_card_received_id'=>[$id],
                    'status_code'=>1,
                    'transaction_date'=>$record->credit_card_transaction_date,
                ];
                ReceivedOrder::update_credit_received_method($parm);
                IncomeOrder::store_income_order($record->credit_card_posting_date);

            } else {
                return abort(404);
            }

            wToast(__('信用卡狀態取消成功'));

            return redirect()->route('cms.credit_manager.record', ['id' => $id]);
        }

        return view('cms.settings.credit_manager.record', [
            'form_action'=>route('cms.credit_manager.record', ['id' => $id]),
            'record'=>$record,
        ]);
    }


    public function record_edit(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:acc_received_credit,id',
        ]);

        $record = IncomeOrder::get_credit_card_received_list([$id])->first();
        if($record->credit_card_status_code != 0){
            return abort(404);
        }

        $card_type = CrdCreditCard::distinct('title')->groupBy('title')->orderBy('id', 'asc')->pluck('title', 'id')->toArray();
        $total_grades = GeneralLedger::total_grade_list();
        $checkout_area = [
            'taipei'=>'台北',
            'hsinchu'=>'新竹',
        ];

        if($request->isMethod('post')){
            $request->validate([
                'credit_card_number' => 'nullable|string',
                'credit_card_authcode' => 'nullable|string',
                'credit_card_type_code' => 'nullable|exists:crd_credit_cards,id',
                'credit_card_owner_name' => 'nullable|string',
                'credit_card_checkout_date' => 'nullable|date_format:"Y-m-d"',
                'received_grade_id' => 'required|exists:acc_all_grades,id',
                'credit_card_checkout_mode' => 'required|in:online,offline',
                // 'credit_card_area_code' => 'nullable|in:taipei',
                'note' => 'nullable|string',
            ]);
            $data = $request->except('_token');

            DB::table('acc_received_credit')->where('id', $id)->update([
                'cardnumber'=>$data['credit_card_number'],
                // 'authamt'=>$data['authamt'],
                'checkout_date'=>$data['credit_card_checkout_date'],
                'card_type_code'=>array_key_exists($data['credit_card_type_code'], $card_type) ? $data['credit_card_type_code'] : null,
                'card_type'=>array_key_exists($data['credit_card_type_code'], $card_type) ? $card_type[$data['credit_card_type_code']] : null,
                'card_owner_name'=>$data['credit_card_owner_name'],
                'authcode'=>$data['credit_card_authcode'],
                'all_grades_id'=>$data['received_grade_id'],
                'checkout_area_code'=>array_key_exists($data['credit_card_area_code'], $checkout_area) ? $data['credit_card_area_code'] : null,
                'checkout_area'=>array_key_exists($data['credit_card_area_code'], $checkout_area) ? $checkout_area[$data['credit_card_area_code']] : null,
                // 'installment'=>$data['installment'],
                // 'status_code'=>$data['status_code'],
                // 'card_nat'=>$data['card_nat'],
                'checkout_mode'=>$data['credit_card_checkout_mode'],
                'updated_at'=>date('Y-m-d H:i:s'),
            ]);

            DB::table('acc_received')->where('id', $record->received_id)->update([
                'note'=>$request['note'],
                'updated_at'=>date('Y-m-d H:i:s'),
            ]);

            wToast(__('信用卡刷卡記錄更新成功'));

            return redirect()->route('cms.credit_manager.record', ['id' => $id]);
        }

        return view('cms.settings.credit_manager.record_edit', [
            'form_action'=>route('cms.credit_manager.record-edit', ['id' => $id]),
            'record'=>$record,
            'card_type'=>$card_type,
            'total_grades'=>$total_grades,
            'checkout_area'=>$checkout_area,
        ]);
    }


    public function ask(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'transaction_date' => 'required|date_format:"Y-m-d"',
                'selected' => 'required|array',
                'selected.*' => 'exists:acc_received_credit,id',
                'credit_card_received_id' => 'required|array',
                'credit_card_received_id.*' => 'exists:acc_received_credit,id',
            ]);

            $compare = array_diff(request('selected'), request('credit_card_received_id'));
            if(count($compare) == 0){
                $parm = [
                    'credit_card_received_id'=>request('credit_card_received_id'),
                    'status_code'=>1,
                    'transaction_date'=>request('transaction_date'),
                ];
                ReceivedOrder::update_credit_received_method($parm);

                wToast(__('信用卡請款成功'));
                return redirect()->route('cms.credit_manager.index');
            }

            wToast(__('信用卡請款失敗', ['type'=>'danger']));
            return redirect()->back();
        }

        $data_list = IncomeOrder::get_credit_card_received_list([], 0)->get();

        return view('cms.settings.credit_manager.ask', [
            'form_action'=>route('cms.credit_manager.ask'),
            'data_list'=>$data_list,
        ]);
    }


    public function claim(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'posting_date' => 'required|date_format:"Y-m-d"',
                'selected' => 'required|array',
                'selected.*' => 'exists:acc_received_credit,id',
                'credit_card_received_id' => 'required|array',
                'credit_card_received_id.*' => 'exists:acc_received_credit,id',
                'amt_net' => 'required|array',
                'amt_net.*' => 'required|numeric|between:0,9999999999.99',
            ]);

            $compare = array_diff(request('selected'), request('credit_card_received_id'));
            if(count($compare) == 0){
                $parm = [
                    'credit_card_received_id'=>request('credit_card_received_id'),
                    'status_code'=>2,
                    'authamt'=>request('authamt'),
                    'amt_percent'=>request('amt_percent'),
                    'amt_net'=>request('amt_net'),
                    'posting_date'=>request('posting_date'),
                ];
                $re = ReceivedOrder::update_credit_received_method($parm);

                wToast(__('信用卡入款成功'));
                return redirect()->route('cms.credit_manager.income-detail', [
                    'id'=>$re->id,
                ]);
            }

            wToast(__('信用卡入款失敗', ['type'=>'danger']));
            return redirect()->back();
        }

        $data_list = IncomeOrder::get_credit_card_received_list([], 1)->get();

        return view('cms.settings.credit_manager.claim', [
            'form_action'=>route('cms.credit_manager.claim'),
            'data_list'=>$data_list,
        ]);
    }


    public function income_detail(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:acc_income_orders,id',
        ]);

        $income = IncomeOrder::leftJoinSub(GeneralLedger::getAllGrade(), 'fee_grade', function($join){
                $join->on('fee_grade.primary_id', '=', 'acc_income_orders.service_fee_grade_id');
            })
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'net_grade', function($join){
                $join->on('net_grade.primary_id', '=', 'acc_income_orders.net_grade_id');
            })
            ->leftJoin('usr_users AS creator', function($join){
                $join->on('creator.id', '=', 'acc_income_orders.creator_id');
            })
            ->leftJoin('usr_users AS affirmant', function($join){
                $join->on('affirmant.id', '=', 'acc_income_orders.affirmant_id');
            })
            ->where('acc_income_orders.id', $id)
            ->selectRaw('
                acc_income_orders.sn,
                acc_income_orders.amt_total_service_fee,
                acc_income_orders.amt_total_net,
                fee_grade.code AS fee_grade_code,
                fee_grade.name AS fee_grade_name,
                net_grade.code AS net_grade_code,
                net_grade.name AS net_grade_name,
                acc_income_orders.posting_date,
                creator.name AS creator_name,
                affirmant.name AS affirmant_name,
                acc_income_orders.created_at,
                acc_income_orders.updated_at
            ')
            ->first();

        $data_list = IncomeOrder::get_credit_card_received_list([], 2, $income->sn)->get();

        return view('cms.settings.credit_manager.income_detail', [
            'income'=>$income,
            'data_list'=>$data_list,
        ]);
    }
}
