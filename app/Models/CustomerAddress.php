<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 消費者收件地址（含預設收件地址）
 */
class CustomerAddress extends Model
{
    use HasFactory;

    protected $table = 'usr_customers_address';

    protected $fillable = [
        'usr_customers_id_fk',
        'name',
        'phone',
        'address',
        'city_id',
        'region_id',
        'addr',
        'is_default_addr',
    ];

    public $timestamps = false;
}
