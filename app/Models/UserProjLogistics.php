<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProjLogistics extends Model
{
    use HasFactory;
    protected $table = 'usr_user_proj_logistics';
    protected $guarded = [];
    public $timestamps = false;
}
