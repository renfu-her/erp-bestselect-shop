<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsrProfile extends Model
{
    use HasFactory;
    protected $table = 'usr_profile';
    protected $guarded = [];
}
