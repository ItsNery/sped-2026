<div class="footer justify-content-center text-light">
    <div class="col-md-12">
        <div class="row ligas">
            <div class="col-md-3 escudo">
                <div class="container">
                    <img class="w-100 px-3" src="{{ asset('img/logo-footer.png') }}"
                        alt="Logo en color guinda y gris de la Secretaría de Planeación y Finanzas"
                        title="Secretaría de Planeación y Finanzas">
                </div>
            </div>
            <div class="col-md-3 sitios">
                <h6 class="bold-text"><b>Sitios de Interés</b></h6>
                <ul class="list-unstyled">
                    <li>
                        <a href="https://puebla.gob.mx/" target="_blank">
                            <i class="fa fa-angle-right mr-2"></i>
                            Gobierno del Estado de Puebla
                        </a>
                    </li>
                    <li>
                        <a href="http://spf.puebla.gob.mx/" target="_blank">
                            <i class="fa fa-angle-right mr-2"></i>
                            Secretaría de Planeación, Finanzas y Administración
                        </a>
                    </li>
                    <li>
                        <a href="https://planeader.puebla.gob.mx/" target="_blank">
                            <i class="fa fa-angle-right mr-2"></i>
                            Portal Planeación
                        </a>
                    </li>
                    <li>
                        <a href="http://agenda2030.puebla.gob.mx/" target="_blank">
                            <i class="fa fa-angle-right mr-2"></i>
                            Agenda 2030
                        </a>
                    </li>
                    <li>
                        <a href="http://ceigep.puebla.gob.mx/" target="_blank">
                            <i class="fa fa-angle-right mr-2"></i>
                            CEIGEP
                        </a>
                    </li>
                </ul>

            </div>
            <div class="col-md-3 ubicacion">
                <h6 class="bold-text ">
                    <b>Ubicación</b>
                </h6>
                <ul class="list-unstyled">
                    <li><i class="fa fa-angle-right me-2"></i>11 Oriente #2224</li>
                    <li><i class="fa fa-angle-right me-2"></i>Col. Azcarate C.P. 72501, Puebla,Pue.</li>
                    <li><i class="fa fa-angle-right me-2"></i>Tel: (222) 2-29-70-00, Ext. 5051</li>
                </ul>
                {{-- <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1885.8389960193363!2d-98.18782655237631!3d19.033905851556835!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x85cfc7113bf027f9%3A0x5019db208e053e0!2sSecretar%C3%ADa%20de%20Planeaci%C3%B3n%20y%20Finanzas!5e0!3m2!1ses-419!2smx!4v1624391021741!5m2!1ses-419!2smx"
                    width="300" height="150" style="border:0;" allowfullscreen="" loading="lazy"></iframe> --}}
            </div>
            <div class="col-md-3 mapa-sitio">
                <h6 class="bold-text"><b>Mapa de sitio</b></h6>
                <div class="d-flex flex-column justify-content-start mapa">

                    {{-- INICIO Y DATOS GENERALES --}}
                    <a class="text-white mb-2" href="#" data-bs-toggle="collapse" data-bs-target="#inicioSubMenu"
                        aria-expanded="false" aria-controls="inicioSubMenu" rel="noopener">
                        <i class="fa-solid fa-house mr-2"></i>
                        Datos generales
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
                                Plan Estatal de Desarrollo 2024-2030
                            </a>
                            <br>
                            <a class="text-white ml-4 mb-2" href="{{ url('/datos-abiertos-hist-ped') }}" rel="noopener">
                                <i class="fa fa-angle-right mx-2"></i>
                                Hist. Planes Estatales de Desarrollo
                            </a>
                            <br>
                            <a class="text-white ml-4 mb-2" href="{{ url('/datos-abiertos-hist-mun') }}" rel="noopener">
                                <i class="fa fa-angle-right mx-2"></i>
                                Hist. Indicadores Municipales
                            </a>
                        </div>
                    </div>

                    {{-- PLAN ESTATAL DE DESARROLLO --}}
                    <a class="text-white mb-2" href="#" data-bs-toggle="collapse" data-bs-target="#planEstSubmenu"
                        aria-expanded="false" aria-controls="planEstSubmenu" rel="noopener">
                        <i class="fa-solid fa-book mr-2"></i>
                        Plan Estatal de Desarrollo
                    </a>
                    <div class="collapse" id="planEstSubmenu">
                        <a class="text-white mb-2 a-sin-decoracion" href="{{ url('/ped') }}">
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
                            Eje 2 - Prosperidad y Estabilidad Económica
                        </a>
                        <br>
                        <a class="text-white mb-2" href="{{ url('/ped/eje-3') }}">
                            <i class="fa fa-angle-right mr-2"></i>
                            Eje 3 - Estado de Derecho, Seguridad y Justicia
                        </a>
                        <br>
                        <a class="text-white mb-2" href="{{ url('/ped/eje-4') }}">
                            <i class="fa fa-angle-right mr-2"></i>
                            Eje 4 - Desarrollo Urbano y Crecimiento Sostenible
                        </a>
                        <br>
                        <a class="text-white mb-2" href="{{ url('/ped/eje-5') }}">
                            <i class="fa fa-angle-right mr-2"></i>
                            Eje 5 - Gobierno Transformador y de Resultados
                        </a>
                        <br>
                        <a class="text-white mb-2" href="{{ url('/ped/eje-6') }}">
                            <i class="fa fa-angle-right mr-2"></i>
                            Eje Transversal - Por Amor a Puebla
                        </a>
                    </div>

                    {{-- PROGRAMAS DERIVADOS --}}
                    <a class="text-white mb-2" href="#" data-bs-toggle="collapse"
                        data-bs-target="#progDeriSubMenu" aria-expanded="false" aria-controls="progDeriSubMenu"
                        rel="noopener">
                        <i class="fa-solid fa-book-open mr-2"></i>
                        Programas Derivados
                    </a>
                    <div class="collapse" id="progDeriSubMenu">
                        <a class="text-white ml-4 mb-2" href="{{ url('/ped-programas/sectoriales') }}" rel="noopener">
                            <i class="fa fa-angle-right mx-2"></i>
                            Programas Sectoriales
                        </a>
                        <br>
                        <a class="text-white ml-4 mb-2" href="{{ url('/ped-programas/especiales') }}" rel="noopener">
                            <i class="fa fa-angle-right mx-2"></i>
                            Programas Especiales
                        </a>
                        <br>
                        <a class="text-white ml-4 mb-2" href="{{ url('/ped-programas/regionales') }}" rel="noopener">
                            <i class="fa fa-angle-right mx-2"></i>
                            Programas Regionales
                        </a>
                        <br>
                        <a class="text-white ml-4 mb-2" href="{{ url('/ped-programas/institucionales') }}" rel="noopener">
                            <i class="fa fa-angle-right mx-2"></i>
                            Programas Institucionales
                        </a>
                    </div>

                    {{-- PLANES MUNICIPALES --}}
                    <a class="text-white mb-2 mt-2" href="{{ url('/pm') }}">
                        <i class="fa fa-book-bookmark mr-2" rel="noopener"></i>
                        Planes Municipales de Desarrollo
                    </a>

                    {{-- UTILIDADES (Buscador y Login) --}}
                    <a class="text-white mb-2" href="#" onclick="openSearchModal();">
                        <i class="fas fa-search mr-2" aria-hidden="true"></i> Buscar
                    </a>
                    <a class="text-white mb-2" href="{{ route('login') }}" title="Iniciar sesión" rel="noopener" target="_self">
                        <i class="fas fa-right-to-bracket mr-2" aria-hidden="true"></i> Entrar
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="container copy">
        @php
        use Carbon\Carbon;
        $currentYear = Carbon::now()->locale('es')->isoFormat('YYYY');
        @endphp
        <p>
            &copy; Secretaría de Planeación, Finanzas y Administración -
            <a href="https://planeacion.puebla.gob.mx/" rel="noopener" title="SS Planeación" target="_blank">
                Subsecretaría de Planeación - {{ ucfirst($currentYear) }}
            </a>
        </p>
        <p>
            Salvo que se indique lo contrario, el contenido está licenciado bajo
            <a href="https://creativecommons.org/licenses/by/4.0/deed.es" rel="license noopener noreferrer"
                target="_blank" title="Creative Commons Atribución 4.0 Internacional">
                CC BY 4.0
            </a>
        </p>
    </div>
</div>
<div class="textura-footer">
    <img src="{{ asset('img/footer.svg') }}" alt="" class="w-100 px-0 mx-0">
</div>