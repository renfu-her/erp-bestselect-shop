<?php

namespace Tests\Feature;

use App\Models\Temps;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TempsTest extends TestCase
{
    use RefreshDatabase;

    public function test_temps_model_exists()
    {
        $shipment = Temps::factory()->create();
        $this->assertModelExists($shipment);
    }

    public function test_create_a_new_temps_model()
    {
        $this->assertDatabaseCount(Temps::class, 0);
        Temps::factory()->create();
        $this->assertDatabaseCount(Temps::class, 1);
    }

    public function test_update_temps_model()
    {
        Temps::factory()->create();
        Temps::where('id', '=', '1')->update([
            'temps' => 'aaaaa'
        ]);
        $this->assertDatabaseHas(Temps::class, [
            'temps' => 'aaaaa'
        ]);
    }
}
