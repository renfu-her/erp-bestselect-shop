<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Enums\Supplier\Payment;
use App\Enums\Payable\ChequeStatus;

use App\Models\AccountPayable;
use App\Models\Customer;
use App\Models\DayEnd;
use App\Models\Depot;
use App\Models\GeneralLedger;
use App\Models\PayableAccount;
use App\Models\PayableCash;
use App\Models\PayableCheque;
use App\Models\PayableDefault;
use App\Models\PayableForeignCurrency;
use App\Models\PayableOther;
use App\Models\PayableRemit;
use App\Models\PayingOrder;
use App\Models\Petition;
use App\Models\StituteOrder;
use App\Models\StituteOrderItem;
use App\Models\Supplier;
use App\Models\User;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class StituteOrderCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();
        $page = getPageCount(Arr::get($query, 'data_per_page', 100)) > 0 ? getPageCount(Arr::get($query, 'data_per_page', 100)) : 100;

        $cond = [];

        $cond['client_key'] = Arr::get($query, 'client_key', null);
        if (gettype($cond['client_key']) == 'string') {
            $key = explode('|', $cond['client_key']);
            $cond['client']['id'] = $key[0];
            $cond['client']['name'] = $key[1];
        } else {
            $cond['client'] = [];
        }

        $cond['so_sn'] = Arr::get($query, 'so_sn', null);
        $cond['source_sn'] = Arr::get($query, 'source_sn', null);

        $cond['stitute_min_price'] = Arr::get($query, 'stitute_min_price', null);
        $cond['stitute_max_price'] = Arr::get($query, 'stitute_max_price', null);
        $stitute_price = [
            $cond['stitute_min_price'],
            $cond['stitute_max_price']
        ];

        $cond['stitute_sdate'] = Arr::get($query, 'stitute_sdate', null);
        $cond['stitute_edate'] = Arr::get($query, 'stitute_edate', null);
        $stitute_payment_date = [
            $cond['stitute_sdate'],
            $cond['stitute_edate']
        ];

        $cond['check_payment'] = Arr::get($query, 'check_payment', 'all');

        $dataList = StituteOrder::stitute_order_list(
            null,
            $cond['client'],
            $cond['so_sn'],
            $cond['source_sn'],
            $stitute_price,
            $stitute_payment_date,
            $cond['check_payment'],
        )->paginate($page)->appends($query);

        $user = User::whereNull('deleted_at')->select('id', 'name', 'title')->get()->toArray();
        $customer = Customer::whereNull('deleted_at')->select('id', 'name', 'email')->get()->toArray();
        $depot = Depot::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $supplier = Supplier::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $client_merged = array_merge($user, $customer, $depot, $supplier);

        $check_payment_status = [
            'all'=>'不限',
            '0'=>'未入款',
            '1'=>'已入款',
        ];

        return view('cms.account_management.stitute.list', [
            'data_per_page' => $page,
            'dataList' => $dataList,
            'cond' => $cond,
            'client' => $client_merged,
            'check_payment_status' => $check_payment_status,
        ]);
    }


    public function create(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'client_key' => 'required|string',
                'grade_id' => 'required|array',
                'grade_id.*' => 'required|exists:acc_all_grades,id',
                'price' => 'nullable|array',
                'price.*' => 'numeric|between:0,9999999.99',
                'qty' => 'nullable|array',
                'qty.*' => 'numeric|between:0,9999999.99',
                'summary' => 'nullable|array',
                'memo' => 'nullable|array',
            ]);

            $client_key = explode('|', request('client_key'));

            if(count($client_key) > 1){
                DB::beginTransaction();

                try {
                    $client = PayingOrder::payee($client_key[0], $client_key[1]);
                    $price = 0;

                    $parm = [
                        'client_id' => $client->id,
                        'client_name' => $client->name,
                        'client_phone' => $client->phone,
                        'client_address' => $client->address,
                        'creator_id' =>auth('user')->user() ? auth('user')->user()->id : null,
                    ];
                    $stitute_order = StituteOrder::create_stitute_order($parm);

                    foreach(request('grade_id') as $key => $value){
                        $total_price = request('price')[$key] * request('qty')[$key];
                        $price += $total_price;

                        $items[] = [
                            'stitute_order_id' => $stitute_order->id,
                            'grade_id' => request('grade_id')[$key],
                            'price' => request('price')[$key],
                            'qty' => request('qty')[$key],
                            'total_price' => $total_price,
                            'summary' => request('summary')[$key],
                            'memo' => request('memo')[$key],
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                    }
                    StituteOrderItem::insert($items);

                    $stitute_order->update([
                        'price' => $price,
                    ]);

                    DB::commit();

                    wToast(__('代墊單建立成功'));

                    return redirect()->route('cms.stitute.show', [
                        'id'=>$stitute_order->id,
                    ]);

                } catch (\Exception $e) {
                    DB::rollback();
                    wToast(__('代墊單建立失敗'), ['type'=>'danger']);
                    return redirect()->back();
                }
            }

            wToast(__('代墊單建立失敗'), ['type'=>'danger']);
            return redirect()->back();
        }

        $user = User::whereNull('deleted_at')->select('id', 'name', 'title')->get()->toArray();
        $customer = Customer::whereNull('deleted_at')->select('id', 'name', 'email')->get()->toArray();
        $depot = Depot::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $supplier = Supplier::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $client_merged = array_merge($user, $customer, $depot, $supplier);

        $total_grades = GeneralLedger::total_grade_list();
        $currency = DB::table('acc_currency')->get();

        return view('cms.account_management.stitute.edit', [
            'method'=>'create',
            'form_action'=>route('cms.stitute.create'),
            'client' => $client_merged,
            'total_grades' => $total_grades,
            'currency' => $currency,
        ]);
    }


    public function edit(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:acc_stitute_orders,id',
        ]);

        if($request->isMethod('post')){
            $request->validate([
                'so_item_id' => 'required|array',
                'so_item_id.*' => 'nullable|exists:acc_stitute_order_items,id',
                'client_key' => 'required|string',
                'grade_id' => 'required|array',
                'grade_id.*' => 'required|exists:acc_all_grades,id',
                'price' => 'nullable|array',
                'price.*' => 'numeric|between:0,9999999.99',
                'qty' => 'nullable|array',
                'qty.*' => 'numeric|between:0,9999999.99',
                'summary' => 'nullable|array',
                'memo' => 'nullable|array',
            ]);

            $client_key = explode('|', request('client_key'));

            DB::beginTransaction();

            try {
                $client = PayingOrder::payee($client_key[0], $client_key[1]);
                $price = 0;

                $dArray = array_diff(StituteOrderItem::where('stitute_order_id', $id)->pluck('id')->toArray(), array_intersect_key(request('so_item_id'), request('grade_id')));
                if($dArray) StituteOrderItem::destroy($dArray);

                foreach(request('grade_id') as $key => $value){
                    $total_price = request('price')[$key] * request('qty')[$key];
                    $price += $total_price;

                    if(request('so_item_id')[$key]){
                        StituteOrderItem::find(request('so_item_id')[$key])->update([
                            'grade_id' => request('grade_id')[$key],
                            'price' => request('price')[$key],
                            'qty' => request('qty')[$key],
                            'total_price' => $total_price,
                            'summary' => request('summary')[$key],
                            'memo' => request('memo')[$key],
                        ]);

                    } else {
                        StituteOrderItem::create([
                            'stitute_order_id' => $id,
                            'grade_id' => request('grade_id')[$key],
                            'price' => request('price')[$key],
                            'qty' => request('qty')[$key],
                            'total_price' => $total_price,
                            'summary' => request('summary')[$key],
                            'memo' => request('memo')[$key],
                        ]);
                    }
                }

                $stitute_order = StituteOrder::find($id);
                $stitute_order->update([
                    'price' => $price,
                    'client_id' =>$client->id,
                    'client_name' =>$client->name,
                    'client_phone' =>$client->phone,
                    'client_address' =>$client->address,
                ]);

                DB::commit();

                wToast(__('代墊單更新成功'));

                return redirect()->route('cms.stitute.show', [
                    'id'=>$id,
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                wToast(__('代墊單更新失敗'), ['type'=>'danger']);
                return redirect()->back();
            }
        }

        $stitute_order = StituteOrder::stitute_order_list($id)->first();

        $user = User::whereNull('deleted_at')->select('id', 'name', 'title')->get()->toArray();
        $customer = Customer::whereNull('deleted_at')->select('id', 'name', 'email')->get()->toArray();
        $depot = Depot::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $supplier = Supplier::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $client_merged = array_merge($user, $customer, $depot, $supplier);

        $total_grades = GeneralLedger::total_grade_list();
        $currency = DB::table('acc_currency')->get();

        return view('cms.account_management.stitute.edit', [
            'method'=>'edit',
            'form_action' => route('cms.stitute.edit', ['id'=>$id]),
            'stitute_order' => $stitute_order,
            'client' => $client_merged,
            'total_grades' => $total_grades,
            'currency' => $currency,
        ]);
    }


    public function show(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:acc_stitute_orders,id',
        ]);

        $stitute_order = StituteOrder::stitute_order_list($id)->first();

        $applied_company = DB::table('acc_company')->where('id', 1)->first();

        $zh_price = num_to_str($stitute_order->so_price);

        $view = 'cms.account_management.stitute.show';
        if (request('action') == 'print') {
            $view = 'doc.print_stitute_order';
        }

        return view($view, [
            'stitute_order' => $stitute_order,
            'applied_company' => $applied_company,
            'zh_price' => $zh_price,
            'relation_order' => Petition::getBindedOrder($id, 'PSG'),
        ]);
    }


    public function destroy($id)
    {
        $stitute_order = StituteOrder::findOrFail($id);

        if($stitute_order->pay_order_id){
            wToast('刪除失敗', ['type'=>'danger']);
            return redirect()->back();

        } else {
            $stitute_order->delete();
            wToast('刪除完成');

            return redirect()->route('cms.stitute.index');
        }
    }


    public function po_edit(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:acc_stitute_orders,id',
        ]);

        $stitute_order = StituteOrder::stitute_order_list($id)->first();

        $paying_order = PayingOrder::find($stitute_order->po_id);
        $payable_data = PayingOrder::get_payable_detail($stitute_order->po_id);

        $tw_price = $stitute_order->so_price - $payable_data->sum('tw_price');

        $total_grades = GeneralLedger::total_grade_list();

        return view('cms.account_management.stitute.po_edit', [
            'breadcrumb_data' => ['id' => $id],
            'form_action' => route('cms.stitute.po-store', ['id' => $id]),
            'previous_url' => route('cms.stitute.show', ['id' => $id]),
            'stitute_order' => $stitute_order,
            'paying_order' => $paying_order,
            'payable_data' => $payable_data,
            'tw_price' => $tw_price,
            'total_grades' => $total_grades,

            'cashDefault' => PayableDefault::where('name', 'cash')->pluck('default_grade_id')->toArray(),
            'chequeDefault' => PayableDefault::where('name', 'cheque')->pluck('default_grade_id')->toArray(),
            'remitDefault' => PayableDefault::where('name', 'remittance')->pluck('default_grade_id')->toArray(),
            'all_currency' => PayableDefault::getCurrencyOptionData()['selectedCurrencyResult']->toArray(),
            'currencyDefault' => PayableDefault::where('name', 'foreign_currency')->pluck('default_grade_id')->toArray(),
            'accountPayableDefault' => PayableDefault::where('name', 'accounts_payable')->pluck('default_grade_id')->toArray(),
            'otherDefault' => PayableDefault::where('name', 'other')->pluck('default_grade_id')->toArray(),

            'transactTypeList' => AccountPayable::getTransactTypeList(),
            'chequeStatus' => ChequeStatus::get_key_value(),
        ]);
    }


    public function po_store(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:acc_stitute_orders,id',
            'acc_transact_type_fk' => 'required|regex:/^[1-6]$/',
            'tw_price' => 'required|numeric',
            'summary'=>'nullable|string',
            'note'=>'nullable|string',
        ]);

        $stitute_order = StituteOrder::findOrFail($id);

        $source_type = app(StituteOrder::class)->getTable();
        $paying_order = PayingOrder::where([
            'source_type'=>$source_type,
            'source_id'=>$id,
        ])->first();

        if (!$paying_order) {
            $product_grade = PayableDefault::where('name', '=', 'product')->first()->default_grade_id;
            $logistics_grade = PayableDefault::where('name', '=', 'logistics')->first()->default_grade_id;

            $result = PayingOrder::createPayingOrder(
                $source_type,
                $id,
                null,
                $request->user()->id,
                1,
                $product_grade,
                $logistics_grade,
                $stitute_order->price,
                '',
                '',
                $stitute_order->client_id,
                $stitute_order->client_name,
                $stitute_order->client_phone,
                $stitute_order->client_address
            );

            $paying_order = PayingOrder::find($result['id']);

            $parm = [
                'id' => $id,
                'pay_order_id' => $result['id'],
            ];
            StituteOrder::update_stitute_order_approval($parm);
        }

        $request->merge([
            'pay_order_id'=>$paying_order->id,
        ]);

        $data = $request->except('_token');

        $payable_type = $data['acc_transact_type_fk'];
        switch ($payable_type) {
            case Payment::Cash:
                PayableCash::storePayableCash($data);
                break;
            case Payment::Cheque:
                $request->validate([
                    'cheque.ticket_number'=>'required|unique:acc_payable_cheque,ticket_number,po_delete,status_code|regex:/^[A-Z]{2}[0-9]{7}$/'
                ]);
                PayableCheque::storePayableCheque($data);
                break;
            case Payment::Remittance:
                PayableRemit::storePayableRemit($data);
                break;
            case Payment::ForeignCurrency:
                PayableForeignCurrency::storePayableCurrency($data);
                break;
            case Payment::AccountsPayable:
                PayableAccount::storePayablePayableAccount($data);
                break;
            case Payment::Other:
                PayableOther::storePayableOther($data);
                break;
        }

        $payable_data = PayingOrder::get_payable_detail($paying_order->id);
        if (count($payable_data) > 0 && $paying_order->price == $payable_data->sum('tw_price')) {
            $paying_order->update([
                'balance_date' => date('Y-m-d H:i:s'),
                'payment_date' => $data['payment_date'],
            ]);

            DayEnd::match_day_end_status($data['payment_date'], $paying_order->sn);
        }

        if (PayingOrder::find($paying_order->id) && PayingOrder::find($paying_order->id)->balance_date) {
            return redirect()->route('cms.stitute.po-show', [
                'id' => $id,
            ]);

        } else {
            return redirect()->route('cms.stitute.po-edit', [
                'id' => $id,
            ]);
        }
    }


    public function po_show(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id'=>'required|exists:acc_stitute_orders,id',
        ]);

        $applied_company = DB::table('acc_company')->where('id', 1)->first();

        $stitute_order = StituteOrder::stitute_order_list($id)->first();

        $zh_price = num_to_str($stitute_order->so_price);

        $paying_order = PayingOrder::findOrFail($stitute_order->po_id);
        $payable_data = PayingOrder::get_payable_detail($stitute_order->po_id);
        $data_status_check = PayingOrder::payable_data_status_check($payable_data);

        if (!$paying_order->balance_date) {
            // return abort(404);

            return redirect()->route('cms.stitute.po-edit', [
                'id' => $id,
            ]);
        }

        $accountant = User::whereIn('id', $payable_data->pluck('accountant_id_fk')->toArray())->get();
        $accountant = array_unique($accountant->pluck('name')->toArray());
        asort($accountant);

        if($paying_order && $paying_order->append_po_id){
            $append_po = PayingOrder::find($paying_order->append_po_id);
            $paying_order->append_po_link = PayingOrder::paying_order_link($append_po->source_type, $append_po->source_id, $append_po->source_sub_id, $append_po->type);
        }

        $view = 'cms.account_management.stitute.po_show';
        if (request('action') == 'print') {
            $view = 'doc.print_stitute_po';
        }

        return view($view, [
            'breadcrumb_data' => ['id' => $id],
            'applied_company' => $applied_company,
            'stitute_order' => $stitute_order,
            'zh_price' => $zh_price,
            'paying_order' => $paying_order,
            'payable_data' => $payable_data,
            'data_status_check' => $data_status_check,
            'accountant'=>implode(',', $accountant),
            'relation_order' => Petition::getBindedOrder($paying_order->id, 'ISG'),
        ]);
    }
}
