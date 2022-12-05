<?php

namespace App\Http\Controllers\Cms\AdminManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use App\Models\Petition;

class RefExpenditurePetitionCtrl extends Controller
{
    public function edit(Request $request, $current_sn)
    {
        if($request->isMethod('post')){
            $request->validate([
                'back_url' => ['required', 'string'],
                'order' => ['nullable', 'array']
            ]);

            $d = $request->all();

            DB::beginTransaction();
            $order = isset($d['order']) ? $d['order'] : [];
            $order = array_values(array_filter($order));

            $o_order = DB::table('pet_order_sn')
                ->leftJoin('pet_petition as pet', function ($join) {
                    $join->on('pet.id', '=', 'pet_order_sn.source_id')
                        ->where('pet_order_sn.source_type', '=', 'petition');
                })
                ->leftJoin('exp_expenditure as exp', function ($join) {
                    $join->on('exp.id', '=', 'pet_order_sn.source_id')
                        ->where('pet_order_sn.source_type', '=', 'expenditure');
                })
                ->where('pet_order_sn.order_sn', $current_sn)
                ->select('pet_order_sn.id', DB::raw('IF(pet.sn IS NULL, exp.sn, pet.sn) AS order_sn'))
                ->get();

            $dArray = array_diff($o_order->pluck('order_sn', 'id')->toArray(), $order);
            if($dArray) DB::table('pet_order_sn')->whereIn('id', array_flip($dArray))->delete();

            $err = false;
            $errors = [];
            foreach($order as $key => $value){
                $re = Petition::reverseBind($current_sn, $value);

                if ($re['success'] != '1') {
                    $err = true;
                    $errors['order.' . $key] = $re['message'];
                }
            }

            if($err){
                DB::rollBack();
                return redirect()->back()->withInput($d)->withErrors($errors);
            }

            DB::commit();
            wToast('更新完成');
            return redirect($d['back_url']);
        }

        $orders = array_map(function ($n) {
                return $n->order_sn;
            }, DB::table('pet_order_sn')
                ->leftJoin('pet_petition as pet', function ($join) {
                    $join->on('pet.id', '=', 'pet_order_sn.source_id')
                        ->where('pet_order_sn.source_type', '=', 'petition');
                })
                ->leftJoin('exp_expenditure as exp', function ($join) {
                    $join->on('exp.id', '=', 'pet_order_sn.source_id')
                        ->where('pet_order_sn.source_type', '=', 'expenditure');
                })
                ->where('pet_order_sn.order_sn', $current_sn)
                ->select(DB::raw('IF(pet.sn IS NULL, exp.sn, pet.sn) AS order_sn'))
                ->get()->toArray()
            );

        return view('cms.admin_management.ref_expenditure_petition.edit', [
            'form_action'=>route('cms.ref_expenditure_petition.edit', ['current_sn' => $current_sn]),
            'back_url'=>url()->previous(),
            'order' => $orders,
        ]);
    }
}
