<?php

namespace App\Models;

use App\Enums\Globals\FrontendApiUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Template extends Model
{
    use HasFactory;
    protected $table = 'idx_template';
    protected $guarded = [];

    public static function storeNew(Request $request)
    {
        $request = self::validInputValue($request);
        return DB::transaction(function () use ($request
        ) {
            $id = Template::create([
                'title' => $request->input('title')
                , 'group_id' => $request->input('group_id')
                , 'style_type' => $request->input('style_type')
                , 'is_public' => $request->input('is_public')?? 1
            ])->id;
            return $id;
        });
    }

    public static function validInputValue(Request $request) {
        $request->validate([
            'title' => 'required|string'
            , 'group_id' => 'required|numeric'
            , 'style_type' => 'required|numeric'
        ]);
        return $request;
    }

    public static function updateData(Request $request, int $id)
    {
        $request = self::validInputValue($request);
        $data = Template::where('id', '=', $id);
        $dataGet = $data->get()->first();
        return DB::transaction(function () use ($request, $id, $dataGet
        ) {
            if (null != $dataGet) {
                $updateData = [
                    'title' => $request->input('title')
                    , 'group_id' => $request->input('group_id')
                    , 'style_type' => $request->input('style_type')
                    , 'is_public' => $request->input('is_public')?? 1
                ];

                Template::where('id', '=', $id)
                    ->update($updateData);
            }

            return $id;
        });
    }

    public static function destroyById(int $id)
    {
        Template::destroy($id);
    }

    public static function sort(Request $request)
    {
        $req_template_ids = $request->input('template_id');
        if (isset($req_template_ids) && 0 < count($req_template_ids)) {
            $template_ids = implode(',', $req_template_ids);
            $condtion = '';
            foreach ($req_template_ids as $sort => $id) {
                $condtion = $condtion. ' when '. $id. ' then '. $sort;
            }
            DB::update('update idx_template set sort = case id'
                . $condtion
                . ' end where id in ('. $template_ids. ')'
            );
        }
    }

    public static function getList($is_public = null) {
        $result = DB::table('idx_template as template')
            ->select(
                'template.id',
                'template.title',
                'template.group_id',
                'template.style_type',
                'template.is_public',
                'template.sort',
            );
        if ($is_public) {
            $result->where('template.is_public', '=', $is_public);
        }
        return $result;
    }

    public static function getListWithWeb($is_public = null) {
        $groupDoman = frontendUrl(FrontendApiUrl::collection());
        $result = DB::table('idx_template as template')
            ->select(
                'template.id',
                'template.title',
                'template.group_id',
                'template.style_type',
                'template.is_public',
                'template.sort',
            );
        if ($is_public) {
            $result->where('template.is_public', '=', $is_public);
        }
        return $result;
    }
}
