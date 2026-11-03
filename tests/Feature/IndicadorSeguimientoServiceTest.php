<?php

namespace Tests\Feature;

use App\Models\Indicador;
use App\Services\IndicadorSeguimientoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndicadorSeguimientoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_devuelve_estado_vencido_cuando_la_fecha_proxima_es_ante_hoy(): void
    {
        $this->travelTo(Carbon::parse('2026-11-03 12:00:00'));
        $indicador = $this->createIndicator('2020-01-01');
        $service = new IndicadorSeguimientoService();

        $seguimiento = $service->seguimiento($indicador, true);

        $this->assertSame('Vencido', $seguimiento['estado_plazo']);
    }

    public function test_devuelve_estado_vence_hoy_cuando_la_fecha_proxima_es_hoy(): void
    {
        $this->travelTo(Carbon::parse('2026-11-03 12:00:00'));
        $indicador = $this->createIndicator('2026-11-03');
        $service = new IndicadorSeguimientoService();

        $seguimiento = $service->seguimiento($indicador, true);

        $this->assertSame('Vence hoy', $seguimiento['estado_plazo']);
    }

    public function test_devuelve_estado_proximo_cuando_la_fecha_proxima_esta_dentro_de_la_ventana(): void
    {
        $this->travelTo(Carbon::parse('2026-11-03 12:00:00'));
        $indicador = $this->createIndicator('2026-11-13');
        $service = new IndicadorSeguimientoService();

        $seguimiento = $service->seguimiento($indicador, true);

        $this->assertSame('Próximo', $seguimiento['estado_plazo']);
    }

    public function test_devuelve_estado_programado_cuando_la_fecha_proxima_esta_detras_de_la_ventana(): void
    {
        $this->travelTo(Carbon::parse('2026-11-03 12:00:00'));
        $indicador = $this->createIndicator('2027-09-11');
        $service = new IndicadorSeguimientoService();

        $seguimiento = $service->seguimiento($indicador, true);

        $this->assertSame('Programado', $seguimiento['estado_plazo']);
    }

    public function test_devuelve_sin_fecha_confiable_cuando_no_hay_fecha_proxima(): void
    {
        $service = new IndicadorSeguimientoService();

        $this->assertSame('Sin fecha confiable', $service->estadoPlazo(null));
    }

    public function test_usa_la_fecha_del_indicador_como_fuente_cuando_no_hay_datos_anuales_con_fecha(): void
    {
        $this->travelTo(Carbon::parse('2026-11-03 12:00:00'));
        $indicador = $this->createIndicator('2026-11-03');
        $service = new IndicadorSeguimientoService();

        $seguimiento = $service->seguimiento($indicador, true);

        $this->assertSame('indicador', $seguimiento['fecha_proxima_fuente']);
        $this->assertSame('Vence hoy', $seguimiento['estado_plazo']);
    }

    public function test_usa_el_anio_del_dato_con_fecha_como_fuente(): void
    {
        $indicador = $this->createIndicator('2020-01-01');
        $indicador->datosAnuales()->create([
            'anio' => 2025,
            'valor_dato' => 10,
            'fecha_actualizacion' => '2024-05-10',
            'validado' => true,
            'modificado' => false,
        ]);
        $indicador->refresh();
        $service = new IndicadorSeguimientoService();

        $seguimiento = $service->seguimiento($indicador, true);

        $this->assertSame('anio', $seguimiento['fecha_proxima_fuente']);
        $this->assertSame('2024-05-10', $seguimiento['fecha_proxima_actualizacion']->toDateString());
    }

    public function test_devuelve_el_ultimo_dato_y_su_validacion_con_el_modo_seleccionado(): void
    {
        $indicador = $this->createIndicator('2020-01-01');
        $indicador->datosAnuales()->create([
            'anio' => 2024,
            'valor_dato' => 10,
            'fecha_actualizacion' => '2023-05-10',
            'validado' => false,
            'modificado' => true,
        ]);
        $indicador->datosAnuales()->create([
            'anio' => 2025,
            'valor_dato' => 20,
            'fecha_actualizacion' => '2024-05-10',
            'validado' => true,
            'modificado' => false,
        ]);
        $service = new IndicadorSeguimientoService();

        $soloValidados = $service->seguimiento($indicador, true);
        $todos = $service->seguimiento($indicador, false);

        $this->assertEquals(20, $soloValidados['ultimo_dato']);
        $this->assertSame(2025, $soloValidados['ultimo_anio']);
        $this->assertTrue($soloValidados['ultimo_dato_validado']);
        $this->assertEquals(20, $todos['ultimo_dato']);
        $this->assertSame(2025, $todos['ultimo_anio']);
        $this->assertTrue($todos['ultimo_dato_validado']);
    }

    public function test_respeta_validacion_y_conserva_un_valor_cero(): void
    {
        $indicador = $this->createIndicator('2028-01-01');
        $indicador->datosAnuales()->create(['anio' => 2024, 'valor_dato' => 0, 'fecha_actualizacion' => '2027-01-01', 'validado' => true]);
        $indicador->datosAnuales()->create(['anio' => 2025, 'valor_dato' => 12, 'fecha_actualizacion' => '2026-01-01', 'validado' => false]);
        $indicador->refresh();
        $service = new IndicadorSeguimientoService();

        $validados = $service->seguimiento($indicador, true);
        $todos = $service->seguimiento($indicador, false);

        $this->assertEquals(0, $validados['ultimo_dato']);
        $this->assertSame(2024, $validados['ultimo_anio']);
        $this->assertSame('2027-01-01', $validados['fecha_proxima_actualizacion']->toDateString());
        $this->assertSame(2025, $todos['ultimo_anio']);
        $this->assertFalse($todos['ultimo_dato_validado']);
        $this->assertSame('2026-01-01', $todos['fecha_proxima_actualizacion']->toDateString());
    }

    public function test_clasifica_actualizacion_por_fecha_y_periodicidad(): void
    {
        $this->travelTo(Carbon::parse('2026-06-15 12:00:00'));
        $service = new IndicadorSeguimientoService();
        $casos = [
            ['Anual', 2025, null, 'Desactualizado'],
            ['Anual', 2026, null, 'Actualizado'],
            ['Bienal', 2025, null, 'Actualizado'],
            ['Bienal', 2024, null, 'Desactualizado'],
            ['Trimestral', 2026, null, 'Dato del año disponible; periodo sin verificar'],
            ['Trimestral', 2025, null, 'Desactualizado'],
            ['Ciclo escolar', 2026, null, 'Información insuficiente'],
            ['Anual', null, null, 'Información insuficiente'],
            ['Anual', 2025, '2026-06-16', 'Dentro del plazo'],
            ['Anual', 2026, '2026-06-15', 'Vence hoy'],
            ['Anual', 2026, '2026-06-14', 'Desactualizado'],
        ];

        foreach ($casos as [$periodicidad, $anio, $fecha, $estado]) {
            $indicador = $this->createIndicator($fecha ?? '2028-01-01');
            $indicador->periodicidad = $periodicidad;
            if ($anio !== null) {
                $indicador->datosAnuales()->create(['anio' => $anio, 'valor_dato' => 0, 'validado' => true]);
            }
            $indicador->refresh();
            $indicador->periodicidad = $periodicidad;
            $indicador->fecha_actualizacion = $fecha;

            $this->assertSame($estado, $service->seguimiento($indicador)['estado_actualizacion'], "$periodicidad / $anio / $fecha");
        }
    }

    private function createIndicator(?string $fechaActualizacion): Indicador
    {
        return Indicador::create([
            'nombre' => 'Indicador de prueba',
            'programa_derivado' => 'Plan de prueba',
            'programa' => 'Eje de prueba',
            'cod_tematica' => 'T1',
            'tematica' => 'Temática de prueba',
            'linea_base' => 2023,
            'dato_linea_base' => '10',
            'meta_anio' => 2030,
            'meta' => '20',
            'unidad_medida' => 'Porcentaje',
            'fuente' => 'Fuente de prueba',
            'descripcion' => 'Descripción de prueba',
            'periodicidad' => 'Anual',
            'cobertura' => 'Estatal',
            'tendencia' => 'Mayor es mejor',
            'fecha_actualizacion' => $fechaActualizacion,
            'formula' => 'Dato / meta',
            'indicador_validado' => false,
        ]);
    }
}
