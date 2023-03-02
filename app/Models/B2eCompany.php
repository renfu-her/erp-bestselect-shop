<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class B2eCompany extends Model
{
    use HasFactory;
    protected $table = 'b2e_company';
    protected $guarded = [];

    public static function dataList($keyword = null)
    {

        $re = DB::table('b2e_company as company')
            ->leftJoin('prd_sale_channels as sale_channel', 'company.salechannel_id', '=', 'sale_channel.id')
            ->leftJoin('usr_users as user', 'company.user_id', '=', 'user.id')
            ->select(['company.*', 'sale_channel.title as sale_channel_title', 'user.name as user_name']);

        if ($keyword) {
            $re->where(function ($query) use ($keyword) {
                $query->where('company.title', 'like', "%$keyword%")
                    ->orWhere('company.short_title', 'like', "%$keyword%")
                    ->orWhere('company.vat_no', 'like', "%$keyword%");
            });
        }
        return $re;

    }

}
