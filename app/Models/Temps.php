<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Temps extends Model
{
    use HasFactory;

    protected $table = 'shipment_temps';

    public static function findTempsIdByName(string $tempsName)
    {
        return self::where('temps', '=', $tempsName)
                    ->select('id')
                    ->get()
                    ->first()->id;
    }
}
