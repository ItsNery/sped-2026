<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'is_system_account')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_system_account')->default(false)->after('is_active');
            });
        }

        $systemPermissions = [
            'administrar-sistema',
            'proteger-cuenta-sistema',
        ];

        foreach ($systemPermissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $superAdministrator = Role::firstOrCreate([
            'name' => 'SuperAdministrador',
            'guard_name' => 'web',
        ]);

        $superAdministrator->syncPermissions(Permission::where('guard_name', 'web')->get());

        Role::where('name', 'Administrador')
            ->where('guard_name', 'web')
            ->first()
            ?->givePermissionTo('proteger-cuenta-sistema');

        $systemUser = null;
        $configuredEmail = env('SPED_SUPERADMIN_EMAIL');

        if ($configuredEmail) {
            $systemUser = User::where('email', $configuredEmail)->first();
        }

        if (!$systemUser) {
            $administratorRoleId = Role::where('name', 'Administrador')
                ->where('guard_name', 'web')
                ->value('id');

            if ($administratorRoleId) {
                $systemUser = User::whereHas('roles', function ($query) use ($administratorRoleId) {
                    $query->where('roles.id', $administratorRoleId);
                })->orderBy('id')->first();
            }
        }

        if ($systemUser) {
            $systemUser->forceFill(['is_system_account' => true])->save();
            $systemUser->assignRole($superAdministrator);
        }
    }

    public function down(): void
    {
        $systemUser = User::where('is_system_account', true)->first();

        if ($systemUser) {
            $systemUser->removeRole('SuperAdministrador');
            $systemUser->forceFill(['is_system_account' => false])->save();
        }

        Role::where('name', 'SuperAdministrador')->where('guard_name', 'web')->delete();
        Permission::whereIn('name', ['administrar-sistema', 'proteger-cuenta-sistema'])
            ->where('guard_name', 'web')
            ->delete();

        if (Schema::hasColumn('users', 'is_system_account')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_system_account');
            });
        }
    }
};
