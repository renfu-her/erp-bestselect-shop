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

        $dataList = CustomerProfit::dataList($search['name'], $search['sn'], $search['status'])->paginate(10);

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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //   dd(ProfitStatus::getValueWithDesc());
        //  dd(ProfitStatus::getValueWithDesc());
        return view('cms.admin.customer_profit.edit', [
            'method' => 'edit',
            'customer' => Customer::where('id', $data->customer_id)->get()->first(),
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
            'identity_id' => ['required'],
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
        // dd($has_child);
        $update = [
            'status' => $d['status'],
            'status_title' => ProfitStatus::fromValue($d['status'])->description,
            'identity_id' => $d['identity_id'],
            'profit_rate' => $d['profit_rate'],
            'parent_profit_rate' => $d['parent_profit_rate'],
            'bank_id' => $d['bank_id'],
            'bank_account' => $d['bank_account'],
            'bank_account_name' => $d['bank_account_name'],
            'profit_type' => $d['profit_type'],
            'has_child' => $has_child,
        ];

        // dd($update);

        CustomerProfit::where('id', $id)->update($update);

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
