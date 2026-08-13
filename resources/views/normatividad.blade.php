@extends('layouts.plantilla')
@section('title', 'Normatividad')
@section('meta-description',
    'Sección de Normatividad del Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del
    Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
    'Normatividad - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('og-description',
    'Sección de Normatividad del Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del
    Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
    'Normatividad - Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
    del Estado de Puebla')
@section('twitter-description',
    'Sección de Normatividad del Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del
    Estado de Puebla')
@section('css')
@endsection
@section('content')
    <div class="normatividad">
        <section class="normatividad__hero">
            <div class="normatividad__hero-container">
                <div class="normatividad__hero-content">
                    <span class="normatividad__hero-tag">Marco jurídico</span>
                    <h1 class="normatividad__hero-title">Normatividad</h1>
                    <p class="normatividad__hero-desc">Consulta los documentos normativos que orientan la planeación, el seguimiento y la evaluación del desarrollo del Estado de Puebla.</p>
                </div>
            </div>
        </section>
        <main class="normatividad__main">
            <div class="container normatividad__container">
                <p class="normatividad__intro">Documentos disponibles para consulta y descarga.</p>
            <div class="container my-4">
                <ul class="normatividad__list">
                    <li class="normatividad__item">
                        <div class="normatividad__info">
                            <h3 class="normatividad__title">Constitución Política del Estado de Puebla</h3>
                            <span class="normatividad__format">
                                <i class="fas fa-file-pdf"></i> PDF
                            </span>
                        </div>
                        <a href="https://ojp.puebla.gob.mx/legislaciondelestado?catid=9" class="normatividad__download"
                            target="_blank">
                            <i class="fas fa-globe"></i> Consultar
                        </a>
                    </li>
                    <li class="normatividad__item">
                        <div class="normatividad__info">
                            <h3 class="normatividad__title">Ley de Planeación para el Desarrollo del Estado de Puebla</h3>
                            <span class="normatividad__format">
                                <i class="fas fa-file-pdf"></i>
                                PDF
                            </span>
                        </div>
                        <a href="https://ojp.puebla.gob.mx/legislacion-del-estado/item/161-ley-de-planeacion-para-el-desarrollo-del-estado-de-puebla"
                            class="normatividad__download" download target="_blank">
                            <i class="fas fa-globe"></i>
                            Consultar
                        </a>
                    </li>
                    <li class="normatividad__item">
                        <div class="normatividad__info">
                            <h3 class="normatividad__title">Lineamientos para la operación del SEI</h3>
                            <span class="normatividad__format">
                                <i class="fas fa-file-pdf"></i>
                                PDF
                            </span>
                        </div>
                        <a href="https://ojp.puebla.gob.mx/legislacion-del-estado/item/1752-lineamientos-para-la-operacion-del-sistema-estatal-de-informacion"
                            class="normatividad__download" download target="_blank">
                            <i class="fas fa-globe"></i>
                            Consultar
                        </a>
                    </li>
                    <li class="normatividad__item">
                        <div class="normatividad__info">
                            <h3 class="normatividad__title">Lineamientos para la operación del SED</h3>
                            <span class="normatividad__format">
                                <i class="fas fa-file-pdf"></i>
                                PDF
                            </span>
                        </div>
                        <a href="https://ojp.puebla.gob.mx/legislacion-del-estado/item/1654-lineamientos-generales-para-el-seguimiento-y-evaluacion-de-los-documentos-rectores-y-programas-presupuestarios-de-la-administracion-publica-del-estado-de-puebla"
                            class="normatividad__download" download target="_blank">
                            <i class="fas fa-globe"></i>
                            Consultar
                        </a>
                    </li>
                    {{-- <li class="normatividad__item">
                        <div class="normatividad__info">
                            <h3 class="normatividad__title">Manual del SPED</h3>
                            <span class="normatividad__format">
                                <i class="fas fa-file-pdf"></i>
                                PDF
                            </span>
                        </div>
                        <a href="#" class="normatividad__download" download target="_blank">
                            <i class="fas fa-clock"></i>
                            Proximamente
                        </a>
                    </li> --}}
                    <li class="normatividad__item">
                        <div class="normatividad__info">
                            <h3 class="normatividad__title">Lineamientos para la operación del SPED</h3>
                            <span class="normatividad__format">
                                <i class="fas fa-file-pdf"></i>
                                PDF
                            </span>
                        </div>
                        <a href="https://ojp.puebla.gob.mx/legislacion-del-estado/item/3810-lineamientos-para-la-operacion-del-sistema-de-informacion-para-el-seguimiento-a-la-planeacion-y-evaluacion-del-desarrollo-en-el-estado-de-puebla"
                            class="normatividad__download" download target="_blank">
                            <i class="fas fa-globe"></i>
                            Consultar
                        </a>
                    </li>
                    <li class="normatividad__item">
                        <div class="normatividad__info">
                            <h3 class="normatividad__title">Guía para la modificación de indicadores</h3>
                            <span class="normatividad__format">
                                <i class="fas fa-file-pdf"></i>
                                PDF
                            </span>
                        </div>
                        <a href="{{ asset('docs/normatividad/GuiaModInd.pdf') }}" class="normatividad__download"
                            target="_blank">
                            <i class="fas fa-globe"></i>
                            Consultar
                        </a>
                    </li>
                </ul>
                <div class="row">
                    {{-- <div class="col-md-3">
                        <div class="panel__image panel__image--book">
                            <a href="components/PDF/Constitucion_del_Estado_de_Puebla.pdf" class="books__book__image"
                                target="_blank">
                                <div class="books__book__img">
                                    <img src="{{ asset('img/Portadas/portada1.png') }}" class="w-100"
                                        alt="Imagen de portada del documento">
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel__image panel__image--book">
                            <a href="components/PDF/Ley_de_Planeaci%c3%b3n_para_el_Desarrollo_del_Estado_de_Puebla.pdf"
                                class="books__book__image" target="_blank">
                                <div class="books__book__img">
                                    <img src="{{ asset('img/Portadas/portada2.png') }}" class="w-100"
                                        alt="Imagen de portada del documento">
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel__image panel__image--book">
                            <a href="components/PDF/Lineamientos_para_la_Operaci%c3%b3n_del_SEI.pdf"
                                class="books__book__image" target="_blank">
                                <div class="books__book__img">
                                    <img src="{{ asset('img/Portadas/portada4.png') }}" class="w-100"
                                        alt="Imagen de portada del documento">
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel__image panel__image--book">
                            <a href="components/PDF/Lineamientos_para_la_Operaci%c3%b3n_del_SED.pdf"
                                class="books__book__image" target="_blank">
                                <div class="books__book__img">
                                    <img src="{{ asset('img/Portadas/portada3.png') }}" class="w-100"
                                        alt="Imagen de portada del documento">
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel__image panel__image--book">
                            <a href="components/PDF/Manual_SPED_Etapa1.pdf" class="books__book__image" target="_blank">
                                <div class="books__book__img">
                                    <img src="{{ asset('img/Portadas/portada5.png') }}" class="w-100"
                                        alt="Imagen de portada del documento">
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel__image panel__image--book">
                            <a href="components/PDF/Lineamientos_SPED.pdf" class="books__book__image" target="_blank">
                                <div class="books__book__img">
                                    <img src="{{ asset('img/Portadas/portada6.png') }}" class="w-100"
                                        alt="Imagen de portada del documento">
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel__image panel__image--book">
                            <a href="components/ZIP/Guia_para_la_modificacion_de_Indicadores.zip" class="books__book__image"
                                target="_blank">
                                <div class="books__book__img">
                                    <img src="{{ asset('img/Portadas/portada7.png') }}" class="w-100"
                                        alt="Imagen de portada del documento">
                                </div>
                            </a>
                        </div>
                    </div> --}}
                </div>
            </div>
            </div>
        </main>
    </div>
@section('jss-final')
@endsection
@endsection
