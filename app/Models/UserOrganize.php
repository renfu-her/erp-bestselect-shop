<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class UserOrganize extends Model
{
    use HasFactory;
    protected $table = 'usr_user_organize';
    protected $guarded = [];
    public $timestamps = false;

    public static function initData()
    {
        $root_title = '喜鴻購物';
        $url = "https://www.besttour.com.tw/api/empdep.asp?type=6";
        $re = Http::get($url)->json();

        $r_id = self::create(['title' => $root_title,
            'level' => 1])->id;

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

        self::rebuild_tree(1,1);
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

}
