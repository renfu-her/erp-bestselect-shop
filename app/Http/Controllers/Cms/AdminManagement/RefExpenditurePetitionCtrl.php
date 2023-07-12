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

            $old_p_e = DB::table('pet_order_sn')
                ->leftJoin('pet_petition as pet', function ($join) {
                    $join->on('pet.id', '=', 'pet_order_sn.source_id')
                        ->where('pet_order_sn.source_type', '=', 'petition');
                })
                ->leftJoin('exp_expenditure as exp', function ($join) {
                    $join->on('exp.id', '=', 'pet_order_sn.source_id')
                        ->where('pet_order_sn.source_type', '=', 'expenditure');
                })
                ->where('pet_order_sn.order_sn', $current_sn)
                ->select('pet_order_sn.source_type', 'pet_order_sn.source_id')
                ->get();
            foreach ($old_p_e as $ope_value) {
                DB::table('pet_order_sn')
                ->where('source_id', $ope_value->source_id,)
                ->where('source_type', $ope_value->source_type)
                ->delete();
            }

            $err = false;
            $errors = [];
            $new_p_e = array_filter($order, function($v){
                return (substr($v, 0, 3) == 'PET' || substr($v, 0, 3) == 'EXP');
            });
            foreach($new_p_e as $n_p_e_key => $n_p_e_value){
                $re = Petition::reverseBind($current_sn, $n_p_e_value);

                if ($re['success'] != '1') {
                    $err = true;
                    $errors['order.' . $n_p_e_key] = $re['message'];
                }
            }
            $new_other = array_filter($order, function($v) {
                return (substr($v, 0, 3) != 'PET' && substr($v, 0, 3) != 'EXP');
            });
            foreach($new_other as $n_o_key => $n_o_value){
                foreach($new_p_e as $npe_value){
                    $res = Petition::reverseBind($n_o_value, $npe_value);

                    if ($res['success'] != '1') {
                        $err = true;
                        $errors['order.' . $n_o_key] = $res['message'];
                    }
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

        $current_p_e = DB::table('pet_order_sn')
            ->where('order_sn', $current_sn)
            ->select('source_type', 'source_id')
            ->get()->toArray();

        foreach ($current_p_e as $value) {
            $orders = array_merge($orders, DB::table('pet_order_sn')
                ->where('source_type', $value->source_type)
                ->where('source_id', $value->source_id)
                ->where('order_sn', '!=', $current_sn)
                ->pluck('order_sn')
                ->toArray()
            );
        }

        return view('cms.admin_management.ref_expenditure_petition.edit', [
            'form_action'=>route('cms.ref_expenditure_petition.edit', ['current_sn' => $current_sn]),
            'back_url'=>url()->previous(),
            'order' => array_unique($orders),
        ]);
    }
}
