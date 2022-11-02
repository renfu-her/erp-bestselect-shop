<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UserOrganize extends Model
{
    use HasFactory;
    protected $table = 'usr_user_organize';
    protected $guarded = [];
    public $timestamps = false;

    public static function dataList()
    {
        $re = DB::table('usr_user_organize as a')
            ->leftJoin('usr_user_organize as b', 'a.id', '=', 'b.parent')
            ->leftJoin('usr_users as user_a', 'user_a.id', '=', 'a.user_id')
            ->leftJoin('usr_users as user_b', 'user_b.id', '=', 'b.user_id')
            ->select(['a.id as department_id',
                'a.title as department_title',
                'b.id as group_id',
                'b.title as group_title',
                'user_a.name as a_name',
                'user_b.name as b_name'])
            ->where('a.parent', '1')->get();

        $output = [];

        foreach ($re as $key => $value) {
            if (!isset($output[$value->department_id])) {
                $output[$value->department_id] = (object) [
                    'department_id' => $value->department_id,
                    'department_title' => $value->department_title,
                    'a_name' => $value->a_name,
                    'group' => [],
                ];

            }

            $output[$value->department_id]->group[] = $value;
        }

        return $output;

    }

    public static function initData()
    {
        $root_title = '喜鴻購物';
        $url = "https://www.besttour.com.tw/api/empdep.asp?type=6";
        $re = Http::get($url)->json();

        $r_id = self::create(['title' => $root_title,
            'level' => 1])->id;

        //  DB::beginTransaction();

        foreach ($re as $dep1) {
            $d1_id = self::create(['title' => $dep1['dep1'],
                'parent' => $r_id,
                'level' => 2])->id;
            foreach ($dep1['dep2'] as $dep2) {
                self::create(['title' => $dep2['dep2'],
                    'parent' => $d1_id,
                    'level' => 3]);
            }
        }

        self::rebuild_tree(1, 1);
        //  DB::commit();
        // dd($re);
    }

    public static function rebuild_tree($parent, $left)
    {
        $right = $left + 1;
        //  dd($left,$right);
        $re = self::where('parent', $parent)->get();

        foreach ($re as $pp) {
            $right = self::rebuild_tree($pp->id, $right);
        }

        self::where('id', $parent)->update([
            'lft' => $left,
            'rgt' => $right,
        ]);

        return $right + 1;

    }

    public static function auditList($user_id)
    {   
        $user = User::where('id', $user_id)->get()->first();
        $ids = [];
        $audid = [];
        $no1 = user::where('account', '04001')->select('id', 'name', 'title')->get()->first();
        $audid[] = $no1;
        $ids[] = $no1->id;

        if($user_id ==  $no1->id ){
            return $audid;
        }

        $no2 = user::where('account', '00001')->select('id', 'name', 'title')->get()->first();
        $audid[] = $no2;
        $ids[] = $no2->id;

        if($user_id ==  $no2->id ){
            return $audid;
        }

        $group_admin = self::getDepartmentAdmin(3, $user->group);
        if ($group_admin && $group_admin->user_id != $user->id) {
            $audid[] = $group_admin;
            $ids[] = $group_admin->user_id;
        }

        $department_admin = self::getDepartmentAdmin(2, $user->department);
        if ($department_admin && $department_admin->user_id != $user->id && !in_array( $department_admin->user_id,$ids)) {
            $audid[] = $department_admin;
        }

        return $audid;

    }

    public static function getDepartmentAdmin($level_type, $group_title)
    {
        return DB::table('usr_user_organize as org')
            ->join('usr_users as user', 'org.user_id', '=', 'user.id')
            ->select('org.user_id', 'user.name', 'user.title')
            ->where('level', $level_type)
            ->where('org.title', $group_title)->get()->first();
    }

}
