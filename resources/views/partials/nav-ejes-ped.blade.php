{{-- resources/views/partials/nav-ejes-ped.blade.php --}}
@php
    // Definimos los ejes una sola vez
    $ejes = [
        1 => ['nombre' => 'Eje 1', 'url' => '/ped/eje-1'],
        2 => ['nombre' => 'Eje 2', 'url' => '/ped/eje-2'],
        3 => ['nombre' => 'Eje 3', 'url' => '/ped/eje-3'],
        4 => ['nombre' => 'Eje 4', 'url' => '/ped/eje-4'],
        5 => ['nombre' => 'Eje 5', 'url' => '/ped/eje-5'],
        6 => ['nombre' => 'Eje Transversal', 'url' => '/ped/eje-6'],
    ];
@endphp

<div class="row mx-0 banner-container">
    <img src="{{ asset('img/Banners/Banner_PED/Eje_' . $ejeActivo . '.jpg') }}" 
         alt="Banner del Eje {{ $ejeActivo }}" 
         class="w-100 px-0 shadow-sm banner-img"
         style="max-height: 400px; object-fit: cover; border-radius: 8px;">
</div>

<div class="row mx-0 mb-4 nav-moderna ocultar_submenu">
    <div class="col-12 px-0">
        <div class="bg-light rounded-pill p-1 shadow-sm d-flex w-100 border" style="overflow-x: auto;">
            
            @foreach ($ejes as $numEje => $eje)
                <a href="{{ url($eje['url']) }}" 
                   class="flex-fill text-center text-decoration-none py-2 px-3 nav-item-modern {{ $ejeActivo == $numEje ? 'active shadow-sm' : '' }}">
                    {{ $eje['nombre'] }}
                </a>
            @endforeach

        </div>
    </div>
</div>

<style>
    /* Estilos del menú tipo píldora */
    .nav-moderna .nav-item-modern {
        color: #5c636a;
        font-weight: 500;
        font-size: 0.95rem;
        border-radius: 50px;
        transition: all 0.2s ease-in-out;
        white-space: nowrap;
    }
    .nav-moderna .nav-item-modern:hover:not(.active) {
        color: #212529;
        background-color: rgba(0,0,0,0.04);
    }
    
    /* El estado activo (puedes cambiar el #0056b3 por el color institucional) */
    .nav-moderna .nav-item-modern.active {
        color: #ffffff;
        background-color: var(--color-eje{{ $ejeActivo }});
        font-weight: 600;
    }

    @media print {
        .banner-container, .nav-moderna {
            display: none !important;
        }
    }
</style>