<x-indicador-municipal-layout>
    @section('title', 'Administración Indicadores Municipales: Inicio')
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            {{ __('Inicio') }}
        </h2>
    </x-slot>
    <div class="container contenedor-tarjetas">
        <div class="tarjeta-sped">
            <div class="tarjeta-sped-details">
                <img src="{{ asset('assets-administrador/img/modulo_indicadores.png') }}" alt="">
            </div>
            <a href="{{ route('panel-indicadores-municipales.index') }}" target="_self" title="Indicadores"
                class="tarjeta-sped-button">Ver</a>
        </div>
    </div>
</x-indicador-municipal-layout>
