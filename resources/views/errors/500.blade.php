@extends('layouts.plantilla')

@section('title', 'Error 500 - Error Interno del Servidor')

@section('meta-description',
'Ha ocurrido un error inesperado en nuestros servidores. Estamos trabajando para solucionarlo lo antes posible.')

@section('canonical-url', url()->current())

{{-- Open Graph --}}
@section('og-title', 'Error 500 - Error Interno - Sistema de Información para el Seguimiento a la Planeación')
@section('og-description', 'Nuestros servidores encontraron un problema inesperado. Por favor intente más tarde.')
@section('og:url', url()->current())

{{-- Twitter --}}
@section('twitter-title', 'Error 500 - Error Interno')
@section('twitter-description', 'Ocurrió un error en el servidor. Estamos trabajando en ello.')

@section('css')
{{-- Si usaste el CSS compartido que te pasé antes (.container-error), no necesitas agregar nada extra aquí --}}
@endsection

@section('content')
{{-- Usamos la misma clase compartida para mantener el diseño del pájaro y colores --}}
<section class="container-error">
    <h1 class="status-code">500</h1>
    <h2 class="message-title">¡Ups! Algo salió mal</h2>

    <p class="message-text">
        Lo sentimos, nuestros servidores encontraron un error inesperado y no pudieron completar tu solicitud.
        Este problema ha sido registrado y nuestro equipo técnico ya ha sido notificado.
    </p>

    <ul>
        <li>
            Puede ser un problema temporal del sistema.
        </li>
        <li>
            Intenta recargar la página en unos minutos.
        </li>
        <li>
            Si el problema persiste, por favor contáctanos.
        </li>
    </ul>

    <div class="d-flex gap-3 flex-wrap justify-content-center">
        {{-- Botón de recargar (Muy útil en errores 500) --}}
        <button onclick="location.reload();" class="home-button border-0 cursor-pointer me-3">
            <i class="fa-solid fa-rotate-right"></i> Reintentar
        </button>

        <a href="/" class="home-button">
            Volver al Inicio
        </a>
    </div>
</section>
@endsection