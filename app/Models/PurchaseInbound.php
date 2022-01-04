<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PurchaseInbound extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'pcs_purchase_inbound';
    protected $guarded = [];

}
