<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('cms.role.list', [
            "dataList"       => Role::roleList('user'),
            'is_super_admin' => Auth::user()->hasrole('Super Admin')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {

        return view('cms.role.edit', [
            'method'         => 'create',
            'formAction'     => Route('cms.role.create'),
            'permissions'    => Permission::getPermissionGroups('user'),
            'is_super_admin' => Auth::user()->hasrole('Super Admin')
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
            'title' => ['required', 'string']
        ]);
        $permission_id = Arr::get($_POST, 'permission_id', []);

        $uData = $request->only('title');
        Role::createRole($uData['title'], 'user', $permission_id);
        wToast('新增完成');
        return redirect(Route('cms.role.index'));
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
        $data = Role::where('id', '=', $id)->get()->first();
        if (!$data) {
            return abort(404);
        }

        $permission_id = Role::getRolePermissions($id, function ($arr) {
            return array_map(function ($n) {
                return $n->permission_id;
            }, $arr);
        });

        return view('cms.role.edit', [
            'method'         => 'edit', 'id' => $id,
            'formAction'     => Route('cms.role.edit', ['id' => $id]),
            'data'           => $data,
            'permissions'    => Permission::getPermissionGroups('user'),
            'permission_id'  => $permission_id,
            "is_super_admin" => Auth::user()->hasrole('Super Admin')
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

        $request->validate([
            'title' => "required", 'permission_id' => 'array'
        ]);

        $title = $request->input('title');
        $permission_id = Arr::get($_POST, 'permission_id', []);
        Role::updateRoleAndPermission($id, $title, $permission_id);

        wToast('檔案更新完成');
        return redirect(Route('cms.role.index'));
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
        Role::delRole($id);
        wToast('資料刪除完成');
        return redirect(Route('cms.role.index'));
    }
}
