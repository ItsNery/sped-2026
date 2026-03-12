<link rel="stylesheet" type="text/css" href="{{ asset('css/tab_puebla.css') }}">
@foreach ($regionesConIndicadores as $index => $regionData)
    <div class="row ficha" id="indicador_{{ $index + 1 }}" style="background-color:#A96C12; display: none;">
        <h2 style="color:#fff;">Indicadores de {{ $regionData['region'] }}</h2>
        <div class="container">
            @if ($regionData['indicadores']->isEmpty())
                <p>No se encontraron indicadores para esta región.</p>
            @else
                @foreach ($regionData['indicadores'] as $indicador)
                    <div class="row mb-1">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <div class="card overflow-hidden">
                                <div class="card-content card_indicador">
                                    <div class="card-body">
                                        <a href="{{ route('ficha-tecnica.show', $indicador->id) }}"
                                            style="text-decoration:none;">
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-10">
                                                    <div class="titulo">
                                                        {{ $indicador->nombre }}
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-2">
                                                    <div class="ultimo">Resultado
                                                        {{ $indicador->anio_reciente ?? 'Sin datos' }}
                                                    </div>
                                                    <div class="datos_eje1">
                                                        {{ $indicador->dato_reciente ?? 'Sin datos' }}
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="align-self-left" style="text-align: left">
                                                        @foreach ($indicador->ods->unique('id') as $ods)
                                                            <img src="{{ asset('/img/Icons_ODS/' . $ods->id . '.png') }}"
                                                                alt="Imagen de ODS {{ $ods->id }}"
                                                                class="hvr-wobble-top"
                                                                style="width:60px; border-radius: 5px 5px 5px 5px;">
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-xl-3 col-xs-12 col-sm-12 col-12 hvr-grow">
                            <div class="card">
                                <div class="card-content card_indicador1">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xl-12 col-xs-12 col-sm-12 col-12 col-md-12 datos">
                                                Unidad de Medida
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xl-12 col-xs-12 col-sm-12 col-12 col-md-12 datos_eje2">
                                                {{ $indicador->unidad_medida }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-xs-12 col-sm-12 col-12 hvr-grow">
                            <div class="card">
                                <div class="card-content card_indicador1">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xl-12 col-xs-12 col-sm-12 col-12 col-md-12 datos">
                                                Tendencia
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xl-12 col-xs-12 col-sm-12 col-12 col-md-12 datos_eje2">
                                                {{ $indicador->tendencia }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-xs-12 col-sm-12 col-12 hvr-grow">
                            <div class="card">
                                <div class="card-content card_indicador1">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xl-12 col-xs-12 col-sm-12 col-12 col-md-12 datos">
                                                Línea Base {{ $indicador->linea_base }}
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xl-12 col-xs-12 col-sm-12 col-12 col-md-12 datos_eje2">
                                                {{ $indicador->dato_linea_base }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-xs-12 col-sm-12 col-12 hvr-grow">
                            <div class="card">
                                <div class="card-content card_indicador1">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xl-12 col-xs-12 col-sm-12 col-12 col-md-12 datos">
                                                Meta 2024
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xl-12 col-xs-12 col-sm-12 col-12 col-md-12 datos_eje2">
                                                {{ $indicador->meta_2024 }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endforeach
