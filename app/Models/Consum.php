<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Consum extends Model
{
    use HasFactory;
    protected $table = 'lgt_consum';


    public static function deleteById($id)
    {
        Consum::where('id', $id)->delete();
    }

}
