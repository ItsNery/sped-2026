<div class="footer justify-content-center text-light">
    <div class="col-md-12">
        <div class="row ligas">
            <div class="col-md-3 d-flex justify-content-center px-5">
                <img class="logo-footer-admin" src="{{ asset('img/logo-footer.png') }}"
                    alt="Logo en color guinda y gris de la Secretaría de Planeación y Finanzas"
                    title="Secretaría de Planeación y Finanzas">
            </div>
            <div class="col-md-3">
                <h6 class="bold-text"><b>Sitios de Interés</b></h6>
                <ul class="list-unstyled">
                    <li>
                        <a href="https://puebla.gob.mx/" target="_blank" style="text-decoration:none; color:#fff;">
                            <i class="fa fa-angle-right mr-2"></i>
                            Gobierno del Estado de Puebla
                        </a>
                    </li>
                    <li>
                        <a href="http://spf.puebla.gob.mx/" target="_blank" style="text-decoration:none; color:#fff;">
                            <i class="fa fa-angle-right mr-2"></i>
                            Secretaría de Planeación, Finanzas y Administración
                        </a>
                    </li>
                    <li>
                        <a href="https://planeader.puebla.gob.mx/" target="_blank"
                            style="text-decoration:none; color:#fff;">
                            <i class="fa fa-angle-right mr-2"></i>
                            Portal Planeación
                        </a>
                    </li>
                    <li>
                        <a href="http://agenda2030.puebla.gob.mx/" target="_blank"
                            style="text-decoration:none; color:#fff;">
                            <i class="fa fa-angle-right mr-2"></i>
                            Agenda 2030
                        </a>
                    </li>
                    <li>
                        <a href="http://ceigep.puebla.gob.mx/" target="_blank"
                            style="text-decoration:none; color:#fff;">
                            <i class="fa fa-angle-right mr-2"></i>
                            CEIGEP
                        </a>
                    </li>
                </ul>

            </div>
            <div class="col-md-3">
                <h6 class="bold-text ">
                    <b>Ubicación</b>
                </h6>
                <ul class="list-unstyled">
                    <li><i class="fa fa-angle-right me-2"></i>11 Oriente #2224</li>
                    <li><i class="fa fa-angle-right me-2"></i>Col. Azcarate C.P. 72501, Puebla,Pue.</li>
                    <li><i class="fa fa-angle-right me-2"></i>Tel: (222) 2-29-70-00, Ext. 5051</li>
                </ul>
            </div>
            <div class="col-md-3 mapa-sitio">
                <h6 class="bold-text"><b>Mapa de sitio</b></h6>
                <div class="d-flex flex-column justify-content-start mapa">
                    <a class="text-white mb-2" href="#" data-bs-toggle="collapse" data-bs-target="#inicioSubMenu"
                        aria-expanded="false" aria-controls="inicioSubMenu" rel="noopener">
                        <i class="fa-solid fa-house mr-2"></i>
                        Inicio
                    </a>
                    <div class="collapse" id="inicioSubMenu">
                        <a class="text-white mb-2 a-sin-decoracion" href="{{ url('/') }}">
                            <i class="fa fa-angle-right mr-2"></i>
                            Inicio
                        </a>
                        <br>
                        <a class="text-white mb-2" href="{{ url('/informacion-general') }}">
                            <i class="fa fa-angle-right mr-2"></i>
                            Información General
                        </a>
                        <br>
                        <a class="text-white mb-2" href="{{ url('/normatividad') }}">
                            <i class="fa fa-angle-right mr-2"></i>
                            Normatividad
                        </a>
                        <br>
                        <a class="text-white mb-2" href="#" data-bs-toggle="collapse"
                            data-bs-target="#datosAbiertosSubMenu" aria-expanded="false"
                            aria-controls="datosAbiertosSubMenu" rel="noopener">
                            <i class="fa fa-angle-right mr-2"></i>
                            Datos Abiertos</a>
                        <div class="collapse" id="datosAbiertosSubMenu">
                            <a class="text-white ml-4 mb-2" href="{{ url('/datos-abiertos-ped') }}" rel="noopener">
                                <i class="fa fa-angle-right mx-2"></i>
                                Hist. Plan Estatal de Desarrollo 2019-2024
                            </a>
                            <br>
                            <a class="text-white ml-4 mb-2" href="{{ url('/datos-abiertos-mod-ped') }}" rel="noopener">
                                <i class="fa fa-angle-right mx-2"></i>
                                Hist. Mod. y Adec. PED 2019-2024
                            </a>
                            <br>

                            <a class="text-white ml-4 mb-2" href="{{ url('/datos-abiertos-mod-ped') }}" rel="noopener">
                                <i class="fa fa-angle-right mx-2"></i>
                                Hist. Indicadores Municipales 2019-2024
                            </a>
                        </div>
                    </div>
                    <a class="text-white mb-2" href="#" data-bs-toggle="collapse" data-bs-target="#planEstSubmenu"
                        aria-expanded="false" aria-controls="planEstSubmenu" rel="noopener">
                        <i class="fa-solid fa-book mr-2"></i>
                        Plan Estatal de Desarrollo
                    </a>
                    <div class="collapse" id="planEstSubmenu">
                        <a class="text-white mb-2 a-sin-decoracion" href="{{ url('/') }}">
                            <i class="fa fa-angle-right mr-2"></i>
                            Visión general
                        </a>
                        <br>
                        <a class="text-white mb-2" href="{{ url('/ped/eje-1') }}">
                            <i class="fa fa-angle-right mr-2"></i>
                            Eje 1 - Humanismo con Bienestar
                        </a>
                        <br>
                        <a class="text-white mb-2" href="{{ url('/ped/eje-2') }}">
                            <i class="fa fa-angle-right mr-2"></i>
                            Eje 2 - Prosperidad y Estabilidad
                            Económica
                        </a>
                        <br>
                        <a class="text-white mb-2" href="{{ url('/ped/eje-3') }}">
                            <i class="fa fa-angle-right mr-2"></i>
                            Eje 3 - Estado de Derecho, Seguridad y
                            Justicia
                        </a>
                        <br>
                        <a class="text-white mb-2" href="{{ url('/ped/eje-4') }}">
                            <i class="fa fa-angle-right mr-2"></i>
                            Eje 4 - Desarrollo Urbano y Crecimiento
                            Sostenible
                        </a>
                        <br>
                        <a class="text-white mb-2" href="{{ url('/ped/eje-5') }}">
                            <i class="fa fa-angle-right mr-2"></i>
                            Eje 5 - Gobierno Transformador y de
                            Resultados
                        </a>
                        <br>
                        <a class="text-white mb-2" href="{{ url('/ped/eje-6') }}">
                            <i class="fa fa-angle-right mr-2"></i>
                            Eje Transversal - Por Amor a Puebla
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    &nbsp;

    <div class="text-center">
        @php
            use Carbon\Carbon;
            $currentYear = Carbon::now()->locale('es')->isoFormat('YYYY');
        @endphp
        <p>
            &copy; Secretaría de Planeación, Finanzas y Administración -
            <a href="https://planeacion.puebla.gob.mx/" rel="noopener" title="SS Planeación" target="_blank">
                Subsecretaría de Planeación
            </a>
            {{ ucfirst($currentYear) }}
        </p>
    </div>

</div>
