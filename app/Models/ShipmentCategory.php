<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentCategory extends Model
{
    use HasFactory;

    protected $table = 'shi_category';

    protected $fillable = [
        'category'
    ];

    public static function findCategoryIdByName(string $category)
    {
        return self::where('category', '=', $category)
            ->select('id')
            ->get()
            ->first()->id;
    }
}
