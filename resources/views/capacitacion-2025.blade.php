@extends('layouts.plantilla')
@section('title', 'Capacitación Virtual: Uso del SPED')
@section('meta-description',
'Página de Capacitación Virtual: Uso del SPED')
@section('canonical-url', url()->current())
@section('og-title',
'Capacitación Virtual: Uso del SPED')
@section('og-description',
'Bienvenido a la página de Capacitación Virtual: Uso del SPED.')
@section('og:url', url()->current())
@section('twitter-title',
'Capacitación Virtual: Uso del SPED')
@section('twitter-description',
'Bienvenido a la página de Capacitación Virtual: Uso del SPED.')
@section('css')
@endsection
@section('jss-inicial')
@endsection
@section('content')
@php
// CONFIGURACIÓN
// 1. Fecha de estreno (Formato Y-m-d H:i:s)
$fechaEstreno = \Carbon\Carbon::parse('2025-12-22 11:00:00');
// 2. Datos del video (Puedes pasarlos desde el controlador o ponerlos aquí)
$videoMeta = [
'titulo' => 'Capacitación Virtual: Uso del SPED',
'duracion' => '15:37 min',
'peso' => '161 MB',
'formato' => 'MP4 (Alta Definición)'
];

$ahora = \Carbon\Carbon::now();
$yaDisponible = $ahora->greaterThanOrEqualTo($fechaEstreno);
// Para pruebas inmediatas, descomenta la siguiente línea:
// $yaDisponible = true;
@endphp
<section class="py-5 bg-light">
    <div class="container">
        {{-- Título de la Sección --}}
        <div class="row mb-4 text-center">
            <div class="col-12">
                <h2 style="color: #691C32; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                    Material Audiovisual
                </h2>
                <div style="width: 80px; height: 3px; background-color: #BC955C; margin: 10px auto;"></div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">

                {{-- TARJETA DEL VIDEO --}}
                <div class="gov-video-card">

                    {{-- Encabezado de la tarjeta (Tipo carpeta o expediente) --}}
                    <div class="gov-card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fa-solid fa-film me-2"></i> {{ $videoMeta['titulo'] }}</span>
                            @if($yaDisponible)
                            <span class="badge bg-success">DISPONIBLE</span>
                            @else
                            <span class="badge bg-secondary">PROGRAMADO</span>
                            @endif
                        </div>
                    </div>

                    {{-- CUERPO: Lógica de visualización --}}
                    <div class="gov-card-body">
                        @if ($yaDisponible)
                        {{-- CASO 1: EL VIDEO YA ESTÁ DISPONIBLE --}}
                        <div class="video-wrapper">
                            <video controls poster="{{ asset('img/Presentacion_SPED_Capacitacion.jpg') }}" class="w-100 rounded">
                                <source src="{{ asset('videos/Capacitacion_SPED.mp4') }}" type="video/mp4">
                                Tu navegador no soporta la etiqueta de video.
                            </video>
                        </div>

                        {{-- Barra de Metadatos (Diseño Técnico) --}}
                        <div class="gov-meta-bar">
                            <div class="meta-item">
                                <i class="fa-regular fa-clock"></i>
                                <div>
                                    <small>Duración</small>
                                    <strong>{{ $videoMeta['duracion'] }}</strong>
                                </div>
                            </div>
                            <div class="meta-item border-start border-end">
                                <i class="fa-solid fa-database"></i>
                                <div>
                                    <small>Tamaño</small>
                                    <strong>{{ $videoMeta['peso'] }}</strong>
                                </div>
                            </div>
                            <div class="meta-item">
                                <i class="fa-solid fa-video"></i>
                                <div>
                                    <small>Formato</small>
                                    <strong>{{ $videoMeta['formato'] }}</strong>
                                </div>
                            </div>
                        </div>

                        @else
                        {{-- CASO 2: CUENTA REGRESIVA --}}
                        <div class="countdown-wrapper">
                            <div class="overlay-content text-center">
                                <i class="fa-solid fa-lock fa-3x mb-3 text-muted"></i>
                                <h4 class="mb-4" style="color: #691C32;">Este contenido estará disponible en:</h4>

                                {{-- Contador JS --}}
                                <div id="countdown" class="d-flex justify-content-center gap-3"
                                    data-date="{{ $fechaEstreno->format('Y-m-d H:i:s') }}">

                                    <div class="time-box">
                                        <span id="d">00</span>
                                        <small>Días</small>
                                    </div>
                                    <div class="time-box">
                                        <span id="h">00</span>
                                        <small>Hrs</small>
                                    </div>
                                    <div class="time-box">
                                        <span id="m">00</span>
                                        <small>Min</small>
                                    </div>
                                    <div class="time-box">
                                        <span id="s">00</span>
                                        <small>Seg</small>
                                    </div>
                                </div>

                                <p class="mt-4 text-muted">
                                    Disponible el: <strong>22 de Diciembre, 2025 - 11:00 AM</strong>
                                </p>
                            </div>
                        </div>
                        @endif

                    </div>

                    {{-- Pie de tarjeta decorativo --}}
                    <div class="gov-card-footer"></div>
                </div>

            </div>
        </div>
    </div>
</section>
@section('jss-final')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const countdownEl = document.getElementById('countdown');

        // Si no existe el elemento contador, significa que el video ya está disponible
        if (!countdownEl) return;

        const targetDate = new Date(countdownEl.dataset.date).getTime();

        const timer = setInterval(function() {
            const now = new Date().getTime();
            const distance = targetDate - now;

            if (distance < 0) {
                clearInterval(timer);
                // ¡Tiempo cumplido! Recargamos la página para mostrar el video
                window.location.reload();
                return;
            }

            // Cálculos matemáticos para tiempo
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Actualizar DOM
            document.getElementById('d').innerText = days < 10 ? '0' + days : days;
            document.getElementById('h').innerText = hours < 10 ? '0' + hours : hours;
            document.getElementById('m').innerText = minutes < 10 ? '0' + minutes : minutes;
            document.getElementById('s').innerText = seconds < 10 ? '0' + seconds : seconds;

        }, 1000);
    });
</script>
@endsection
@endsection