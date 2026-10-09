<?php

namespace Tests\Feature;

use App\Models\Indicador;
use App\Models\Institucion;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FichaTechnicalPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_repeats_the_logos_with_a_table_header(): void
    {
        $pdf = view('ficha-tecnica-pdf', [
            'indicador' => $this->fichaIndicator(),
            'chartConfig' => ['ultimoDato' => null, 'anioUltimoDato' => null, 'chartVal' => 0],
            'semaforizacion' => 'No Clasificado',
            'colorSemaforo' => '#6c757d',
            'esDatoLineaBase' => true,
            'pdfAsset' => static fn (string $path): string => $path,
            'pdfCss' => file_get_contents(public_path('css/estilos.css')),
            'pdfEcharts' => '',
            'pdfFichaJs' => '',
        ])->render();

        $this->assertStringContainsString('<thead class="ficha-pdf__header">', $pdf);
        $this->assertStringContainsString('class="ficha-pdf__intro"', $pdf);
        $this->assertStringContainsString('Ficha técnica del indicador', $pdf);
        $this->assertStringContainsString('Indicador de prueba', $pdf);
        $this->assertMatchesRegularExpression('/\.ficha-pdf__header\s*\{\s*display:\s*table-header-group;/', $pdf);
        $this->assertMatchesRegularExpression('/@page\s*\{[^}]*margin:\s*5mm 5mm 16mm;/', $pdf);
        $this->assertMatchesRegularExpression(
            '/\.ficha-pdf__heading\s*\{[^}]*background:\s*rgba\(12,\s*49,\s*45,\s*0\.08\);/',
            $pdf,
        );
        $this->assertSame(1, substr_count($pdf, 'class="ficha-pdf__subtitle"'));
    }

    public function test_ficha_page_prevents_duplicate_pdf_downloads(): void
    {
        $page = view('ficha-tecnica', ['indicador' => $this->fichaIndicator()])->render();

        $this->assertStringContainsString('id="fichaDownloadButton"', $page);
        $this->assertStringContainsString('data-download-label', $page);
        $this->assertStringContainsString("downloadButton.dataset.generating = 'true';", $page);
        $this->assertStringContainsString("textContent = 'Generando ficha...';", $page);
        $this->assertStringContainsString('await fetch(downloadButton.href)', $page);
        $this->assertStringContainsString('resetDownloadButton();', $page);
    }

    public function test_download_reuses_cached_pdf_when_the_ficha_content_is_unchanged(): void
    {
        Storage::fake('local');
        $institucion = Institucion::create([
            'nombre' => 'Institución de prueba',
            'titular' => 'Titular de prueba',
        ]);
        $indicador = Indicador::create([
            'nombre' => 'Indicador cacheado',
            'programa_derivado' => 'Programa Sectorial',
            'programa' => 'Programa de prueba',
            'cod_tematica' => 'T1',
            'tematica' => 'Temática de prueba',
            'id_institucion' => $institucion->id,
            'linea_base' => 2024,
            'dato_linea_base' => 10,
            'meta_anio' => 2030,
            'meta' => 20,
            'meta_2024' => 20,
            'unidad_medida' => 'Porcentaje',
            'fuente' => 'Fuente de prueba',
            'descripcion' => 'Descripción de prueba',
            'periodicidad' => 'Anual',
            'cobertura' => 'Estatal',
            'tendencia' => 'Mayor es mejor',
            'fecha_actualizacion' => '2026-01-01',
            'formula' => 'Dato / meta',
        ]);
        $preview = $this->get(route('ficha-tecnica.preview', $indicador))->assertOk();
        $cachePath = "fichas-tecnicas/{$indicador->id}.pdf";
        $fingerprintPath = "fichas-tecnicas/{$indicador->id}.sha256";
        $pdfCacheado = 'PDF desde cache';

        Storage::disk('local')->put($cachePath, $pdfCacheado);
        Storage::disk('local')->put($fingerprintPath, hash('sha256', $preview->getContent()));

        $this->get(route('ficha-tecnica.download', $indicador))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertContent($pdfCacheado);
    }

    private function fichaIndicator(): Indicador
    {
        $indicador = new Indicador([
            'nombre' => 'Indicador de prueba',
            'slug' => 'indicador-de-prueba',
            'programa_derivado' => 'Programa derivado de prueba',
            'programa' => 'Programa de prueba',
            'tematica' => 'Temática de prueba',
            'descripcion' => 'Descripción de prueba',
            'formula' => 'Fórmula de prueba',
            'unidad_medida' => 'Porcentaje',
            'linea_base' => 2024,
            'dato_linea_base' => 10,
            'tendencia' => 'Ascendente',
            'meta_anio' => 2030,
            'meta' => 20,
            'fuente' => 'Fuente de prueba',
            'cobertura' => 'Estatal',
        ]);
        $indicador->setRelation('institucion', new Institucion(['nombre' => 'Institución de prueba']));
        $indicador->setRelation('indicadorable', null);
        $indicador->setRelation('programasInstitucionales', new EloquentCollection());
        $indicador->setRelation('datosAnuales', new EloquentCollection());
        $indicador->setRelation('ods', new EloquentCollection());

        return $indicador;
    }
}
