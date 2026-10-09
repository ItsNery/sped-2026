<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        Permission::findOrCreate('ver-logs', 'web');
        Permission::findOrCreate('ver-panel-avance-general', 'web');
    }

    public function down(): void
    {
        Permission::whereIn('name', ['ver-logs', 'ver-panel-avance-general'])
            ->where('guard_name', 'web')
            ->delete();
    }
};
