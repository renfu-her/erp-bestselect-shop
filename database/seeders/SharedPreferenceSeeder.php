<?php

namespace Database\Seeders;

use App\Enums\Globals\SharedPreference\Category;
use App\Enums\Globals\SharedPreference\Event;
use App\Enums\Globals\SharedPreference\Feature;
use App\Enums\Globals\SharedPreference\Type;
use App\Enums\Globals\StatusOffOn;
use App\Models\SharedPreference;
use Illuminate\Database\Seeder;

class SharedPreferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SharedPreference::firstOrCreate([
            'category' => Category::mail()->value
            , 'event' => Event::mail_order()->value
            , 'feature' => Feature::mail_order_established()->value
        ], [
            'type' => Type::offon()->value
            , 'title' => Feature::getDescription(Feature::mail_order_established()->value)
            , 'status' => StatusOffOn::Off()->value
        ]);
        SharedPreference::firstOrCreate([
            'category' => Category::mail()->value
            , 'event' => Event::mail_order()->value
            , 'feature' => Feature::mail_order_paid()->value
        ], [
            'type' => Type::offon()->value
            , 'title' => Feature::getDescription(Feature::mail_order_paid()->value)
            , 'status' => StatusOffOn::Off()->value
        ]);
        SharedPreference::firstOrCreate([
            'category' => Category::mail()->value
            , 'event' => Event::mail_order()->value
            , 'feature' => Feature::mail_order_shipped()->value
        ], [
            'type' => Type::offon()->value
            , 'title' => Feature::getDescription(Feature::mail_order_shipped()->value)
            , 'status' => StatusOffOn::Off()->value
        ]);
    }
}
