<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CatProgramaDerivadoSectorial;
use Illuminate\Support\Str;

class PublicProgramasController extends Controller
{
    /**
     * Muestra la lista de programas sectoriales.
     */
    public function indexSectoriales()
    {
        $sectoriales = CatProgramaDerivadoSectorial::has('indicadores')->get();
        return view('ped-programas-sectoriales', compact('sectoriales'));
    }

    /**
     * Muestra el detalle de un programa sectorial específico por su slug (nombre).
     */
    public function showSectorial($slug)
    {
        // Buscar el programa cuyo nombre slugificado coincida con el parámetro de la URL
        $programa = CatProgramaDerivadoSectorial::all()->first(function ($item) use ($slug) {
            return Str::slug($item->nombre) === $slug;
        });

        if (!$programa) {
            abort(404, 'Programa Sectorial no encontrado.');
        }

        // Cargar indicadores relacionados con sus ODS y datos anuales validados
        $programa->load([
            'indicadores.ods',
            'indicadores.datosAnuales' => function ($query) {
                $query->where('validado', true);
            }
        ]);

        // Procesar cada indicador para agregar el dato más reciente (lógica homologada con HomeController)
        $programa->indicadores->each(function ($indicador) {
            $datoReciente = $this->obtenerDatoReciente($indicador->datos_anuales_validados);

            // Si no hay dato reciente, usar línea base como fallback (similar a HomeController)
            if (is_null($datoReciente['valor'])) {
                $anioParaVista = $indicador->linea_base;
                $valorOriginalLB = $indicador->dato_linea_base;
                if ($valorOriginalLB !== null && trim((string)$valorOriginalLB) !== '') {
                    $valorNumericoLB = filter_var($valorOriginalLB, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
                    if (is_numeric($valorNumericoLB)) {
                        $valorParaVista = number_format((float)str_replace(',', '', $valorNumericoLB), 2, '.', '');
                    } else {
                        $valorParaVista = $valorOriginalLB;
                    }
                } else {
                    $valorParaVista = 'Sin datos';
                }
                $datoReciente = ['anio' => $anioParaVista, 'valor' => $valorParaVista];
            }

            $indicador->setAttribute('dato_reciente', $datoReciente['valor']);
            $indicador->setAttribute('anio_reciente', $datoReciente['anio']);
        });

        return view('programa-sectorial', compact('programa'));
    }

    /**
     * Helper para obtener el dato más reciente de una colección de datos anuales.
     */
    private function obtenerDatoReciente($datosAnualesCollection)
    {
        if (!$datosAnualesCollection || $datosAnualesCollection->isEmpty()) {
            return ['anio' => null, 'valor' => null];
        }

        $datoRecienteEncontrado = $datosAnualesCollection
            ->filter(function ($datoAnual) {
                return isset($datoAnual->valor_dato) && !is_null($datoAnual->valor_dato) && trim((string) $datoAnual->valor_dato) !== '';
            })
            ->sortByDesc('anio')
            ->first();

        if ($datoRecienteEncontrado) {
            $anio = $datoRecienteEncontrado->anio;
            $valorOriginal = $datoRecienteEncontrado->valor_dato;
            try {
                $valorNumerico = filter_var($valorOriginal, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
                if (is_numeric($valorNumerico)) {
                    $valorFloat = (float) str_replace(',', '', $valorNumerico);
                    return ['anio' => $anio, 'valor' => number_format($valorFloat, 2, '.', '')];
                } else {
                    return ['anio' => $anio, 'valor' => $valorOriginal];
                }
            } catch (\Exception $e) {
                return ['anio' => $anio, 'valor' => $valorOriginal];
            }
        }

        return ['anio' => null, 'valor' => null];
    }
}
