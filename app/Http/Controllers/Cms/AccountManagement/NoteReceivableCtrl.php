<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\GeneralLedger;
use App\Models\NoteReceivableOrder;
use App\Models\NoteReceivableLog;
use App\Models\User;
use App\Models\ReceivedOrder;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use App\Enums\Received\ChequeStatus;
use App\Enums\Area\Area;

class NoteReceivableCtrl extends Controller
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

        $cond['banks'] = Arr::get($query, 'banks', null);

        $cond['deposited_area_code'] = Arr::get($query, 'deposited_area_code', null);

        $cond['ticket_number'] = Arr::get($query, 'ticket_number', null);

        $cond['drawer'] = Arr::get($query, 'drawer', null);

        $cond['undertaker'] = Arr::get($query, 'undertaker', []);
        if (gettype($cond['undertaker']) == 'string') {
            $cond['undertaker'] = explode(',', $cond['undertaker']);
        } else {
            $cond['undertaker'] = [];
        }

        $cond['received_min_price'] = Arr::get($query, 'received_min_price', null);
        $cond['received_max_price'] = Arr::get($query, 'received_max_price', null);
        $received_price = [
            $cond['received_min_price'],
            $cond['received_max_price']
        ];

        $cond['ro_receipt_sdate'] = Arr::get($query, 'ro_receipt_sdate', null);
        $cond['ro_receipt_edate'] = Arr::get($query, 'ro_receipt_edate', null);
        $ro_receipt_date = [
            $cond['ro_receipt_sdate'],
            $cond['ro_receipt_edate']
        ];

        $cond['cheque_c_n_sdate'] = Arr::get($query, 'cheque_c_n_sdate', null);
        $cond['cheque_c_n_edate'] = Arr::get($query, 'cheque_c_n_edate', null);
        $cheque_c_n_date = [
            $cond['cheque_c_n_sdate'],
            $cond['cheque_c_n_edate']
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
            $data_list = NoteReceivableOrder::get_cheque_received_list(
                [],
                $cond['cheque_status_code'],
                $cond['banks'],
                $cond['deposited_area_code'],
                $cond['ticket_number'],
                $cond['drawer'],
                $cond['undertaker'],
                $received_price,
                $ro_receipt_date,
                $cheque_c_n_date,
                $cheque_due_date,
                $cheque_cashing_date
            )->paginate(99999)->appends($query);

            return view('cms.account_management.note_receivable.list_print',[
                'data_list' => $data_list,
            ]);
        }

        $data_list = NoteReceivableOrder::get_cheque_received_list(
                [],
                $cond['cheque_status_code'],
                $cond['banks'],
                $cond['deposited_area_code'],
                $cond['ticket_number'],
                $cond['drawer'],
                $cond['undertaker'],
                $received_price,
                $ro_receipt_date,
                $cheque_c_n_date,
                $cheque_due_date,
                $cheque_cashing_date
            )->paginate($page)->appends($query);


        $cheque_status_code = ChequeStatus::get_key_value();

        $banks = DB::table('acc_received_cheque')->whereNotNull('banks')->groupBy('banks')->orderBy('banks', 'asc')->distinct()->get()->pluck('banks')->toArray();

        $checkout_area = Area::get_key_value();

        $undertaker = User::get();

        return view('cms.account_management.note_receivable.list', [
            'data_per_page' => $page,
            'data_list' => $data_list,
            'cond' => $cond,
            'cheque_status_code' => $cheque_status_code,
            'banks' => $banks,
            'checkout_area' => $checkout_area,
            'undertaker' => $undertaker,
        ]);
    }


    public function record(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:acc_received_cheque,id',
        ]);

        $cheque = NoteReceivableOrder::get_cheque_received_list($id)->first();
        if(! $cheque){
            return abort(404);
        } else {
            $cheque->link = ReceivedOrder::received_order_link($cheque->ro_source_type, $cheque->ro_source_id);
        }

        return view('cms.account_management.note_receivable.record', [
            'cheque' => $cheque,
        ]);
    }


    public function ask(Request $request, $type)
    {
        $request->merge([
            'type'=>$type,
        ]);

        $request->validate([
            'type' => 'required|in:collection,nd,cashed',
        ]);

        $status_name = ChequeStatus::getDescription($type);

        if($request->isMethod('post')){
            $request->validate([
                'c_n_date' => 'date|date_format:Y-m-d|required_if:type,collection,nd',
                'cashing_date' => 'date|date_format:Y-m-d|required_if:type,cashed',
                'selected' => 'required|array',
                'selected.*' => 'exists:acc_received_cheque,id',
                'cheque_received_id' => 'required|array',
                'cheque_received_id.*' => 'exists:acc_received_cheque,id',
            ]);

            $compare = array_diff(request('selected'), request('cheque_received_id'));
            if(count($compare) == 0){
                DB::beginTransaction();

                try {
                    if($type == 'collection' || $type == 'nd'){
                        $qd = request('c_n_date');

                        $parm = [
                            'cheque_received_id'=>request('cheque_received_id'),
                            'status_code'=>$type,
                            'status'=>$status_name,
                            'c_n_date'=>$qd,
                        ];
                        NoteReceivableOrder::update_cheque_received_method($parm);

                    } else if($type == 'cashed'){
                        $qd = request('cashing_date');

                        $parm = [
                            'cheque_received_id'=>request('cheque_received_id'),
                            'amt_net'=>request('amt_net'),
                            'status_code'=>$type,
                            'status'=>$status_name,
                            'cashing_date'=>$qd,
                        ];
                        $re = NoteReceivableOrder::update_cheque_received_method($parm);
                    }

                    DB::commit();
                    wToast(__('整批' . $status_name . '儲存成功'));

                    return redirect()->route('cms.note_receivable.detail', ['type'=>$type, 'qd' => $qd]);

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
        if($type == 'collection'){
            $status = 'received';
            $data_list = NoteReceivableOrder::get_cheque_received_list(null, $status)->where('_cheque.c_n_date', null)->get();

        } else if($type == 'nd'){
            $status = 'nd';
            $data_list = NoteReceivableOrder::get_cheque_received_list(null, $status)->where('_cheque.c_n_date', null)->get();

        } else if($type == 'cashed'){
            $cheque_cashing_date = [request('cc_sdate'), request('cc_edate')];

            $status = ['received', 'collection', 'nd', 'demand', 'cashed'];
            $data_list = NoteReceivableOrder::get_cheque_received_list(null, $status, null, null, null, null, null, null, null, null, null, $cheque_cashing_date)->get();

        }

        return view('cms.account_management.note_receivable.ask', [
            'breadcrumb_data' => ['title'=>'整批' . $status_name],
            'previous_url' => route('cms.note_receivable.index'),
            'form_action' => route('cms.note_receivable.ask', ['type'=>$type]),
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
            'type' => 'required|in:collection,nd,cashed',
        ]);


        if($type == 'collection'){
            $title = '託收明細';
        } else if($type == 'nd'){
            $title = '次交票明細';
        } else if($type == 'cashed'){
            $title = '兌現明細';
        }

        if($type == 'collection' || $type == 'nd'){
            $data_list = NoteReceivableOrder::get_cheque_received_list(null, $type, null, null, null, null, null, null, null, [request('qd'), request('qd')])->get();

            return view('cms.account_management.note_receivable.detail', [
                'breadcrumb_data' => ['title'=>$title],
                'previous_url' => route('cms.note_receivable.ask', ['type'=>$type]),
                'type' => $type,
                'data_list' => $data_list,
            ]);

        } else if($type == 'cashed'){
            $data_list = NoteReceivableOrder::get_cheque_received_list(null, $type, null, null, null, null, null, null, null, null, null, [request('qd'), request('qd')])->whereNotNull('_cheque.sn')->get();

            $note_receivable_order = NoteReceivableOrder::leftJoinSub(GeneralLedger::getAllGrade(), 'grade', function($join) {
                $join->on('grade.primary_id', 'acc_note_receivable_orders.net_grade_id');
            })->whereDate('acc_note_receivable_orders.cashing_date', '=', request('qd'))->first();

            return view('cms.account_management.note_receivable.nro_detail', [
                'breadcrumb_data' => ['title'=>$title],
                'previous_url' => route('cms.note_receivable.ask', ['type'=>$type]),
                'type' => $type,
                'data_list' => $data_list,
                'note_receivable_order' => $note_receivable_order,
            ]);
        }
    }


    public function reverse($id)
    {
        $log = NoteReceivableLog::reverse_cheque_status($id);

        if($log){
            wToast('應收票據取消兌現成功');
            return redirect()->route('cms.note_receivable.record', ['id'=>$id]);

        } else {
            wToast('應收票據取消兌現失敗', ['type'=>'danger']);
            return redirect()->back();
        }
    }
}
