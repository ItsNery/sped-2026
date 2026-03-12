@foreach ($odsResultados as $odsId => $resultados)
    <div id="ods_{{ $odsId }}" class="modal">
        <div class="modal_content">
            <br>
            <h4 class="text-center">
                <img class="img-fluid" src="{{ asset('img/Icons_ODS/' . $odsId . '.png') }}" width="3%" />
                Número de Indicadores por Programa - <b>{{ $resultados->sum('numero_indicadores') }}</b>
            </h4>
            {{-- <table class="table">
                <thead>
                    <tr>
                        <th scope="col"><img class="img-fluid" src="{{ asset('img/iconos/1-PED.png') }}" width="100%" /></th>
                        <th scope="col"><img class="img-fluid" src="{{ asset('img/iconos/2-Sectoriales.png') }}" width="100%" /></th>
                        <th scope="col"><img class="img-fluid" src="{{ asset('img/iconos/3-Especial.png') }}" width="100%" /></th>
                        <th scope="col"><img class="img-fluid" src="{{ asset('img/iconos/4-Institucional.png') }}" width="100%" /></th>
                        <th scope="col"><img class="img-fluid" src="{{ asset('img/iconos/5-Regionales.png') }}" width="100%" /></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="text-center">
                        <td><b>{{ $resultados->where('programa_derivado', 'Plan Estatal de Desarrollo')->sum('numero_indicadores') }}</b></td>
                        <td><b>{{ $resultados->where('programa_derivado', 'Programa Sectorial')->sum('numero_indicadores') }}</b></td>
                        <td><b>{{ $resultados->where('programa_derivado', 'Programa Especial')->sum('numero_indicadores') }}</b></td>
                        <td><b>{{ $resultados->where('programa_derivado', 'Programa Institucional')->sum('numero_indicadores') }}</b></td>
                        <td><b>{{ $resultados->where('programa_derivado', 'Programa Regional')->sum('numero_indicadores') }}</b></td>
                    </tr>
                </tbody>
            </table> --}}
            <div class="container my-4">
                <div class="row text-center">
                    @foreach ([['icon' => '1-PED.png', 'label' => 'Plan Estatal de Desarrollo', 'value' => $resultados->where('programa_derivado', 'Plan Estatal de Desarrollo')->sum('numero_indicadores')], ['icon' => '2-Sectoriales.png', 'label' => 'Programa Sectorial', 'value' => $resultados->where('programa_derivado', 'Programa Sectorial')->sum('numero_indicadores')], ['icon' => '3-Especial.png', 'label' => 'Programa Especial', 'value' => $resultados->where('programa_derivado', 'Programa Especial')->sum('numero_indicadores')], ['icon' => '4-Institucional.png', 'label' => 'Programa Institucional', 'value' => $resultados->where('programa_derivado', 'Programa Institucional')->sum('numero_indicadores')], ['icon' => '5-Regionales.png', 'label' => 'Programa Regional', 'value' => $resultados->where('programa_derivado', 'Programa Regional')->sum('numero_indicadores')]] as $item)
                        <div class="col-6 col-md text-center my-3">
                            <img class="img-fluid" src="{{ asset('img/iconos/' . $item['icon']) }}"
                                alt="{{ $item['label'] }}" />
                            <p class="mt-2 mb-0"><b>{{ $item['value'] }}</b></p>
                            {{-- <small class="text-muted">{{ $item['label'] }}</small> --}}
                        </div>
                    @endforeach
                </div>
            </div>

            <a href="#" class="modal_close">X</a>
        </div>
    </div>
@endforeach
