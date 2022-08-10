<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 公佈欄Model
 */
class BulletinBoard extends Model
{
    use HasFactory;

    protected $table = 'idx_news';
    protected $fillable = [
        'id',
        'title',
        'content',
        'weight',
        'type',
        'expire_time',
        'usr_users_id_fk',
    ];

    public $timestamps = true;
}
