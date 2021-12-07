<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role as ModelsRole;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends ModelsRole
{
    use HasFactory,SoftDeletes;
    protected $table = 'per_roles';

    public static function roleList($guard_name = "admin", $company_id = null)
    {
        $union = self::where('guard_name', '=', $guard_name)->where("name", '=', "Super Admin")
            ->select('id', 'name', 'title');

        return self::where('guard_name', '=', $guard_name)
            ->where("company_id", '=', $company_id)
            ->where("name", "<>", "Super Admin")
            ->union($union)
            ->select('id', 'name', 'title')
            ->orderBy('id', 'ASC')
            ->get();
    }

    public static function getUserRoles($user_id, $guard, callable $callback = null)
    {

        switch ($guard) {
            case "user":
                $model_type = "App\Models\User";
                break;
            default:
                $model_type = "App\Models\Admin";
        }

        $re = DB::table('per_model_has_roles as mr')
            ->where('model_type', '=', $model_type)
            ->where('model_id', '=', $user_id)
            ->get()->toArray();
//        var_dump($re);

        if ($callback) {
            return $callback($re);
        }

        return $re;
    }

    public static function updateUserRoles($user_id, $guard, $role_ids = [])
    {

        switch ($guard) {
            case "user":
                $model_type = "App\Models\User";
                break;
            default:
                $model_type = "App\Models\Admin";
        }

        DB::table('per_model_has_roles')
            ->where('model_type', '=', $model_type)
            ->where('model_id', '=', $user_id)
            ->delete();

        $model_type::where('id', '=', $user_id)->get()->first()
            ->assignRole($role_ids);
    }

    public static function createRole($name, $guard, $permission_id = [], $company_id = null)
    {
        return DB::transaction(function () use ($name, $guard, $permission_id, $company_id) {

            $role_no = str_pad((self::where('company_id', '=', $company_id)
                    ->withTrashed()
                    ->get()
                    ->count()) + 1, 4, '0', STR_PAD_LEFT);

            if ($company_id) {
                $role_no = "$role_no@$company_id";
            } else {
                $role_no = "$role_no@admin";
            }

            $re = Role::create([
                'guard_name' => $guard,
                'name' => $role_no,
                'title' => $name,
                'company_id' => $company_id,
            ]);

            $re->givePermissionTo($permission_id);
        });
    }

    public static function updateRoleAndPermission($id, $name, $permission_id = [])
    {
        Role::where('id', '=', $id)->update(['name' => $name, 'title' => $name]);
        DB::table('per_role_has_permissions')->where('role_id', '=', $id)->delete();
        Role::where('id', '=', $id)->get()->first()->givePermissionTo($permission_id);
    }

    public static function getRolePermissions($role_id, callable $callback = null)
    {
        $re = DB::table('per_role_has_permissions')->where('role_id', '=', $role_id)->get()->toArray();

        if ($callback) {
            return $callback($re);
        }

        return $re;
    }

    public static function delRole($role_id)
    {
        $role = self::where('id', '=', $role_id)->get()->first();
        if ($role->name == 'Super Admin') {
            return;
        }
        $role->revokePermissionTo($role->permissions);
        $role->delete();
    }

    public static function roleOption($guard_name)
    {
        return self::where('guard_name', '=', $guard_name)
                    ->select('title')
                    ->get();
    }

    public static function getRoleTitle($guard_name, $role_id)
    {
        return self::where('guard_name', '=', $guard_name)
                    ->where('id', '=', $role_id)
                    ->select('title')
                    ->get()
                    ->toArray();
    }
}
