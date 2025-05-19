<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TikType extends Model
{
    protected $table = 'tik_types';

    protected $fillable = [
        'name',
        'code',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // 與商品的關聯
    public function products()
    {
        return $this->hasMany(Product::class, 'tik_type_id');
    }
}
