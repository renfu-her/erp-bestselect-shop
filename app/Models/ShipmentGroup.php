<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentGroup extends Model
{
    use HasFactory;

    protected $table = 'shi_group';

    protected $fillable = [
        'name',
        'temps_fk',
        'method',
        'note',
    ];

}
