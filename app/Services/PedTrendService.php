<?php

namespace App\Services;

use Illuminate\Support\Collection;

class PedTrendService
{
    /**
     * Resume el comportamiento histórico observado de un universo de indicadores.
     */
    public function summarize(Collection $indicadores, bool $soloValidados = true, ?int $anioDesde = null, ?int $anioHasta = null): array
    {
        $series = [];
        $comparaciones = [
            'mejoran' => 0,
            'retroceden' => 0,
            'estables' => 0,
            'sin_comparacion' => 0,
        ];
        $mejoras = [];
        $retrocesos = [];

        foreach ($indicadores as $indicador) {
            $datos = $indicador->datosAnuales
                ->filter(function ($dato) use ($soloValidados) {
                    if ($soloValidados && !$dato->validado) {
                        return false;
                    }

                    return $dato->anio !== null
                        && $dato->valor_dato !== null
                        && trim((string) $dato->valor_dato) !== ''
                        && is_numeric(str_replace(',', '', (string) $dato->valor_dato));
                })
                ->sortBy('anio')
                ->groupBy('anio')
                ->map(fn ($datosAnio) => $datosAnio->sortByDesc('id')->first());

            if ($anioDesde !== null) {
                $datos = $datos->filter(fn ($dato, $anio) => (int) $anio >= $anioDesde);
            }
            if ($anioHasta !== null) {
                $datos = $datos->filter(fn ($dato, $anio) => (int) $anio <= $anioHasta);
            }

            $avances = $datos->mapWithKeys(function ($dato, $anio) use ($indicador) {
                $avance = $this->calcularAvance($indicador, (float) str_replace(',', '', (string) $dato->valor_dato));
                return [(int) $anio => $avance];
            })->filter(fn ($avance) => $avance !== null);

            foreach ($avances as $anio => $avance) {
                if (!isset($series[$anio])) {
                    $series[$anio] = ['avance' => 0, 'evaluables' => 0, 'indicadores' => 0];
                }
                $series[$anio]['indicadores']++;
                $series[$anio]['avance'] += $avance;
                $series[$anio]['evaluables']++;
            }

            $historico = $avances->sortKeys();
            $actual = $historico->last();
            $anterior = $historico->slice(-2, 1)->first();

            if ($actual === null || $anterior === null) {
                $comparaciones['sin_comparacion']++;
                continue;
            }

            $variacion = $actual - $anterior;
            $registro = [
                'id' => $indicador->id,
                'nombre' => $indicador->nombre,
                'institucion' => $indicador->institucion?->nombre ?? 'Sin institución',
                'variacion' => round($variacion, 2),
                'avance_actual' => round($actual, 2),
            ];

            if (abs($variacion) <= 1) {
                $comparaciones['estables']++;
            } elseif ($variacion > 0) {
                $comparaciones['mejoran']++;
                $mejoras[] = $registro;
            } else {
                $comparaciones['retroceden']++;
                $retrocesos[] = $registro;
            }
        }

        $series = collect($series)->sortKeys()->map(function ($datos, $anio) {
            return [
                'anio' => (int) $anio,
                'avance' => $datos['evaluables'] > 0 ? round($datos['avance'] / $datos['evaluables'], 2) : null,
                'evaluables' => $datos['evaluables'],
                'indicadores' => $datos['indicadores'],
            ];
        })->values();

        return [
            'series' => $series,
            'comparaciones' => $comparaciones,
            'mejoras' => collect($mejoras)->sortByDesc('variacion')->take(5)->values(),
            'retrocesos' => collect($retrocesos)->sortBy('variacion')->take(5)->values(),
            'anio_desde' => $series->first()['anio'] ?? $anioDesde,
            'anio_hasta' => $series->last()['anio'] ?? $anioHasta,
        ];
    }

    private function calcularAvance($indicador, float $valor): ?float
    {
        $meta = str_replace(',', '', (string) $indicador->meta);
        if (!is_numeric($meta) || (float) $meta === 0.0) {
            return null;
        }

        $meta = (float) $meta;
        $tendencia = strtolower(trim((string) $indicador->tendencia));

        return match ($tendencia) {
            'mayor es mejor', 'constante' => ($valor / $meta) * 100,
            'menor es mejor' => $valor == 0.0 ? null : ($meta / $valor) * 100,
            default => null,
        };
    }
}
