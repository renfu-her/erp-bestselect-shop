<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Addr extends Model
{
    use HasFactory;
    protected $table = 'loc_addr';
    public $timestamps = false;

    protected $fillable = [
        'title',
        'zipcode',
        'parent_id',
        'service_area_id',
    ];

    /**
     * @param String|Null $can_service null:列出所有 "charter":列出包車終點地址,"service":列出可服務區域(預設)
     * @param Array|Null $start_city_id null:列出所有 ['start_city_id' => {起始地點},'car_type' => {車型},'temp_id' => {溫層}]
     */

    public static function getCitys($can_service = null, $options = null)
    {
        if (!$can_service) {
            return self::whereNull('parent_id')->select('id as city_id', 'title as city_title')->get()->toArray();
        }

        switch ($can_service) {
            case 'charter':
                $re = DB::table('prd_charter_products as cp')
                    ->join('loc_addr as addr', 'cp.end_city_id', '=', 'addr.id')
                    ->select('addr.id as city_id', 'addr.title as city_title', 'cp.start_city_id', 'cp.end_city_id',
                        'cp.price',
                        'cp.car_type')
                    ->distinct();

                if ($options) {
                    if (isset($options['company_id']) && $options['company_id']) {
                        $re->where('cp.company_id', $options['company_id']);
                    }

                    if (isset($options['start_city_id']) && $options['start_city_id']) {
                        $re->where('cp.start_city_id', $options['start_city_id']);
                    }

                    if (isset($options['car_type']) && !is_null($options['car_type'])) {
                        $re->where('cp.car_type', $options['car_type']);
                    }

                    if (isset($options['temp_id']) && $options['temp_id']) {
                        $re->where('cp.temp_id', $options['temp_id']);
                    }

                }

                break;

            default:
                $sub = DB::table('loc_addr as a1')
                    ->join('loc_service_areas as area', 'area.id', '=', 'a1.service_area_id')
                    ->select('city_id')
                    ->distinct();

                $re = DB::table('loc_addr as addr')
                    ->join(DB::raw("({$sub->toSql()}) as citys"), function ($join) {
                        $join->on('citys.city_id', '=', 'addr.id');
                    })
                    ->select('addr.id as city_id', 'addr.title as city_title')
                    ->mergeBindings($sub);
        }

        return json_decode(json_encode($re->orderBy('addr.id', 'ASC')->get()->toArray()), true);

    }

    public static function getRegions($city_id, $can_service = null)
    {
        $re = self::where('parent_id', $city_id);
        if ($can_service) {
            $re->whereNotNull('service_area_id');
        }
        return $re->select('id as region_id', 'title as region_title', 'zipcode', 'parent_id as city_id', 'service_area_id')->get()->toArray();
    }

    /**
     * @param $addr 地址
     * 對地址作格式處理， 如果無法找到對應地區、縣市，則回傳預設$default
     * @return mixed|object
     */
    public static function addrFormating($addr)
    {
        $default = (object) array(
            'city_id' => null,
            'city_title' => null,
            'region_id' => null,
            'region_title' => null,
            'addr' => $addr,
            'zipcode' => null,
        );

        $addr = preg_replace('/^(台|臺)灣(省|)/i', '', $addr);

        // preg_match('/([\x{4e00}-\x{9fa5}]+[市縣])([\x{4e00}-\x{9fa5}]+[區鄉鎮])(.*)/u', $addr, $matches);

        $matches = catchAddress($addr);

        if (count($matches) === 0) {
            return $default;
        } else {
            $query = "SELECT
            city.id as city_id,
            city.title as city_title,
            region.id as region_id,
            region.title as region_title,
            region.zipcode as zipcode
            FROM
            loc_addr as city LEFT JOIN loc_addr as region ON city.id = region.parent_id
            WHERE city.title = ? AND region.title=?";

            $re = DB::select($query, [$matches[1], $matches[2]]);
            if (count($re) === 0) {
                return $default;
            }

            $re = $re[0];
            $re->addr = $matches[3];
            return $re;
        }
        //
    }

    /**
     * @param $region_id 地區id
     * @param $addr string 格式: 中山路1號1樓
     *
     * @return string 格式加上郵遞區號:311 新竹縣五峰鄉中山路1號1樓
     */
    public static function fullAddr($region_id, $addr)
    {
        if (!$region_id) {
            return $addr;
        }
        $sql = "SELECT CONCAT(r.zipcode,' ',c.title,r.title) as title FROM loc_addr as r
        LEFT JOIN loc_addr as c ON r.parent_id = c.id
        WHERE r.id = $region_id";

        $re = DB::select($sql);

        return $re[0]->title . $addr;
    }
}
