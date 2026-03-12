@extends('layouts.plantilla')
@section('title', 'Datos Abiertos: Histórico de los Planes Estatales de Desarrollo')
@section('meta-description',
    'Sección del Histórico de los Datos Abiertos los Planes Estatales de Desarrollo, dentro del
    Sistema de Información para el
    Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
    'Datos Abiertos: Histórico de los Planes Estatales de Desarrollo - Sistema de Información para el Seguimiento a la
    Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og-description',
    'Sección del Histórico de los Datos Abiertos los Planes Estatales de Desarrollo dentro del
    Sistema de Información para el
    Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
    'Datos Abiertos: Histórico de los Planes Estatales de Desarrollo - Sistema de Información para el Seguimiento a la
    Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('twitter-description',
    'Sección del Histórico de los Datos Abiertos los Planes Estatales de Desarrollo dentro del
    Sistema de Información para el
    Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla.')
@section('content')
    <div class="container">
        <h1 class="my-3">Datos Abiertos: Histórico de los Planes Estatales de Desarrollo</h1>
        <img class="w-100 block-auto" src="{{ asset('img/pleca-nueva.png') }}" title="Pleca"
            alt="Pleca conformada por una línea partida por cuatro colores">
        &nbsp;
        <h4 class="text-justify my-3">Los datos abiertos disponibles pueden ser utilizados, reutilizados y
            redistribuidos libremente por cualquier persona, se encuentran sujetos al requerimiento de atribución de
            la misma manera en que aparecen.</h4>
        <section id="ped-section" class="mb-5">
            <h3 class="h3 section-title">Plan Estatal de Desarrollo 2019-2024</h3>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="nav flex-column nav-pills" id="ped-pills-tab" role="tablist" aria-orientation="vertical">
                        <button class="nav-link active" id="ped-generales-tab" data-bs-toggle="pill"
                            data-bs-target="#ped-generales" type="button" role="tab" aria-controls="ped-generales"
                            aria-selected="true">
                            Datos Generales
                        </button>
                        <button class="nav-link" id="ped-plan-tab" data-bs-toggle="pill" data-bs-target="#ped-plan"
                            type="button" role="tab" aria-controls="ped-plan" aria-selected="false">
                            Plan Estatal de Desarrollo
                        </button>
                        <button class="nav-link" id="ped-programas-tab" data-bs-toggle="pill"
                            data-bs-target="#ped-programas" type="button" role="tab" aria-controls="ped-programas"
                            aria-selected="false">
                            Programas Derivados
                        </button>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="tab-content" id="ped-pills-tabContent">
                        <div class="tab-pane fade show active" id="ped-generales" role="tabpanel"
                            aria-labelledby="ped-generales-tab">
                            <h4 class="h5 mb-3 fw-semibold" style="color: var(--color-primary);">Datos Generales
                                (PED)</h4>
                            <p class="mb-4 text-muted">Metadatos y catálogos relacionados con el Plan Estatal de
                                Desarrollo original.</p>
                            <table class="table table-bordered data-table">
                                <thead>
                                    <tr>
                                        <th>Nombre del Conjunto</th>
                                        <th>Formatos de Descarga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Base - Total de Indicadores</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/datos-generales/BD_Completa.json') }}"
                                                title="Descargar JSON" download="BD_Completa_JSON">
                                                <img src="{{ asset('img/js.png') }}" alt="Icono de JS" title="Icono JS">
                                                {{-- <img class="icon-json"
                                                            alt="JSON"> --}}
                                            </a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/datos-generales/BD_Completa.csv') }}"
                                                title="Descargar CSV" download="BD_Completa_CSV">
                                                {{-- <img class="icon-csv"
                                                            alt="CSV"> --}}
                                                <img src="{{ asset('img/csv.png') }}" alt="Icono de CSV" title=" Icono CSV">
                                            </a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/datos-generales/BD_Completa.xlsx') }}"
                                                title="Descargar XLS" download="BD_Completa_XLS">
                                                <img src="{{ asset('img/xls.png') }}" alt="Icono de XLS" srcset="Icono XLS">
                                                {{-- <img class="icon-xls"
                                                            alt="XLS"> --}}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Base - Indicadores del Plan Estatal de Desarrollo</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/datos-generales/BD_PED.json') }}"
                                                title="Descargar JSON" download="BD_PED.json"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS" title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/datos-generales/BD_PED.csv') }}"
                                                title="Descargar CSV" download="BD_PED.csv"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/datos-generales/BD_PED.xlsx') }}"
                                                title="Descargar XLS" download="BD_PED.csv"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Base - Indicadores de los Programas Derivados</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/datos-generales/BD_PD.json') }}"
                                                download="" title="Descargar JSON"><img src="{{ asset('img/js.png') }}"
                                                    alt="Icono de JS" title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/datos-generales/BD_PD.csv') }}"
                                                download="" title="Descargar CSV"><img src="{{ asset('img/csv.png') }}"
                                                    alt="Icono de CSV" title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/datos-generales/BD_PD.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="ped-plan" role="tabpanel" aria-labelledby="ped-plan-tab">
                            <h4 class="h5 mb-3 fw-semibold" style="color: var(--color-primary);">Plan Estatal
                                de
                                Desarrollo 2019-2024</h4>
                            <p class="mb-4 text-muted">Conjuntos de datos específicos del PED original
                                (indicadores, metas, etc.).</p>
                            <table class="table table-bordered data-table">
                                <thead>
                                    <tr>
                                        <th>Nombre del Conjunto</th>
                                        <th>Formatos de Descarga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Eje 1 Seguridad Pública, Justicia y Estado de Derecho</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/ped/BD_Eje_1.json') }}"
                                                download="" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/ped/BD_Eje_1.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/ped/BD_Eje_1.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Eje 2 Recuperación del Campo Poblano</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/ped/BD_Eje_2.json') }}"
                                                download="" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/ped/BD_Eje_2.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/ped/BD_Eje_2.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Eje 3 Desarrollo Económico para Todas y Todos</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/ped/BD_Eje_3.json') }}"
                                                download="" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/ped/BD_Eje_3.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/ped/BD_Eje_3.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Eje 4 Disminución de las Desigualdades</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/ped/BD_Eje_4.json') }}"
                                                download="" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/ped/BD_Eje_4.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/ped/BD_Eje_4.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Eje 5 Gobierno Democrático, Innovador y Transparente</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/ped/BD_Eje_5.json') }}"
                                                download="" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/ped/BD_Eje_5.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/ped/BD_Eje_5.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Enfoques Transversales</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/ped/BD_Eje_6.json') }}"
                                                download="" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/ped/BD_Eje_6.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/ped/BD_Eje_6.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="ped-programas" role="tabpanel"
                            aria-labelledby="ped-programas-tab">
                            <h4 class="h5 mb-3 fw-semibold" style="color: var(--color-primary);">Programas
                                Derivados (PED)</h4>
                            <p class="mb-4 text-muted">Programas sectoriales, regionales o especiales derivados
                                del PED.</p>
                            <table class="table table-bordered data-table">
                                <thead>
                                    <tr>
                                        <th>Nombre del Conjunto</th>
                                        <th>Formatos de Descarga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Programas Sectoriales</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/pd/BD_Sectoriales.json') }}"
                                                download="" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/pd/BD_Sectoriales.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/pd/BD_Sectoriales.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Programas Especiales</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/pd/BD_Especiales.json') }}"
                                                download="" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/pd/BD_Especiales.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/pd/BD_Especiales.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Programas Institucionales</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/pd/BD_Institucionales.json') }}"
                                                download="" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/pd/BD_Institucionales.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/pd/BD_Institucionales.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Programas Regionales</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/pd/BD_Regionales.json') }}"
                                                download="" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/pd/BD_Regionales.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/ped/pd/BD_Regionales.xlsx') }}"
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

        <section id="modificacion-section" class="mb-5">
            <h3 class="h3 section-title">Modificación y Adecuación del Plan Estatal de Desarrollo 2019-2024</h3>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="nav flex-column nav-pills" id="mod-pills-tab" role="tablist"
                        aria-orientation="vertical">
                        <button class="nav-link active" id="mod-generales-tab" data-bs-toggle="pill"
                            data-bs-target="#mod-generales" type="button" role="tab" aria-controls="mod-generales"
                            aria-selected="true">
                            Datos Generales
                        </button>
                        <button class="nav-link" id="mod-plan-tab" data-bs-toggle="pill" data-bs-target="#mod-plan"
                            type="button" role="tab" aria-controls="mod-plan" aria-selected="false">
                            Modificación y Adecuación del Plan Estatal de Desarrollo
                        </button>
                        <button class="nav-link" id="mod-programas-tab" data-bs-toggle="pill"
                            data-bs-target="#mod-programas" type="button" role="tab" aria-controls="mod-programas"
                            aria-selected="false">
                            Programas Derivados
                        </button>
                        <button class="nav-link" id="reporte-eje-tab" data-bs-toggle="pill"
                            data-bs-target="#reporte-eje" type="button" role="tab" aria-controls="reporte-eje"
                            aria-selected="false">
                            Reporte Ejecutivo
                        </button>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="tab-content" id="mod-pills-tabContent">
                        <div class="tab-pane fade show active" id="mod-generales" role="tabpanel"
                            aria-labelledby="mod-generales-tab">
                            <h4 class="h5 mb-3 fw-semibold" style="color: var(--color-primary);">Datos
                                Generales (Modificación)</h4>
                            <p class="mb-4 text-muted">Metadatos y catálogos relacionados con la modificación y
                                adecuación del PED.</p>
                            <table class="table table-bordered data-table">
                                <thead>
                                    <tr>
                                        <th>Nombre del Conjunto</th>
                                        <th>Formatos de Descarga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Base - Total de Indicadores</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/datos-generales/BD_Completa.json') }}"
                                                title="Descargar JSON" download=""><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/datos-generales/BaseCompletaModPED.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/datos-generales/BaseCompletaModPED.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Base - Indicadores de la Modificación y Adecuación del Plan Estatal de
                                            Desarrollo</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/datos-generales/BD_PED.json') }}"
                                                download="" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/datos-generales/BD_PED2.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/datos-generales/BD_PED2.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Base - Indicadores de los Programas Derivados</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/datos-generales/BD_PD.json') }}"
                                                download="" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/datos-generales/BaseProgramasDerivadosModPED.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/datos-generales/BaseProgramasDerivadosModPED.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="mod-plan" role="tabpanel" aria-labelledby="mod-plan-tab">
                            <h4 class="h5 mb-3 fw-semibold" style="color: var(--color-primary);">Modificación y
                                Adecuación del Plan Estatal de Desarrollo</h4>
                            <p class="mb-4 text-muted">Conjuntos de datos específicos de la modificación
                                (indicadores ajustados, nuevos programas, etc.).</p>
                            <table class="table table-bordered data-table">
                                <thead>
                                    <tr>
                                        <th>Nombre del Conjunto</th>
                                        <th>Formatos de Descarga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Eje 1 Justicia Social y Fortalecimiento del Estado de Derecho</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje_1.json') }}"
                                                title="Descargar JSON" download=""><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje1P2.csv') }}"
                                                title="Descargar CSV" download=""><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje1P2.xlsx') }}"
                                                title="Descargar XLS" download=""> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Eje 2 Sostenibilidad Territorial y Desarrollo Integral</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje_2.json') }}"
                                                download="" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje2P2.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje2P2.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Eje 3 Fortalecimiento del Campo e Impulso a la Economía Justa y Social</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje_3.json') }}"
                                                title="Descargar JSON"><img src="{{ asset('img/js.png') }}"
                                                    alt="Icono de JS" title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje3P2.csv') }}"
                                                title="Descargar CSV"><img src="{{ asset('img/csv.png') }}"
                                                    alt="Icono de CSV" title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje3P2.xlsx') }}"
                                                title="Descargar XLS"> <img src="{{ asset('img/xls.png') }}"
                                                    alt="Icono de XLS" srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Eje 4 Desarrollo Integral, Educación y Diversidad Cultural</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje_4.json') }}"
                                                download="" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje4P2.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje4P2.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Eje 5 Transparencia, Participación Ciudadana y Combate a la Corrupción</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje_5.json') }}"
                                                download="" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje5P2.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje5P2.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="mod-programas" role="tabpanel"
                            aria-labelledby="mod-programas-tab">
                            <h4 class="h5 mb-3 fw-semibold" style="color: var(--color-primary);">Programas
                                Derivados</h4>
                            <p class="mb-4 text-muted">Programas sectoriales, regionales o especiales derivados
                                o ajustados tras la modificación del PED.</p>
                            <table class="table table-bordered data-table">
                                <thead>
                                    <tr>
                                        <th>Nombre del Conjunto</th>
                                        <th>Formatos de Descarga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Programas Sectoriales</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/pd/BD_Sectoriales.json') }}"
                                                download="" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/pd/BD_Sectoriales2.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/pd/BD_Sectoriales2.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Programas Especiales</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/pd/BD_Especiales.json') }}"
                                                title="Descargar JSON" download=""><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/pd/BD_Especiales2.csv') }}"
                                                title="Descargar CSV" download=""><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/pd/BD_Especiales2.xlsx') }}"
                                                title="Descargar XLS" download=""> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Programas Institucionales</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/pd/BD_Institucionales.json') }}"
                                                title="Descargar JSON"download=""><img src="{{ asset('img/js.png') }}"
                                                    alt="Icono de JS" title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/pd/BD_Institucionales2.csv') }}"
                                                title="Descargar CSV"download=""><img src="{{ asset('img/csv.png') }}"
                                                    alt="Icono de CSV" title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/pd/BD_Institucionales2.xlsx') }}"
                                                title="Descargar XLS"download=""> <img src="{{ asset('img/xls.png') }}"
                                                    alt="Icono de XLS" srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Programa Regional</td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/pd/BD_Regionales.json') }}"
                                                download="" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/pd/BD_Regional2.csv') }}"
                                                download="" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/mod-ped/pd/BD_Regional2.xlsx') }}"
                                                download="" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="reporte-eje" role="tabpanel" aria-labelledby="reporte-eje-tab">
                            <h4 class="h5 mb-3 fw-semibold" style="color: var(--color-primary);">Reporte Ejecutivo</h4>
                            <p class="mb-4 text-muted">Reporte ejecutivo de cumplimiento de indicadores del SPED.</p>
                            <table class="table table-bordered data-table">
                                <thead>
                                    <tr>
                                        <th>Nombre del Conjunto</th>
                                        <th>Formatos de Descarga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Reporte Ejecutivo 2024 </td>
                                        <td class="download-icons">
                                            <a href="{{ asset('docs/datos-abiertos/2019-2024/reporte/Reporte_Ejecutivo_2024.pdf') }}"
                                                download="" title="Descargar PDF"><img
                                                    src="{{ asset('img/PDF.png') }}" alt="Icono de PDF"
                                                    title=" Icono PDF"></a>
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
