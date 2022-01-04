<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentGroup extends Model
{
    use HasFactory;

    protected $table = 'shipment_group';

    protected $fillable = [
        'name'
    ];

}
