<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ResetDependencyPasswordsCommandTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_dry_run_does_not_change_passwords_or_create_a_report(): void
    {
        $user = $this->dependencyUser(isActive: true);
        $reportPath = storage_path('framework/testing/passwords-' . uniqid() . '.csv');

        $this->artisan('sped:reset-dependency-passwords', ['--report' => $reportPath])
            ->expectsOutputToContain('Modo DRY-RUN')
            ->assertSuccessful();

        $this->assertTrue(Hash::check('original-password', $user->fresh()->password));
        $this->assertFileDoesNotExist($reportPath);
    }

    public function test_execute_resets_only_active_dependency_users_and_writes_a_private_report(): void
    {
        $activeDependency = $this->dependencyUser(isActive: true);
        $inactiveDependency = $this->dependencyUser(isActive: false);
        $otherUser = User::factory()->create(['password' => Hash::make('original-password')]);
        $reportPath = storage_path('framework/testing/passwords-' . uniqid() . '.csv');

        $this->artisan('sped:reset-dependency-passwords', ['--execute' => true, '--report' => $reportPath])
            ->expectsOutputToContain('Contrasenas restablecidas: 1')
            ->assertSuccessful();

        $report = array_map('str_getcsv', file($reportPath, FILE_IGNORE_NEW_LINES));
        $password = $report[1][3];

        $this->assertSame(['ID', 'Nombre', 'Correo', 'Contraseña temporal'], $report[0]);
        $this->assertTrue(Hash::check($password, $activeDependency->fresh()->password));
        $this->assertFalse(Hash::check('original-password', $activeDependency->fresh()->password));
        $this->assertTrue(Hash::check('original-password', $inactiveDependency->fresh()->password));
        $this->assertTrue(Hash::check('original-password', $otherUser->fresh()->password));
    }

    private function dependencyUser(bool $isActive): User
    {
        Role::findOrCreate('Enlace dependencia');
        $user = User::factory()->create([
            'is_active' => $isActive,
            'password' => Hash::make('original-password'),
        ]);
        $user->assignRole('Enlace dependencia');

        return $user;
    }
}
