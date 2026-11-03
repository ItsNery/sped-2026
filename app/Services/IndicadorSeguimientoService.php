<?php

namespace App\Services;

use App\Models\Indicador;
use Carbon\Carbon;

class IndicadorSeguimientoService
{
    public const ESTADO_SIN_FECHA_CONFIABLE = 'Sin fecha confiable';
    public const ESTADO_VENCIDO = 'Vencido';
    public const ESTADO_VENCE_HOY = 'Vence hoy';
    public const ESTADO_PROXIMO = 'Próximo';
    public const ESTADO_PROGRAMADO = 'Programado';

    public function __construct(
        private int $ventanaDias = 30,
        private ?Carbon $fechaReferencia = null,
    ) {}

    /**
     * Calcula el seguimiento de un indicador: último dato, periodicidad, fecha
     * próxima, estado de plazo y motivos de información insuficiente.
     *
     * @return array{
     *     ultimo_dato: float|string|null,
     *     ultimo_anio: int|null,
     *     ultimo_dato_validado: bool|null,
     *     periodicidad: string|null,
     *     fecha_proxima_actualizacion: Carbon|null,
     *     fecha_proxima_fuente: string|null,
     *     estado_plazo: string,
     *     avance: float|null,
     *     semaforizacion: string,
     *     evidencia: array{valor_dato: float|string|null, anio: int|null, fecha_actualizacion: Carbon|null, validado: bool|null},
     *     motivos: array<int, string>,
     * }
     */
    public function seguimiento(Indicador $indicador, bool $soloValidados = true): array
    {
        $resultado = $indicador->calcularSemaforizacion($soloValidados);
        $ultimo = $this->ultimoDato($indicador, $soloValidados);
        $fechaProxima = $this->fechaProximaActualizacion($indicador, $soloValidados);

        return [
            'ultimo_dato' => $ultimo['valor'],
            'ultimo_anio' => $ultimo['anio'],
            'ultimo_dato_validado' => $ultimo['validado'],
            'periodicidad' => $indicador->periodicidad,
            'fecha_proxima_actualizacion' => $fechaProxima['fecha'],
            'fecha_proxima_fuente' => $fechaProxima['fuente'],
            'estado_plazo' => $this->estadoPlazo($fechaProxima['fecha']),
            'estado_actualizacion' => $this->estadoActualizacion($indicador->periodicidad, $ultimo['anio'], $fechaProxima['fecha']),
            'avance' => $resultado['avance'],
            'semaforizacion' => $resultado['semaforizacion'],
            'evidencia' => [
                'valor_dato' => $ultimo['valor'],
                'anio' => $ultimo['anio'],
                'fecha_actualizacion' => $ultimo['fecha'],
                'validado' => $ultimo['validado'],
            ],
            'motivos' => $this->motivos($indicador, $ultimo, $resultado['avance'], $fechaProxima['fecha']),
        ];
    }

    private function estadoActualizacion(?string $periodicidad, ?int $ultimoAnio, ?Carbon $fecha): string
    {
        if ($fecha !== null) {
            return match ($this->estadoPlazo($fecha)) {
                self::ESTADO_VENCIDO => 'Desactualizado',
                self::ESTADO_VENCE_HOY => 'Vence hoy',
                default => 'Dentro del plazo',
            };
        }

        if ($ultimoAnio === null) {
            return 'Información insuficiente';
        }

        $periodicidad = mb_strtolower(trim((string) $periodicidad));
        $anioActual = ($this->fechaReferencia ?? Carbon::now())->year;
        $intervalos = ['anual' => 1, 'bienal' => 2, 'bianual' => 2, 'trienal' => 3, 'quinquenal' => 5, 'sexenal' => 6];
        if (isset($intervalos[$periodicidad])) {
            return $ultimoAnio + $intervalos[$periodicidad] <= $anioActual ? 'Desactualizado' : 'Actualizado';
        }

        if (in_array($periodicidad, ['mensual', 'bimestral', 'trimestral', 'cuatrimestral', 'semestral'], true)) {
            return $ultimoAnio < $anioActual ? 'Desactualizado' : 'Dato del año disponible; periodo sin verificar';
        }

        return 'Información insuficiente';
    }

    /**
     * Determina el estado del plazo a partir de la fecha próxima y la ventana.
     */
    public function estadoPlazo(?Carbon $fecha): string
    {
        if ($fecha === null) {
            return self::ESTADO_SIN_FECHA_CONFIABLE;
        }

        $hoy = ($this->fechaReferencia ?? Carbon::now())->copy()->startOfDay();
        $fecha = $fecha->copy()->startOfDay();

        if ($fecha->lt($hoy)) {
            return self::ESTADO_VENCIDO;
        }

        if ($fecha->lte($hoy)) {
            return self::ESTADO_VENCE_HOY;
        }

        if ($fecha->lte($hoy->copy()->addDays($this->ventanaDias))) {
            return self::ESTADO_PROXIMO;
        }

        return self::ESTADO_PROGRAMADO;
    }

    /**
     * Resuelve el último dato con valor del indicador, aplicando el modo de validación.
     *
     * @return array{valor: float|string|null, anio: int|null, validado: bool|null, fecha: Carbon|null}
     */
    private function ultimoDato(Indicador $indicador, bool $soloValidados): array
    {
        $candidatos = $indicador->datosAnuales
            ->filter(fn ($dato) => $dato->valor_dato !== null && trim((string) $dato->valor_dato) !== '');

        if ($soloValidados) {
            $candidatos = $candidatos->where('validado', true);
        }

        $ultimo = $candidatos->sortByDesc('anio')->first();

        if ($ultimo === null) {
            return ['valor' => null, 'anio' => null, 'validado' => null, 'fecha' => null];
        }

        return [
            'valor' => $ultimo->valor_dato,
            'anio' => (int) $ultimo->anio,
            'validado' => (bool) $ultimo->validado,
            'fecha' => $ultimo->fecha_actualizacion,
        ];
    }

    /**
     * Resuelve la fecha próxima de actualización y de dónde proviene.
     *
     * @return array{fecha: Carbon|null, fuente: string|null}
     */
    private function fechaProximaActualizacion(Indicador $indicador, bool $soloValidados): array
    {
        $conFecha = $indicador->datosAnuales->filter(function ($dato) use ($soloValidados) {
            return (!$soloValidados || $dato->validado)
                && $dato->valor_dato !== null && trim((string) $dato->valor_dato) !== ''
                && isset($dato->fecha_actualizacion);
        });

        $fecha = null;
        $fuente = null;

        if ($conFecha->isNotEmpty()) {
            $datoConFecha = $conFecha->sortByDesc('anio')->first();
            $fecha = $this->parseFecha($datoConFecha->fecha_actualizacion);
            $fuente = 'anio';
        } elseif (!empty($indicador->fecha_actualizacion)) {
            $fecha = $this->parseFecha($indicador->fecha_actualizacion);
            $fuente = 'indicador';
        }

        return ['fecha' => $fecha, 'fuente' => $fuente];
    }

    private function parseFecha(mixed $fecha): ?Carbon
    {
        if ($fecha === null || $fecha === '') {
            return null;
        }

        try {
            return Carbon::parse($fecha);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array{valor: float|string|null, anio: int|null, validado: bool|null, fecha: Carbon|null}  $ultimo
     * @return array<int, string>
     */
    private function motivos(Indicador $indicador, array $ultimo, ?float $avance, ?Carbon $fechaProxima): array
    {
        $motivos = [];

        if ($ultimo['valor'] === null) {
            $motivos[] = 'Sin dato anual';
        } elseif ($avance === null) {
            $motivos[] = $this->motivoSinEvaluacion($indicador);
        }

        if ($avance !== null && $avance < 71) {
            $motivos[] = 'Avance insuficiente';
        }

        if (!$indicador->indicador_validado) {
            $motivos[] = 'Pendiente de validación';
        }

        if ($fechaProxima === null) {
            $motivos[] = self::ESTADO_SIN_FECHA_CONFIABLE;
        }

        return array_values(array_unique($motivos));
    }

    private function motivoSinEvaluacion(Indicador $indicador): string
    {
        $meta = str_replace(',', '', (string) $indicador->meta);

        if (!is_numeric($meta) || (float) $meta === 0.0) {
            return 'Sin meta válida';
        }

        return 'Sin tendencia definida';
    }
}
