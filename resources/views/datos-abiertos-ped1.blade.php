@extends('layouts.plantilla')
@section('title', 'Datos Abiertos')
@section('meta-description',
    'Sección de los Datos Abiertos del Plan Estatal de Desarrollo 2019-2024 dentro del Sistema de Información para el
    Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
    'Datos Abiertos - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og-description',
    'Sección de los Datos Abiertos del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de Información para el
    Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
    'Datos Abiertos - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('twitter-description',
    'Sección de los Datos Abiertos del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de Información para el
    Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla.')
@section('css')
@endsection
@section('jss-inicial')
@endsection
@section('content')
    <div class="container">
        <h1 class="my-3">Datos Abiertos</h1>
        <img class="w-100 block-auto" src="{{ asset('img/pleca-nueva.png') }}" title="Pleca"
            alt="Pleca conformada por una línea partida por cuatro colores">
        &nbsp;
        <h4 class="text-justify my-3">Los datos abiertos disponibles pueden ser utilizados, reutilizados y
            redistribuidos libremente por cualquier persona, se encuentran sujetos al requerimiento de atribución de
            la misma manera en que aparecen.</h4>
        <section id="ped-section" class="mb-5">
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
                        {{-- Comentado el 19 de mayo por que no hay derivados --}}
                        {{-- <button class="nav-link" id="ped-programas-tab" data-bs-toggle="pill"
                            data-bs-target="#ped-programas" type="button" role="tab" aria-controls="ped-programas"
                            aria-selected="false">
                            Programas Derivados
                        </button> --}}
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="tab-content" id="ped-pills-tabContent">
                        <div class="tab-pane fade show active" id="ped-generales" role="tabpanel"
                            aria-labelledby="ped-generales-tab">
                            <h4 class="h5 mb-3 fw-semibold" style="color: var(--color-primary);">Datos Generales
                                (PED)</h4>
                            <p class="mb-4 text-muted">Metadatos y catálogos relacionados con el Plan Estatal de
                                Desarrollo 2024-2030.</p>
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
                                            <form action="{{ route('datos-abiertos-ped.json') }}" method="POST"
                                                style="display: inline-block;" target="_blank">
                                                {{-- target="_blank" para abrir el JSON en una nueva pestaña --}}
                                                @csrf
                                                <input type="hidden" name="parametro" value="total-indicadores">
                                                {{-- No necesitas 'nombre_archivo' para una respuesta JSON en el navegador --}}

                                                <button type="submit" title="Ver Datos Completos PED en JSON"
                                                    class="btn btn-link p-0">
                                                    <img src="{{ asset('img/js.png') }}" alt="Icono de JSON">
                                                    {{-- Necesitarás un icono JSON --}}
                                                </button>
                                            </form>
                                            <form action="{{ route('datos-abiertos-ped.csv') }}" method="POST"
                                                style="display: inline-block;">
                                                @csrf
                                                <input type="hidden" name="parametro" value="total-indicadores">
                                                <input type="hidden" name="nombre_archivo" value="BD_Completa_CSV">

                                                <button type="submit" title="Descargar BD Completa PED en CSV"
                                                    class="btn btn-link p-0">
                                                    <img src="{{ asset('img/csv.png') }}" alt="Icono de CSV">
                                                </button>
                                            </form>
                                            {{-- <form action="{{ route('datos-abiertos-ped') }}" method="POST"
                                                style="display: inline-block;">
                                                @csrf
                                                <input type="hidden" name="parametro" value="total-indicadores-ped">
                                                <input type="hidden" name="nombre_archivo" value="BD_Completa">

                                                <button type="submit" title="Descargar BD Completa PED en XLS"
                                                    class="btn btn-link p-0"
                                                    style="border:none; background:none; cursor:pointer;">
                                                    <img src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                        style="vertical-align: middle;">
                                                </button>
                                            </form> --}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Base - Indicadores del Plan Estatal de Desarrollo</td>
                                        <td class="download-icons">
                                            <form action="{{ route('datos-abiertos-ped.json') }}" method="POST"
                                                style="display: inline-block;" target="_blank">
                                                {{-- target="_blank" para abrir el JSON en una nueva pestaña --}}
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-ped">
                                                {{-- No necesitas 'nombre_archivo' para una respuesta JSON en el navegador --}}

                                                <button type="submit"
                                                    title="Ver Datos Completos de los Indicadores del PED en JSON"
                                                    class="btn btn-link p-0">
                                                    <img src="{{ asset('img/js.png') }}" alt="Icono de JSON">
                                                    {{-- Necesitarás un icono JSON --}}
                                                </button>
                                            </form>
                                            <form action="{{ route('datos-abiertos-ped.csv') }}" method="POST"
                                                style="display: inline-block;">
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-ped">
                                                <input type="hidden" name="nombre_archivo" value="BD_Completa_CSV">

                                                <button type="submit" title="Descargar BD Completa PED en CSV"
                                                    class="btn btn-link p-0">
                                                    <img src="{{ asset('img/csv.png') }}" alt="Icono de CSV">
                                                    {{-- Necesitarás un icono CSV --}}
                                                </button>
                                            </form>
                                            {{-- <form action="{{ route('datos-abiertos-ped') }}" method="POST"
                                                style="display: inline-block;">
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-ped">
                                                <input type="hidden" name="nombre_archivo" value="BD_PED_Completa">

                                                <button type="submit" title="Descargar BD Completa PED en XLS"
                                                    class="btn btn-link p-0"
                                                    style="border:none; background:none; cursor:pointer;">
                                                    <img src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                        style="vertical-align: middle;">
                                                </button>
                                            </form> --}}
                                        </td>
                                    </tr>
                                    {{-- <tr>
                                        <td>Base - Indicadores de los Programas Derivados</td>
                                        <td class="download-icons">
                                            <a href="#" title="Descargar JSON"><img src="{{ asset('img/js.png') }}"
                                                    alt="Icono de JS" title="Icono JS"></a>
                                            <a href="#" title="Descargar CSV"><img src="{{ asset('img/csv.png') }}"
                                                    alt="Icono de CSV" title=" Icono CSV"></a>
                                            <a href="#" title="Descargar XLS"> <img src="{{ asset('img/xls.png') }}"
                                                    alt="Icono de XLS" srcset="Icono XLS"></a>
                                        </td>
                                    </tr> --}}
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="ped-plan" role="tabpanel" aria-labelledby="ped-plan-tab">
                            <h4 class="h5 mb-3 fw-semibold" style="color: var(--color-primary);">Plan Estatal
                                de
                                Desarrollo 2024-2030</h4>
                            <p class="mb-4 text-muted">Conjuntos de datos específicos del PED
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
                                        <td>Eje 1 - Humanismo con Bienestar</td>
                                        <td class="download-icons">
                                            <form action="{{ route('datos-abiertos-ped.json') }}" method="POST"
                                                style="display: inline-block;" target="_blank">
                                                {{-- target="_blank" para abrir el JSON en una nueva pestaña --}}
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-eje1-ped">
                                                {{-- No necesitas 'nombre_archivo' para una respuesta JSON en el navegador --}}

                                                <button type="submit"
                                                    title="Ver Datos Completos de los Indicadores del Eje 1 del PED en JSON"
                                                    class="btn btn-link p-0">
                                                    <img src="{{ asset('img/js.png') }}" alt="Icono de JSON">
                                                    {{-- Necesitarás un icono JSON --}}
                                                </button>
                                            </form>
                                            <form action="{{ route('datos-abiertos-ped.csv') }}" method="POST"
                                                style="display: inline-block;">
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-eje1-ped">
                                                <input type="hidden" name="nombre_archivo" value="BD_Eje1_PED_Completa">

                                                <button type="submit"
                                                    title="Descargar BD Completa de los Indicadores del Eje 1 del PED en CSV"
                                                    class="btn btn-link p-0">
                                                    <img src="{{ asset('img/csv.png') }}" alt="Icono de CSV">
                                                    {{-- Necesitarás un icono CSV --}}
                                                </button>
                                            </form>
                                            {{-- <form action="{{ route('datos-abiertos-ped') }}" method="POST"
                                                style="display: inline-block;">
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-eje1-ped">
                                                <input type="hidden" name="nombre_archivo" value="BD_Eje1_PED_Completa">

                                                <button type="submit"
                                                    title="Descargar BD Completa de los Indicadores del Eje 1 del PED"
                                                    class="btn btn-link p-0"
                                                    style="border:none; background:none; cursor:pointer;">
                                                    <img src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                        style="vertical-align: middle;">
                                                </button>
                                            </form> --}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Eje 2 - Prosperidad y Estabilidad
                                            Económica</td>
                                        <td class="download-icons">
                                            <form action="{{ route('datos-abiertos-ped.json') }}" method="POST"
                                                style="display: inline-block;" target="_blank">
                                                {{-- target="_blank" para abrir el JSON en una nueva pestaña --}}
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-eje2-ped">
                                                {{-- No necesitas 'nombre_archivo' para una respuesta JSON en el navegador --}}

                                                <button type="submit"
                                                    title="Ver Datos Completos de los Indicadores del Eje 2 del PED en JSON"
                                                    class="btn btn-link p-0">
                                                    <img src="{{ asset('img/js.png') }}" alt="Icono de JSON">
                                                    {{-- Necesitarás un icono JSON --}}
                                                </button>
                                            </form>
                                            <form action="{{ route('datos-abiertos-ped.csv') }}" method="POST"
                                                style="display: inline-block;">
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-eje2-ped">
                                                <input type="hidden" name="nombre_archivo" value="BD_Eje2_PED_Completa">

                                                <button type="submit"
                                                    title="Descargar BD Completa de los Indicadores del Eje 2 del PED en CSV"
                                                    class="btn btn-link p-0">
                                                    <img src="{{ asset('img/csv.png') }}" alt="Icono de CSV">
                                                    {{-- Necesitarás un icono CSV --}}
                                                </button>
                                            </form>
                                            {{-- <form action="{{ route('datos-abiertos-ped') }}" method="POST"
                                                style="display: inline-block;">
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-eje2-ped">
                                                <input type="hidden" name="nombre_archivo" value="BD_Eje2_PED_Completa">

                                                <button type="submit"
                                                    title="Descargar BD Completa de los Indicadores del Eje 2 del PED"
                                                    class="btn btn-link p-0"
                                                    style="border:none; background:none; cursor:pointer;">
                                                    <img src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                        style="vertical-align: middle;">
                                                </button>
                                            </form> --}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Eje 3 - Estado de Derecho, Seguridad y
                                            Justicia</td>
                                        <td class="download-icons">
                                            <form action="{{ route('datos-abiertos-ped.json') }}" method="POST"
                                                style="display: inline-block;" target="_blank">
                                                {{-- target="_blank" para abrir el JSON en una nueva pestaña --}}
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-eje3-ped">
                                                {{-- No necesitas 'nombre_archivo' para una respuesta JSON en el navegador --}}

                                                <button type="submit"
                                                    title="Ver Datos Completos de los Indicadores del Eje 3 del PED en JSON"
                                                    class="btn btn-link p-0">
                                                    <img src="{{ asset('img/js.png') }}" alt="Icono de JSON">
                                                    {{-- Necesitarás un icono JSON --}}
                                                </button>
                                            </form>
                                            <form action="{{ route('datos-abiertos-ped.csv') }}" method="POST"
                                                style="display: inline-block;">
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-eje3-ped">
                                                <input type="hidden" name="nombre_archivo" value="BD_Eje3_PED_Completa">

                                                <button type="submit"
                                                    title="Descargar BD Completa de los Indicadores del Eje 3 del PED en CSV"
                                                    class="btn btn-link p-0">
                                                    <img src="{{ asset('img/csv.png') }}" alt="Icono de CSV">
                                                    {{-- Necesitarás un icono CSV --}}
                                                </button>
                                            </form>
                                            {{-- <form action="{{ route('datos-abiertos-ped') }}" method="POST"
                                                style="display: inline-block;">
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-eje3-ped">
                                                <input type="hidden" name="nombre_archivo" value="BD_Eje3_PED_Completa">

                                                <button type="submit"
                                                    title="Descargar BD Completa de los Indicadores del Eje 3 del PED"
                                                    class="btn btn-link p-0"
                                                    style="border:none; background:none; cursor:pointer;">
                                                    <img src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                        style="vertical-align: middle;">

                                                </button>
                                            </form> --}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Eje 4 - Desarrollo Urbano y Crecimiento
                                            Sostenible</td>
                                        <td class="download-icons">
                                            <form action="{{ route('datos-abiertos-ped.json') }}" method="POST"
                                                style="display: inline-block;" target="_blank">
                                                {{-- target="_blank" para abrir el JSON en una nueva pestaña --}}
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-eje4-ped">
                                                {{-- No necesitas 'nombre_archivo' para una respuesta JSON en el navegador --}}

                                                <button type="submit"
                                                    title="Ver Datos Completos de los Indicadores del Eje 4 del PED en JSON"
                                                    class="btn btn-link p-0">
                                                    <img src="{{ asset('img/js.png') }}" alt="Icono de JSON">
                                                    {{-- Necesitarás un icono JSON --}}
                                                </button>
                                            </form>
                                            <form action="{{ route('datos-abiertos-ped.csv') }}" method="POST"
                                                style="display: inline-block;">
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-eje4-ped">
                                                <input type="hidden" name="nombre_archivo" value="BD_Eje4_PED_Completa">

                                                <button type="submit"
                                                    title="Descargar BD Completa de los Indicadores del Eje 4 del PED en CSV"
                                                    class="btn btn-link p-0">
                                                    <img src="{{ asset('img/csv.png') }}" alt="Icono de CSV">
                                                    {{-- Necesitarás un icono CSV --}}
                                                </button>
                                            </form>
                                            {{-- <form action="{{ route('datos-abiertos-ped') }}" method="POST"
                                                style="display: inline-block;">
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-eje4-ped">
                                                <input type="hidden" name="nombre_archivo" value="BD_Eje4_PED_Completa">

                                                <button type="submit"
                                                    title="Descargar BD Completa de los Indicadores del Eje 4 del PED"
                                                    class="btn btn-link p-0"
                                                    style="border:none; background:none; cursor:pointer;">
                                                    <img src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                        style="vertical-align: middle;">

                                                </button>
                                            </form> --}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Eje 5 - Gobierno Transformador y de
                                            Resultados</td>
                                        <td class="download-icons">
                                            <form action="{{ route('datos-abiertos-ped.json') }}" method="POST"
                                                style="display: inline-block;" target="_blank">
                                                {{-- target="_blank" para abrir el JSON en una nueva pestaña --}}
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-eje5-ped">
                                                {{-- No necesitas 'nombre_archivo' para una respuesta JSON en el navegador --}}

                                                <button type="submit"
                                                    title="Ver Datos Completos de los Indicadores del Eje 5 del PED en JSON"
                                                    class="btn btn-link p-0">
                                                    <img src="{{ asset('img/js.png') }}" alt="Icono de JSON">
                                                    {{-- Necesitarás un icono JSON --}}
                                                </button>
                                            </form>
                                            <form action="{{ route('datos-abiertos-ped.csv') }}" method="POST"
                                                style="display: inline-block;">
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-eje5-ped">
                                                <input type="hidden" name="nombre_archivo" value="BD_Eje5_PED_Completa">

                                                <button type="submit"
                                                    title="Descargar BD Completa de los Indicadores del Eje 5 del PED en CSV"
                                                    class="btn btn-link p-0">
                                                    <img src="{{ asset('img/csv.png') }}" alt="Icono de CSV">
                                                    {{-- Necesitarás un icono CSV --}}
                                                </button>
                                            </form>
                                            {{-- <form action="{{ route('datos-abiertos-ped') }}" method="POST"
                                                style="display: inline-block;">
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-eje5-ped">
                                                <input type="hidden" name="nombre_archivo" value="BD_Eje5_PED_Completa">

                                                <button type="submit"
                                                    title="Descargar BD Completa de los Indicadores del Eje 5 del PED"
                                                    class="btn btn-link p-0"
                                                    style="border:none; background:none; cursor:pointer;">
                                                    <img src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                        style="vertical-align: middle;">

                                                </button>
                                            </form> --}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Eje Transversal - Por Amor a Puebla</td>
                                        <td class="download-icons">
                                            <form action="{{ route('datos-abiertos-ped.json') }}" method="POST"
                                                style="display: inline-block;" target="_blank">
                                                {{-- target="_blank" para abrir el JSON en una nueva pestaña --}}
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-eje6-ped">
                                                {{-- No necesitas 'nombre_archivo' para una respuesta JSON en el navegador --}}

                                                <button type="submit"
                                                    title="Ver Datos Completos de los Indicadores del Eje 6 del PED en JSON"
                                                    class="btn btn-link p-0">
                                                    <img src="{{ asset('img/js.png') }}" alt="Icono de JSON">
                                                    {{-- Necesitarás un icono JSON --}}
                                                </button>
                                            </form>
                                            <form action="{{ route('datos-abiertos-ped.csv') }}" method="POST"
                                                style="display: inline-block;">
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-eje6-ped">
                                                <input type="hidden" name="nombre_archivo" value="BD_Eje6_PED_Completa">

                                                <button type="submit"
                                                    title="Descargar BD Completa de los Indicadores del Eje 6 del PED en CSV"
                                                    class="btn btn-link p-0">
                                                    <img src="{{ asset('img/csv.png') }}" alt="Icono de CSV">
                                                    {{-- Necesitarás un icono CSV --}}
                                                </button>
                                            </form>
                                            {{-- <form action="{{ route('datos-abiertos-ped') }}" method="POST"
                                                style="display: inline-block;">
                                                @csrf
                                                <input type="hidden" name="parametro" value="indicadores-eje6-ped">
                                                <input type="hidden" name="nombre_archivo" value="BD_Eje6_PED_Completa">

                                                <button type="submit"
                                                    title="Descargar BD Completa de los Indicadores del Eje 6 del PED"
                                                    class="btn btn-link p-0"
                                                    style="border:none; background:none; cursor:pointer;">
                                                    <img src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                        style="vertical-align: middle;">

                                                </button>
                                            </form> --}}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        {{-- Comentado el 19 de mayo por que aun no hay derivados --}}
                        {{-- <div class="tab-pane fade" id="ped-programas" role="tabpanel"
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
                                            <a href="#" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="#" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="#" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Programas Especiales</td>
                                        <td class="download-icons">
                                            <a href="#" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="#" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="#" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Programas Institucionales</td>
                                        <td class="download-icons">
                                            <a href="#" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="#" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="#" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Programas Regionales</td>
                                        <td class="download-icons">
                                            <a href="#" title="Descargar JSON"><img
                                                    src="{{ asset('img/js.png') }}" alt="Icono de JS"
                                                    title="Icono JS"></a>
                                            <a href="#" title="Descargar CSV"><img
                                                    src="{{ asset('img/csv.png') }}" alt="Icono de CSV"
                                                    title=" Icono CSV"></a>
                                            <a href="#" title="Descargar XLS"> <img
                                                    src="{{ asset('img/xls.png') }}" alt="Icono de XLS"
                                                    srcset="Icono XLS"></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> --}}
                    </div>
                </div>
            </div>
        </section>
    </div>

@endsection
