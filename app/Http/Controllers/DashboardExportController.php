<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Browsershot\Browsershot;

class DashboardExportController extends Controller
{
    public function pdf(Request $request)
    {
        $this->ensureAccess();
        $data = $this->dashboardData($request);
        $html = view('exports.dashboard-executive-pdf', $data)->render();

        $pdf = Browsershot::html($html)
            ->setNodeBinary(config('browsershot.node_binary', 'node'))
            ->setNodeModulePath(base_path('node_modules'))
            ->setEnvironmentOptions([
                'PUPPETEER_CACHE_DIR' => storage_path('app/puppeteer'),
            ])
            ->paperSize(297, 210, 'mm')
            ->margins(8, 8, 10, 8)
            ->showBackground()
            ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
            ->pdf();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="dashboard-ped-' . now()->format('Ymd-His') . '.pdf"',
        ]);
    }

    public function xlsx(Request $request)
    {
        $this->ensureAccess();
        $data = $this->dashboardData($request);
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('SPED')
            ->setTitle('Dashboard ejecutivo del PED');

        $this->writeSummary($spreadsheet->getActiveSheet(), $data);
        $this->writePriorities($spreadsheet->createSheet(), $data['actionQueue']);
        $this->writeAxes($spreadsheet->createSheet(), $data['ejesData']);
        $this->writeInstitutions($spreadsheet->createSheet(), $data['institucionesData']);
        $this->writePrograms($spreadsheet->createSheet(), $data['programasData']);
        $this->writeTrend($spreadsheet->createSheet(), $data['trend']);
        $this->writeMethodology($spreadsheet->createSheet());

        $directory = storage_path('app/exports');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $path = $directory . DIRECTORY_SEPARATOR . 'dashboard-ped-' . uniqid('', true) . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, 'dashboard-ped-' . now()->format('Ymd-His') . '.xlsx')->deleteFileAfterSend(true);
    }

    private function dashboardData(Request $request): array
    {
        $view = app(DashboardController::class)->index($request);
        abort_unless(method_exists($view, 'getData'), 500, 'No fue posible preparar el dashboard.');

        return $view->getData();
    }

    private function ensureAccess(): void
    {
        $user = auth()->user();
        abort_unless(
            $user
                && (int) $user->id_municipio === 0
                && ($user->isAdministrator() || $user->can('ver-panel-avance-general')),
            403
        );
    }

    private function styleSheet($sheet, string $title, array $headers): void
    {
        $sheet->setTitle(substr($title, 0, 31));
        $sheet->fromArray([$headers], null, 'A1');
        $lastColumn = $sheet->getHighestColumn();
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0C312D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->freezePane('A2');
        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    private function writeSummary($sheet, array $data): void
    {
        $sheet->setTitle('Resumen');
        $sheet->fromArray([
            ['Dashboard ejecutivo del PED', null],
            ['Plan', $data['plan']->nombre],
            ['Modo', $data['soloValidados'] ? 'Solo validados' : 'Todos los registrados'],
            ['Fecha de corte', $data['fechaCorte']?->format('d/m/Y') ?? 'Sin fecha'],
            [],
            ['Métrica', 'Valor'],
            ['Avance promedio', $data['avanceGlobalPromedio']],
            ['Cobertura de evaluación', $data['metricasGlobal']['cobertura_evaluacion']],
            ['Indicadores registrados', $data['totalIndicadores']],
            ['Indicadores validados', $data['totalIndicadoresValidados']],
            ['Señales críticas', $data['indicadoresCriticos']],
        ], null, 'A1');
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 15, 'color' => ['rgb' => '0C312D']],
        ]);
        $sheet->getStyle('A6:B6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0C312D']],
        ]);
        $sheet->getColumnDimension('A')->setWidth(32);
        $sheet->getColumnDimension('B')->setWidth(24);
    }

    private function writePriorities($sheet, $items): void
    {
        $this->styleSheet($sheet, 'Prioridades', ['Indicador', 'Institución', 'Motivo', 'Avance', 'Último dato', 'Año']);
        $rows = $items->map(fn ($item) => [$item['nombre'], $item['institucion'], $item['motivo'], $item['avance'], $item['fecha_dato'], $item['anio']])->all();
        if ($rows) $sheet->fromArray($rows, null, 'A2');
    }

    private function writeAxes($sheet, $items): void
    {
        $this->styleSheet($sheet, 'Ejes', ['Eje', 'Nombre', 'Avance', 'Cobertura', 'Evaluables', 'Total']);
        $rows = collect($items)->map(fn ($item) => [$item['numero'], $item['nombre'], $item['avance'], $item['cobertura'], $item['evaluables'], $item['total']])->all();
        if ($rows) $sheet->fromArray($rows, null, 'A2');
    }

    private function writeInstitutions($sheet, $items): void
    {
        $this->styleSheet($sheet, 'Instituciones', ['Institución', 'Avance', 'Cobertura', 'Validados', 'Total', 'Señales']);
        $rows = collect($items)->map(fn ($item) => [$item['nombre'], $item['avance'], $item['cobertura'], $item['validados'], $item['total'], $item['criticos']])->all();
        if ($rows) $sheet->fromArray($rows, null, 'A2');
    }

    private function writePrograms($sheet, $groups): void
    {
        $this->styleSheet($sheet, 'Programas', ['Programa', 'Tipo', 'Avance', 'Cobertura', 'Evaluables', 'Total']);
        $rows = $groups->flatMap(fn ($group) => $group['programas'])->map(fn ($item) => [$item['nombre'], $item['tipo'], $item['avance'], $item['cobertura'], $item['evaluables'], $item['total_indicadores']])->all();
        if ($rows) $sheet->fromArray($rows, null, 'A2');
    }

    private function writeTrend($sheet, array $trend): void
    {
        $this->styleSheet($sheet, 'Serie histórica', ['Año', 'Avance promedio', 'Evaluables', 'Indicadores']);
        $rows = collect($trend['series'])->map(fn ($item) => [$item['anio'], $item['avance'], $item['evaluables'], $item['indicadores']])->all();
        if ($rows) $sheet->fromArray($rows, null, 'A2');
    }

    private function writeMethodology($sheet): void
    {
        $sheet->setTitle('Metodología');
        $sheet->fromArray([
            ['Elemento', 'Descripción'],
            ['Modo de datos', 'La exportación conserva el filtro de datos validados o todos los registrados.'],
            ['Avance', 'Se calcula contra la meta y la tendencia configurada para cada indicador.'],
            ['Semáforo', 'Excedido >= 110%; Aceptable >= 91%; Moderado >= 71%; Insuficiente < 71%.'],
            ['Evolución', 'Compara los dos últimos años disponibles por indicador.'],
            ['Proyección', 'La serie histórica representa comportamiento observado y no constituye un pronóstico.'],
        ], null, 'A1');
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0C312D']],
        ]);
        $sheet->getColumnDimension('A')->setWidth(24);
        $sheet->getColumnDimension('B')->setWidth(100);
        $sheet->getStyle('B2:B6')->getAlignment()->setWrapText(true);
    }
}
