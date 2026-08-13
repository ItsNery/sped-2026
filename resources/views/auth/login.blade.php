<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>
        Inicio de Sesión | Sistema de Información para el Seguimiento a la Planeación y Evaluación del Desarrollo
        del Estado de Puebla
    </title>
    <link href="{{ asset('assets-administrador/css/estilos_login.css') }}" rel="stylesheet">
    <link href="{{ asset('assets-administrador/img/logo.ico') }}" rel="icon" />
    <!-- Styles -->
    <link href="{{ asset('fontAwesome/css/fontawesome.css') }}" rel="stylesheet">
    <link href="{{ asset('fontAwesome/css/brands.css') }}" rel="stylesheet">
    <link href="{{ asset('fontAwesome/css/solid.css') }}" rel="stylesheet">
    <link href="{{ asset('css/datatables.min.css') }}" rel="stylesheet">
</head>

<body>
    <div class="container mx-0 my-0">
        <div class="left">
            <div class="header">
                <img src="{{ asset('img/cadena-sped-blanco.png') }}" />
                <h2 class="animation a1">Bienvenido</h2>
                <h4 class="animation a2">Sistema de Información para el <br>Seguimiento a la Planeación y Evaluación
                    <br>del Desarrollo en el Estado de Puebla
                </h4>
            </div>
            <form method="POST" action="{{ route('login') }}" class="form" novalidate>
                @csrf
                <input type="text" id="email" name="email" value="{{ old('email') }}"
                    class="form-field animation a3" placeholder="Correo" required>
                @error('email')
                    <div class="alert-custom animation a3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12" y2="16"></line>
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
                <div class="password-field animation a4">
                    <input type="password" id="password" name="password" class="form-field"
                        placeholder="Contraseña" autocomplete="current-password" required>
                    <button type="button" id="toggle-password" class="password-toggle"
                        aria-label="Mostrar contraseña" aria-pressed="false">
                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
                @error('password')
                    <div class="alert-custom animation a3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12" y2="16"></line>
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
                <button type="submit" class="animation a6 cursor-pointer">
                    {{ __('Ingresar') }}
                </button>
            </form>
        </div>
        <div id="cambiado" class="right"></div>
    </div>
    <script type="text/javascript">
        document.querySelector("div#cambiado").style.backgroundImage =
            "url('{{ asset('assets-administrador/img/fondos/imagen') }}" + Math.floor(Math.random() * 26) + ".webp')";

        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('toggle-password');

        passwordToggle.addEventListener('click', function () {
            const isVisible = passwordInput.type === 'text';
            passwordInput.type = isVisible ? 'password' : 'text';
            passwordToggle.setAttribute('aria-pressed', String(!isVisible));
            passwordToggle.setAttribute('aria-label', isVisible ? 'Mostrar contraseña' : 'Ocultar contraseña');
            passwordToggle.querySelector('i').classList.toggle('fa-eye', isVisible);
            passwordToggle.querySelector('i').classList.toggle('fa-eye-slash', !isVisible);
            passwordInput.focus();
        });
    </script>
</body>

</html>
