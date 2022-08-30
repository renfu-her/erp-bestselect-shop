<?php

namespace Database\Seeders;

use App\Models\PermissionGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PageAuthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $r = include 'pageAuths.php';
        $guard = 'user';
        foreach ($r as $v) {
            $group = PermissionGroup::where('title', $v['unit'])
                ->where('guard_name', $guard)->get()->first();
            if (!$group) {
                $id = PermissionGroup::create(['title' => $v['unit'], 'guard_name' =>  $guard])->id;
            } else {
                $id = $group->id;
            }

            foreach ($v['permissions'] as $p) {
                $per = Permission::where('guard_name', $guard)
                    ->where('name', $p[0])
                    ->get()->first();
                if (!$per) {
                    Permission::create(['guard_name' => $guard, 'name' => $p[0], 'title' => $p[1], 'group_id' => $id]);
                   // echo $key.":".$p[0].":".$p[1] . " done!\n";
                }

            }
        }
        $this->delPermission($guard);
    }

    //刪除原本已加入 但後面欲刪除的權限 一併刪除對應角色和人員的欲刪除權限
    private function delPermission($guard) {
        $permission_to_del = include 'pageAuthsToDel.php';
        foreach ($permission_to_del as $val_del) {
            $group = PermissionGroup::where('title', $val_del['unit'])
                ->where('guard_name', $guard)->get()->first();
            if (isset($group)) {
                foreach ($val_del['permissions'] as $per) {
                    $permission = Permission::where('guard_name', '=', $guard)->where('name', '=', $per[0])->where('group_id', '=', $group->id);
                    $permission_get = $permission->first();
                    if (isset($permission_get)) {
                        $permission->delete();
                        DB::table('per_role_has_permissions')->where('permission_id', '=', $permission_get->id)->delete();
                        DB::table('per_model_has_permissions')->where('permission_id', '=', $permission_get->id)->delete();
                    }
                }
            }
        }
    }
}
