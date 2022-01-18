<?php

namespace App\Models;

use App\Enums\Purchase\LogFeature;
use App\Enums\Purchase\LogFeatureEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PurchaseLog extends Model
{
    use HasFactory;
    protected $table = 'pcs_purchase_log';
    protected $guarded = [];

    public static function stockChange($purchase_id, $product_style_id, $feature, $feature_id, $event, $qty, $note = null, $user_id, $user_name)
    {
        if (!LogFeature::hasKey($feature)) {
            return ['success' => 0, 'error_msg' => 'feature error'];
        }

        if (!LogFeatureEvent::hasKey($event)) {
            return ['success' => 0, 'error_msg' => 'event error'];
        }

        return DB::transaction(function () use ($purchase_id, $product_style_id, $feature, $feature_id, $event, $qty, $note, $user_id, $user_name) {
            self::create([
                'purchase_id' => $purchase_id,
                'product_style_id' => $product_style_id,
                'feature' => $feature,
                'feature_id' => $feature_id,
                'event' => $event,
                'qty' => $qty,
                'note' => $note,
                'user_id' => $user_id,
                'user_name' => $user_name]);

            return ['success' => 1];

        });
    }

}
