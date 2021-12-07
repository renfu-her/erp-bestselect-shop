<?php

namespace Database\Seeders;

use App\Models\PermissionGroup;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class permissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $r = include 'pageAuths.php';

        //  Permission::create(['guard_name' => 'admin', 'name' => $p[0],'title'=>$p[1],'group_id'=> $id]);
        $guard = 'user';

        foreach ($r as $v) {
            $id = PermissionGroup::create(['title' => $v['unit'], 'guard_name' => $guard])->id;
            foreach ($v['permissions'] as $p) {
                Permission::create(['guard_name' => $guard, 'name' => $p[0], 'title' => $p[1], 'group_id' => $id]);
            }
        }
        Role::create(['guard_name' => $guard, 'name' => 'Super Admin', 'title' => '超級管理員']);

       

        //  $permission = Permission::create(['guard_name' => 'admin', 'name' => 'publish articles','title'=>'發佈']);

    }
}
