<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PermissionGroup extends Model // deprecated
{
    use HasFactory;
    protected $table = 'per_permission_groups';
    protected $guarded = [];

    static function getPermissions($guard_name = "admin")
    {
        $sub =  DB::table('per_permissions as ps')
            ->select('ps.group_id')
            ->selectRaw('GROUP_CONCAT("{\\"id\\":",ps.id,",\\"title\\":","\\"",ps.title,"\\"",",\\"name\\":","\\"",ps.name,"\\"","}") as permissions')
            ->groupBy('ps.group_id');
        
        $re = DB::table('per_permission_groups as group')
            ->leftJoin(DB::raw("({$sub->toSql()}) as permissions"), function ($join) {
                $join->on('group.id', '=', 'permissions.group_id');
            })
            ->select('id', 'title', 'permissions')
            ->mergeBindings($sub)
            ->orderBy('group.id');

        if($guard_name){
            $re->where("group.guard_name", '=', $guard_name);
        }
 
        $re = $re->get();

        foreach ($re as $key => $v) {
            $re[$key]->permissions = json_decode("[" . $v->permissions . "]");
        }

        return $re;
    }

}
