<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentCategory extends Model
{
    use HasFactory;

    protected $table = 'shi_category';

    protected $fillable = [
        'category'
    ];
}
