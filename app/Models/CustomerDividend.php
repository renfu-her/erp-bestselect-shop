<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerDividend extends Model
{
    use HasFactory;
    protected $table = 'usr_customer_dividend';
    protected $guarded = [];
}
