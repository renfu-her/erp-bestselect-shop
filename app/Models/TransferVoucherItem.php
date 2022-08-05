<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TransferVoucherItem extends Model
{
    use HasFactory;

    protected $table = 'acc_transfer_voucher_items';
    protected $guarded = [];
}
