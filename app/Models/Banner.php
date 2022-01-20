<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    use HasFactory;
    protected $table = 'idx_banner';
    protected $guarded = [];

    protected $path_banner = 'idx_banner/';

    public function storeNewBanner(Request $request)
    {
        $this->validInputValue($request);
        return DB::transaction(function () use ($request
        ) {
            $id = Banner::create([
                'title' => $request->input('title')
                , 'event_type' => $request->input('event_type')
                , 'event_id' => $request->input('event_id')
                , 'event_url' => $request->input('event_url')
                , 'is_public' => $request->input('is_public')
            ])->id;

            $imgData_Pc = null;
            if ($request->hasfile('img_pc')) {
                $imgData_Pc = $request->file('img_pc')->store($this->path_banner.$id);
            }

            $imgData_Phone = null;
            if ($request->hasfile('img_phone')) {
                $imgData_Phone = $request->file('img_phone')->store($this->path_banner.$id);
            }
            Banner::where('id', '=', $id)
                ->update([
                    'img_pc' => $imgData_Pc
                    , 'img_phone' => $imgData_Phone
                ]);

            return $id;
        });
    }

    public function validInputValue(Request $request) {
        $request->validate([
            'title' => 'required|string'
            , 'event_type' => 'required|string'
            , 'event_id' => 'required_without:event_url|numeric'
            , 'event_url' => 'required_without:event_id|string'
            , 'img_pc' => 'required|nullable|image'
            , 'img_phone' => 'required|nullable|image'
            , 'is_public' => 'required|numeric'
        ]);
    }

    public function updateBanner(Request $request, int $id)
    {
        $this->validInputValue($request);
        return DB::transaction(function () use ($request, $id
        ) {
            $bannerData = Banner::where('id', '=', $id);
            $bannerDataGet = $bannerData->get()->first();
            if (null != $bannerDataGet) {
                Storage::delete($bannerDataGet->img_pc);
                Storage::delete($bannerDataGet->img_phone);

                $imgData_Pc = null;
                if ($request->has('img_pc')) {
                    $imgData_Pc = $request->file('img_pc')->store($this->path_banner.$id);
                }

                $imgData_Phone = null;
                if ($request->hasfile('img_phone')) {
                    $imgData_Phone = $request->file('img_phone')->store($this->path_banner.$id);
                }

                Banner::where('id', '=', $id)
                    ->update([
                        'title' => $request->input('title')
                        , 'event_type' => $request->input('event_type')
                        , 'event_id' => $request->input('event_id')
                        , 'event_url' => $request->input('event_url')
                        , 'img_pc' => $imgData_Pc
                        , 'img_phone' => $imgData_Phone
                        , 'is_public' => $request->input('is_public')
                    ]);
            }

            return $id;
        });
    }

    public function destroyById(int $id)
    {
        Banner::destroy($id);
    }

    public static function getList() {
        return DB::table('idx_banner as banner');
    }
}
