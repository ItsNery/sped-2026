<table id="tabla-indicadores" class="table table-striped table-bordered">
    <thead>
        <tr>
            <th scope="col">No.</th>
            <th>Indicador</th>
            <th>Programa Derivado</th>
            <th>Programa</th>
            <th>Periodicidad</th>
            <th>Fecha Actualización</th>
            <th>Acciones</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($indicadores as $indicador)
            <tr>
                <td scope="row">
                    {{ $indicador->id }}
                </td>
                <td>
                    <a href="{{ route('panel-indicadores.show', $indicador->id) }}">
                        {{ $indicador->nombre }}
                    </a>
                </td>
                <td>
                    {{ $indicador->programa_derivado }}
                </td>
                <td>
                    {{ $indicador->programa }}
                </td>
                <td>
                    {{ $indicador->periodicidad }}
                </td>
                <td>
                    {{ $indicador->fecha_actualizacion }}
                </td>
                <td>
                    <div class="flex justify-center rounded-lg text-lg" role="group">
                        <!-- botón editar -->
                        @if ($indicador->indicador_validado == 1)
                            <span class="badge text-bg-success"> Validado </span>
                        @else
                            <a href="{{ route('panel-indicadores.edit', $indicador->id) }}" class="">
                                <button class="btn btn-secondary">
                                    Editar
                                </button>
                            </a>
                            <!-- botón borrar -->
                            {{-- @if (auth()->user()->id === 1)
                                <form action="{{ route('panel-indicadores.destroy', $indicador->id) }}" method="POST"
                                    class="formEliminar">
                                    @csrf
                                    @method('DELETE')
                                    <button style="color: black" type="submit" class="btn btn-danger">
                                        Borrar
                                    </button>
                                </form>
                            @endif --}}
                        @endif
                    </div>
                </td>
                {{-- <td>
                    @if ($indicador->datosAnuales)
                        @if ($indicador->datosAnuales->modificado === 1)
                            <span class="badge bg-warning text-dark">Indicador modificado</span>
                        @else
                            <span class="badge bg-success">Sin cambios</span>
                        @endif
                    @else
                        <span class="badge bg-secondary">Sin datos</span>
                    @endif
                </td> --}}
                <td>
                    {{-- Primero, verifica si la colección datosAnuales no está vacía --}}
                    @if ($indicador->datosAnuales && $indicador->datosAnuales->isNotEmpty())
                        {{--
                                                    Luego, verifica si ALGUNO de los registros DatoAnual en la colección
                                                    tiene la propiedad 'modificado' establecida en true (o 1).
                                                    Usamos el método 'contains' de la colección con un callback,
                                                    o el método 'where' para filtrar y luego 'isNotEmpty'.
                                                    --}}
                        @if ($indicador->datosAnuales->where('modificado', true)->isNotEmpty())
                            <span class="badge bg-warning text-dark">Indicador modificado</span>
                        @else
                            <span class="badge bg-success">Sin cambios</span>
                        @endif
                    @else
                        <span class="badge bg-secondary">Sin datos anuales</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
