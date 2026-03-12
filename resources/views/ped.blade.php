@extends('layouts.plantilla')
@section('title', 'Plan Estatal de Desarrollo 2024 - 2030')
@section('meta-description',
    'Sección de la Plan Estatal de Desarrollo 2024 - 2030 dentro del Sistema de
    Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
    'Plan Estatal de Desarrollo 2024 - 2030 - Sistema de Información para el Seguimiento a la
    Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og-description',
    'Sección de la Plan Estatal de Desarrollo 2024 - 2030 dentro del Sistema de
    Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
    'Plan Estatal de Desarrollo 2024 - 2030 - Sistema de Información para el Seguimiento a la
    Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('twitter-description',
    'Sección de la Plan Estatal de Desarrollo 2024 - 2030 dentro del Sistema de
    Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla')
@section('css')
@endsection
@section('jss-inicial')
@endsection
@section('content')
    {{-- <img src="{{ asset('img/Banners/Banner_PED/Banner_Mod_PED.jpg') }}" width="100%" class="px-0"> --}}
    <img src="{{ asset('img/Banners/Banner_PED/PED.jpg') }}" alt="banner del Eje 1" class="w-100 px-0">
    <div class="row nav_ejes">
        <div class="col-md-2 nav_eje_item nav_eje1 ocultar_submenu">
            <a href="{{ url('/ped/eje-1') }}" class="dropbtn nav_eje_link">Eje 1</a>
        </div>
        <div class="col-md-2 nav_eje_item nav_eje2 ocultar_submenu">
            <a href="{{ url('/ped/eje-2') }}" class="dropbtn nav_eje_link">Eje 2</a>
        </div>
        <div class="col-md-2 nav_eje_item nav_eje3 ocultar_submenu">
            <a href="{{ url('/ped/eje-3') }}" class="dropbtn nav_eje_link">Eje 3</a>
        </div>
        <div class="col-md-2 nav_eje_item nav_eje4 ocultar_submenu">
            <a href="{{ url('/ped/eje-4') }}" class="dropbtn nav_eje_link">Eje 4</a>
        </div>
        <div class="col-md-2 nav_eje_item nav_eje5 ocultar_submenu">
            <a href="{{ url('/ped/eje-5') }}" class="dropbtn nav_eje_link">Eje 5</a>
        </div>
        <div class="col-md-2 nav_eje_item nav_eje6 ocultar_submenu">
            <a href="{{ url('/ped/eje-6') }}" class="dropbtn nav_eje_link">Eje Transversal</a>
        </div>
    </div>
    &nbsp;
    <div class="row contenido container m-auto background-vector-2  ">
        <div class="col-md-6">
            <img src="{{ asset('img/esquemas/PED2024-2030.png') }}" alt="" class="w-100">
        </div>
        <div class="col-md-6">
            <div id="texto_plan2" class="col-md-12">
                <p>
                    El PED 2024-2030 se distingue por su carácter innovador y por mantener
                    plena observancia de las disposiciones jurídicas. Su rasgo más sobresaliente
                    es la colaboración inédita de los poderes jurisdiccionales en su elaboración.
                    Este hecho marca un punto de inflexión en el estado porque se adopta una
                    gobernanza inclusiva como elemento eficaz de planeación, con lo cual se
                    fortalece la visión integral del desarrollo y se consolida el modelo de gobierno que
                    se habrá de seguir. Este modelo es el del Humanismo Mexicano, planteado en el
                    Plan Nacional de Desarrollo 2025-2030, el cual se verá reflejado en la entidad bajo
                    un enfoque de Bioética Social que se cimienta en tres dimensiones:
                </p>
                <p>
                    a) Seguridad. Mediante un trabajo coordinado, se garantizarán entornos
                    seguros que permitan tener condiciones de vida dignas y la protección ante
                    adversidades.
                </p>
                <p>
                    b) Justicia. Este término representará equidad en el acceso a la salud,
                    educación, oportunidades laborales y mecanismos eficaces para corregir
                    discrepancias estructurales.
                </p>
                <p>
                    c) Riqueza Comunitaria. Se instrumentará una nueva forma de gobernar,
                    que implicará la priorización de los derechos sociales, reconociendo al ser
                    humano desde su integridad como un agente capaz de fortalecer la solidaridad,
                    la cultura, la participación, el sentido de pertenencia y los saberes ancestrales.

                </p>
                <p>
                    Como se puede observar, este documento trasciende la mera gestión
                    administrativa. Se erige como una guía que nos orientará en la superación de
                    los desafíos que enfrentamos, con el objetivo de reducir las desigualdades
                    sociales, fortalecer la seguridad, impulsar el desarrollo económico, asegurar la
                    sostenibilidad ambiental y consolidar un gobierno eficiente y transparente.
                </p>
            </div>
        </div>
    </div>
@section('jss-final')

@endsection
@endsection
