@extends('layouts.plantilla')
@section('title', 'Programas Derivados Institucionales del Plan Estatal de Desarrollo 2024-2030')
@section('meta-description',
'Sección de los Programas Derivados Institucionales del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de
Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del
Estado de Puebla')
@section('canonical-url', url()->current())
@section('og-title',
'Programas Derivados Institucionales del Plan Estatal de Desarrollo 2024-2030 - Sistema de Información para el
Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('og-description',
'Sección de los Programas Derivados Institucionales del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de
Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del
Estado de Puebla')
@section('og:url', url()->current())
@section('twitter-title',
'Programas Derivados Institucionales del Plan Estatal de Desarrollo 2024-2030 - Sistema de Información para el
Seguimiento a la Planeación y Evaluación del Desarrollo
del Estado de Puebla')
@section('twitter-description',
'Sección de los Programas Derivados Institucionales del Plan Estatal de Desarrollo 2024-2030 dentro del Sistema de
Información para el Seguimiento a la Planeación y Evaluación del Desarrollo del
Estado de Puebla')
@section('css')
<style>
    .hover-shadow {
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;  
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .filter-btn {
        font-weight: 500;
        letter-spacing: 0.3px;
    }
    .program-card-col {
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
</style>
@endsection
@section('jss-inicial')
@endsection
@section('content')
@include('partials.nav-unificada', [
'tipoNav' => 'derivados',
'itemActivo' => 'App\Models\CatProgramaDerivadoInstitucional',
'colorTema' => '#B72D33'
])
<div class="row contenido mb-5 mt-3">
    <div class="col-sm-12 col-md-3 offset-md-1 d-flex justify-content-center align-items-center">
        <img class="img-fluid" src="{{ asset('img/what3.png') }}" style="max-height: 120px;">
    </div>
    <div class="col-sm-12 col-md-7 d-flex align-items-center">
        <p class="fs-4 text-muted border-start ps-4" style="border-width: 4px !important; border-color: #B72D33 !important;">
            Son instrumentos que identifican, organizan y orientan los instrumentos de política con los que cuentan las Dependencias y
            Entidades de la Administración Pública Estatal, para fortalecer sus capacidades y de acuerdo a sus
            atribuciones y funciones, coadyuvar al cumplimiento de los objetivos y metas del Plan Estatal de Desarrollo.
        </p>
    </div>
</div>

<div class="container mb-5">
    <!-- Barra de Filtros -->
    <div class="row mb-5">
        <div class="col-12 d-flex justify-content-center flex-wrap gap-2" id="filter-buttons">
            <button class="btn btn-danger rounded-pill px-4 py-2 filter-btn shadow-sm me-2 active" data-filter="all">
                Todos
            </button>
            @foreach ($grupos as $grupo)
                <button class="btn btn-outline-danger rounded-pill px-4 py-2 filter-btn shadow-sm me-2" data-filter="{{ Str::slug($grupo) }}">
                    {{ $grupo }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Grid de Programas -->
    <div class="row g-4" id="programs-grid">
        @foreach ($programas as $programa)
        <div class="col-12 col-md-6 col-lg-4 program-card-col" data-grupo="{{ Str::slug($programa->grupo) }}">
            <a href="{{ url('/ped-programas/institucionales/' . Illuminate\Support\Str::slug($programa->nombre)) }}"
                class="card shadow-sm border-0 rounded-4 p-3 text-decoration-none h-100 d-flex flex-row align-items-center gap-3 hover-shadow"
                style="background: #ffffff; border-left: 5px solid {{ $programa->color ?? '#B72D33' }} !important;">
                
                <!-- Círculo de siglas con el color del programa -->
                <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 text-white fw-bold shadow-sm"
                    style="background-color: {{ $programa->color ?? '#B72D33' }}; width: 55px; height: 55px; font-size: 1.1rem; min-width: 55px;">
                    {{ $programa->siglas }}
                </div>
                
                <!-- Info -->
                <div class="flex-grow-1 min-w-0">
                    <h5 class="mb-1 text-dark fw-bold text-truncate-2" style="font-size: 0.95rem; line-height: 1.3;" title="{{ $programa->nombre }}">
                        {{ $programa->nombre }}
                    </h5>
                    <span class="badge bg-light text-muted border text-uppercase" style="font-size: 0.7rem;">
                        {{ $programa->grupo ?? 'General' }}
                    </span>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>
@section('jss-final')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.filter-btn');
    const cards = document.querySelectorAll('.program-card-col');

    buttons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active classes
            buttons.forEach(btn => {
                btn.classList.remove('btn-danger', 'active');
                btn.classList.add('btn-outline-danger');
            });

            // Add active class
            this.classList.add('btn-danger', 'active');
            this.classList.remove('btn-outline-danger');

            const filterValue = this.getAttribute('data-filter');

            cards.forEach(card => {
                if (filterValue === 'all') {
                    card.style.display = 'block';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'scale(1)';
                    }, 50);
                } else {
                    const cardGrupo = card.getAttribute('data-grupo');
                    if (cardGrupo === filterValue) {
                        card.style.display = 'block';
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'scale(1)';
                        }, 50);
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.9)';
                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 300);
                    }
                }
            });
        });
    });
});
</script>
@endsection
@endsection