<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentMethod extends Model
{
    use HasFactory;

    protected $table = 'shi_method';
    protected $fillable = [
        'method',
    ];

    public static function findShipmentMethodIdByName(string $methodName)
    {
        return self::where('method', '=', $methodName)
            ->select('id')
            ->get()
            ->first()->id;
    }
}
