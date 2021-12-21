<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Depot extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'depot';

    protected $fillable = [
        'name',
        'sender',
        'address',
        'tel',
        'city_id',
        'region_id',
        'addr',
    ];
}
