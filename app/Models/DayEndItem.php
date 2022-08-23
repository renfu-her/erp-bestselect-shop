<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class DayEndItem extends Model
{
    use HasFactory;

    protected $table = 'acc_day_end_items';
    protected $guarded = [];
}
