<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\DayEnd;
use App\Models\GeneralLedger;
use App\Models\User;
use App\Models\TransferVoucher;
use App\Models\TransferVoucherItem;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TransferVoucherCtrl extends Controller
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

        $dataList = TransferVoucher::voucher_list(
            null,
            $cond['company_id'],
            $cond['tv_sn'],
            $tv_price,
            $voucher_date,
            $cond['audit_status'],
            true
        )->paginate($page)->appends($query);

        $company = DB::table('acc_company')->get();

        $audit_status = [
            'all'=>'不限',
            '0'=>'未審核',
            '1'=>'已審核',
        ];

        return view('cms.account_management.transfer_voucher.list', [
            'data_per_page' => $page,
            'dataList' => $dataList,
            'cond' => $cond,
            'company' => $company,
            'audit_status' => $audit_status,
        ]);
    }


    public function create(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'voucher_date' => 'required|date|date_format:Y-m-d',
                'grade_id' => 'required|array',
                'grade_id.*' => 'required|exists:acc_all_grades,id',
                'summary' => 'nullable|array',
                'memo' => 'nullable|array',
                'debit_credit_code' => 'required|array',
                'debit_credit_code.*' => 'in:debit,credit',
                'currency_id' => 'nullable|array',
                'currency_id.*' => 'exists:acc_currency,id',
                'rate' => 'nullable|array',
                'rate.*' => 'numeric|between:0,9999999.99',
                'currency_price' => 'nullable|array',
                'currency_price.*' => 'numeric|between:0,9999999.99',
                'department' => 'nullable|array',
                'department.*' => 'exists:usr_users,department',
            ]);

            DB::beginTransaction();

            try {
                $debit_price = 0;
                $credit_price = 0;

                $parm = [
                    'voucher_date' => request('voucher_date'),
                    'debit_price' => $debit_price,
                    'credit_price' => $credit_price,
                ];

                $voucher = TransferVoucher::create_transfer_voucher($parm);

                foreach(request('grade_id') as $key => $value){
                    $items[] = [
                        'voucher_id' => $voucher->id,
                        'grade_id' => request('grade_id')[$key],
                        'summary' => request('summary')[$key],
                        'memo' => request('memo')[$key],
                        'debit_credit_code' => request('debit_credit_code')[$key],
                        'currency_id' => request('currency_id') && isset(request('currency_id')[$key]) ? request('currency_id')[$key] : null,
                        'rate' => request('rate')[$key],
                        'currency_price' => request('currency_price')[$key],
                        'final_price' => request('rate')[$key] * request('currency_price')[$key],
                        'department' => request('department') && isset(request('department')[$key]) ? request('department')[$key] : null,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    if(request('debit_credit_code')[$key] == 'debit'){
                        $debit_price += request('rate')[$key] * request('currency_price')[$key];
                    } else if(request('debit_credit_code')[$key] == 'credit'){
                        $credit_price += request('rate')[$key] * request('currency_price')[$key];
                    }
                }

                TransferVoucherItem::insert($items);

                $voucher->update([
                    'debit_price' => $debit_price,
                    'credit_price' => $credit_price,
                ]);


                DayEnd::match_day_end_status($voucher->created_at, $voucher->sn);

                DB::commit();
                wToast(__('轉帳傳票儲存成功'));

                return redirect()->route('cms.transfer_voucher.show', [
                    'id' => $voucher->id,
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                wToast(__('轉帳傳票儲存失敗', ['type'=>'danger']));
                return redirect()->back();
            }
        }

        $total_grades = GeneralLedger::total_grade_list();
        $currency = DB::table('acc_currency')->get();
        $department = User::whereNotNull('department')->groupBy('department')->orderBy('department', 'asc')->distinct()->get()->pluck('department')->toArray();

        return view('cms.account_management.transfer_voucher.edit', [
            'method' => 'create',
            'previous_url' => route('cms.transfer_voucher.index'),
            'form_action' => route('cms.transfer_voucher.create'),
            'total_grades' => $total_grades,
            'currency' => $currency,
            'department'=>$department,
        ]);
    }


    public function show(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:acc_transfer_voucher,id',
        ]);

        $voucher = TransferVoucher::voucher_list($id)->first();
        if(! $voucher){
            return abort(404);
        }

        return view('cms.account_management.transfer_voucher.show', [
            'voucher' => $voucher,
        ]);
    }


    public function edit(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:acc_transfer_voucher,id',
        ]);

        if($request->isMethod('post')){
            $request->validate([
                'tv_item_id' => 'required|array',
                'tv_item_id.*' => 'nullable|exists:acc_transfer_voucher_items,id',
                'voucher_date' => 'required|date|date_format:Y-m-d',
                'grade_id' => 'required|array',
                'grade_id.*' => 'required|exists:acc_all_grades,id',
                'summary' => 'nullable|array',
                'memo' => 'nullable|array',
                'debit_credit_code' => 'required|array',
                'debit_credit_code.*' => 'in:debit,credit',
                'currency_id' => 'nullable|array',
                'currency_id.*' => 'exists:acc_currency,id',
                'rate' => 'nullable|array',
                'rate.*' => 'numeric|between:0,9999999.99',
                'currency_price' => 'nullable|array',
                'currency_price.*' => 'numeric|between:0,9999999.99',
                'department' => 'nullable|array',
                'department.*' => 'exists:usr_users,department',
            ]);

            DB::beginTransaction();

            try {
                foreach(request('grade_id') as $key => $value){
                    if(request('tv_item_id')[$key]){
                        TransferVoucherItem::find(request('tv_item_id')[$key])->update([
                            'grade_id' => request('grade_id')[$key],
                            'summary' => request('summary')[$key],
                            'memo' => request('memo')[$key],
                            'debit_credit_code' => request('debit_credit_code')[$key],
                            'currency_id' => request('currency_id') && isset(request('currency_id')[$key]) ? request('currency_id')[$key] : null,
                            'rate' => request('rate')[$key],
                            'currency_price' => request('currency_price')[$key],
                            'final_price' => request('rate')[$key] * request('currency_price')[$key],
                            'department' => request('department') && isset(request('department')[$key]) ? request('department')[$key] : null,
                        ]);

                    } else {
                        TransferVoucherItem::create([
                            'voucher_id' => $id,
                            'grade_id' => request('grade_id')[$key],
                            'summary' => request('summary')[$key],
                            'memo' => request('memo')[$key],
                            'debit_credit_code' => request('debit_credit_code')[$key],
                            'currency_id' => request('currency_id') && isset(request('currency_id')[$key]) ? request('currency_id')[$key] : null,
                            'rate' => request('rate')[$key],
                            'currency_price' => request('currency_price')[$key],
                            'final_price' => request('rate')[$key] * request('currency_price')[$key],
                            'department' => request('department') && isset(request('department')[$key]) ? request('department')[$key] : null,
                        ]);
                    }
                }

                $debit_price = TransferVoucherItem::where([
                    'voucher_id' => $id,
                    'debit_credit_code' => 'debit',
                ])->sum('final_price');

                $credit_price = TransferVoucherItem::where([
                    'voucher_id' => $id,
                    'debit_credit_code' => 'credit',
                ])->sum('final_price');

                TransferVoucher::find($id)->update([
                    'voucher_date' => request('voucher_date'),
                    'debit_price' => $debit_price,
                    'credit_price' => $credit_price,
                ]);

                DB::commit();
                wToast(__('轉帳傳票更新成功'));

                return redirect()->route('cms.transfer_voucher.show', [
                    'id' => $id,
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                wToast(__('轉帳傳票更新失敗', ['type'=>'danger']));
                return redirect()->back();
            }
        }

        $voucher = TransferVoucher::voucher_list($id)->first();
        if(! $voucher){
            return abort(404);
        }

        $total_grades = GeneralLedger::total_grade_list();
        $currency = DB::table('acc_currency')->get();
        $department = User::whereNotNull('department')->groupBy('department')->orderBy('department', 'asc')->distinct()->get()->pluck('department')->toArray();

        return view('cms.account_management.transfer_voucher.edit', [
            'method' => 'edit',
            'previous_url' => route('cms.transfer_voucher.show', ['id' => $id]),
            'form_action' => route('cms.transfer_voucher.edit', ['id' => $id]),
            'voucher' => $voucher,
            'total_grades' => $total_grades,
            'currency' => $currency,
            'department'=>$department,
        ]);
    }


    public function destroy($id)
    {
        $target = TransferVoucher::delete_voucher($id);

        if($target){
            wToast('刪除完成');
        } else {
            wToast('刪除失敗', ['type'=>'danger']);
        }

        return redirect()->route('cms.transfer_voucher.index');
    }
}
