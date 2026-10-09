<?php

namespace Tests\Feature;

use App\Http\Controllers\IndicadorController;
use App\Models\CatEje;
use App\Models\CatPlanEstatalDesarrollo;
use App\Models\Indicador;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IndicatorTemplateTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_downloaded_template_separates_meta_year_and_value(): void
    {
        $response = app(IndicadorController::class)->downloadTemplate();
        ob_start();
        $response->sendContent();
        $content = (string) ob_get_clean();

        $path = storage_path('framework/testing/indicator-template-' . uniqid() . '.xlsx');
        file_put_contents($path, $content);
        $sheet = IOFactory::load($path)->getActiveSheet();

        $this->assertSame('Meta (Año)', $sheet->getCell('M1')->getValue());
        $this->assertSame('Meta (Dato)', $sheet->getCell('N1')->getValue());
        $this->assertSame('2015', (string) $sheet->getCell('X1')->getValue());
        $this->assertSame('2030', (string) $sheet->getCell('AM1')->getValue());
    }

    public function test_import_uses_the_meta_year_and_value_from_the_current_template(): void
    {
        $this->prepareImporter();

        $path = 'temp_imports/indicator-meta-' . uniqid() . '.xlsx';
        Storage::put($path, $this->spreadsheetContent());
        session(['importFilePath' => $path]);

        $response = app(IndicadorController::class)->confirmImport(new Request());
        $this->assertTrue($response->getData(true)['success'], $response->getData(true)['message']);

        $indicator = Indicador::where('nombre', 'Indicador con meta separada')->firstOrFail();
        $this->assertSame(2030, $indicator->meta_anio);
        $this->assertSame('25', $indicator->meta);
        $this->assertSame('25', $indicator->meta_2024);
    }

    public function test_import_accepts_the_legacy_single_meta_column(): void
    {
        $this->prepareImporter();

        $path = 'temp_imports/indicator-legacy-meta-' . uniqid() . '.xlsx';
        Storage::put($path, $this->spreadsheetContent(legacyMeta: true));
        session(['importFilePath' => $path]);

        $response = app(IndicadorController::class)->confirmImport(new Request());
        $this->assertTrue($response->getData(true)['success'], $response->getData(true)['message']);

        $indicator = Indicador::where('nombre', 'Indicador con meta separada')->firstOrFail();
        $this->assertSame(2030, $indicator->meta_anio);
        $this->assertSame('25', $indicator->meta);
    }

    private function prepareImporter(): void
    {
        $plan = CatPlanEstatalDesarrollo::forceCreate([
            'id' => 3,
            'nombre' => 'PED 2024-2030',
            'gobernador' => 'Gobernador de prueba',
        ]);
        CatEje::create([
            'plan_id' => $plan->id,
            'numero' => 1,
            'nombre' => 'Humanismo con Bienestar',
            'color' => '#9d1738',
        ]);
        $user = User::factory()->create();
        Role::create(['name' => 'Administrador']);
        $user->assignRole('Administrador');
        $this->actingAs($user);
    }

    private function spreadsheetContent(bool $legacyMeta = false): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = [
            'ID (Opcional)', 'Nombre Indicador', 'Plan Estatal (Exacto)', 'Tipo Programa (Eje, Sectorial, Especial...)',
            'Nombre Programa Derivado (Exacto)', 'Eje / Programa', 'ID Usuario Responsable', 'ID Institución Responsable',
            'Temática', 'Línea Base (Año)', 'Dato Línea Base', 'Unidad de Medida',
        ];
        $headers = [...$headers, ...($legacyMeta ? ['Meta'] : ['Meta (Año)', 'Meta (Dato)'])];
        $headers = [...$headers, 'Fuente', 'Liga', 'Descripción', 'Periodicidad', 'Cobertura', 'Tendencia', 'Fórmula', 'ODS (Sep. comas)', 'Fecha Actualización'];

        $row = [
            '', 'Indicador con meta separada', 'PED 2024-2030', 'Eje', '', 'Humanismo con Bienestar', '', '',
            'Temática', '2024', '10', 'Porcentaje',
        ];
        $row = [...$row, ...($legacyMeta ? ['25'] : ['2030', '25'])];
        $row = [...$row, 'Fuente', '', '', 'Anual', 'Estatal', 'Mayor es mejor', 'Formula', '', '2026-01-01'];
        $sheet->fromArray([$headers, $row]);

        ob_start();
        (new Xlsx($spreadsheet))->save('php://output');

        return (string) ob_get_clean();
    }
}
