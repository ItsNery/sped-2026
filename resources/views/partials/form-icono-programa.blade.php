@php
    $defaultIcono = $defaultIcono ?? 'fa-layer-group';
    $iconoActual = old('icono', $programa->icono ?? $defaultIcono);
@endphp

<div class="mb-3">
    <label class="form-label custom-section-title" for="icono">
        <i class="fa-solid fa-icons"></i> Icono del programa
    </label>
    <input type="text" name="icono" id="icono" list="fontawesome-icons"
        class="form-control @error('icono') is-invalid @enderror" value="{{ $iconoActual }}"
        pattern="fa-[a-z0-9-]+" maxlength="50" required placeholder="fa-chart-line"
        title="Escribe una clase de icono Font Awesome, por ejemplo: fa-chart-line">
    <datalist id="fontawesome-icons">
        <option value="fa-industry">Industria</option>
        <option value="fa-chart-line">Avance</option>
        <option value="fa-briefcase">Trabajo</option>
        <option value="fa-star">Especial</option>
        <option value="fa-lightbulb">Innovación</option>
        <option value="fa-heart">Bienestar</option>
        <option value="fa-map-location-dot">Regional</option>
        <option value="fa-earth-americas">Territorio</option>
        <option value="fa-route">Ruta</option>
        <option value="fa-building">Institución</option>
        <option value="fa-landmark">Gobierno</option>
        <option value="fa-users">Personas</option>
        <option value="fa-layer-group">General</option>
    </datalist>
    @error('icono')
        <small class="invalid-feedback"><strong>{{ $message }}</strong></small>
    @enderror
    <small class="form-text text-muted">
        Guarda solo el nombre de la clase, por ejemplo <code>fa-chart-line</code>.
    </small>
</div>
