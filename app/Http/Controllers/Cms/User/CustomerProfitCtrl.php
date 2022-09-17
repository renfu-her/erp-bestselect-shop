<?php

namespace App\Http\Controllers\Cms\User;

use App\Enums\Customer\ProfitStatus;
use App\Enums\Customer\ProfitType;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\CustomerProfit;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CustomerProfitCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // dd(CustomerProfit::dataList()->get()->toArray());
        //   dd('aa');
        $query = $request->query();
        $search['status'] = Arr::get($query, 'status', null);
        $search['name'] = Arr::get($query, 'name', null);
        $search['sn'] = Arr::get($query, 'sn', null);

        $dataList = CustomerProfit::dataList($search['name'], $search['sn'], $search['status'])->paginate(10)->appends($query);

        //  $customer = Customer::getCustomerBySearch($name)->paginate(10)->appends($query);

        return view('cms.admin.customer_profit.list', [
            'name' => '',
            'sn' => '',
            "dataList" => $dataList,
            'status' => ProfitStatus::getValueWithDesc(),
            'formAction' => Route('cms.customer-profit.index'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customers = CustomerProfit::getUser()->whereNull('cp.id')->get();

        $parentCustomers = CustomerProfit::getUser()->where('cp.has_child', '1')->get();

        return view('cms.admin.customer_profit.edit', [
            'method' => 'add',
            'customers' => $customers,
            'parentCustomers' => $parentCustomers,
            'status' => ProfitStatus::getValueWithDesc(),
            'banks' => Bank::get(),
            'profitType' => ProfitType::getValueWithDesc(),
            'formAction' => route("cms.customer-profit.create"),
            'data' => null,
            'customer' => null,
        ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'status' => 'required',
            'identity_sn' => 'required',
            'parent_profit_rate' => 'required',
            'profit_rate' => 'required',
            'bank_id' => 'required',
            'bank_account' => 'required',
            'bank_account_name' => 'required',
        ]);

        $d = $request->all();
        DB::beginTransaction();

        if (isset($d['parent_customer_id'])) {
            Customer::where('id', $d['customer_id'])->update([
                'recommend_id' => $d['parent_customer_id'],
            ]);

            $profit_rate = $d['profit_rate'];
            $parent_profit_rate = $d['parent_profit_rate'];
        }else{
            $profit_rate = 100;
            $parent_profit_rate = 0;
        }

        $re = CustomerProfit::createProfit($d['customer_id'], $d['bank_id'], $d['bank_account'], $d['bank_account_name'], $d['identity_sn']);
        if ($re['success'] == '0') {
            return redirect()->back()->withErrors(['customer_id' => $re['message']]);
        }

        CustomerProfit::where('id', $re['id'])->update([
            'status' => ProfitStatus::fromValue($d['status']),
            'status_title' => ProfitStatus::fromValue($d['status'])->description,
            'profit_rate' => $profit_rate,
            'parent_profit_rate' => $parent_profit_rate,
            // 'parent_customer_id' => isset($d['parent_customer_id']) ? $d['parent_customer_id'] : null,
        ]);

        DB::commit();
        wToast('新增完成');
        return redirect(route('cms.customer-profit.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {

        //  dd(CustomerProfit::where('id',$id)->get());
        $data = CustomerProfit::where('id', $id)->get()->first();

        if (!$data) {
            return abort(404);
        }

        return view('cms.admin.customer_profit.edit', [
            'method' => 'edit',
            'customer' => Customer::detail($data->customer_id)->get()->first(),
            'status' => ProfitStatus::getValueWithDesc(),
            'banks' => Bank::get(),
            'profitType' => ProfitType::getValueWithDesc(),
            'data' => $data,
            'formAction' => route("cms.customer-profit.edit", ['id' => $id]),

        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $request->validate([
            'identity_sn' => ['required'],
            'status' => ['required'],
            'parent_profit_rate' => ['numeric'],
            'profit_rate' => ['numeric'],
            'profit_type' => ['required'],
            'bank_id' => ['required'],
            'bank_account' => ['required'],
            'bank_account_name' => ['required'],
        ]);

        $d = $request->all();

        $has_child = Arr::get($d, 'has_child', 0);

        DB::beginTransaction();
        $profit = CustomerProfit::where('id', $id)->get()->first();
        $customer = Customer::where('id', $profit->customer_id)->get()->first();

        if ($customer->recommend_id) {
            $profit_rate = $d['profit_rate'];
            $parent_profit_rate = $d['parent_profit_rate'];
        } else {
            $profit_rate = 100;
            $parent_profit_rate = 0;
        }

        // dd($has_child);
        $update = [
            'status' => $d['status'],
            'status_title' => ProfitStatus::fromValue($d['status'])->description,
            'identity_sn' => $d['identity_sn'],
            'profit_rate' => $profit_rate,
            'parent_profit_rate' => $parent_profit_rate,
            'bank_id' => $d['bank_id'],
            'bank_account' => $d['bank_account'],
            'bank_account_name' => $d['bank_account_name'],
            'profit_type' => $d['profit_type'],
            'has_child' => $has_child,
        ];

        // dd($update);

        CustomerProfit::where('id', $id)->update($update);
        DB::commit();
        wToast('修改完成');

        return redirect(route('cms.customer-profit.index'));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
