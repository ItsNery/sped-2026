@php
    $programas = $programas ?? collect();
    $tipoNombre = $tipoNombre ?? 'Programas derivados';
    $colorTema = $colorTema ?? '#0c312d';
    $descripcion = $descripcion ?? '';
    $grupos = $grupos ?? collect();
@endphp

@include('partials.nav-unificada', [
    'tipoNav' => 'derivados',
    'itemActivo' => $modeloActivo,
    'colorTema' => $colorTema,
])

<main class="derivados-dashboard" style="--derivado-color: {{ $colorTema }};">
    <section class="derivados-dashboard__intro">
        <div class="derivados-dashboard__container">
            <span class="derivados-dashboard__eyebrow">Plan Estatal de Desarrollo 2024-2030</span>
            <h1 class="derivados-dashboard__title">{{ $tipoNombre }}</h1>
            <p class="derivados-dashboard__description">{{ $descripcion }}</p>
        </div>
    </section>

    <section class="derivados-dashboard__container">
        <div class="derivados-dashboard__panel">
            <div class="derivados-dashboard__search">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="search" class="form-control derivados-search"
                        placeholder="Buscar programa por nombre..." autocomplete="off">
                </div>
            </div>

            @if($grupos->isNotEmpty())
                <div class="derivados-dashboard__filters">
                    <button type="button" class="derivados-dashboard__filter active" data-filter="all">Todos</button>
                    @foreach($grupos as $grupo)
                        <button type="button" class="derivados-dashboard__filter" data-filter="{{ Illuminate\Support\Str::slug($grupo) }}">
                            {{ $grupo }}
                        </button>
                    @endforeach
                </div>
            @endif

            @if($programas->isNotEmpty())
                <div class="derivados-dashboard__grid">
                    @foreach($programas as $programa)
                        @php
                            $nombre = $programa->nombre;
                            $grupo = $programa->grupo ?? $tipoNombre;
                            $iconoPorTipo = [
                                'sectoriales' => 'fa-industry',
                                'especiales' => 'fa-star',
                                'regionales' => 'fa-map-location-dot',
                                'institucionales' => 'fa-building',
                            ];
                            $icono = $programa->icono ?? ($iconoPorTipo[$tipoSlug] ?? 'fa-layer-group');
                        @endphp
                        <div class="derivados-dashboard__item" data-nombre="{{ strtolower($nombre) }}"
                            data-grupo="{{ Illuminate\Support\Str::slug($grupo) }}">
                            <a href="{{ url('/ped-programas/' . $tipoSlug . '/' . Illuminate\Support\Str::slug($nombre)) }}"
                                class="derivados-dashboard__card" style="--programa-color: {{ $colorTema }};"
                                title="{{ $nombre }}">
                                <span class="derivados-dashboard__icon" aria-hidden="true">
                                    <i class="fas {{ $icono }}"></i>
                                </span>
                                <span class="derivados-dashboard__body">
                                    <span class="derivados-dashboard__name">{{ $nombre }}</span>
                                    <span class="derivados-dashboard__tag">{{ $grupo }}</span>
                                </span>
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="derivados-dashboard__empty d-none derivados-empty-search">
                    No se encontraron programas con los filtros seleccionados.
                </div>
            @else
                <div class="derivados-dashboard__empty">No hay programas disponibles.</div>
            @endif
        </div>
    </section>
</main>

@section('jss-final')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const search = document.querySelector('.derivados-search');
            const items = document.querySelectorAll('.derivados-dashboard__item');
            const filters = document.querySelectorAll('.derivados-dashboard__filter');
            const empty = document.querySelector('.derivados-empty-search');
            let activeFilter = 'all';

            function filterPrograms() {
                const text = search ? search.value.toLowerCase().trim() : '';
                let visible = 0;

                items.forEach(function(item) {
                    const matchesName = (item.dataset.nombre || '').includes(text);
                    const matchesGroup = activeFilter === 'all' || item.dataset.grupo === activeFilter;
                    const show = matchesName && matchesGroup;
                    item.classList.toggle('d-none', !show);
                    if (show) visible++;
                });

                if (empty) empty.classList.toggle('d-none', visible > 0);
            }

            if (search) search.addEventListener('input', filterPrograms);
            filters.forEach(function(filter) {
                filter.addEventListener('click', function() {
                    filters.forEach(function(button) { button.classList.remove('active'); });
                    this.classList.add('active');
                    activeFilter = this.dataset.filter;
                    filterPrograms();
                });
            });
        });
    </script>
@endsection
