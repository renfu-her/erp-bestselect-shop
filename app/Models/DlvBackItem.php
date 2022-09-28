<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DlvBackItem extends Model
{
    use HasFactory;
    protected $table = 'dlv_back_items';
    protected $guarded = [];
    public $timestamps = true;


}
