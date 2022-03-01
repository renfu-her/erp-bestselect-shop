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

    public static function createData($customer_id, $identity_id, $sn = null, $level = null, $can_bind = 0)
    {
        $CIdata = CustomerIdentity::where('customer_id', $customer_id)
            ->where('identity_id', $identity_id)->get()->first();

        if (!$CIdata) {
            return CustomerIdentity::create([
                'customer_id' => $customer_id,
                'identity_id' => $identity_id,
                'sn' => $sn,
                'level' => $level,
                'can_bind' => $can_bind,
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
}
