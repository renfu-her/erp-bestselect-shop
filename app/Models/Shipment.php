<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Shipment extends Model
{
    use HasFactory;

    protected $table = 'shipment';
    protected $fillable = [
        'group_id_fk',
        'temps_fk',
        'method',
        'note',
        'min_price',
        'max_price',
        'dlv_fee',
        'dlv_cost',
        'at_most',
        'is_above',
    ];

    public function getShipmentList()
    {
        return DB::table('shipment')->join('shipment_group as group', 'group_id_fk', '=', 'group.id')
                                        ->join('shipment_temps', 'temps_fk', '=', 'shipment_temps.id')
                                        ->orderBy('group.id')
                                        ->orderBy('min_price');
    }

    public function getDataFieldFromFormRequest(Request $request)
    {
        $req = $request->all();
        $name = $req['name'];
        $temps = $req['temps'];
        $method = $req['method'];
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
            'name' => $name ,
            'temps' => $temps ,
            'method' => $method ,
            'note' => $note ,
            'ruleNumArray' => $ruleNumArray ,
        ];
    }

    public function storeShipRule(
        array $ruleNumArray,
        string $name,
        string $temps,
        string $method,
        string $note
    ) {
        $tempsId = Temps::findTempsIdByName($temps);

        $groupId = ShipmentGroup::create([
                    'name' => $name
                ])->id;

        for ($i =0; $i < count($ruleNumArray); $i++) {
            self::create([
                'group_id_fk' => $groupId,
                'temps_fk' => $tempsId,
                'method' => $method,
                'note' => $note,
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
        string $name,
        string $temps,
        string $method,
        string $note
    ) {
        $tempsId = Temps::findTempsIdByName($temps);

        ShipmentGroup::where('id', '=', $groupId)
                    ->update(['name' => $name]);
        self::where('group_id_fk', '=', $groupId)
            ->delete();

        for ($i =0; $i < count($ruleNumArray); $i++) {
            self::insert([
                'group_id_fk' => $groupId,
                'temps_fk' => $tempsId,
                'method' => $method,
                'note' => $note,
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
        return DB::table('shipment_group as group')
            ->where('group.id', '=', $groupId)
            ->join('shipment', 'group.id', '=', 'group_id_fk')
            ->join('shipment_temps as _temps', '_temps.id', '=', 'shipment.temps_fk')
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
