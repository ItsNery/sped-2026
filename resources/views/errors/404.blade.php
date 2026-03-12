@extends('layouts.plantilla')
@section('title', 'Error 404 - Página no encontrada')
@section('meta-description',
    'La página que buscas no existe. Por favor, verifica la URL o regresa al inicio del Sistema
    de Información para el Seguimiento a la Planeación y Evaluación del Estado de Puebla.')
@section('canonical-url', url()->current())
@section('og-title',
    'Error 404 - Página no encontrada - Sistema de Información para el Seguimiento a la Planeación y Evaluación del
    Desarrollo
    del Estado de Puebla')
@section('og-description',
    'La página que buscas no está disponible. Regresa al inicio o utiliza nuestro buscador para
    encontrar la información que necesitas.')
@section('og:url', url()->current())
@section('twitter-title',
    'Error 404 - Página no encontrada - Sistema de Información para el Seguimiento a la Planeación y Evaluación del
    Desarrollo
    del Estado de Puebla')
@section('twitter-description', 'La página no existe. Visita el inicio del Sistema de Información para el Seguimiento a
    la Planeación del Estado de Puebla.')
@section('css')
@endsection
@section('jss-inicial')
@endsection
@section('content')
    <section class="container-404">
        <h1 class="status-code">404</h1>
        <h2 class="message-title">Página No Encontrada</h2>
        <p class="message-text">
            Lo sentimos, la página que buscas no existe o ha sido movida. Esto podría deberse a alguna de las siguientes
            razones:
        </p>
        <ul>
            <li>
                Un marcador o favorito caducado
            </li>
            <li>
                Una dirección mal escrita
            </li>
            <li>
                Un motor de búsquedas que tiene un listado caducado para este sitio
            </li>
            <li>
                Usted no tiene acceso a esta página
            </li>
        </ul>
        <a href="/" class="home-button">Volver al Inicio</a>
    </section>
@section('jss-final')
@endsection
@endsection
