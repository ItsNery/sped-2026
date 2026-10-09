<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SpreadsheetValueSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MediumSecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_attempts_require_log_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('panel-accesos.index'))
            ->assertForbidden();
    }

    public function test_user_indicator_drill_down_requires_dashboard_permission(): void
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();

        $this->actingAs($user)
            ->get(route('usuarios.indicadores', $targetUser))
            ->assertForbidden();
    }

    public function test_users_with_log_permission_can_view_login_attempts(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('ver-logs', 'web'));

        $this->actingAs($user)
            ->get(route('panel-accesos.index'))
            ->assertOk();
    }

    public function test_administrators_can_view_login_attempts(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('Administrador', 'web'));

        $this->actingAs($user)
            ->get(route('panel-accesos.index'))
            ->assertOk();
    }

    public function test_authorized_users_can_access_protected_dashboard_details(): void
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('ver-panel-avance-general', 'web'));

        $this->actingAs($user)
            ->get(route('usuarios.indicadores', $targetUser))
            ->assertOk();
    }

    public function test_csv_formula_prefixes_are_escaped(): void
    {
        $values = SpreadsheetValueSanitizer::sanitizeCsvRow([
            '=SUM(A1:A2)',
            '+SUM(A1:A2)',
            '-SUM(A1:A2)',
            '@SUM(A1:A2)',
            'texto seguro',
            123,
        ]);

        $this->assertSame([
            "'=SUM(A1:A2)",
            "'+SUM(A1:A2)",
            "'-SUM(A1:A2)",
            "'@SUM(A1:A2)",
            'texto seguro',
            123,
        ], $values);
    }

    public function test_spreadsheet_formula_values_are_written_as_text(): void
    {
        SpreadsheetValueSanitizer::bindStrings();
        $sheet = (new Spreadsheet())->getActiveSheet();
        $sheet->setCellValue('A1', '=SUM(A2:A3)');

        $cell = $sheet->getCell('A1');

        $this->assertSame(DataType::TYPE_STRING, $cell->getDataType());
        $this->assertSame('=SUM(A2:A3)', $cell->getValue());
    }

    public function test_empty_annual_data_routes_are_not_registered(): void
    {
        $this->get('/panel-indicadores/1/datos-anuales')
            ->assertNotFound();
    }
}
