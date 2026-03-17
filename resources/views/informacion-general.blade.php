@extends('layouts.plantilla')
@section('title', 'Información General')
@section('meta-description',
'Sección de Información General del Sistema de Información para el Seguimiento a la Planeación y Evaluación del
Desarrollo
del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
'Información General - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('og-description',
'Sección de Información General del Sistema de Información para el Seguimiento a la Planeación y Evaluación del
Desarrollo
del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
'Información General - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('twitter-description',
'Sección de Información General del Sistema de Información para el Seguimiento a la Planeación y Evaluación del
Desarrollo
del Estado de Puebla')
@section('css')
@endsection
@section('jss-inicial')
@endsection
@section('content')
<section class="seccion-inicial">
    <div class="custom-container">
        <div class="left-panel">
            <img src="{{ asset('img/que-es-nuevo.png') }}"
                alt="Panel izquierdo con diseño gráfico que indica 'Qué es el Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo'">
        </div>
        <div class="right-panel">
            <p>Es una herramienta integradora para el seguimiento puntual al avance de los Indicadores Estratégicos, así
                como los Indicadores Sectoriales, Especiales, Institucionales y Regionales, establecidos en los
                documentos programáticos vigentes.</p>
        </div>
    </div>
</section>
<section class="cont-esquema">
    <div class="container banner-container">
        <div class="row banner-content" data-aos="fade-down" data-aos-delay="350">
            <div class="col-md-12 imagen" data-aos="fade-down" data-aos-delay="350">
                <img width="1103" src="{{ asset('img/Banners/General/sepd.png') }}" alt="Sistema Estatal de Planeación Democrática">
            </div>
            <div class="col-md-12 texto">
                <p>El esquema integral define el conjunto de procedimientos y actividades mediante las cuales
                    las
                    instituciones de la Administración Pública Estatal y Municipal, entre sí, y en colaboración con
                    los sectores de la sociedad, toman decisiones para llevar de forma coordinada el proceso de
                    planeación a fin de garantizar el desarrollo integral y sostenible del estado.</p>
            </div>
        </div>
    </div>
</section>

<section class="cont-esquema-1">
    <div class="container ">
        <div class="row" data-aos="fade-down" data-aos-delay="350">
            <div class="col-md-12 imagen" data-aos="fade-down" data-aos-delay="350">
                <img src="{{ asset('img/esquemas/piramide.png') }}" alt="Esquema de piramide de los programas derivados"
                    title="Programas Derivados">
            </div>

            <div class="col-md-4">
                <img src="{{ asset('img/linea-1.png') }}" alt="" class="w-100">
            </div>
            <div class="col-md-8">
                <p>Posteriormente, a través de la vinculación con el Sistema de Evaluación del Desempeño, el
                    esquema
                    integral de seguimiento se articula entre toda la APE, la sociedad, las regiones y los
                    municipios.</p>
            </div>

        </div>
    </div>
</section>
<section>
    <div class="row contenido container m-auto background-vector-2 mt-4">
        <div class="col-md-6 d-flex align-items-center justify-content-center">
            <img src="{{ asset('img/esquemas/PED2024-2030.png') }}" alt="Esquema PED" class="w-100 shadow-sm rounded-4">
        </div>
        <div class="col-md-6 mt-4 mt-md-0">
            <div id="texto_plan2" class="p-3">
                <h2 class="fw-bold mb-4" style="color: #9d2449;">Visión Estratégica</h2>
                <p class="text-muted lh-lg text-justify">
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
                <ul class="text-muted lh-lg">
                    <li><strong class="text-dark">a) Seguridad.</strong> Mediante un trabajo coordinado, se garantizarán entornos seguros que permitan tener condiciones de vida dignas y la protección ante adversidades.</li>
                    <li><strong class="text-dark">b) Justicia.</strong> Este término representará equidad en el acceso a la salud, educación, oportunidades laborales y mecanismos eficaces para corregir discrepancias estructurales.</li>
                    <li><strong class="text-dark">c) Riqueza Comunitaria.</strong> Se instrumentará una nueva forma de gobernar, que implicará la priorización de los derechos sociales, reconociendo al ser humano desde su integridad como un agente capaz de fortalecer la solidaridad, la cultura, la participación, el sentido de pertenencia y los saberes ancestrales.</li>
                </ul>
                <p class="text-muted lh-lg text-justify mt-3">
                    Como se puede observar, este documento trasciende la mera gestión
                    administrativa. Se erige como una guía que nos orientará en la superación de
                    los desafíos que enfrentamos, con el objetivo de reducir las desigualdades
                    sociales, fortalecer la seguridad, impulsar el desarrollo económico, asegurar la
                    sostenibilidad ambiental y consolidar un gobierno eficiente y transparente.
                </p>
            </div>
        </div>
    </div>
</section>
<section class="cont-esquema-2">
    <div class="container">
        <div class="row">
            <div class="col-md-12 imagen" data-aos="fade-down" data-aos-delay="350">
                <img src="{{ asset('img/Banners/General/esquema_sed.png') }}" alt="Esquema de Seguimiento del Sistema de Evaluación del Desempeño">
            </div>
            <div class="col-md-12 texto">
                <p>De tal forma el SPED automatiza el proceso de seguimiento de las acciones y metas de los instrumentos
                    de planeación, el Informe de Gobierno, los programas presupuestales y los ODS de la Agenda 2030.</p>
            </div>
        </div>
    </div>
</section>
<!-- <section class="esquema-final">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <img class="w-100 block-auto" title="Monitoreo y Seguimiento" alt="Texto Monitoreo y Seguimiento"
                    src="{{ asset('img/esquemas/info_6.png') }}">
            </div>
            <div class="col-md-8"></div>
            <div class="col-md-12">
                <img class="w-100 block-auto" alt="Diagrama con el desgloce de los indicadores" title="Indicadores"
                    src="{{ asset('img/esquemas/esquema_indicadores_actualizado.png') }}">
            </div>
        </div>
    </div>
</section> -->
@endsection

@section('jss-final')

@endsection