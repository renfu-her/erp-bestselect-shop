<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Permission;
use App\Models\Role;

class UserCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $query = $request->query();
        $user = User::getUserBySearch($query);

        return view('cms.admin.user.list', [
            "dataList" => $user['dataList'], "users" => $user['account'],
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

        return view('cms.admin.user.edit', [
            'method'         => 'create',
            'formAction'     => Route('cms.user.create'),
            'permissions'    => Permission::getPermissionGroups('user'),
            'roles'          => Role::roleList('user'),
            'is_super_admin' => Auth::user()->hasrole('Super Admin'),
            'customer_list'         => Customer::all(),
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
            'account'  => ['required', 'unique:App\Models\User'],
            
        ]);

        $uData = $request->only('account', 'name', 'password', 'customer_id');

        $permission_id = [];
        $role_id = [];

        if ($request->exists('permission_id')) {
            $permission_id = $request->input('permission_id');
        }

        if ($request->exists('role_id')) {
            $role_id = $request->input('role_id');
        }

        User::createUser(
            $uData['name'],
            $uData['account'],
            null,
            $uData['password'],
            $permission_id,
            $role_id,
        );
        wToast('新增完成');
        return redirect(Route('cms.user.index'));
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
        $data = User::where('id', '=', $id)->get()->first();
        if (!$data) {
            return abort(404);
        }

        $role_ids = Role::getUserRoles($id, 'user', function ($arr) {
            return array_map(function ($n) {
                return $n->role_id;
            }, $arr);
        });

        $permission_id = Permission::getPermissions(
            $id,
            'user',
            function ($arr) {
                return array_map(function ($n) {
                    return $n->id;
                }, $arr);
            }
        );

        return view('cms.admin.user.edit', [
            'method'         => 'edit', 'id' => $id,
            'formAction'     => Route('cms.user.edit', ['id' => $id]),
            'data'           => $data,
            'permissions'    => Permission::getPermissionGroups('user'),
            'permission_id'  => $permission_id,
            'roles'          => Role::roleList('user'), 'role_ids' => $role_ids,
            'is_super_admin' => Auth::user()->hasrole('Super Admin'),
            'customer_list'         => Customer::all(),
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
            'name'     => 'required|string', 'role_id' => 'array',
            'customer_id'  => 'required|numeric',
        ]);

        $userData = $request->only('name', 'customer_id');
        $perData = $request->input('permission_id');
        $role_ids = $request->input('role_id');

        $password = $request->input('password');
        if ($password) {
            $userData['password'] = Hash::make($password);
        }

        User::where('id', $id)->update($userData);

        Permission::updateDirectPermissions($id, 'user', $perData);

        Role::updateUserRoles($id, 'user', $role_ids);

        wToast('檔案更新完成');
        return redirect(Route('cms.user.index'));
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
        //
        DB::table('usr_users')->where('id', $id)->delete();

        wToast('資料刪除完成');
        return redirect(Route('cms.user.index'));
    }
}
