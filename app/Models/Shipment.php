<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Shipment extends Model
{
    use HasFactory;

    protected $table = 'shi_rule';
    protected $fillable = [
        'group_id_fk',
        'min_price',
        'max_price',
        'dlv_fee',
        'dlv_cost',
        'at_most',
        'is_above',
    ];

    public function getShipmentList(Request $request, $categoryId = 1)
    {
        $req = $request->all();
        $query = DB::table('shi_rule')->join('shi_group as group', 'group_id_fk', '=', 'group.id')
                                        ->where('group.category_fk', '=', $categoryId)
                                        ->join('shi_temps', 'temps_fk', '=', 'shi_temps.id')
                                        ->join('shi_method', 'group.method_fk', '=', 'shi_method.id')
                                        ->leftJoin('prd_suppliers', 'group.supplier_fk', '=', 'prd_suppliers.id');

        if (isset($req['shi_name'])){
            $query->where('group.name', 'like', '%' . $req['shi_name'] . '%');
        }
        if (isset($req['shi_method'])){
            $query->where('shi_method.id', '=', $req['shi_method']);
        }
        if (isset($req['shi_temps'])){
            $query->where('shi_temps.id', '=', $req['shi_temps']);
        }
        if (isset($req['has_supplier'])){
            if ($req['has_supplier'] == 1) {
                $query->whereNotNull('group.supplier_fk');
            } else {
                $query->whereNull('group.supplier_fk');
            }
        }
        if (isset($req['supplier'])){
            $query->where('prd_suppliers.name', 'like', '%' . $req['supplier'] . '%');
        }

        return $query->orderBy('temps_fk')
                                            ->orderBy('group.name')
                                            ->orderBy('group.method_fk')
                                            ->select([
                                                'group.name',
                                                'shi_rule.group_id_fk',
                                                'shi_temps.temps',
                                                'shi_method.method',
                                                'prd_suppliers.name as supplier',
                                            ]);
    }

    public function getDataFieldFromFormRequest(Request $request)
    {
        $req = $request->all();
        $category = $req['category'];
        $name = $req['name'];
        $temps = $req['temps'];
        $method = $req['method'];
        $supplier_id = $req['supplier_id'];
        $note = $req['note'];
        $min_price = $request->input('min_price');
        $max_price = $request->input('max_price');
        $dlv_fee = $request->input('dlv_fee');
        $dlv_cost = $request->input('dlv_cost');
        $at_most = $request->input('at_most');
        $is_above = $request->input('is_above');

        $ruleNumArray = array();
        for ($i = 0; $i < count($min_price); $i++) {
            $ruleNumArray[] = [
                'min_price' => (int)($min_price[$i]),
                'max_price' => (int)$max_price[$i],
                'dlv_fee' => (int)$dlv_fee[$i],
                'dlv_cost' => (int)$dlv_cost[$i],
                'at_most' => (int)$at_most[$i],
                'is_above' => $is_above[$i]
            ];
        }

        return [
            'category' => $category,
            'name' => $name ,
            'temps' => $temps ,
            'method' => $method ,
            'supplier_id' => $supplier_id ,
            'note' => $note ,
            'ruleNumArray' => $ruleNumArray ,
        ];
    }

    public function storeShipRule(
        array $ruleNumArray,
        string $category,
        string $name,
        string $temps,
        string $method,
        $supplierID,
        $note
    ) {
        $tempsId = Temps::findTempsIdByName($temps);
        $methodId = ShipmentMethod::findShipmentMethodIdByName($method);
        $categoryId = ShipmentCategory::findCategoryIdByName($category);

        $groupId = ShipmentGroup::create([
                    'category_fk' => $categoryId,
                    'name' => $name,
                    'temps_fk' => $tempsId,
                    'method_fk' => $methodId,
                    'supplier_fk' => $supplierID,
                    'note' => $note
                ])->id;

        for ($i =0; $i < count($ruleNumArray); $i++) {
            self::create([
                'group_id_fk' => $groupId,
                'min_price' => $ruleNumArray[$i]['min_price'],
                'max_price' => $ruleNumArray[$i]['max_price'],
                'dlv_fee' => $ruleNumArray[$i]['dlv_fee'],
                'dlv_cost' => $ruleNumArray[$i]['dlv_cost'],
                'at_most' => $ruleNumArray[$i]['at_most'],
                'is_above' => $ruleNumArray[$i]['is_above'],
            ]);
        }
    }

    public function updateShipRule(
        int $groupId,
        array $ruleNumArray,
        string $category,
        string $name,
        string $temps,
        string $method,
        $supplier_id,
        $note
    ) {
        $tempsId = Temps::findTempsIdByName($temps);
        $methodId = ShipmentMethod::findShipmentMethodIdByName($method);
        $categoryId = ShipmentCategory::findCategoryIdByName($category);

        ShipmentGroup::where('id', '=', $groupId)
                    ->update([
                        'category_fk' => $categoryId,
                        'name' => $name,
                        'temps_fk' => $tempsId,
                        'method_fk' => $methodId,
                        'supplier_fk' => $supplier_id,
                        'note' => $note
                    ]);
        self::where('group_id_fk', '=', $groupId)
            ->delete();

        for ($i =0; $i < count($ruleNumArray); $i++) {
            self::insert([
                'group_id_fk' => $groupId,
                'min_price' => $ruleNumArray[$i]['min_price'],
                'max_price' => $ruleNumArray[$i]['max_price'],
                'dlv_fee' => $ruleNumArray[$i]['dlv_fee'],
                'dlv_cost' => $ruleNumArray[$i]['dlv_cost'],
                'at_most' => $ruleNumArray[$i]['at_most'],
                'is_above' => $ruleNumArray[$i]['is_above'],
            ]);
        }
    }

    public function getEditShipmentData(int $groupId)
    {
        return DB::table('shi_group as group')
            ->where('group.id', '=', $groupId)
            ->join('shi_category', 'shi_category.id', '=', 'group.category_fk')
            ->join('shi_rule', 'group.id', '=', 'group_id_fk')
            ->join('shi_temps as _temps', '_temps.id', '=', 'group.temps_fk')
            ->join('shi_method', 'group.method_fk', '=', 'shi_method.id')
            ->get();
    }

    public function deleteShipment(int $groupId)
    {
        self::where('group_id_fk', '=', $groupId)
            ->delete();
        ShipmentGroup::where('id', '=', $groupId)
            ->delete();
    }
}
