{{-- resources/views/partials/nav-unificada.blade.php --}}
@php
// Recibimos variables o asignamos defaults
$tipoNav = $tipoNav ?? 'ped'; // Puede ser 'ped' o 'derivados'
$itemActivo = $itemActivo ?? null; // ID del eje (1,2..) o Modelo del derivado
$colorTema = $colorTema ?? '#0056b3'; // Color de la pastilla activa
$bannerImg = $bannerImg ?? null; // Ruta de la imagen si lleva banner
@endphp

{{-- 1. SECCIÓN DEL BANNER (Opcional) --}}
@if($bannerImg)
<div class="row mx-0 mb-3 banner-container ocultar_impresion">
    <img src="{{ asset($bannerImg) }}"
        alt="Banner de la sección"
        class="w-100 px-0 shadow-sm banner-img">
</div>
@endif

{{-- 2. BARRA DE NAVEGACIÓN MODERNA --}}
<div class="row mx-0 mb-4 nav-moderna ocultar_impresion">
    <div class="col-12 px-0">
        <div class="bg-light rounded-pill p-1 shadow-sm d-flex w-100 border" style="overflow-x: auto;">

            @if($tipoNav === 'ped')
            {{-- MODO 1: EJES DEL PED --}}
            @php
            $ejes = [
            1 => ['nombre' => 'Eje 1', 'url' => '/ped/eje-1'],
            2 => ['nombre' => 'Eje 2', 'url' => '/ped/eje-2'],
            3 => ['nombre' => 'Eje 3', 'url' => '/ped/eje-3'],
            4 => ['nombre' => 'Eje 4', 'url' => '/ped/eje-4'],
            5 => ['nombre' => 'Eje 5', 'url' => '/ped/eje-5'],
            6 => ['nombre' => 'Eje Transversal', 'url' => '/ped/eje-6'],
            ];
            @endphp
            @foreach($ejes as $numEje => $eje)
            @php $esActivo = ($itemActivo == $numEje); @endphp
            <a href="{{ url($eje['url']) }}"
                class="flex-fill text-center text-decoration-none py-2 px-3 nav-item-modern {{ $esActivo ? 'active shadow-sm' : '' }}"
                {!! $esActivo ? 'style="background-color: var(--color-eje' .$numEje.', '.$colorTema.' );"' : '' !!}>
                {{ $eje['nombre'] }}
            </a>
            @endforeach

            @elseif($tipoNav === 'derivados')
            {{-- MODO 2: PROGRAMAS DERIVADOS --}}
            @php
            $derivados = [
            'App\Models\CatProgramaDerivadoSectorial' => ['nombre' => 'Sectoriales', 'url' => '/ped-programas/sectoriales'],
            'App\Models\CatProgramaDerivadoEspecial' => ['nombre' => 'Especiales', 'url' => '/ped-programas/especiales'],
            'App\Models\CatProgramaDerivadoInstitucional' => ['nombre' => 'Institucionales', 'url' => '/ped-programas/institucionales'],
            'App\Models\CatProgramaDerivadoRegional' => ['nombre' => 'Regionales', 'url' => '/ped-programas/regionales'],
            ];
            @endphp
            @foreach($derivados as $modelo => $derivado)
            @php $esActivo = ($itemActivo === $modelo); @endphp
            <a href="{{ url($derivado['url']) }}"
                class="flex-fill text-center text-decoration-none py-2 px-3 nav-item-modern {{ $esActivo ? 'active shadow-sm' : '' }}"
                {!! $esActivo ? 'style="background-color: ' .$colorTema.';"' : '' !!}>
                {{ $derivado['nombre'] }}
            </a>
            @endforeach
            @endif

        </div>
    </div>
</div>