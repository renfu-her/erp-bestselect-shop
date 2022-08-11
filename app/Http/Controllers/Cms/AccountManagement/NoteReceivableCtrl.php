<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\GeneralLedger;
use App\Models\NoteReceivableOrder;
use App\Models\TransferVoucher;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use App\Enums\Received\ChequeStatus;

class NoteReceivableCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();
        $page = getPageCount(Arr::get($query, 'data_per_page', 100)) > 0 ? getPageCount(Arr::get($query, 'data_per_page', 100)) : 100;

        $cond = [];

        $cond['company_id'] = Arr::get($query, 'company_id', []);
        if (gettype($cond['company_id']) == 'string') {
            $cond['company_id'] = explode(',', $cond['company_id']);
        } else {
            $cond['company_id'] = [];
        }

        $cond['tv_sn'] = Arr::get($query, 'tv_sn', null);

        $cond['tv_min_price'] = Arr::get($query, 'tv_min_price', null);
        $cond['tv_max_price'] = Arr::get($query, 'tv_max_price', null);
        $tv_price = [
            $cond['tv_min_price'],
            $cond['tv_max_price']
        ];

        $cond['voucher_sdate'] = Arr::get($query, 'voucher_sdate', null);
        $cond['voucher_edate'] = Arr::get($query, 'voucher_edate', null);
        $voucher_date = [
            $cond['voucher_sdate'],
            $cond['voucher_edate']
        ];

        $cond['audit_status'] = Arr::get($query, 'audit_status', 'all');

        $dataList = NoteReceivableOrder::get_cheque_received_list([], null)->paginate($page)->appends($query);

        $company = DB::table('acc_company')->get();

        $audit_status = [
            'all'=>'不限',
            '0'=>'未審核',
            '1'=>'已審核',
        ];

        return view('cms.account_management.note_receivable.list', [
            'data_per_page' => $page,
            'dataList' => $dataList,
            'cond' => $cond,
            'company' => $company,
            'audit_status' => $audit_status,
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
                        $parm = [
                            'cheque_received_id'=>request('cheque_received_id'),
                            'status_code'=>$type,
                            'status'=>$status_name,
                            'c_n_date'=>request('c_n_date'),
                        ];
                        NoteReceivableOrder::update_cheque_received_method($parm);

                    } else if($type == 'cashed'){
                        $parm = [
                            'cheque_received_id'=>request('cheque_received_id'),
                            'amt_net'=>request('amt_net'),
                            'status_code'=>$type,
                            'status'=>$status_name,
                            'cashing_date'=>request('cashing_date'),
                        ];
                        $re = NoteReceivableOrder::update_cheque_received_method($parm);
                    }

                    DB::commit();
                    wToast(__('整批' . $status_name . '儲存成功'));

                    return redirect()->route('cms.note_receivable.detail', ['type'=>$type, 'qd' => request('c_n_date')]);

                } catch (\Exception $e) {
                    DB::rollback();
                    wToast(__('整批' . $status_name . '儲存失敗', ['type'=>'danger']));
                    return redirect()->back();
                }
            }

            wToast(__('整批' . $status_name . '儲存失敗', ['type'=>'danger']));
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
            $status = ['received', 'collection', 'nd', 'demand', 'cashed'];
            $data_list = NoteReceivableOrder::get_cheque_received_list(null, $status)->get();

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
                'type'=>$type,
                'data_list' => $data_list,
            ]);

        } else if($type == 'cashed'){
            $data_list = NoteReceivableOrder::get_cheque_received_list(null, $type, null, null, null, null, null, null, null, null, null, [request('qd'), request('qd')])->get();

            $note_receivable_order = NoteReceivableOrder::leftJoinSub(GeneralLedger::getAllGrade(), 'grade', function($join) {
                $join->on('grade.primary_id', 'acc_note_receivable_orders.net_grade_id');
            })->whereDate('acc_note_receivable_orders.cashing_date', request('qd'))->first();

            return view('cms.account_management.note_receivable.nro_detail', [
                'breadcrumb_data' => ['title'=>$title],
                'previous_url' => route('cms.note_receivable.ask', ['type'=>$type]),
                'type'=>$type,
                'data_list' => $data_list,
                'note_receivable_order' => $note_receivable_order,
            ]);
        }
    }


    public function destroy($id)
    {
        $target = TransferVoucher::delete_voucher($id);

        if($target){
            wToast('刪除完成');
        } else {
            wToast('刪除失敗', ['type'=>'danger']);
        }

        return redirect()->route('cms.note_receivable.index');
    }
}
