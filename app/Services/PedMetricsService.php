<?php

namespace App\Services;

use App\Models\CatEje;
use App\Models\CatProgramaDerivadoEspecial;
use App\Models\CatProgramaDerivadoRegional;
use App\Models\CatProgramaDerivadoSectorial;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PedMetricsService
{
    /**
     * Resume el avance de un conjunto de indicadores usando la misma regla
     * para las vistas públicas y administrativas.
     */
    public function summarize(Collection $indicadores, bool $soloValidados = true): array
    {
        $distribucion = [
            'rojo' => 0,
            'amarillo' => 0,
            'verde' => 0,
            'azul' => 0,
            'sin_datos' => 0,
        ];
        $motivosNoEvaluables = [
            'sin_datos' => 0,
            'solo_linea_base' => 0,
            'sin_meta' => 0,
            'sin_tendencia' => 0,
            'otro' => 0,
        ];
        $sumaAvance = 0;
        $evaluables = 0;

        foreach ($indicadores as $indicador) {
            $resultado = $indicador->calcularSemaforizacion($soloValidados);
            $avance = $resultado['avance'];

            if ($avance === null) {
                $distribucion['sin_datos']++;
                $motivosNoEvaluables[$this->motivoNoEvaluable($indicador, $soloValidados)]++;
                continue;
            }

            $evaluables++;
            $sumaAvance += $avance;

            if ($avance >= 110) {
                $distribucion['azul']++;
            } elseif ($avance >= 91) {
                $distribucion['verde']++;
            } elseif ($avance >= 71) {
                $distribucion['amarillo']++;
            } else {
                $distribucion['rojo']++;
            }
        }

        $totalRegistrados = $indicadores->count();
        $noEvaluables = $totalRegistrados - $evaluables;

        return [
            'avance_promedio' => $evaluables > 0 ? round($sumaAvance / $evaluables, 2) : 0,
            'total_registrados' => $totalRegistrados,
            'total_evaluables' => $evaluables,
            'total_no_evaluables' => $noEvaluables,
            'cobertura_evaluacion' => $totalRegistrados > 0
                ? round(($evaluables / $totalRegistrados) * 100, 2)
                : 0,
            'cumplen_o_superan' => $distribucion['verde'] + $distribucion['azul'],
            'distribucion' => $distribucion,
            'motivos_no_evaluables' => $motivosNoEvaluables,
        ];
    }

    public function summarizeCached(Collection $indicadores, bool $soloValidados = true): array
    {
        $version = Cache::get('ped_metrics_version', 1);
        $key = 'ped_metrics:' . $version . ':' . ($soloValidados ? 'validated' : 'all') . ':' . sha1($indicadores->pluck('id')->join(','));

        return Cache::remember($key, now()->addMinutes(10), fn () => $this->summarize($indicadores, $soloValidados));
    }

    public function summarizeCompositionCached(Collection $indicadores): array
    {
        $version = Cache::get('ped_metrics_version', 1);
        $key = 'ped_composition:' . $version . ':' . sha1($indicadores->pluck('id')->join(','));

        return Cache::remember($key, now()->addMinutes(10), fn () => $this->summarizeComposition($indicadores));
    }

    /**
     * Resume la estructura del universo de seguimiento del PED.
     */
    public function summarizeComposition(Collection $indicadores): array
    {
        $types = [
            'Ejes de desarrollo' => 0,
            'Sectoriales' => 0,
            'Especiales' => 0,
            'Regionales' => 0,
            'Institucionales' => 0,
        ];
        $institutionalIds = DB::table('programa_institucional_indicador')
            ->whereIn('indicador_id', $indicadores->pluck('id'))
            ->pluck('indicador_id')
            ->flip();

        foreach ($indicadores as $indicador) {
            $type = match ($indicador->indicadorable_type) {
                CatEje::class => 'Ejes de desarrollo',
                CatProgramaDerivadoSectorial::class => 'Sectoriales',
                CatProgramaDerivadoEspecial::class => 'Especiales',
                CatProgramaDerivadoRegional::class => 'Regionales',
                default => $institutionalIds->has($indicador->id) ? 'Institucionales' : null,
            };

            if ($type !== null) {
                $types[$type]++;
            }
        }

        return [
            'por_tipo' => $types,
            'total' => $indicadores->count(),
            'estrategicos' => $types['Ejes de desarrollo'],
            'derivados' => $types['Sectoriales'] + $types['Especiales'] + $types['Regionales'] + $types['Institucionales'],
            'validados' => $indicadores->where('indicador_validado', true)->count(),
            'pendientes' => $indicadores->where('indicador_validado', false)->count(),
            'instituciones' => $indicadores->whereNotNull('id_institucion')->pluck('id_institucion')->unique()->count(),
        ];
    }

    private function motivoNoEvaluable($indicador, bool $soloValidados): string
    {
        $tieneDatoAnual = $soloValidados
            ? $indicador->datosAnuales()->where('validado', true)->whereNotNull('valor_dato')->exists()
            : $indicador->datosAnuales()->whereNotNull('valor_dato')->exists();

        if (!$tieneDatoAnual && !is_null($indicador->dato_linea_base) && trim((string) $indicador->dato_linea_base) !== '') {
            return 'solo_linea_base';
        }

        if (!$tieneDatoAnual && (is_null($indicador->dato_linea_base) || trim((string) $indicador->dato_linea_base) === '')) {
            return 'sin_datos';
        }

        $meta = $indicador->meta_2024 !== null
            ? str_replace(',', '', (string) $indicador->meta_2024)
            : null;

        if (!is_numeric($meta) || (float) $meta === 0.0) {
            return 'sin_meta';
        }

        if (!in_array(strtolower(trim((string) $indicador->tendencia)), ['mayor es mejor', 'menor es mejor', 'constante'], true)) {
            return 'sin_tendencia';
        }

        return 'otro';
    }
}
