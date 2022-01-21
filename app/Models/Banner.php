<?php

namespace App\Models;

use App\Enums\Homepage\BannerEventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class Banner extends Model
{
    use HasFactory;
    protected $table = 'idx_banner';
    protected $guarded = [];

    protected static $path_banner = 'idx_banner/';

    public static function storeNewBanner(Request $request)
    {
        $request = self::validInputValue($request);
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
                $imgData_Pc = $request->file('img_pc')->store(self::$path_banner.$id);
            }

            $imgData_Phone = null;
            if ($request->hasfile('img_phone')) {
                $imgData_Phone = $request->file('img_phone')->store(self::$path_banner.$id);
            }
            Banner::where('id', '=', $id)
                ->update([
                    'img_pc' => $imgData_Pc
                    , 'img_phone' => $imgData_Phone
                ]);

            return $id;
        });
    }

    public static function validInputValue(Request $request) {
        $request->validate([
            'title' => 'required|string'
            , 'event_type' => 'required|string'
//            , 'event_id' => 'required_without:event_url|numeric'
//            , 'event_url' => 'required_without:event_id|string'
            , 'img_pc' => 'max:10000|mimes:jpg,jpeg,png,bmp'
            , 'img_phone' => 'max:10000|mimes:jpg,jpeg,png,bmp'
            , 'is_public' => 'required|numeric'
        ]);

        $event_type = $request->input('event_type');
        $event_id = $request->input('event_id');
        $event_url = $request->input('event_url');
        if (!BannerEventType::hasKey($event_type)) {
            throw ValidationException::withMessages(['event error' => '未選擇類型']);
        }
        if (BannerEventType::none()->value == $event_type) {
            $request->merge(['event_id' => null]);
            $request->merge(['event_url' => null]);
        } else if (BannerEventType::group()->value == $event_type) {
            $request->merge(['event_url' => null]);
            if (null == $event_id) {
                throw ValidationException::withMessages(['event error' => '類型為群組，但未選擇群組']);
            }
        } else if (BannerEventType::url()->value == $event_type) {
            $request->merge(['event_id' => null]);
            if (null == $event_url) {
                throw ValidationException::withMessages(['event error' => '類型為連結，但未輸入連結']);
            }
        }
        return $request;
    }

    public static function updateBanner(Request $request, int $id)
    {
        $request = self::validInputValue($request);
        return DB::transaction(function () use ($request, $id
        ) {
            $bannerData = Banner::where('id', '=', $id);
            $bannerDataGet = $bannerData->get()->first();
            if (null != $bannerDataGet) {
                Storage::delete($bannerDataGet->img_pc);
                Storage::delete($bannerDataGet->img_phone);

                $imgData_Pc = null;
                if ($request->has('img_pc')) {
                    $imgData_Pc = $request->file('img_pc')->store(Banner::$path_banner.$id);
                }

                $imgData_Phone = null;
                if ($request->hasfile('img_phone')) {
                    $imgData_Phone = $request->file('img_phone')->store(Banner::$path_banner.$id);
                }

                $updateData = [
                    'title' => $request->input('title')
                    , 'event_type' => $request->input('event_type')
                    , 'event_id' => $request->input('event_id')
                    , 'is_public' => $request->input('is_public')
                ];
                $updateData = Banner::setValueToArr($updateData, 'event_url', $request->input('event_url'));
                $updateData = Banner::setValueToArr($updateData, 'img_pc', $request->input('img_pc'));
                $updateData = Banner::setValueToArr($updateData, 'img_phone', $request->input('img_phone'));

                Banner::where('id', '=', $id)
                    ->update($updateData);
            }

            return $id;
        });
    }

    private static function setValueToArr($data, $key, $value) {
        //if (null != $data && null != $key && null != $value)
        {
            $data[$key] = $value;
        }
        return $data;
    }

    public static function destroyById(int $id)
    {
        Banner::destroy($id);
    }

    public static function sort(Request $request)
    {
        $req_banner_ids = $request->input('banner_id');
        if (isset($req_banner_ids) && 0 < count($req_banner_ids)) {
            $banner_ids = implode(',', $req_banner_ids);
            $condtion = '';
            foreach ($req_banner_ids as $sort => $id) {
                $condtion = $condtion. ' when '. $id. ' then '. $sort;
            }
            DB::update('update idx_banner set sort = case id'
                . $condtion
                . ' end where id in ('. $banner_ids. ')'
            );
        }
    }

    public static function getList() {
        return DB::table('idx_banner as banner');
    }
}
