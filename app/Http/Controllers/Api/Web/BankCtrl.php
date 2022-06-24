<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;

class BankCtrl extends Controller
{
    //
    public function bankList(Request $request)
    {
        return [
            'status' => '0',
            'data' => Bank::get(),
        ];
    }

}
