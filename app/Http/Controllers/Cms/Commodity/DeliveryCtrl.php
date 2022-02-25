<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInbound;
use Illuminate\Http\Request;

class DeliveryCtrl extends Controller
{
    public function index(Request $request)
    {
        $selectInboundList = PurchaseInbound::getSelectInboundList([])->get();
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
    }

    public function edit(Request $request, int $id)
    {
    }

    public function update(Request $request, int $id)
    {
    }

    public function destroy(Request $request, int $id)
    {
    }
}
