<?php

namespace Tests\Feature;

use App\Models\Institucion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_de_usuario_is_rendered_once_for_an_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $navigation = view('layouts.admin-navigation')->render();

        $this->assertSame(1, substr_count($navigation, 'Manual-Instituciones.pdf'));
    }

    public function test_dependency_liaison_sees_assigned_liaison_contact_details(): void
    {
        $institution = Institucion::create([
            'nombre' => 'Dependencia de prueba',
            'titular' => 'Titular de prueba',
        ]);
        $dependencyLiaison = User::factory()->create(['id_institucion' => $institution->id]);
        $dependencyLiaison->assignRole(Role::findOrCreate('Enlace dependencia', 'web'));
        $liaison = User::factory()->create([
            'name' => 'Enlace asignado',
            'email' => 'enlace@example.test',
            'telefono' => '5551234567',
        ]);
        $liaison->assignRole(Role::findOrCreate('Enlace', 'web'));
        $liaison->instituciones()->attach($institution);

        $this->actingAs($dependencyLiaison);

        $navigation = view('layouts.admin-navigation')->render();

        $this->assertStringContainsString('Enlace asignado', $navigation);
        $this->assertStringContainsString('mailto:enlace@example.test', $navigation);
        $this->assertStringContainsString('tel:5551234567', $navigation);
    }

    public function test_dependency_liaison_without_an_assigned_liaison_sees_an_empty_state(): void
    {
        $institution = Institucion::create([
            'nombre' => 'Dependencia sin enlace',
            'titular' => 'Titular de prueba',
        ]);
        $dependencyLiaison = User::factory()->create(['id_institucion' => $institution->id]);
        $dependencyLiaison->assignRole(Role::findOrCreate('Enlace dependencia', 'web'));

        $this->actingAs($dependencyLiaison);

        $navigation = view('layouts.admin-navigation')->render();

        $this->assertStringContainsString('Sin enlace asignado.', $navigation);
    }
}
