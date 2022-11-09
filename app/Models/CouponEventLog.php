<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponEventLog extends Model
{
    use HasFactory;
    protected $table = 'dis_coupon_event_log';
    protected $guarded = [];
}
