<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrdCreditCard extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'crd_credit_cards';
    protected $guarded = [];
    public $timestamps = false;

}
