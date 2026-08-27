@php
    $decimalesIndicador = $decimalesIndicador ?? 2;
    $normalizarNumero = static function ($valor) {
        if ($valor === null || trim((string) $valor) === '') {
            return null;
        }

        $valorNormalizado = str_replace(',', '', (string) $valor);

        return is_numeric($valorNormalizado) ? (float) $valorNormalizado : null;
    };
    $formatearValor = static function ($valor) use ($normalizarNumero, $decimalesIndicador) {
        $valorNumerico = $normalizarNumero($valor);

        return $valorNumerico === null
            ? ($valor === null || trim((string) $valor) === '' ? 'N/D' : (string) $valor)
            : number_format($valorNumerico, $decimalesIndicador, '.', ',');
    };

    $datosGrafico = $indicador->datos_anuales_validados
        ->filter(fn ($dato) => $normalizarNumero($dato->valor_dato) !== null)
        ->map(fn ($dato) => [
            'periodo' => (string) $dato->anio,
            'valor' => $normalizarNumero($dato->valor_dato),
        ]);

    $valorLineaBase = $normalizarNumero($indicador->dato_linea_base);
    $anioLineaBase = trim((string) $indicador->linea_base);
    if ($valorLineaBase !== null && $anioLineaBase !== '' && !$datosGrafico->contains('periodo', $anioLineaBase)) {
        $datosGrafico->push([
            'periodo' => $anioLineaBase,
            'valor' => $valorLineaBase,
        ]);
    }

    $datosGrafico = $datosGrafico
        ->sortBy(fn ($dato) => (int) $dato['periodo'])
        ->values()
        ->all();
    $metaGrafico = $normalizarNumero($indicador->meta);
    $avanceTexto = isset($indicador->avance_validado)
        ? number_format((float) $indicador->avance_validado, 2) . '% de avance'
        : 'Avance no disponible';
@endphp

<article class="eje-indicator-grid-card" style="--semaforo-color: {{ $colorSemaforo }};">
    <header class="eje-indicator-grid-card__header">
        <a href="{{ route('ficha-tecnica.show', $indicador) }}" title="{{ $indicador->nombre }}">
            {{ $indicador->nombre }}
        </a>
        @if ($indicador->ods->isNotEmpty())
            <div class="eje-indicator-grid-card__ods" aria-label="Objetivos de Desarrollo Sostenible relacionados">
                @foreach ($indicador->ods->unique('id') as $ods_item)
                    <img src="{{ asset('/img/Icons_ODS/' . $ods_item->id . '.png') }}"
                        title="{{ $ods_item->nombre }}" alt="ODS {{ $ods_item->id }}">
                @endforeach
            </div>
        @endif
    </header>

    <div class="eje-indicator-grid-card__main">
        <div class="eje-indicator-grid-card__result">
            <span>Resultado {{ $indicador->anio_reciente_validado ?? '' }}</span>
            <strong class="indicador-cifra {{ $resultadoClase }}">{{ $resultadoTexto }}</strong>
            <small title="{{ $indicador->unidad_medida }}">{{ $indicador->unidad_medida ?: 'Sin unidad de medida' }}</small>
        </div>

        @if (count($datosGrafico) > 0)
            <div class="eje-indicator-mini-chart"
                data-indicator-mini-chart='@json(['datos' => $datosGrafico, 'meta' => $metaGrafico, 'color' => $colorSemaforo])'
                role="img" aria-label="Evolución histórica de {{ $indicador->nombre }}"></div>
        @else
            <div class="eje-indicator-grid-card__chart-empty">Histórico no disponible</div>
        @endif
    </div>

    <dl class="eje-indicator-grid-card__details">
        <div>
            <dt>Línea base {{ $indicador->linea_base }}</dt>
            <dd>{{ $formatearValor($indicador->dato_linea_base) }}</dd>
        </div>
        <div>
            <dt>Meta {{ $indicador->meta_anio }}</dt>
            <dd>{{ $formatearValor($indicador->meta) }}</dd>
        </div>
        <div>
            <dt>Tendencia</dt>
            <dd>{{ $indicador->tendencia ?: 'N/D' }}</dd>
        </div>
    </dl>

    <div class="eje-indicator-grid-card__status" data-bs-toggle="popover" data-bs-trigger="hover focus"
        data-bs-placement="top" title="Estado: {{ $semText }}" data-bs-content="{{ $explicacionDetallada }}"
        tabindex="0">
        <span>{{ $semText }}</span>
        <strong>{{ $esDatoLineaBase ? 'Medición pendiente' : $avanceTexto }}</strong>
    </div>

    <footer class="eje-indicator-grid-card__footer">
        <a href="{{ route('ficha-tecnica.show', $indicador) }}">Ver ficha <span aria-hidden="true">→</span></a>
    </footer>
</article>
