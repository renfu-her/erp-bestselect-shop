<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayingOrders extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'pcs_paying_orders';
    protected $guarded = [];
}
