<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        $customer = Customer::getCustomerBySearch($query);

        return view('cms.admin.customer.list', [
                "dataList" => $customer['dataList']
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //

        return view('cms.admin.customer.edit', [
            'method'         => 'create',
            'formAction'     => Route('cms.customer.create'),
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
            'email'  => ['required', 'unique:App\Models\Customer'],
        ]);

        $uData = $request->only('email', 'name', 'password');

        Customer::createCustomer($uData['name'], $uData['email'], $uData['password']);

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
    public function edit($id)
    {
        $data = Customer::where('id', '=', $id)->get()->first();
        if (!$data) {
            return abort(404);
        }


        return view('cms.admin.customer.edit', [
            'method'         => 'edit', 'id' => $id,
            'formAction'     => Route('cms.customer.edit', ['id' => $id]),
            'data'           => $data,
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
        //
        $request->validate([
            'password' => 'confirmed|min:4|nullable',
            'name'     => 'required|string', 'role_id' => 'array'
        ]);

        $customerData = $request->only('name');

        $password = $request->input('password');
        if ($password) {
            $customerData['password'] = Hash::make($password);
        }

        Customer::where('id', $id)->update($customerData);

        wToast('檔案更新完成');
        return redirect(Route('cms.customer.index'));
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
