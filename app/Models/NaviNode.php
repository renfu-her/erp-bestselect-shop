<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NaviNode extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'idx_navi_node';

    public $timestamps = false;

    public static function createNode($parent_id, $title, $url = null, $group_id = null, $has_child = 0, $type = "url", $target = "_self")
    {
        $data = ['title' => $title,
            'parent_id' => $parent_id,
            'type' => $type,
            'url' => $url,
            'group_id' => $group_id,
            'has_child' => $has_child,
            'target' => $target,
        ];
        
        if ($group_id) {
            $gData = Collection::where('id', $group_id)->select('url')->get()->first();    
            if (!$gData) {
                return ['success' => 0, 'error_msg' => "群組ID無效"];
            }
            $data['type'] = "group";
            $data['url'] = $gData->url;
        }

        if ($parent_id == 0) {
            $data['level'] = 1;
        } else {
            $parent = self::where('id', $parent_id)->select('level', 'has_child')->get()->first();
            if (!$parent || $parent->has_child == 0 || $parent->level == 3) {
                return ['success' => 0, 'error_msg' => "新增失敗"];
            }
            $level = $parent->level + 1;
            $data['level'] = $level;
        }

        $id = self::create($data)->id;

        return ['success' => 1, 'id' => $id];
    }

    public static function tree($id = 0)
    {
        $re = DB::table('idx_navi_node as lv1')
            ->leftJoin('idx_navi_node as lv2', 'lv1.id', '=', 'lv2.parent_id')
            ->leftJoin('idx_navi_node as lv3', 'lv2.id', '=', 'lv3.parent_id')
            ->select('lv1.id as lv1_id',
                'lv1.title as lv1_title',
                'lv1.url as lv1_url',
                'lv1.target as lv1_target',
                'lv1.has_child as lv1_has_child',
                'lv2.id as lv2_id',
                'lv2.title as lv2_title',
                'lv2.url as lv2_url',
                'lv2.target as lv2_target',
                'lv2.has_child as lv2_has_child',
                'lv3.id as lv3_id',
                'lv3.title as lv3_title',
                'lv3.url as lv3_url',
                'lv3.target as lv3_target',
                'lv3.has_child as lv3_has_child')
            ->where('lv1.parent_id', $id)
            ->orderBy('lv1.sort')
            ->orderBy('lv2.sort')
            ->orderBy('lv3.sort')
            ->get()->toArray();
        //  dd($re);
        $tree = [];
        $ids = [];
        foreach ($re as $key => $value) {
            if (!isset($tree[$value->lv1_id])) {
                $tree[$value->lv1_id] = self::_getValue($value, 1);
                $ids[] = $value->lv1_id;
            }

            if ($value->lv2_id) {
                if (!isset($tree[$value->lv1_id]['child'][$value->lv2_id])) {
                    $tree[$value->lv1_id]['child'][$value->lv2_id] = self::_getValue($value, 2);
                    $ids[] = $value->lv2_id;
                }

                if ($value->lv3_id) {
                    if (!isset($tree[$value->lv1_id]['child'][$value->lv2_id]['child'][$value->lv3_id])) {
                        $tree[$value->lv1_id]['child'][$value->lv2_id]['child'][$value->lv3_id] = self::_getValue($value, 3);
                        $ids[] = $value->lv3_id;
                    }
                }
            }
        }

        $tree = self::_array_values($tree);

        return ['ids' => $ids, 'tree' => $tree];
    }

    private static function _getValue($v, $level = 1)
    {
        $re = ['id' => $v->{"lv" . $level . "_id"},
            'title' => $v->{"lv" . $level . "_title"},
        ];

        if ($v->{"lv" . $level . "_has_child"} == 0) {
            $re['url'] = $v->{"lv" . $level . "_url"};
            $re['target'] = $v->{"lv" . $level . "_target"};
        } else {
            $re['child'] = [];
        }

        return $re;
    }

    private static function _array_values($arr)
    {
        foreach ($arr as $key => $value) {
            //  dd($value);
            if (isset($value['child'])) {
                $arr[$key]['child'] = self::_array_values($value['child']);
                unset($arr[$key]['target']);
                unset($arr[$key]['url']);
            }
        }
        return array_values($arr);
    }

    public static function deleteNode($id)
    {
        self::where('id', $id)->delete();
        $tree = self::tree($id);
        if ($tree['ids']) {
            self::whereIn('id', $tree['ids'])->delete();
        }
    }

}
