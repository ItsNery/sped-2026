@extends('layouts.plantilla')
@section('title', 'Datos Abiertos: Histórico de los Indicadores Municipales')
@section('meta-description',
    'Sección de los Datos Abiertos del Histórico de los Indicadores Municipales dentro del
    Sistema de Información para el
    Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
    'Datos Abiertos: Histórico de los Indicadores Municipales - Sistema de Información para el Seguimiento a la Planeación y
    Evaluación del Desarrollo
    del Estado de Puebla')
@section('og-description',
    'Sección de los Datos Abiertos del Histórico de los Indicadores Municipales dentro del
    Sistema de Información para el
    Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
    'Datos Abiertos: Histórico de los Indicadores Municipales - Sistema de Información para el Seguimiento a la Planeación y
    Evaluación del Desarrollo
    del Estado de Puebla')
@section('twitter-description',
    'Sección de los Datos Abiertos del Histórico de los Indicadores Municipales dentro del
    Sistema de Información para el
    Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla.')
@section('content')


    <div class="container">
        <h1 class="my-3">Datos Abiertos: Histórico de los Indicadores Municipales</h1>
        <img class="w-100 block-auto" src="{{ asset('img/pleca-nueva.png') }}" title="Pleca"
            alt="Pleca conformada por una línea partida por cuatro colores">
        &nbsp;
        <h4 class="text-justify my-3">Los datos abiertos disponibles pueden ser utilizados, reutilizados y
            redistribuidos libremente por cualquier persona, se encuentran sujetos al requerimiento de atribución de
            la misma manera en que aparecen.</h4>
        <section id="mun-section" class="mb-5">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="nav flex-column nav-pills" id="ped-pills-tab" role="tablist" aria-orientation="vertical">
                        <button class="nav-link active" id="2019-2024-tab" data-bs-toggle="pill" data-bs-target="#2019-2024"
                            type="button" role="tab" aria-controls="2019-2024" aria-selected="true">
                            2019-2024
                        </button>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="tab-content" id="ped-pills-tabContent">
                        <div class="tab-pane fade show active" id="2019-2024" role="tabpanel"
                            aria-labelledby="2019-2024-tab">
                            <h4 class="h5 mb-3 fw-semibold" style="color: var(--color-primary);">
                                Municipios con convenio
                            </h4>
                            <p class="mb-4 text-muted">
                                Indicadores municipales derivados de los Planes Municipales de Desarrollo.
                            </p>
                            <table class="table table-bordered data-table">
                                <thead>
                                    <tr>
                                        <th>Nombre del Conjunto</th>
                                        <th>Formatos de Descarga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Base - Total de Indicadores: Coatepec</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaCoatepec.csv') }}"
                                                title="Descargar CSV" download="BD_Completa_CSV">
                                                <img src="{{ asset('img/csv.png') }}" alt="Icono de CSV" title=" Icono CSV">
                                            </a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaCoatepec.xlsx') }}"
                                                title="Descargar XLS" download="BD_Completa_XLS">
                                                <img src="{{ asset('img/xls.png') }}" alt="Icono de XLS" srcset="Icono XLS">
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Base - Total de Indicadores: Ocotepec</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaOcotepec.csv') }}"
                                                download="" title="Descargar CSV"><img src="{{ asset('img/csv.png') }}"
                                                    alt="Icono de CSV" title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaOcotepec.xlsx') }}"
                                                download="" title="Descargar XLS"> <img src="{{ asset('img/xls.png') }}"
                                                    alt="Icono de XLS" srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Base - Total de Indicadores: San Andrés Cholula</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaSAN_ANDRES_CHOLULA.csv') }}"
                                                download="" title="Descargar CSV"><img src="{{ asset('img/csv.png') }}"
                                                    alt="Icono de CSV" title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaSAN_ANDRES_CHOLULA.xlsx') }}"
                                                download="" title="Descargar XLS"> <img src="{{ asset('img/xls.png') }}"
                                                    alt="Icono de XLS" srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Base - Total de Indicadores: San Pablo Anicano</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaSAN_PABLO_ANICANO.csv') }}"
                                                download="" title="Descargar CSV"><img src="{{ asset('img/csv.png') }}"
                                                    alt="Icono de CSV" title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaSAN_PABLO_ANICANO.xlsx') }}"
                                                download="" title="Descargar XLS"> <img src="{{ asset('img/xls.png') }}"
                                                    alt="Icono de XLS" srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Base - Total de Indicadores: Tehuacán</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaTEHUACAN.csv') }}"
                                                download="" title="Descargar CSV"><img src="{{ asset('img/csv.png') }}"
                                                    alt="Icono de CSV" title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaTEHUACAN.xlsx') }}"
                                                download="" title="Descargar XLS"> <img src="{{ asset('img/xls.png') }}"
                                                    alt="Icono de XLS" srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Base - Total de Indicadores: Tepanco de López</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaTEPANCO_LOPEZ.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaTEPANCO_LOPEZ.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Base - Total de Indicadores: Tlacotepec de Benito Juárez</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaTLACOTEPEC_BJ.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaTLACOTEPEC_BJ.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Base - Total de Indicadores: Tochimilco</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaTochimilco.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaTochimilco.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Base - Total de Indicadores: Vicente Guerrero</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaVICENTE_GUERRERO.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaVICENTE_GUERRERO.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Base - Total de Indicadores: Zihuateutla</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaZihuateutla.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaZihuateutla.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Base - Total de Indicadores: Zacatlán</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaZACATLAN.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/pm/BaseCompletaZACATLAN.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
