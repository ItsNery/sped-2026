@extends('layouts.plantilla')
@section('title', 'Reportes anuales del SPED')
@section('meta-description',
    'Consulta los reportes anuales de seguimiento de los indicadores del Sistema de Información
    para el Seguimiento a la Planeación y Evaluación del Desarrollo del Estado de Puebla.')
@section('canonical-url', url()->current())
@section('og-title', 'Reportes anuales del SPED')
@section('og-description', 'Consulta los reportes anuales de seguimiento de los indicadores del SPED.')
@section('og:url', url()->current())
@section('twitter-title', 'Reportes anuales del SPED')
@section('twitter-description', 'Consulta los reportes anuales de seguimiento de los indicadores del SPED.')

@section('content')
    <div class="reportes-anuales">
        <section class="reportes-anuales__hero">
            <div class="reportes-anuales__hero-container">
                <div class="reportes-anuales__hero-content">
                    <span class="reportes-anuales__hero-tag">Seguimiento y evaluación</span>
                    <h1 class="reportes-anuales__hero-title">Reportes anuales del SPED</h1>
                    <p class="reportes-anuales__hero-desc">Consulta los resultados anuales del seguimiento a los indicadores
                        del desarrollo del Estado de Puebla.</p>
                </div>
            </div>
        </section>

        <main class="reportes-anuales__main">
            <div class="reportes-anuales__container">
                <p class="reportes-anuales__intro">Reportes disponibles para consulta y descarga.</p>
                <ul class="reportes-anuales__list">
                    <li class="reportes-anuales__item">
                        <div class="reportes-anuales__info">
                            <h2 class="reportes-anuales__title">Reporte Ejecutivo 2025</h2>
                            <span class="reportes-anuales__format">
                                <i class="fas fa-file-pdf"></i>
                                PDF
                            </span>
                        </div>
                        <a href="{{ asset('docs/datos-abiertos/2024-2030/reporte/Reporte_Ejecutivo_2025-1.pdf') }}"
                            class="reportes-anuales__download" target="_blank" rel="noopener">
                            <i class="fas fa-globe"></i>
                            Consultar
                        </a>
                    </li>
                    <li class="reportes-anuales__item">
                        <div class="reportes-anuales__info">
                            <h2 class="reportes-anuales__title">Reporte Ejecutivo 2024</h2>
                            <span class="reportes-anuales__format">
                                <i class="fas fa-file-pdf"></i>
                                PDF
                            </span>
                        </div>
                        <a href="{{ asset('docs/datos-abiertos/2019-2024/reporte/Reporte_Ejecutivo_2024.pdf') }}"
                            class="reportes-anuales__download" target="_blank" rel="noopener">
                            <i class="fas fa-globe"></i>
                            Consultar
                        </a>
                    </li>
                </ul>
            </div>
        </main>
    </div>
@endsection
