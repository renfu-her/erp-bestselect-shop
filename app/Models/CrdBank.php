<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrdBank extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'crd_banks';
    protected $guarded = [];


    public const INSTALLMENT = [
        'none'=>'不分期',
    ];
}
