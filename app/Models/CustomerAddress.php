<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 消費者收件地址（含預設收件地址）
 */
class CustomerAddress extends Model
{
    use HasFactory;

    protected $table = 'usr_customers_address';

    protected $fillable = [
        'usr_customers_id_fk',
        'name',
        'phone',
        'address',
        'city_id',
        'region_id',
        'addr',
        'is_default_addr',
    ];

    public $timestamps = false;

    /**
     * 新增消費者的常用地址
     * @return void
     */
    public static function addCustomerAddress(array $data, $customerId)
    {
        $addresses = [];
        foreach (['ord', 'rec', 'sed'] as $prefix) {
            //選擇「新增地址」時，才新增地址到資料庫
            if ($data[$prefix . '_radio'] === 'new') {
                $addresses[] = [
                    'name'      => $data[$prefix.'_name'],
                    'phone'     => $data[$prefix.'_phone'],
                    'address'   => $data[$prefix.'_address'],
                    'city_id'   => $data[$prefix.'_city_id'],
                    'region_id' => $data[$prefix.'_region_id'],
                    'addr'      => $data[$prefix.'_addr'],
                ];
            }
        }

        $newAddresses = [];
        foreach ($addresses as $address) {
            $addr = Addr::addrFormating($address['address']);
            if (!$addr->city_id) {
                return;
            } else {
                //只取需要的數值，並且作地址格式轉換
                $newAddresses[] = [
                    'name' => $address['name'],
                    'phone' => $address['phone'],
                    'city_id' => $addr->city_id,
                    'city_title' => $addr->city_title,
                    'region_id' => $addr->region_id,
                    'region_title' => $addr->region_title,
                    'zipcode' => $addr->zipcode,
                    'addr' => $addr->addr,
                    'address' => Addr::fullAddr($addr->region_id, $addr->addr),
                ];
            }
        }

        foreach ($newAddresses as $newAddress) {
            //至少要有1筆預設地址
            if (
                CustomerAddress::where([
                    'usr_customers_id_fk' => $customerId,
                    'is_default_addr' => 1,
                ])->exists()) {
                $is_default_addr = 0;
            } else {
                $is_default_addr = 1;
            }

            //確認「新增地址」的資料沒有在資料庫重複，才新增
            if (
                CustomerAddress::where([
                    'usr_customers_id_fk' => $customerId,
                    'name'                => $newAddress['name'],
                    'phone'               => $newAddress['phone'],
                    'address'             => $newAddress['address'],
                    'city_id'             => $newAddress['city_id'],
                    'region_id'           => $newAddress['region_id'],
                    'addr'                => $newAddress['addr'],
                ])->doesntExist()
            ) {
                CustomerAddress::create([
                    'usr_customers_id_fk' => $customerId,
                    'name'                => $newAddress['name'],
                    'phone'               => $newAddress['phone'],
                    'address'             => $newAddress['address'],
                    'city_id'             => $newAddress['city_id'],
                    'region_id'           => $newAddress['region_id'],
                    'addr'                => $newAddress['addr'],
                    'is_default_addr'     => $is_default_addr,
                ]);
            }
        }
    }
}
