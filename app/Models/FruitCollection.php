<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FruitCollection extends Model
{
    use HasFactory;
    protected $table = 'fru_collections';
    protected $guarded = [];
    public $timestamps = false;

}
