<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\PermissionGroup;
use Illuminate\Http\Request;
use App\Models\Permission;
use Illuminate\Http\Response;
use Spatie\Permission\PermissionRegistrar;

class PermissionCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('cms.permission.list', [
            'dataList' => PermissionGroup::getPermissions('user')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('cms.permission.edit', [
            'method' => 'create',
            'formAction' => Route('cms.permission.create'),
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
        //
        $request->validate([
            'title' => 'required|string'
        ]);

        PermissionGroup::create([
            'title' => $request->input('title'),
            'guard_name' => 'user'
        ]);


        wToast('資料儲存完畢');

        return redirect(Route('cms.permission.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
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
     * @return Response
     */
    public function edit($id)
    {
        //
        // dd(PermissionGroup::where('id','=',$id)->get()->first());
        return view('cms.permission.edit', [
            'method' => 'edit',
            'formAction' => Route('cms.permission.edit', ['id' => $id]),
            'data' => PermissionGroup::where('id', '=', $id)->get()->first()
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
            'title' => 'required|string'
        ]);

        PermissionGroup::where('id', '=', $id)->update([
            'title' => $request->input('title')
        ]);


        wToast('資料儲存完畢');

        return redirect(Route('cms.permission.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        PermissionGroup::where('id', '=', $id)->delete();
        Permission::where('group_id', '=', $id)->delete();
        wToast('資料刪除完畢');
        return redirect(Route('cms.permission.index'));
    }


    public function child($id)
    {
        return view('cms.permission.child_list', [
            'dataList' => Permission::where('group_id', '=', $id)->get(),
            'groupId' => $id,
            "breadcrumb_data" => PermissionGroup::where("id", '=', $id)->get()->first()
        ]);
    }

    public function childEdit($id, $cid)
    {
        // dd(PermissionGroup::where('id','=',$id)->get()->first());
        return view('cms.permission.child_edit', [
            'method' => 'edit',
            'formAction' => Route('cms.permission.child-edit', ['id' => $id, 'cid' => $cid]),
            'data' => Permission::where('id', '=', $cid)->get()->first(),
            'groupId' => $id,
            "breadcrumb_data" => PermissionGroup::where("id", '=', $id)->get()->first()
        ]);
    }


    public function childUpdate(Request $request, $id, $cid)
    {
        //
        $request->validate([
            'title' => 'required|string',
            'name' => "required|string|unique:per_permissions,name,$cid,id"
        ]);
        $v = $request->only('title', 'name');
        Permission::where('id', '=', $cid)->update([
            'title' => $v['title'],
            'name' => $v['name']
        ]);

        wToast('資料儲存完畢');

        return redirect(Route('cms.permission.child', ['id' => $id]));
    }


    public function childCreate($id)
    {
        return view('cms.permission.child_edit', [
            'method' => 'create',
            'formAction' => Route('cms.permission.child-create', ['id' => $id]),
            'groupId' => $id,
            "breadcrumb_data" => PermissionGroup::where("id", '=', $id)->get()->first()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function childStore(Request $request, $id)
    {
        //
        $request->validate([
            'title' => 'required|string',
            'name' => "required|string|unique:per_permissions"
        ]);
        $v = $request->only('title', 'name');
        Permission::create([
            'title' => $v['title'],
            'name' => $v['name'],
            'guard_name' => 'user',
            'group_id' => $id
        ]);
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
        wToast('資料儲存完畢');

        return redirect(Route('cms.permission.child', ['id' => $id]));
    }

    public function childDestroy($id, $cid)
    {
        //
        Permission::where('id', '=', $cid)->delete();
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        wToast('資料刪除完畢');
        return redirect(Route('cms.permission.child', ['id' => $id]));
    }
}
