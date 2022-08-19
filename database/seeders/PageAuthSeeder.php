<?php

namespace Database\Seeders;

use App\Models\PermissionGroup;
use Illuminate\Database\Seeder;
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

    }
}
