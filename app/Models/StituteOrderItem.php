<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class StituteOrderItem extends Model
{
    use HasFactory;

    protected $table = 'acc_stitute_order_items';
    protected $guarded = [];
}
