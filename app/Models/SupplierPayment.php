<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class SupplierPayment extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'prd_supplier_payments';
    protected $guarded = [];

}
