<?php

namespace App\Http\Controllers\Cms\AdminManagement;

use App\Http\Controllers\Controller;
use App\Models\Petition;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PetitionCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $request->all();

        $options = [
            'title' => Arr::get($query, 'title'),
            'sn' => Arr::get($query, 'sn'),
        ];

        if ($request->user()->can('cms.petition.admin')) {
            $options['user_id'] = Arr::get($query, 'user');
            $users = User::get();
        } else {
            $options['user_id'] = $request->user()->id;
            $users = User::where('id', $options['user_id'])->get();
        }

        $dataList = Petition::dataList($options)->orderBy('petition.created_at', 'DESC')->paginate(100);

        foreach ($dataList as $data) {
            $data->users = $data->users ? json_decode($data->users) : [];
        }

        //  dd($request->user()->can('cms.petition.admin'));

        return view('cms.admin_management.petition.list', [
            'dataList' => $dataList,
            'users' => $users,
            'data_per_page' => 100,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
        return view('cms.admin_management.petition.edit', [
            'method' => 'create',
            'formAction' => route('cms.petition.create'),
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
        //
        $request->validate(['title' => 'required',
            'content' => 'required',
            'order' => ['nullable', 'array']]);

        $d = $request->all();

        $d['order'] = array_values(array_filter($d['order']));

        $re = Petition::createPetition($request->user()->id, $d['title'], $d['content'], $d['order']);

        if ($re['success'] != '1') {
            $errors = [];
            if ($re['type'] == 'order_sn') {
                foreach ($re['data'] as $value) {
                    $errors['order.' . $value] = '查無單號';
                }
            }
            return redirect()->back()->withInput($d)->withErrors($errors);
        }

        wToast('新增完成');
        return redirect(route('cms.petition.index'));

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Petition::dataList()->where('petition.id', $id)->get()->first();
        if (!$data) {
            return abort(404);
        }

        $data->users = json_decode($data->users);

        $orders = array_map(function ($n) {
            return getErpOrderUrl($n);
        }, Petition::getOrderSn($id, 'petition')->get()->toArray());

        // dd($orders);

        return view('cms.admin_management.petition.show', [
            'method' => 'edit',
            'data' => $data,
            'order' => $orders,
            'formAction' => route('cms.petition.edit', ['id' => $id]),
            'breadcrumb_data' => $data->title,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $data = Petition::dataList()->where('petition.id', $id)->get()->first();
        if (!$data) {
            return abort(404);
        }
        $orders = array_map(function ($n) {
            return $n->order_sn;
        }, Petition::getOrderSn($id, 'petition')->get()->toArray());

        return view('cms.admin_management.petition.edit', [
            'method' => 'edit',
            'data' => $data,
            'order' => $orders,
            'formAction' => route('cms.petition.edit', ['id' => $id]),
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
        $request->validate(['title' => 'required',
            'content' => 'required',
            'order' => ['nullable', 'array']]);

        $d = $request->all();
        DB::beginTransaction();
        Petition::where('id', $id)->update([
            'title' => $d['title'],
            'content' => $d['content'],
        ]);
        $d['order'] = array_values(array_filter($d['order']));

        $re = Petition::updateOrderSn($d['order'], $id, 'petition');

        if ($re['success'] != '1') {
            $errors = [];
            if ($re['type'] == 'order_sn') {
                foreach ($re['data'] as $value) {
                    $errors['order.' . $value] = '查無單號';
                }
            }
            DB::rollBack();
            return redirect()->back()->withInput($d)->withErrors($errors);
        }
        DB::commit();
        wToast('更新完成');
        return redirect(route('cms.petition.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        Petition::where('id', $id)->delete();

        wToast('刪除完成');
        return redirect(route('cms.petition.index'));
    }

    // 簽核列表
    public function auditList(Request $request)
    {
        $query = $request->all();

        $options = [
            'title' => Arr::get($query, 'title'),
            'sn' => Arr::get($query, 'sn'),
            'audit' => $request->user()->id,
            'user_id' => Arr::get($query, 'user'),
        ];

        $dataList = Petition::dataList($options)->orderBy('petition.created_at', 'DESC')->paginate(100);

        foreach ($dataList as $data) {
            $data->users = $data->users ? json_decode($data->users) : [];

        }

        return view('cms.admin_management.petition.list', [
            'dataList' => $dataList,
            'type' => 'audit',
            'users' => User::get(),
            'data_per_page' => 100,
        ]);
    }

    public function auditEdit(Request $request, $id)
    {
        $data = Petition::dataList()->where('petition.id', $id)->get()->first();
        if (!$data) {
            return abort(404);
        }

        $data->users = json_decode($data->users);

        $orders = array_map(function ($n) {
            return getErpOrderUrl($n);
        }, Petition::getPetitionOrders($id)->get()->toArray());

        // dd($orders);

        return view('cms.admin_management.petition.show', [
            'method' => 'edit',
            'data' => $data,
            'order' => $orders,
            'type' => 'audit',
            'formAction' => route('cms.petition.edit', ['id' => $id]),
            'breadcrumb_data' => $data->title,
        ]);
    }

    public function auditConfirm(Request $request, $id)
    {

        Petition::confirm($id, $request->user()->id);
        wToast('審核完成');
        return redirect(route('cms.petition.audit-list'));

    }

}
