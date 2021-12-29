<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Shipment extends Model
{
    use HasFactory;

    protected $table = 'shipment';

    public function storeShipRule(Request $request)
    {

    }
}
