<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Depot;

class DepotTest extends TestCase
{
    use RefreshDatabase;

    public function test_depot_model_exists()
    {
        $depot = Depot::factory()->create();
        $this->assertModelExists($depot);
    }

    public function test_create_one_depot_model()
    {
        $this->assertDatabaseCount(Depot::class, 0);
        Depot::factory()->create();
        $this->assertDatabaseCount(Depot::class, 1);
    }

    public function test_update_one_depot_model()
    {
        Depot::factory()->create();
        Depot::where('id', '=', '1')->update([
            'name'     => '喜鴻新竹',
            'sender'      => 'yoyo',
            'can_tally'      => 0,
            'addr'      => '忠孝西路30號',
            'city_id'   => 73,
            'region_id' => 74,
            'tel'       => '0512345678',
            'address' => '300 新竹市東區忠孝西路30號'
        ]);
        $this->assertDatabaseHas(Depot::class, ['tel' => '0512345678']);
    }

    public function test_delete_one_depot_model()
    {
        Depot::factory()->create();
        $depot = Depot::where('id', '=', '1');
        $depot->delete();
        $this->assertDeleted($depot);
    }
}
