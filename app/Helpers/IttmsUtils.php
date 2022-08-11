<?php
namespace App\Helpers;

use Illuminate\Support\Str;

class IttmsUtils {
    /*
    * 轉出包含查詢值的sql語法
    * https://stackoverflow.com/questions/27314506/laravel-how-to-get-query-with-bindings
    */
    public static function getEloquentSqlWithBindings($query) {
        $sqlQuery = Str::replaceArray(
            '?',
            collect($query->getBindings())
                ->map(function ($i) {
                    if (is_object($i)) {
                        $i = (string)$i;
                    }
                    return (is_string($i)) ? "'$i'" : $i;
                })->all(),
            $query->toSql());
        return $sqlQuery;
    }

    /**
     * 合併陣列 by key
     * 需先排好KEY順序
     * https://stackoverflow.com/a/15900096/2790722
     * @param $a1
     * @param $a2
     * @param $Ckey
     * @return array
     */
    public static function merge_array_common_key($a1, $a2, $Ckey) {
        $merge = array_merge($a1,$a2);
        $keys = array();
        foreach ($merge as $key => $value) {
            if(isset($keys[$value[$Ckey]])){
                $merge[$keys[$value[$Ckey]]] += $value;
                unset($merge[$key]);
                continue;
            }
            $keys[$value[$Ckey]] = $key;
        }
        return $merge;
    }

    /**
     * 設定分頁效果
     * @param $list
     * @param $request
     * @return mixed
     */
    public static function setPager($list, $request) {
        $d = $request->all();
        if (isset($list) && isset($d['page']) && isset($d['limit'])) {
            $limit = is_numeric($d['limit']) ? $d['limit'] : 10;
            $offset = (($d['page'] ?? 1) - 1) * $limit;
            $list = $list->offset($offset)->limit($limit);
        }
        return $list;
    }
}
