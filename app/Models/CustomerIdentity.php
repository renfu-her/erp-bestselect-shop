<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CustomerIdentity extends Model
{
    use HasFactory;

    protected $table = 'usr_customer_identity';
    protected $guarded = [];

    public static function add($customer_id, $identity_code, $sn = null, $level = null, $can_bind = null, $groupby_company_id = null)
    {
        $identity = DB::table('usr_identity')->where('code', $identity_code)->get()->first();

        // 判斷是否超過兩種身份
        if (CustomerIdentity::where('customer_id', $customer_id)->count() > 2) {
            return null;
        }
        $CIdata = CustomerIdentity::where('customer_id', $customer_id)
            ->where('identity_id', $identity->id)->get()->first();

        if (is_null($can_bind)) {
            $can_bind = $identity->can_bind;
        }

        if (!$CIdata) {
            return CustomerIdentity::create([
                'customer_id' => $customer_id,
                'identity_id' => $identity->id,
                'identity_title' => $identity->title,
                'identity_code' => $identity->code,
                'sn' => $sn,
                'level' => $level,
                'can_bind' => $can_bind,
                'groupby_company_id' => $groupby_company_id,
            ])->id;
        }
    }

    public static function updateCanBind($customer_id, $identity, $can_bind = 0)
    {
        $CIdata = CustomerIdentity::where('customer_id', $customer_id)
            ->where('identity', $identity);
        $CIdataGet = $CIdata->get()->first();
        if (null != $CIdataGet) {
            return DB::transaction(function () use ($CIdata, $CIdataGet, $can_bind) {
                $CIdata->update([
                    'can_bind' => $can_bind,
                ]);
                return $CIdataGet->id;
            });
        }
    }

    public static function getSalechannels($customer_id, $without = [])
    {

        $sale = DB::table('usr_customer_identity as ci')
            ->leftJoin('usr_identity as identity', 'ci.identity_id', '=', 'identity.id')
            ->leftJoin('usr_identity_salechannel as isa', 'identity.id', '=', 'isa.identity_id')
            ->leftJoin('prd_sale_channels as sale', 'sale.id', '=', 'isa.sale_channel_id')
            ->select('sale.id as sale_channel_id', 'sale.title as sale_channel_title')
            ->where('ci.customer_id', $customer_id)
            ->whereNull('sale.deleted_at');

        if ($without) {
            $sale->whereNotIn('sale.id', $without);
        }

        return $sale;
    }
}
