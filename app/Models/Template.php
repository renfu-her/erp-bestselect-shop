<?php

namespace App\Models;

use App\Helpers\IttmsDBB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class Template extends Model
{
    use HasFactory;
    protected $table = 'idx_template';
    protected $guarded = [];
    protected static $path_banner = 'idx_banner/';

    public static function storeNew(Request $request)
    {
        $request = self::validInputValue($request);
        $result = IttmsDBB::transaction(function () use ($request
        ) {
            $id = Template::create([
                'title' => $request->input('title')
                , 'group_id' => $request->input('group_id')
                , 'style_type' => $request->input('style_type')
                , 'is_public' => $request->input('is_public') ?? 1,
            ])->id;
            $d = $request->all();
            for ($i = 0; $i < 3; $i++) {
                if (isset($d['group_id' . $i])) {
                    DB::table('idx_template_child')->insert([
                        'template_id' => $id,
                        'group_id' => $d['group_id' . $i],
                    ]);
                }
            }

            return ['success' => 1, 'id' => $id];
        });
        return $result['id'] ?? null;
    }

    public static function validInputValue(Request $request)
    {
        $request->validate([
            'title' => 'required|string'
            , 'group_id' => 'required|numeric'
            , 'style_type' => 'required|numeric',
        ]);
        return $request;
    }

    public static function updateData(Request $request, int $id)
    {
        $request = self::validInputValue($request);
        $data = Template::where('id', '=', $id);
        $dataGet = $data->get()->first();
        $result = IttmsDBB::transaction(function () use ($request, $id, $dataGet
        ) {
            if (null != $dataGet) {
                $updateData = [
                    'title' => $request->input('title')
                    , 'group_id' => $request->input('group_id')
                    , 'style_type' => $request->input('style_type')
                    , 'is_public' => $request->input('is_public') ?? 1,
                ];

                Template::where('id', '=', $id)
                    ->update($updateData);

                $d = $request->all();
                
                for ($i = 0; $i < 3; $i++) {
                    $file = self::uploadFile($request, 'file' . $i, $id);
                  
                    $uploadData = [];
                    if ($file) {
                        $uploadData['file'] = $file;
                    }
                    if ($d['id' . $i]) {
                        
                        $uploadData['group_id'] = $d['group_id' . $i];
                    
                        DB::table('idx_template_child')->where('id', $d['id' . $i])
                            ->update($uploadData);
                    } else {
                        if (isset($d['group_id' . $i])) {
                            $uploadData['group_id'] = $d['group_id' . $i];
                            $uploadData['template_id'] = $id;
                            DB::table('idx_template_child')
                                ->insert($uploadData);
                        }
                    }
                }

            }

            return ['success' => 1, 'id' => $id];
        });
        return $result['id'] ?? null;
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
                $condtion = $condtion . ' when ' . $id . ' then ' . $sort;
            }
            DB::update('update idx_template set sort = case id'
                . $condtion
                . ' end where id in (' . $template_ids . ')'
            );
        }
    }

    public static function getList($is_public = null)
    {
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

    public static function getListWithWeb($is_public = null)
    {
        $result = DB::table('idx_template as template')
            ->select(
                'template.title',
                //    DB::raw('concat("' . FrontendApiUrl::collection() . '") as event'),
                'template.group_id as collection_id',
                'template.style_type as type',
                // 'template.sort',
            )->orderBy('template.sort');
        if ($is_public) {
            $result->where('template.is_public', '=', $is_public);
        }
        return $result;
    }

    public static function childList($template_id)
    {
        return DB::table('idx_template_child')->where('template_id', $template_id);
    }

    private static function uploadFile($request, $filename, $id)
    {
        if (!$request->hasfile($filename)) {
            return false;
        }

        $re = false;
       
        $img = Image::make($request->file($filename)->path())
            ->resize(1360, 453)->encode('webp', 50);

        $fileHashName = $request->file($filename)->hashName();
        $filename = self::$path_banner . $id . '/' . explode('.', $fileHashName)[0] . ".webp";

        if (Storage::disk('local')->put($filename, $img)) {
            $re = $filename;
        }

        return $filename;
    }

}
