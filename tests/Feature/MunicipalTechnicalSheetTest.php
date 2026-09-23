<?php

namespace Tests\Feature;

use App\Models\CatDimension;
use App\Models\CatMunicipio;
use App\Models\CatNivel;
use App\Models\CatRegion;
use App\Models\CatTipo;
use App\Models\IndicadorMunicipal;
use App\Models\MunicipioConvenio;
use App\Models\PeriodicidadIndicadorMunicipal;
use App\Models\ResultadoIndicadorMunicipal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MunicipalTechnicalSheetTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_ficha_renders_standard_sections_and_current_values(): void
    {
        $datos = $this->municipalFichaData();

        $this->get(route('mostrarFicha', $datos['indicador']))
            ->assertOk()
            ->assertSee('Ficha técnica Indicador municipal de prueba')
            ->assertSee('Plan de Desarrollo Municipal de prueba')
            ->assertSee('municipales.css?v=', false)
            ->assertSee('class="container mt-4 contenedor__ficha ficha-page ficha-municipal-page"', false)
            ->assertSee('Alineación a la planeación')
            ->assertSee('Detalle técnico del indicador')
            ->assertSee('Características del indicador')
            ->assertSee('Seguimiento al indicador')
            ->assertSee('Principales resultados')
            ->assertSee('Comportamiento histórico del indicador')
            ->assertSee('Dependencia municipal de prueba')
            ->assertSee('Fórmula de prueba')
            ->assertSee('Último dato disponible - 2024')
            ->assertSee('45')
            ->assertSeeInOrder(['2024', '45'], false)
            ->assertSee('Resultado del periodo más reciente')
            ->assertSee('Meta 2027')
            ->assertSee('Descargar ficha')
            ->assertSee('function valueLabel', false)
            ->assertSee("label: valueLabel('#246257', 'top')", false)
            ->assertSee('labelLayout', false)
            ->assertDontSee('Imprimir ficha')
            ->assertDontSee('Valor Alcanzado')
            ->assertDontSee('<table', false)
            ->assertDontSee('Volver a indicadores')
            ->assertDontSee('Meta 2030');
    }

    public function test_public_ficha_uses_the_municipality_fallback_without_an_agreement(): void
    {
        $datos = $this->municipalFichaData(withAgreement: false);

        $this->get(route('mostrarFicha', $datos['indicador']))
            ->assertOk()
            ->assertSee('Municipio de prueba')
            ->assertDontSee('Volver a indicadores');
    }

    public function test_municipal_pdf_renders_the_same_indicator_details(): void
    {
        $datos = $this->municipalFichaData();
        $indicador = $datos['indicador']->fresh()->load([
            'ods',
            'periodicidad',
            'tipo',
            'nivel',
            'dimension',
        ]);
        $indicador->setAttribute('dato_2023', 10);
        $indicador->setAttribute('dato_2024', 45);

        $pdf = view('ficha-tecnica-municipal-pdf', [
            'indicador' => $indicador,
            'municipio' => $datos['municipio'],
            'nombreMunicipio' => 'Municipio de prueba',
            'ultimoDato' => $datos['ultimoDato'],
            'ultimoResultado' => $datos['ultimoResultado'],
            'anioMeta' => 2027,
            'anioInicioGrafica' => 2023,
            'anioFinGrafica' => 2027,
            'pdfAsset' => static fn (string $path): string => $path,
            'pdfEcharts' => '',
        ])->render();

        $this->assertStringContainsString('Alineación a la planeación', $pdf);
        $this->assertStringContainsString('Detalle técnico del indicador', $pdf);
        $this->assertStringContainsString('Características del indicador', $pdf);
        $this->assertStringContainsString('Seguimiento al indicador', $pdf);
        $this->assertStringContainsString('Plan de Desarrollo Municipal de prueba', $pdf);
        $this->assertStringContainsString('Resultado del periodo más reciente', $pdf);
        $this->assertStringContainsString('Meta 2027', $pdf);
        $this->assertStringContainsString('function valueLabel', $pdf);
        $this->assertStringContainsString("label: valueLabel('#246257', 'top')", $pdf);
        $this->assertStringContainsString('labelLayout', $pdf);
        $this->assertStringNotContainsString('Valor alcanzado', $pdf);
        $this->assertStringNotContainsString('<table', $pdf);
        $this->assertStringNotContainsString('Meta 2030', $pdf);
    }

    /**
     * @return array{
     *     indicador: IndicadorMunicipal,
     *     municipio: ?MunicipioConvenio,
     *     ultimoDato: ResultadoIndicadorMunicipal,
     *     ultimoResultado: ResultadoIndicadorMunicipal
     * }
     */
    private function municipalFichaData(bool $withAgreement = true): array
    {
        $region = CatRegion::create(['nombre_region' => 'Región de prueba']);
        $municipio = CatMunicipio::create([
            'nombre' => 'Municipio de prueba',
            'region_id' => $region->id,
        ]);
        $municipioConvenio = $withAgreement
            ? MunicipioConvenio::create([
                'id_municipio' => $municipio->id,
                'icono' => 'icono.png',
                'objetivo' => 'Objetivo de prueba',
                'convenio' => 'convenio.pdf',
                'banner' => 'banner.jpg',
            ])
            : null;
        $periodicidad = PeriodicidadIndicadorMunicipal::create(['nombre' => 'Anual']);
        $tipo = CatTipo::create(['nombre' => 'Gestión']);
        $nivel = CatNivel::create(['nombre' => 'Componente', 'tipo_id' => $tipo->id]);
        $dimension = CatDimension::create(['nombre' => 'Eficacia', 'nivel_id' => $nivel->id]);
        $indicador = IndicadorMunicipal::create([
            'indicador' => 'Indicador municipal de prueba',
            'instrumento' => 'Plan de Desarrollo Municipal de prueba',
            'eje_indicador' => 'Eje de prueba',
            'tematica' => 'Temática de prueba',
            'descripcion' => 'Descripción de prueba',
            'unidad_medida' => 'Porcentaje',
            'linea_base' => 2023,
            'dato_linea' => 10,
            'meta_2024' => 60,
            'fuente' => 'Fuente municipal de prueba',
            'liga' => 'https://example.test/fuente',
            'periodicidad_id' => $periodicidad->id,
            'cobertura' => 'Municipal',
            'tendencia' => 'Ascendente',
            'id_tipo' => $tipo->id,
            'id_nivel' => $nivel->id,
            'id_dimension' => $dimension->id,
            'formula' => 'Fórmula de prueba',
            'dependencia' => 'Dependencia municipal de prueba',
            'publica' => true,
            'id_municipio' => $municipio->id,
            'proxima_actualizacion' => '2027-01-31',
        ]);
        $ultimoDato = ResultadoIndicadorMunicipal::create([
            'id_indicador' => $indicador->id,
            'periodicidad_id' => $periodicidad->id,
            'año' => 2024,
            'periodo' => 2,
            'dato' => 45,
            'resultado' => 'Resultado de cierre',
        ]);
        ResultadoIndicadorMunicipal::create([
            'id_indicador' => $indicador->id,
            'periodicidad_id' => $periodicidad->id,
            'año' => 2024,
            'periodo' => 3,
            'dato' => null,
            'resultado' => 'Resultado sin dato',
        ]);
        $ultimoResultado = ResultadoIndicadorMunicipal::create([
            'id_indicador' => $indicador->id,
            'periodicidad_id' => $periodicidad->id,
            'año' => 2025,
            'periodo' => 1,
            'dato' => null,
            'resultado' => 'Resultado del periodo más reciente',
        ]);

        return [
            'indicador' => $indicador,
            'municipio' => $municipioConvenio,
            'ultimoDato' => $ultimoDato,
            'ultimoResultado' => $ultimoResultado,
        ];
    }
}
