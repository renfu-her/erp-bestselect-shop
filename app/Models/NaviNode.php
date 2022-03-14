<?php

namespace App\Models;

use App\Enums\Globals\FrontendApiUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NaviNode extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'idx_navi_node';

    public $timestamps = false;

    public static function nodeList($parent_id)
    {
        return DB::table('idx_navi_node as node')
            ->select(
                'node.id as id',
                'node.title as node_title',
                'node.url as url',
                'node.sort as sort',
                'node.level as level',
                'node.has_child as has_child',
                'col.name as group_title')
            ->leftJoin('collection as col', 'node.event_id', '=', 'col.id')
            ->where('node.parent_id', $parent_id)
            ->orderBy('node.sort');
    }

    public static function forBreadcrumb($level)
    {
        $arrLevel = explode('-', $level);

        $re = DB::table("idx_navi_node as lv0")->where('lv0.id', end($arrLevel))
            ->select('lv0.title as lv0_title', 'lv0.id as lv0_id');
        for ($i = 1; $i < count($arrLevel) - 1; $i++) {
            $re->leftJoin("idx_navi_node as lv" . ($i), 'lv' . ($i - 1) . '.parent_id', '=', 'lv' . ($i) . '.id')
                ->addSelect("lv$i.title as lv{$i}_title")
                ->addSelect("lv$i.id as lv{$i}_id");
        }

        $re = $re->get()->first();

        $b = [];
        $path = '';
        for ($i = count($arrLevel) - 2; $i >= 0; $i--) {
            // dd($i);
            $path = $path . "-" . $re->{"lv{$i}_id"};
            $b[] = [
                'id' => $re->{"lv{$i}_id"},
                'title' => $re->{"lv{$i}_title"},
                'path' => '0' . $path,
            ];

        }

        return $b;
    }

    public static function createNode($parent_id, $title, $url = null, $event_id = null, $has_child = 0, $event = "url", $target = "_self")
    {
        $data = ['title' => $title,
            'has_child' => $has_child,
            'parent_id' => $parent_id,
            'event' => null,
            'url' => null,
            'event_id' => null,
            'target' => null,
        ];

        if ($has_child == 0) {
            $re = self::hasChildProcess($data, $event, $url, $event_id, $target);
            if ($re) {
                return $re;
            }
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
        self::cacheProcess();
        return ['success' => 1, 'id' => $id];
    }

    public static function updateNode($id, $title, $url = null, $event_id = null, $has_child = 0, $event = "url", $target = "_self")
    {
        $data = ['title' => $title,
            'has_child' => $has_child,
            'event' => null,
            'url' => null,
            'event_id' => null,
            'target' => null,
        ];

        if ($has_child == 0) {
            $re = self::hasChildProcess($data, $event, $url, $event_id, $target);
            if ($re) {
                return $re;
            }
        }

        $id = self::where('id', $id)->update($data);
        self::cacheProcess();

        return ['success' => 1];
    }

    private static function hasChildProcess(&$data, $event, $url, $event_id, $target)
    {
        switch ($event) {
            case 'url':
                $data['url'] = $url;
                $data['event_id'] = null;
                break;
            case 'group':
                $gData = Collection::where('id', $event_id)->select('url', 'name')->get()->first();
                if (!$gData) {
                    return ['success' => 0, 'error_msg' => "群組ID無效"];
                }
                $data['url'] = $gData->url;
                $data['event_id'] = $event_id;
                $data['sub_title'] = $gData->name;
                break;
        }

        $data['event'] = $event;
        $data['target'] = $target;
        return null;
    }

    public static function tree($id = 0)
    {
        $sub = DB::table('idx_navi_node as n')
            ->leftJoin('collection as c', 'n.event_id', '=', 'c.id')
            ->select('n.*', 'c.name as event_title');

        $re = DB::table(DB::raw("({$sub->toSql()}) as lv1"))
            ->leftJoin(DB::raw("({$sub->toSql()}) as lv2"), function ($join) {
                $join->on('lv1.id', '=', 'lv2.parent_id');
            })
            ->leftJoin(DB::raw("({$sub->toSql()}) as lv3"), function ($join) {
                $join->on('lv2.id', '=', 'lv3.parent_id');
            });

        for ($i = 1; $i < 4; $i++) {
            $re->addSelect('lv' . $i . '.id as lv' . $i . '_id');
            $re->addSelect('lv' . $i . '.title as lv' . $i . '_title');
            $re->addSelect('lv' . $i . '.url as lv' . $i . '_url');
            $re->addSelect('lv' . $i . '.target as lv' . $i . '_target');
            $re->addSelect('lv' . $i . '.has_child as lv' . $i . '_has_child');
            $re->addSelect('lv' . $i . '.event_id as lv' . $i . '_event_id');
            $re->addSelect('lv' . $i . '.event as lv' . $i . '_event');
            $re->addSelect('lv' . $i . '.level as lv' . $i . '_level');
            $re->addSelect('lv' . $i . '.event_title as lv' . $i . '_event_title');
            $re->addSelect('lv' . $i . '.sub_title as lv' . $i . '_sub_title');
        }

        $re = $re->where('lv1.parent_id', $id)
            ->orderBy('lv1.sort')
            ->orderBy('lv2.sort')
            ->orderBy('lv3.sort')
            ->mergeBindings($sub)
            ->get()->toArray();

        
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
        $re = [
            'id' => $v->{"lv" . $level . "_id"},
            'title' => $v->{"lv" . $level . "_title"},
            'sub_title' => $v->{"lv" . $level . "_sub_title"},
            'event' => $v->{"lv" . $level . "_event"},
            'level' => $v->{"lv" . $level . "_level"},
        ];



        if ($v->{"lv" . $level . "_has_child"} == 0) {
            if ($v->{"lv" . $level . "_event_id"}) {
                $re['event_id'] = $v->{"lv" . $level . "_event_id"};
                $re['url'] = FrontendApiUrl::collection() . "/" . ($v->{"lv" . $level . "_event_id"}) . "/" . ($v->{"lv" . $level . "_event_title"});
            } else {
                $re['event_id'] = null;
                $re['url'] = $v->{"lv" . $level . "_url"};
            }
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
        self::cacheProcess();

    }

    public static function sort($sorts = null)
    {

        $sorts = implode(',', array_map(function ($n, $k) {
            return "(" . $n . "," . ($k * 10) . ")";
        }, $sorts, array_keys($sorts)));

        DB::select("INSERT INTO idx_navi_node
        (id, sort)
        VALUES $sorts
        ON DUPLICATE KEY UPDATE
            sort = VALUES(sort)");

        self::cacheProcess();

    }

    public static function updateMultiLevel($childs, $parent_id = 0, $level = 0)
    {

        $level++;
        foreach ($childs as $key => $child) {

            self::where('id', $child->id)->update([
                'parent_id' => $parent_id,
                'level' => $level,
                'sort' => $key * 10,
            ]);

            if (isset($child->child)) {

                self::updateMultiLevel($child->child, $child->id, $child->level);
            }

        }
    }

    public static function cacheProcess()
    {
        Cache::put('tree', (self::tree())['tree']);
    }

}
