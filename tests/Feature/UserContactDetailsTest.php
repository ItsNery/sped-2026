<?php

namespace Tests\Feature;

use App\Models\Institucion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserContactDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_a_liaison_with_a_phone_number(): void
    {
        $administrator = User::factory()->create();
        $administrator->givePermissionTo(Permission::findOrCreate('crear-usuario', 'web'));
        $institution = Institucion::create([
            'nombre' => 'Dependencia de prueba',
            'titular' => 'Titular de prueba',
        ]);
        Role::findOrCreate('Enlace', 'web');

        $response = $this->actingAs($administrator)->post(route('panel-usuarios.store'), [
            'name' => 'Enlace de prueba',
            'email' => 'enlace@example.test',
            'telefono' => '5551234567',
            'password' => 'password',
            'password_confirmation' => 'password',
            'roles' => 'Enlace',
            'instituciones' => [$institution->id],
        ]);

        $response->assertRedirect(route('panel-usuarios.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'enlace@example.test',
            'telefono' => '5551234567',
        ]);
    }

    public function test_administrator_can_update_a_liaison_phone_number(): void
    {
        $administrator = User::factory()->create();
        $administrator->givePermissionTo(Permission::findOrCreate('editar-usuario', 'web'));
        $institution = Institucion::create([
            'nombre' => 'Dependencia de prueba',
            'titular' => 'Titular de prueba',
        ]);
        $liaison = User::factory()->create(['telefono' => '5550000000']);
        $liaison->assignRole(Role::findOrCreate('Enlace', 'web'));
        $liaison->instituciones()->attach($institution);

        $response = $this->actingAs($administrator)->patch(route('panel-usuarios.update', $liaison), [
            'name' => $liaison->name,
            'email' => $liaison->email,
            'telefono' => '5551234567',
            'password' => '',
            'password_confirmation' => '',
            'roles' => 'Enlace',
            'instituciones' => [$institution->id],
        ]);

        $response->assertRedirect(route('panel-usuarios.index'));
        $this->assertDatabaseHas('users', [
            'id' => $liaison->id,
            'telefono' => '5551234567',
        ]);
    }
}
