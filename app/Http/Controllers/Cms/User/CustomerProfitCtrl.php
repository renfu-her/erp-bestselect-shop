<?php

namespace App\Http\Controllers\Cms\User;

use App\Enums\Customer\ProfitStatus;
use App\Enums\Customer\ProfitType;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\CustomerProfit;
use Illuminate\Http\Request;

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
        $dataList = CustomerProfit::dataList()->paginate(10);
        //  $customer = Customer::getCustomerBySearch($name)->paginate(10)->appends($query);

        return view('cms.admin.customer_profit.list', [
            'name' => '',
            'sn' => '',
            "dataList" => $dataList,
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
            'customer' => Customer::where('id', $id)->get()->first(),
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
        //
        dd($_POST);
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
