<?php

namespace Tests\Feature;

use App\Models\CatPlanEstatalDesarrollo;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_example()
    {
        CatPlanEstatalDesarrollo::forceCreate([
            'id' => 3,
            'nombre' => 'Plan de prueba',
            'gobernador' => 'Titular de prueba',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
