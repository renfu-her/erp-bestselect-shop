<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class OnePage extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'opg_one_page';
    protected $guarded = [];

    public static function dataList($title = null)
    {
       
        $re = DB::table('opg_one_page as one')
            ->join('collection', 'one.collection_id', '=', 'collection.id')
            ->join('prd_sale_channels as channel', 'one.sale_channel_id', '=', 'channel.id')
            ->select(['one.*', 'collection.name as collection_title', 'channel.title as salechannel_title'])
            ->whereNull('one.deleted_at');

        if ($title) {
            $re->where('one.title', 'like', "%$title%");
        }

        return $re;

    }

    public static function changeActiveStatus($id)
    {
        $active = self::where('id', $id)
            ->get()
            ->first()
            ->active;

        if ($active) {
            self::where('id', $id)->update(['active' => 0]);
        } else {
            self::where('id', $id)->update(['active' => 1]);
        }
    }
}
