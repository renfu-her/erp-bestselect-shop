<?php

namespace Tests\Feature;

use App\Models\Shipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShipmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_shipment_model_exists()
    {
        $shipment = Shipment::factory()->create();
        $this->assertModelExists($shipment);
    }

    public function test_create_a_new_shipment_model()
    {
        $this->assertDatabaseCount(Shipment::class, 0);
        Shipment::factory()->create();
        $this->assertDatabaseCount(Shipment::class, 1);
    }

    public function test_update_shipment_model()
    {
        Shipment::factory()->create();
        Shipment::where('id', '=', '1')->update([

        ]);
        $this->assertDatabaseHas(Shipment::class, [

        ]);
    }
}
