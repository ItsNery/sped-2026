# Sidebar del administrador

## Alcance

El layout administrativo del SPED incorpora la funcionalidad visual y de interacción del sidebar de `fichas_municipales`, adaptada a las rutas, permisos y opciones existentes en este proyecto.

No se agregaron módulos nuevos ni se eliminaron destinos administrativos existentes.

## Comportamiento

- Sidebar fijo de 250 px en escritorio.
- Estado colapsado de 60 px con solo iconos.
- Botón superior para expandir o contraer el sidebar.
- Estado de escritorio persistido en `localStorage` con la clave `spedAdminSidebarCollapsed`.
- Sidebar deslizable en pantallas menores a 992 px.
- Overlay y botón de cierre para navegación móvil.
- Indicador visual de la ruta activa.
- Elementos de navegación visibles según los permisos y roles existentes.
- Usuarios sin permiso de panel general pero con acceso a indicadores entran directamente a `panel-indicadores.index`.

## Navegación adaptada

- Inicio y dashboard ejecutivo.
- Gestión de indicadores y municipios.
- Administración de usuarios, roles, bitácora y accesos.
- Manuales existentes.
- Catálogos existentes del SPED.
- Cierre de sesión.

El destino `/dashboard` conserva el comportamiento para administradores y usuarios con `ver-panel-avance-general`. Para un usuario `Enlace`, funciona como puerta de entrada compatible y redirige a sus indicadores.

## Archivos

- `resources/views/layouts/admin-navigation.blade.php`: estructura y navegación del sidebar.
- `resources/views/layouts/app.blade.php`: integración del sidebar, barra superior y comportamiento responsive.
- `public/css/estilos-admin.css`: estilos del sidebar, estados colapsado/móvil y barra superior.

## Referencia

La implementación visual y de interacción se basó en:

- `C:\laragon\www\fichas_municipales\resources\views\layouts\admin-navigation.blade.php`
- `C:\laragon\www\fichas_municipales\resources\views\layouts\admin.blade.php`
- `C:\laragon\www\fichas_municipales\public\css\custom.css`

La referencia se utilizó únicamente para portar el comportamiento del sidebar. Las rutas, nombres, permisos y contenidos corresponden al SPED.

## Verificación

- Compilación Blade del layout.
- Render del dashboard administrativo.
- Rutas existentes conservadas.
- Sin cambios de base de datos.
