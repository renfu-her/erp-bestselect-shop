<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SaleChannel;
use App\Models\User;
use App\Models\UserProjLogistics;
use App\Models\UserSalechannel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
        $roleData = Role::whereNull('deleted_at')
                    ->select([
                        'id',
                        'title',
                    ])
                    ->get();
        $roleDataArray = collect($roleData)->keyBy('id');

        return view('cms.admin.user.list', [
            "roleData" => $roleDataArray->all(),
            "dataList" => $user,
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
        $employeeRoleId = Role::where([
            'deleted_at' => null,
            'title' => '員工',
        ])
            ->select('id')
            ->get()
            ->first()
            ->id;

        return view('cms.admin.user.edit', [
            'method' => 'create',
            'employeeRoleId' => $employeeRoleId ?? null,
            'formAction' => Route('cms.user.create'),
            'permissions' => Permission::getPermissionGroups('user'),
            'roles' => Role::roleList('user'),
            'is_super_admin' => Auth::user()->hasRole('Super Admin'),
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
            'account' => ['required', 'unique:App\Models\User'],

        ]);

        $uData = $request->only('account', 'name', 'password');
        $lgt_user = $request->input('lgt_user');

        $permission_id = [];
        $role_id = [];

        if ($request->exists('permission_id')) {
            $permission_id = $request->input('permission_id');
        }

        if ($request->exists('role_id')) {
            $role_id = $request->input('role_id');
        }

        $user = User::createUser(
            $uData['name'],
            $uData['account'],
            null,
            $uData['password'],
            $permission_id,
            $role_id,
        );

        $logisticUserApiToken = User::getLogisticApiToken($request->user()->id)->user_token;
        $modifyLogisticUser = UserProjLogistics::modifyLogisticUser($logisticUserApiToken, $user, ['user' => $lgt_user]);
        if ($modifyLogisticUser['success'] == 0) {
            throw ValidationException::withMessages(['lgt_user' => $modifyLogisticUser['error_msg']]);
        }

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
        //正式機才做
        if(env('APP_ENV') == 'rel') {
            $user_lgt = User::getLogisticUserIsOpen($id);
        } else {
            wToast('非正式環境 無法編輯物流權限', ['type'=>'danger']);
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
            'method' => 'edit', 'id' => $id,
            'formAction' => Route('cms.user.edit', ['id' => $id]),
            'data' => $data,
            'user_lgt' => $user_lgt ?? null,
            'permissions' => Permission::getPermissionGroups('user'),
            'permission_id' => $permission_id,
            'roles' => Role::roleList('user'), 'role_ids' => $role_ids,
            'is_super_admin' => Auth::user()->hasrole('Super Admin'),
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
            'name' => 'required|string', 'role_id' => 'array',
        ]);

        $userData = $request->only('name');
        $perData = $request->input('permission_id');
        $role_ids = $request->input('role_id');

        $password = $request->input('password');
        if ($password) {
            $userData['password'] = Hash::make($password);
        }
        $lgt_user = $request->input('lgt_user');

        //正式機才做
        if(env('APP_ENV') == 'rel'){
            $logisticUserApiToken = User::getLogisticApiToken($request->user()->id)->user_token;
            $modifyLogisticUser = UserProjLogistics::modifyLogisticUser($logisticUserApiToken, $id, ['user' => $lgt_user]);
            if ($modifyLogisticUser['success'] == 0) {
                throw ValidationException::withMessages(['lgt_user' => $modifyLogisticUser['error_msg']]);
            }
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

    public function salechannel(Request $request, $id)
    {
        //  dd(SaleChannel::get()->toArray());

        $current_channel = array_map(function ($n) {
            return $n['salechannel_id'];
        }, UserSalechannel::where('user_id', $id)->get()->toArray());

        return view('cms.admin.user.salechannel', [
            'method' => 'edit',
            'id' => $id,
            'formAction' => Route('cms.user.salechannel', ['id' => $id]),
            'channels' => SaleChannel::get()->toArray(),
            'current_channel'=>$current_channel
        ]);

    }

    public function updateSalechannel(Request $request, $id)
    {
        $d = $request->input('channel_id');

        if (!$d) {
            $d = [];
        }

        UserSalechannel::updateSalechannel($id, $d);
        wToast('儲存完成');
        return redirect(Route('cms.user.index'));

    }
}
