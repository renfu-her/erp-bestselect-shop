<?php

namespace App\Http\Controllers\Cms;

use App\Enums\Customer\Newsletter;
use App\Http\Controllers\Controller;
use App\Models\Addr;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $query = $request->query();
        $name = Arr::get($query, 'name', '');
        $email = Arr::get($query, 'email', '');
        $customer = Customer::getCustomerBySearch($name)->paginate(10)->appends($query);

        return view('cms.admin.customer.list', [
            'name' => $name,
            'email' => $email,
            "dataList" => $customer,
            'formAction' => Route('cms.customer.index'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request)
    {
        $query = $request->query();

        $recentCity = $request->old('city_id');
        $bind = Arr::get($query, 'bind');
        $data = [];
        if ($bind) {
            $data = $request->user();
        }

        $regions = [];
        if ($recentCity) {
            $regions = Addr::getRegions($recentCity);
        }

        return view('cms.admin.customer.edit', [
            'method' => 'create',
            'formAction' => Route('cms.customer.create'),
            'customer_list' => Customer::all(),
            'citys' => Addr::getCitys(),
            'regions' => $regions,
            'bind' => $bind,
            'data' => $data,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'password' => 'confirmed|min:4', 'name' => 'required|string',
            'email' => ['required', 'email:rfc,dns', 'unique:App\Models\Customer'],
        ]);

        $uData = $request->only('email', 'name', 'password'
            , 'phone', 'birthday', 'acount_status', 'bind_customer_id'
            , 'address', 'city_id', 'region_id', 'addr'
        );

        $address = null;
        if (is_numeric($uData['region_id'])) {
            $address = Addr::fullAddr($uData['region_id'], $uData['addr']);
        }

        Customer::createCustomer($uData['name']
            , $uData['email']
            , $uData['password']
            , $uData['phone'] ?? null
            , $uData['birthday'] ?? null
            , $uData['acount_status'] ?? 0
            , $uData['bind_customer_id'] ?? null
            , $address
            , is_numeric($uData['city_id']) ? $uData['city_id'] : null
            , is_numeric($uData['region_id']) ? $uData['region_id'] : null
            , $uData['addr'] ?? null
        );

        if ($request->input('bind') == '1') {
            User::customerBinding($request->user()->id, $uData['email']);
            wToast('新增並綁定完成');
            return redirect(Route('cms.usermnt.customer-binding'));
        }

        wToast('新增完成');
        return redirect(Route('cms.customer.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function edit(Request $request, $id)
    {
        $data = Customer::getCustomer($id)->get()->first();
        if (!$data) {
            return abort(404);
        }
        $recentCity = $request->old('city_id');
        $regions = [];
        if ($recentCity) {
            $regions = Addr::getRegions($recentCity);
        } else {
            $regions = Addr::getRegions($data['city_id']);
        }

        return view('cms.admin.customer.edit', [
            'method' => 'edit', 'id' => $id,
            'formAction' => Route('cms.customer.edit', ['id' => $id]),
            'data' => $data,
            'customer_list' => Customer::all(),
            'citys' => Addr::getCitys(),
            'regions' => $regions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $uData = $request->only('email', 'name', 'sex'
            , 'phone', 'birthday', 'acount_status', 'newsletter', 'bind_customer_id'
            , 'address', 'city_id', 'region_id', 'addr'
        );

        $address = null;
        if (is_numeric($uData['region_id'])) {
            $address = Addr::fullAddr($uData['region_id'], $uData['addr']);
        }

        $updateArr = [
            'name' => $uData['name'],
            'sex' => $uData['sex'] ?? null,
            'phone' => $uData['phone'],
            'birthday' => $uData['birthday'] ?? null,
            'address' => $address,
            'city_id' => is_numeric($uData['city_id']) ? $uData['city_id'] : null,
            'region_id' => is_numeric($uData['region_id']) ? $uData['region_id'] : null,
            'addr' => $uData['addr'] ?? null,
            'newsletter' => $uData['newsletter'] ?? Newsletter::subscribe()->value,
            'bind_customer_id' => $uData['bind_customer_id'] ?? null,
        ];
        $password = $request->input('password');
        if ($password) {
            $updateArr['password'] = Hash::make($password);
        }
        $acount_status = $request->input('acount_status');
        if (0 == $acount_status || 1 == $acount_status) {
            $updateArr['acount_status'] = $acount_status;
        }
        DB::transaction(function () use ($id, $updateArr
        ) {
            Customer::where('id', $id)->update($updateArr);
            return ['success' => 1, 'error_msg' => "", 'id' => $id];
        });

        wToast('檔案更新完成');
        return redirect(Route('cms.customer.edit', ['id' => $id]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        Customer::deleteById($id);

        wToast('資料刪除完成');
        return redirect(Route('cms.customer.index'));
    }
}
