<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndicadorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $semaforo = $this->calcularSemaforizacion(true);

        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'slug' => $this->slug,
            'descripcion' => $this->descripcion,
            'programa_derivado' => $this->programa_derivado,
            'programa' => $this->programa,
            'tematica' => $this->tematica,
            'linea_base' => $this->linea_base,
            'dato_linea_base' => $this->dato_linea_base,
            'meta_2024' => $this->meta_2024,
            'unidad_medida' => $this->unidad_medida,
            'fuente' => $this->fuente,
            'liga' => $this->liga,
            'periodicidad' => $this->periodicidad,
            'cobertura' => $this->cobertura,
            'tendencia' => $this->tendencia,
            'formula' => $this->formula,
            'fecha_actualizacion' => $this->fecha_actualizacion,
            'indicador_validado' => (bool) $this->indicador_validado,
            'institucion' => $this->whenLoaded('institucion', fn () => $this->institucion ? [
                'id' => $this->institucion->id,
                'nombre' => $this->institucion->nombre,
                'titular' => $this->institucion->titular,
            ] : null),
            'ods' => $this->whenLoaded('ods', fn () => $this->ods->map(fn ($ods) => [
                'id' => $ods->id,
                'nombre' => $ods->nombre,
            ])->values()->all()),
            'datos_anuales' => $this->whenLoaded('datosAnuales', fn () => $this->datosAnuales->map(fn ($dato) => [
                'id' => $dato->id,
                'anio' => $dato->anio,
                'valor_dato' => $dato->valor_dato,
                'resultados' => $dato->resultados,
                'observaciones' => $dato->observaciones,
                'fecha_actualizacion' => $dato->fecha_actualizacion?->format('Y-m-d'),
            ])->values()->all()),
            'avance_real_time' => $semaforo['avance'],
            'semaforo_real_time' => $semaforo['semaforizacion'],
            'anio_ultimo_dato_validado' => $semaforo['anio_ultimo_dato'],
            'ultimo_dato_validado' => $semaforo['ultimo_dato'],
        ];
    }
}
