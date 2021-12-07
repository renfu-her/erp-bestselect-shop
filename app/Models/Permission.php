<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Permission extends Model
{
    use HasFactory;
    protected $table = 'per_permissions';
    protected $guarded = [];

    static function getPermissionGroups($guard_name = 'admin')
    {
        $sub =  DB::table('per_permissions as ps')
            ->select('ps.group_id')
            ->selectRaw('GROUP_CONCAT("{\\"id\\":",ps.id,",\\"title\\":","\\"",ps.title,"\\"","}") as permissions')
            ->groupBy('ps.group_id');

        $re = DB::table('per_permission_groups as group')
            ->join(DB::raw("({$sub->toSql()}) as permissions"), function ($join) {
                $join->on('group.id', '=', 'permissions.group_id');
            })
            ->where("group.guard_name", '=', $guard_name)
            ->select('id', 'title', 'permissions')
            ->mergeBindings($sub)->get();


        foreach ($re as $key => $v) {
            $re[$key]->permissions = json_decode("[" . $v->permissions . "]");
        }

        return $re;
    }

    static function updatePermissions($id, $guard = null, $per = [])
    {
        switch ($guard) {
            case "user":
                $model_type = "App\Models\User";
                break;
            default:
                $model_type = "App\Models\Admin";
        }


        DB::table('per_model_has_permissions')
            ->where('model_id', '=', $id)
            ->where('model_type', '=', $model_type)
            ->delete();

        
    //    dd($per);
        $model_type::where('id', '=', $id)->get()->first()->givePermissionTo($per);
    }

    static function getPermissions($id, $guard, callable $callback = null)
    {

        switch ($guard) {
            case "user":
                $model_type = "App\Models\User";
                break;
            default:
                $model_type = "App\Models\Admin";
        }

        $re = DB::table('per_model_has_permissions')
            ->select('permission_id as id')
            ->where('model_id', '=', $id)
            ->where('model_type', '=', $model_type)
            ->get()->toArray();

        if ($callback) {
            return $callback($re);
        }

        return $re;
    }
}
