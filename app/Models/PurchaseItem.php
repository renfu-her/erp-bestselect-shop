<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'pcs_purchase_items';
    protected $guarded = [];
}
