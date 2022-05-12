<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            Helper\HelperSeeder::class,
            permissionSeeder::class,
            PageAuthSeeder::class,
            UserSeeder::class,
            CustomerSeeder::class,
            DepotSeeder::class,
            SupplierSeeder::class,
            TempsSeeder::class,
            ShipmentSeeder::class,
            SaleChannelSeeder::class,
            ProductSeeder::class,
//            PurchaseSeeder::class,
            AccountingSeeder::class,
            naviNodeSeeder::class,
            IncomeExpenditureSeeder::class,
            DiscountSeeder::class,
            OrderSeeder::class,
//            DeliverySeeder::class,
        ]);
    }
}
