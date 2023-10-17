<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SaleChannel;
use App\Models\User;
use App\Models\UserProjLogistics;
use App\Models\UserSalechannel;
use App\Models\UsrProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
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
        if (!isset($query['profit'])) {
            $query['profit'] = 'all';
        }
        $user = User::getUserBySearch($query, 30);

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
            'profitStauts' => ['all' => '不限', '0' => '無', '1' => '有'],
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
        if (env('APP_ENV') == 'rel') {
            $user_lgt = User::getLogisticUserIsOpen($id);
        } else {
            wToast('非正式環境 無法編輯物流權限', ['type' => 'danger']);
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
        if (env('APP_ENV') == 'rel') {
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

        User::where('id', $id)->delete();

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
            'current_channel' => $current_channel,
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

    public function profile($id)
    {
        // dd('aa');

        $profile = UsrProfile::where('user_id', $id)->get()->first();
        if (!$profile) {
            UsrProfile::create(['user_id' => $id]);

        }
        $profile = UsrProfile::dataList()->where('user_id', $id)->get()->first();

        return view('cms.admin.user.profile', [
            'method' => 'view',
            'id' => $id,
            'data' => $profile,
        ]);
    }

    public function editProfile($id)
    {
        $profile = UsrProfile::where('user_id', $id)->get()->first();
        if (!$profile) {
            UsrProfile::create(['user_id' => $id]);

        }
        $profile = UsrProfile::dataList()->where('user_id', $id)->get()->first();

        return view('cms.admin.user.profile', [
            'method' => 'edit',
            'id' => $id,
            'data' => $profile,
        ]);
    }

    public function updateProfile(Request $request, $id)
    {
        $d = $request->all();

        UsrProfile::where('user_id', $id)->update([
            'en_name' => $d['en_name'],
            'identity' => $d['identity'],
        //    'gender' => $d['gender'],
            'live_with_family' => $d['live_with_family'],
            'performance_statistics' => $d['performance_statistics'],
            'job_title' => $d['job_title'],
            'date_of_job_entry' => $d['date_of_job_entry'],
            'date_of_job_leave' => $d['date_of_job_leave'],
            'ability_english' => $d['ability_english'],
            'english_certification' => $d['english_certification'],
            'ability_japanese' => $d['ability_japanese'],
            'japanese_certification' => $d['japanese_certification'],
            'date_of_insurance_entry' => $d['date_of_insurance_entry'],
            'date_of_insurance_leave' => $d['date_of_insurance_leave'],
            'labor_insurance' => $d['labor_insurance'],
            'labor_insurance_oop' => $d['labor_insurance_oop'],
            'health_insurance' => $d['health_insurance'],
            'health_insurance_oop' => $d['health_insurance_oop'],
            'health_insurance_dependents' => $d['health_insurance_dependents'],
            'tel' => $d['tel'],
            'household_tel' => $d['household_tel'],
            'phone' => $d['phone'],
            'office_tel' => $d['office_tel'],
            'office_tel_ext' => $d['office_tel_ext'],
            'office_fax' => $d['office_fax'],
            'contact_person' => $d['contact_person'],
            'contact_person_tel' => $d['contact_person_tel'],
            'birthday' => $d['birthday'],
            'blood_type' => $d['blood_type'],
            'education' => $d['education'],
            'education_department' => $d['education_department'],
            'service_area' => $d['service_area'],
            'office_address' => $d['office_address'],
            'address' => $d['address'],
            'household_address' => $d['household_address'],
            'email' => $d['email'],
            'disc_category' => $d['disc_category'],
            'certificates' => $d['certificates'],
            'insurance_certification' => $d['insurance_certification'],
            'history' => $d['history'],
            'note' => $d['note'],
        ]);

        wToast('修改完成');
        return redirect(Route('cms.user.profile', ['id' => $id]));

    }
}
