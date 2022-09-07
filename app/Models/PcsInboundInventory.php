<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PcsInboundInventory extends Model
{
    use HasFactory;
    protected $table = 'pcs_inbound_inventory';
    protected $guarded = [];
}
