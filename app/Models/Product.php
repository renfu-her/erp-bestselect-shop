<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'prd_products';
    protected $guarded = [];

    public static function createProduct($title, $user_id, $category_id, $feature = null, $url = null, $slogan = null, $active_sdate = null, $active_edate = null, $has_tax = 0)
    {
        return DB::transaction(function () use ($title,
            $user_id,
            $category_id,
            $feature,
            $url,
            $slogan,
            $active_sdate,
            $active_edate,
            $has_tax) {

            $sku = "P" . date("ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))
                    ->withTrashed()
                    ->get()
                    ->count()) + 1, 3, '0', STR_PAD_LEFT);

            $id = self::create([
                "title" => $title,
                "sku" => $sku,
                "user_id" => $user_id,
                "category_id" => $category_id,
                "feature" => $feature,
                "url" => $url,
                "slogan" => $slogan,
                "active_sdate" => $active_sdate,
                "active_edate" => $active_edate,
                "has_tax" => $has_tax,
            ])->id;

            return [
                'sku' => $sku,
                'id' => $id,
            ];

        });
    }

    public static function updateProduct($id,$title, $user_id, $category_id, $feature = null, $url = null, $slogan = null, $active_sdate = null, $active_edate = null, $has_tax = 0){

        self::where('id',$id)->update([
            "title" => $title,
            "user_id" => $user_id,
            "category_id" => $category_id,
            "feature" => $feature,
            "url" => $url,
            "slogan" => $slogan,
            "active_sdate" => $active_sdate,
            "active_edate" => $active_edate,
            "has_tax" => $has_tax,
        ]);
    }

}
