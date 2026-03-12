@extends('layouts.plantilla')
@section('title', 'Contribución Agenda 2030 de la Modificación y Adecuación del PED 2019-2024')
@section('meta-description',
    'Sección dedicada al seguimiento al cumplimiento de la Agenda 2030 de la Modificación y Adecuación del Plan Estatal de
    Desarrollo 2019-2024 dentro del
    Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
    'Contribución Agenda 2030 de la Modificación y Adecuación del PED 2019-2024 - Sistema de Información para el Seguimiento
    a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og-description',
    'Sección dedicada al seguimiento al cumplimiento de la Agenda 2030 de la Modificación y Adecuación del Plan Estatal de
    Desarrollo 2019-2024 dentro del
    Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
    'Contribución Agenda 2030 de la Modificación y Adecuación del PED 2019-2024 - Sistema de Información para el Seguimiento
    a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('twitter-description',
    'Sección dedicada al seguimiento al cumplimiento de la Agenda 2030 de la Modificación y Adecuación del Plan Estatal de
    Desarrollo 2019-2024 dentro del
    Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('css')
    <style>
        .modal {
            visibility: hidden;
            opacity: 1;
            position: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(77, 77, 77, .7);
            transition: all .4s;
        }

        .modal:target {
            visibility: visible;
            opacity: 1;
        }

        .modal_content {
            border-radius: 4px;
            position: absolute;
            width: 70%;
            background: #fff;
        }

        .modal_close {
            position: absolute;
            top: 15px;
            right: 15px;
            color: #585858;
            text-decoration: none;
        }

        .row.ligas p {
            color: var(--text-color);
        }

        .modal_content {
            background-color: var(--bg-content);
        }
    </style>
@endsection
@section('jss-inicial')
@endsection
@section('content')
    &nbsp;
    <div class="row ligas agenda text-center">
        <h1 class="mb-4">
            Contribución Agenda 2030 del Plan Estatal de Desarrollo 2019-2024
        </h1>
        <img src="{{ asset('img/pleca-SPED.png') }}" class="img-fluid mb-4 px-0" alt="Pleca SPED">
        <div class="row g-4 align-items-center">
            <!-- Columna con ODS -->
            <div class="col-md-4 odeses mx-auto">
                @foreach ($odsResultados as $odsId => $resultados)
                    <div class="flip ods">
                        <div class="front" style="background-image: url({{ asset('img/Icons_ODS/' . $odsId . '.png') }})">
                            <span class="text-shadow"></span>
                        </div>
                        <div class="back">
                            <a href="#ods_{{ $odsId }}" class="text-decoration-none">
                                <h2>{{ $resultados->sum('numero_indicadores') }}</h2>
                                <h4>Indicadores</h4>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="col-md-6 texto-agenda mx-auto">
                <div class="ind-alineados w-100 text-center">
                    <img src="{{ asset('img/Agenda-Ind.png') }}" class="img-fluid mb-3" alt="Indicadores Agenda">
                    <h4 class="num-ind">{{ $totalIndicadores }}</h4>
                    <div class="alineados">
                        <p class="i">Indicadores alineados a los 17 ODS</p>
                        <p class="ods">(Objetivos de Desarrollo Sostenible)</p>
                    </div>
                </div>
                <p>
                    El modelo de planeación establecido, plantea un esquema de atención
                    innovador, el cual se enfoca en diversos objetivos y estrategias que den respuesta a las principales
                    necesidades y retos que enfrenta la entidad, y en consecuencia permitirá que las acciones realizadas
                    que contribuyan al cumplimiento de los ODS de una manera integral y transversal.
                </p>
                <p>
                    Además, para garantizar la contribución a la Agenda 2030 se revisaron los
                    17 ODS y sus respectivas metas, con la finalidad de identificar las dimensiones y los sectores de la
                    población que atiendan, de manera que se puedan encontrar puntos de contacto entre ambos enfoques.
                </p>
                <a href="http://agenda2030.puebla.gob.mx/" target="_blank" class="btn2">
                    <img src="{{ asset('img/ODS-agenda.png') }}" alt="Agenda 2030">
                    <p>
                        Portal Agenda 2030
                    </p>
                </a>
            </div>
        </div>
    </div>
    {{-- @include('layouts.modals-ods-2') --}}
    @include('layouts.modals-ods-2', ['odsResultados' => $odsResultados])
@section('jss-final')
@endsection
@endsection
