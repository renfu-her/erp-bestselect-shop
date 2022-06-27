<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GroupbyCompany extends Model
{
    use HasFactory;
    protected $table = 'usr_groupbuy_company';
    protected $guarded = [];

    public static function createMain($title, $is_active, $childs = [])
    {
        DB::beginTransaction();

        $id = self::create(['title' => $title, 'is_active' => $is_active])->id;
        $errors = [];
        foreach ($childs as $key => $value) {
            self::createChild($id, $value['title'], $value['code'], $value['active'], $key, $errors);
        }

        if (count($errors) > 0) {
            DB::rollBack();
            return ['success' => '0', 'errors' => $errors];
        }
        DB::commit();
        return ['success' => '1'];

        //   DB::rollBack();

    }

    public static function updateMain($id, $title, $is_active, $childs = [], $oChilds = [])
    {
        DB::beginTransaction();

        self::where('id', $id)->update(['title' => $title, 'is_active' => $is_active]);
        $errors = [];
        foreach ($childs as $key => $value) {
            self::createChild($id, $value['title'], $value['code'], $value['active'], $key, $errors);
        }

        foreach ($oChilds as $key => $value) {
            self::updateChild($value['id'], $value['title'], $value['code'], $value['active'], $key, $errors);
        }

        if (count($errors) > 0) {
            DB::rollBack();
            return ['success' => '0', 'errors' => $errors];
        }
        DB::commit();
        return ['success' => '1'];

    }

    public static function createChild($parent_id, $title, $code, $active, $idx, &$error)
    {
        if (self::where('code', $code)->get()->first()) {
            $error[] = ['index' => $idx, 'type' => 'n', 'message' => '代碼重複'];
        }

        self::create([
            'parent_id' => $parent_id,
            'title' => $title,
            'code' => $code,
            'is_active' => $active,
        ]);

    }

    public static function updateChild($id, $title, $code, $active, $idx, &$error)
    {
        if (self::where('code', $code)->where('id', '<>', $id)->get()->first()) {
            $error[] = ['index' => $idx, 'type' => 'o', 'message' => '代碼重複'];
        }

        self::where('id', $id)->update([
            'title' => $title,
            'code' => $code,
            'is_active' => $active,
        ]);

    }

    public static function getData($code)
    {
        return DB::table('usr_groupbuy_company as gc1')
            ->leftJoin('usr_groupbuy_company as gc2', 'gc1.panret_id', '=', 'gc2.id')
            ->select('gc1.title as title', 'gc2.title as parent_title')
            ->where('gc1.code', $code);
    }

}
