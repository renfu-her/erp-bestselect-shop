<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCreateLog extends Model
{
    use HasFactory;
    protected $table = 'ord_order_create_log';
    protected $guarded = [];
}
