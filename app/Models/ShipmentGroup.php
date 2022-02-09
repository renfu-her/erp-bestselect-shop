<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentGroup extends Model
{
    use HasFactory;

    protected $table = 'shi_group';

    protected $fillable = [
        'category_fk',
        'name',
        'temps_fk',
        'method_fk',
        'note',
    ];

}
