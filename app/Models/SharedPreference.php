<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SharedPreference extends Model
{
    use HasFactory;

    protected $table = 'shared_preference';
    protected $guarded = [];

}
