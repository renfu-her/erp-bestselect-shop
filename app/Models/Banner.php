<?php

namespace App\Models;

use App\Enums\Globals\FrontendApiUrl;
use App\Enums\Globals\LinkType;
use App\Helpers\IttmsDBB;
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
        $result = IttmsDBB::transaction(function () use ($request
        ) {
            $id = Banner::create([
                'title' => $request->input('title')
                , 'event_type' => $request->input('event_type')
                , 'event_id' => $request->input('event_id')
                , 'event_url' => $request->input('event_url')
                , 'target' => $request->input('target')
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

            return ['success' => 1, 'id' => $id];
        });
        return $result['id'] ?? null;
    }

    public static function validInputValue(Request $request) {
        $request->validate([
            'title' => 'required|string'
            , 'event_type' => 'required|string'
//            , 'event_id' => 'required_without:event_url|numeric'
//            , 'event_url' => 'required_without:event_id|string'
            , 'img_pc' => 'max:10000|mimes:jpg,jpeg,png,bmp'
            , 'img_phone' => 'max:10000|mimes:jpg,jpeg,png,bmp'
            , 'target' => 'required|string'
            , 'is_public' => 'required|numeric'
        ]);

        $event_type = $request->input('event_type');
        $event_id = $request->input('event_id');
        $event_url = $request->input('event_url');
        if (!FrontendApiUrl::hasKey($event_type)) {
            throw ValidationException::withMessages(['event_error' => '未選擇類型']);
        }
        if (FrontendApiUrl::collection()->key == $event_type) {
            $request->merge(['event_url' => null]);
            if (null == $event_id) {
                throw ValidationException::withMessages(['event_error' => '類型為群組，但未選擇群組']);
            }
        } else if (FrontendApiUrl::url()->key == $event_type) {
            $request->merge(['event_id' => null]);
            if (null == $event_url) {
                throw ValidationException::withMessages(['event_error' => '類型為連結，但未輸入連結']);
            }
        } else if (FrontendApiUrl::product()->key == $event_type) {
            $request->merge(['event_url' => null]);
            if (null == $event_id) {
                throw ValidationException::withMessages(['event_error' => '類型為商品，但未選擇商品']);
            }
        }
        return $request;
    }

    public static function updateBanner(Request $request, int $id, $is_del_old_img = false)
    {
        $request = self::validInputValue($request);
        $bannerData = Banner::where('id', '=', $id);
        $bannerDataGet = $bannerData->get()->first();
        return IttmsDBB::transaction(function () use ($request, $id, $bannerDataGet, $is_del_old_img
        ) {
            if (null != $bannerDataGet) {

                //img_pc = '有'  then 刪除原有、更新圖片
                //img_pc = '' && del_img_pc = del then 刪除圖片
                //img_pc = '' && del_img_pc = '' then 不動
                $imgData_Pc = null;
                if ($request->has('img_pc')) {
                    Storage::delete($bannerDataGet->img_pc);
                    $imgData_Pc = $request->file('img_pc')->store(Banner::$path_banner.$id);
                } else if (true == $is_del_old_img) {
                    Storage::delete($bannerDataGet->img_pc);
                } else {
                    $imgData_Pc = $bannerDataGet->img_pc;
                }

                $imgData_Phone = null;
                if ($request->hasfile('img_phone')) {
                    Storage::delete($bannerDataGet->img_phone);
                    $imgData_Phone = $request->file('img_phone')->store(Banner::$path_banner.$id);
                } else if (true == $is_del_old_img) {
                    Storage::delete($bannerDataGet->img_phone);
                } else {
                    $imgData_Phone = $bannerDataGet->img_phone;
                }

                $updateData = [
                    'title' => $request->input('title')
                    , 'event_type' => $request->input('event_type')
                    , 'event_id' => $request->input('event_id')
                    , 'target' => $request->input('target')
                    , 'is_public' => $request->input('is_public')
                ];
                $updateData = Banner::setValueToArr($updateData, 'event_url', $request->input('event_url'));
                $updateData = Banner::setValueToArr($updateData, 'img_pc', $imgData_Pc);
                $updateData = Banner::setValueToArr($updateData, 'img_phone', $imgData_Phone);

                Banner::where('id', '=', $id)
                    ->update($updateData);
            }

            return ['success' => 1, 'id' => $id];
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

    public static function getList($is_public = null) {
        $result = DB::table('idx_banner as banner')
            ->select(
                'banner.id',
                'banner.title',
                'banner.event_type',
                'banner.event_id',
                'banner.event_url',
                'banner.img_pc',
                'banner.target',
                'banner.is_public',
                'banner.sort'
            );
        if ($is_public) {
            $result->where('banner.is_public', '=', $is_public);
        }
        return $result;
    }

    /**
     * @param $is_public bool 是否公開顯示橫幅廣告區塊
     * API回傳banner橫幅廣告區塊資訊
     * @return \Illuminate\Database\Query\Builder
     */
    public static function getListWithWeb($is_public = null)
    {
        // 回傳群組：群組id
        // 連結完整的URL
        $queryCase_link = 'case
            when event_type = "collection"
                then banner.event_id
            when event_type = "product"
                then products.sku
            when event_type = "url"
                then banner.event_url
            else null
            end';

        $result = DB::table('idx_banner as banner')
            ->leftJoin('collection', function ($join) {
                $join->on('collection.id', '=', 'banner.event_id')
                    ->where('collection.is_public', '=', 1)
                    ->where('banner.event_type', '=', FrontendApiUrl::collection);
            })
            ->leftJoin('prd_products as products', function ($join) {
                $join->on('products.id', '=', 'banner.event_id');
                $join->where('banner.event_type', '=', FrontendApiUrl::product);
            })
            ->select(
                'banner.title',
                'banner.target',
                'banner.event_type as type'
            )
            ->selectRaw(
                'IFNULL(banner.img_pc, "") as src'
            )
            ->selectRaw('('. $queryCase_link . ') as type_value');
        if ($is_public) {
            $result->where('banner.is_public', '=', $is_public);
        }
        return $result;
    }
}
