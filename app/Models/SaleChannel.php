<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleChannel extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'prd_sale_channels';
    protected $guarded = [];

}
