<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\GeneralLedger;
use App\Models\NotePayableOrder;
use App\Models\NotePayableLog;
use App\Models\PayableDefault;
use App\Models\PayingOrder;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use App\Enums\Payable\ChequeStatus;

class NotePayableCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();
        $page = getPageCount(Arr::get($query, 'data_per_page', 100)) > 0 ? getPageCount(Arr::get($query, 'data_per_page', 100)) : 100;

        $cond = [];

        $cond['cheque_status_code'] = Arr::get($query, 'cheque_status_code', []);
        if (gettype($cond['cheque_status_code']) == 'string') {
            $cond['cheque_status_code'] = explode(',', $cond['cheque_status_code']);
        } else {
            $cond['cheque_status_code'] = [];
        }

        $cond['cheque_payable_grade_id'] = Arr::get($query, 'cheque_payable_grade_id', []);
        if (gettype($cond['cheque_payable_grade_id']) == 'string') {
            $cond['cheque_payable_grade_id'] = explode(',', $cond['cheque_payable_grade_id']);
        } else {
            $cond['cheque_payable_grade_id'] = [];
        }

        $cond['ticket_number'] = Arr::get($query, 'ticket_number', null);

        $cond['payable_min_price'] = Arr::get($query, 'payable_min_price', null);
        $cond['payable_max_price'] = Arr::get($query, 'payable_max_price', null);
        $payable_price = [
            $cond['payable_min_price'],
            $cond['payable_max_price']
        ];

        $cond['payment_sdate'] = Arr::get($query, 'payment_sdate', null);
        $cond['payment_edate'] = Arr::get($query, 'payment_edate', null);
        $payment_date = [
            $cond['payment_sdate'],
            $cond['payment_edate']
        ];

        $cond['cheque_due_sdate'] = Arr::get($query, 'cheque_due_sdate', null);
        $cond['cheque_due_edate'] = Arr::get($query, 'cheque_due_edate', null);
        $cheque_due_date = [
            $cond['cheque_due_sdate'],
            $cond['cheque_due_edate']
        ];

        $cond['cheque_cashing_sdate'] = Arr::get($query, 'cheque_cashing_sdate', null);
        $cond['cheque_cashing_edate'] = Arr::get($query, 'cheque_cashing_edate', null);
        $cheque_cashing_date = [
            $cond['cheque_cashing_sdate'],
            $cond['cheque_cashing_edate']
        ];

        if(request('action') == 'print'){
            $data_list = NotePayableOrder::get_cheque_payable_list(
                [],
                $cond['cheque_status_code'],
                $cond['cheque_payable_grade_id'],
                $cond['ticket_number'],
                $payable_price,
                $payment_date,
                $cheque_due_date,
                $cheque_cashing_date
            )->paginate(99999)->appends($query);

            return view('cms.account_management.note_payable.list_print',[
                'data_list' => $data_list,
            ]);
        }

        $data_list = NotePayableOrder::get_cheque_payable_list(
                [],
                $cond['cheque_status_code'],
                $cond['cheque_payable_grade_id'],
                $cond['ticket_number'],
                $payable_price,
                $payment_date,
                $cheque_due_date,
                $cheque_cashing_date
            )->paginate($page)->appends($query);

        $cheque_status_code = ChequeStatus::get_key_value();

        $cheque_payable_grade = PayableDefault::leftJoinSub(GeneralLedger::getAllGrade(), 'grade', function($join) {
                $join->on('grade.primary_id', 'acc_payable_default.default_grade_id');
            })
            ->select(
                'acc_payable_default.name',
                'grade.primary_id as grade_id',
                'grade.code as grade_code',
                'grade.name as grade_name'
            )
            ->where('acc_payable_default.name', 'cheque')
            ->get();

        return view('cms.account_management.note_payable.list', [
            'data_per_page' => $page,
            'data_list' => $data_list,
            'cond' => $cond,
            'cheque_status_code' => $cheque_status_code,
            'cheque_payable_grade' => $cheque_payable_grade,
        ]);
    }


    public function record(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:acc_payable_cheque,id',
        ]);

        $cheque = NotePayableOrder::get_cheque_payable_list($id)->first();
        if(! $cheque){
            return abort(404);
        } else {
            $cheque->link = PayingOrder::paying_order_link($cheque->po_source_type, $cheque->po_source_id, $cheque->po_source_sub_id, $cheque->po_type);
        }

        return view('cms.account_management.note_payable.record', [
            'cheque' => $cheque,
        ]);
    }


    public function ask(Request $request, $type)
    {
        $request->merge([
            'type'=>$type,
        ]);

        $request->validate([
            'type' => 'required|in:cashed',
        ]);

        $status_name = ChequeStatus::getDescription($type);

        if($request->isMethod('post')){
            $request->validate([
                'cashing_date' => 'date|date_format:Y-m-d|required_if:type,cashed',
                'selected' => 'required|array',
                'selected.*' => 'exists:acc_payable_cheque,id',
                'cheque_payable_id' => 'required|array',
                'cheque_payable_id.*' => 'exists:acc_payable_cheque,id',
            ]);

            $compare = array_diff(request('selected'), request('cheque_payable_id'));
            if(count($compare) == 0){
                DB::beginTransaction();

                try {
                    if($type == 'cashed'){
                        $qd = request('cashing_date');

                        $parm = [
                            'cheque_payable_id'=>request('cheque_payable_id'),
                            'amt_net'=>request('amt_net'),
                            'status_code'=>$type,
                            'status'=>$status_name,
                            'cashing_date'=>$qd,
                        ];
                        $re = NotePayableOrder::update_cheque_payable_method($parm);
                    }

                    DB::commit();
                    wToast(__('整批' . $status_name . '儲存成功'));

                    return redirect()->route('cms.note_payable.detail', ['type'=>$type, 'qd' => $qd]);

                } catch (\Exception $e) {
                    DB::rollback();
                    wToast(__('整批' . $status_name . '儲存失敗'), ['type'=>'danger']);
                    return redirect()->back();
                }
            }

            wToast(__('整批' . $status_name . '儲存失敗'), ['type'=>'danger']);
            return redirect()->back();
        }

        $status = null;
        if($type == 'cashed'){
            $cheque_cashing_date = [request('cc_sdate'), request('cc_edate')];

            $status = ['paid', 'cashed'];
            $data_list = NotePayableOrder::get_cheque_payable_list(null, $status, null, null, null, null, null, $cheque_cashing_date)->get();

        }

        return view('cms.account_management.note_payable.ask', [
            'breadcrumb_data' => ['title'=>'整批' . $status_name],
            'previous_url' => route('cms.note_payable.index'),
            'form_action' => route('cms.note_payable.ask', ['type'=>$type]),
            'type'=>$type,
            'data_list'=>$data_list,
        ]);
    }


    public function detail(Request $request, $type)
    {
        $request->merge([
            'qd'=>request('qd'),
            'type'=>$type,
        ]);

        $request->validate([
            'qd' => 'required|date|date_format:Y-m-d',
            'type' => 'required|in:cashed',
        ]);


        if($type == 'cashed'){
            $title = '兌現明細';

            $data_list = NotePayableOrder::get_cheque_payable_list(null, $type, null, null, null, null, null, [request('qd'), request('qd')])->whereNotNull('_cheque.sn')->get();

            $note_payable_order = NotePayableOrder::leftJoinSub(GeneralLedger::getAllGrade(), 'grade', function($join) {
                $join->on('grade.primary_id', 'acc_note_payable_orders.net_grade_id');
            })->whereDate('acc_note_payable_orders.cashing_date', '=', request('qd'))->first();

            return view('cms.account_management.note_payable.npo_detail', [
                'breadcrumb_data' => ['title'=>$title],
                'previous_url' => route('cms.note_payable.ask', ['type'=>$type]),
                'type' => $type,
                'data_list' => $data_list,
                'note_payable_order' => $note_payable_order,
            ]);
        }
    }


    public function reverse($id)
    {
        $log = NotePayableLog::reverse_cheque_status($id);

        if($log){
            wToast('應付票據取消兌現成功');
            return redirect()->route('cms.note_payable.record', ['id'=>$id]);

        } else {
            wToast('應付票據取消兌現失敗', ['type'=>'danger']);
            return redirect()->back();
        }
    }


    public function checkbook(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'alphabet_character' => 'required|between:2,2|regex:/^[A-Z]+$/',
                'min_number' => 'required|between:0,9999999',
                'max_number' => 'required|between:0,9999999',
            ]);

            $chr = request('alphabet_character');
            $max = request('max_number');
            $min = request('min_number');

            if($max < $min){
                wToast(__('票據起始號碼不可大於票據結束號碼'), ['type'=>'danger']);
                return redirect()->back();
            }

            ini_set('memory_limit', '-1');

            // for($i = $min; $i <= $max; $i++){
            //     $ticket_number[] = $chr . str_pad($i, 7, '0', STR_PAD_LEFT);
            // }

            // $data_list = NotePayableOrder::get_cheque_payable_list(null, null, null, $ticket_number, null, null, null, null);

            $data_list = NotePayableOrder::get_cheque_payable_list(null, null, null, null, null, null, null, null)->where(function ($q) use ($chr, $max, $min) {
                $_max = $chr . str_pad($max, 7, '0', STR_PAD_LEFT);
                $_min = $chr . str_pad($min, 7, '0', STR_PAD_LEFT);
                $q->whereBetween('_cheque.ticket_number', [$_min, $_max]);
            });

            return view('cms.account_management.note_payable.checkbook_print', [
                'data_list' => $data_list,
                'printer' => auth('user')->user() ? auth('user')->user()->name : null,
            ]);
        }

        return view('cms.account_management.note_payable.checkbook_set', [
            'previous_url' => route('cms.note_payable.index'),
            'form_action' => route('cms.note_payable.checkbook'),
        ]);
    }
}
