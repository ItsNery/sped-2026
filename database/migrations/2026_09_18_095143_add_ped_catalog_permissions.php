<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::findOrCreate('administrar-catalogos-ped', 'web');

        Role::whereIn('name', ['Administrador', 'SuperAdministrador'])
            ->where('guard_name', 'web')
            ->each(fn (Role $role) => $role->givePermissionTo($permission));
    }

    public function down(): void
    {
        Permission::where('name', 'administrar-catalogos-ped')
            ->where('guard_name', 'web')
            ->delete();
    }
};
