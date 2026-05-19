# 📊 SPED - Sistema de Planeación Estatal de Desarrollo

Un sistema integral de gestión, seguimiento, validación y reporte de indicadores de rendimiento para la administración pública. SPED permite a las instituciones gubernamentales capturar, validar y visualizar indicadores alineados con los Objetivos de Desarrollo Sostenible (ODS) y los planes de desarrollo estatales.

## 🎯 Descripción General

SPED es una plataforma web empresarial construida sobre Laravel 12 que centraliza la gestión de indicadores de desempeño institucional. Proporciona herramientas avanzadas para:

- **Gestión Integral de Indicadores**: Crear, editar, validar y eliminar indicadores de rendimiento
- **Seguimiento de Datos Anuales**: Mantener históricos de datos validados con evidencia
- **Dashboard Inteligente**: Visualización en tiempo real mediante gráficos interactivos (ApexCharts)
- **API de Datos Abiertos**: Exposición pública de indicadores para integración externa
- **Sistema de Validación Multiuso**: Flujo de trabajo para validación de datos por roles
- **Control de Acceso Granular**: Gestión de permisos basada en roles (spatie/laravel-permission)
- **Gestión de Municipios y Convenios**: Asociación de indicadores con territorios específicos
- **Informes y Exportación**: Generación de reportes en Excel (PHPOffice)

## 🛠️ Stack Tecnológico

### Backend
- **Framework**: Laravel 12 (PHP 8.2+)
- **ORM**: Eloquent
- **Autenticación**: Laravel Jetstream + Sanctum
- **Autorización**: Spatie Laravel-Permission (RBAC)
- **Tablas de Datos**: Yajra Laravel-DataTables
- **Excel**: PHPOffice PHPSpreadsheet

### Frontend
- **Build Tool**: Laravel Mix / Webpack 5
- **Estilos**: Tailwind CSS 3.1 + Bootstrap 5.1
- **JavaScript**: Alpine.js 3.2 + Lodash
- **Componentes**: Livewire 3 (componentes reactivos del lado del servidor)
- **Gráficos**: ApexCharts.js
- **Animaciones**: AOS (Animate On Scroll)
- **Carrusel**: Swiper

### Base de Datos
- MySQL/MariaDB
- Migraciones Laravel con seeders

## 📋 Requisitos

- **PHP**: 8.2+
- **Composer**: Última versión
- **Node.js**: 14+ y npm/yarn
- **MySQL/MariaDB**: 5.7+
- **Git**

## 🚀 Instalación y Configuración

### 1. Clonar el Repositorio
```bash
git clone https://github.com/ItsNery/sped-2026.git
cd sped
```

### 2. Instalar Dependencias PHP
```bash
composer install
```

### 3. Instalar Dependencias JavaScript
```bash
npm install
```

### 4. Configurar Variables de Entorno
```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env` con tus credenciales:
```env
APP_NAME="SPED"
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sped
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Crear Base de Datos
```bash
mysql -u root -p
CREATE DATABASE sped CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Ejecutar Migraciones
```bash
php artisan migrate
php artisan db:seed
```

### 7. Generar Clave de Aplicación
```bash
php artisan key:generate
```

### 8. Compilar Assets
```bash
npm run development
# o para producción:
npm run production
```

### 9. Iniciar Servidor Local
```bash
php artisan serve
# Disponible en http://localhost:8000
```

### 10. Compilar Assets en Tiempo Real (Opcional)
En otra terminal:
```bash
npm run watch
```

## 📁 Estructura del Proyecto

```
sped/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   └── IndicadorApiController.php    # API pública de indicadores
│   │   │   ├── DashboardController.php           # Lógica del dashboard
│   │   │   ├── IndicadorController.php           # CRUD de indicadores
│   │   │   ├── UserController.php                # Gestión de usuarios
│   │   │   ├── IndicadorMunicipalController.php  # Indicadores por municipio
│   │   │   ├── InstitucionController.php         # Gestión de instituciones
│   │   │   └── ... otros controladores
│   │   ├── Middleware/                           # Middleware personalizado
│   │   └── Kernel.php                            # Configuración HTTP
│   ├── Models/
│   │   ├── Indicador.php                         # Modelo principal
│   │   ├── DatoAnualIndicador.php               # Histórico anual
│   │   ├── User.php                              # Usuario
│   │   ├── Institucion.php                       # Institución
│   │   ├── CatMunicipio.php                      # Catálogo de municipios
│   │   ├── Ods.php                               # Objetivos de Desarrollo Sostenible
│   │   └── ... otros modelos
│   ├── Observers/                                # Observadores de modelos
│   ├── Listeners/                                # Event listeners (ej: logs de cambios)
│   ├── Livewire/                                 # Componentes Livewire
│   ├── Actions/                                  # Acciones de Jetstream
│   ├── Providers/                                # Service Providers
│   ├── Helpers.php                               # Funciones auxiliares
│   └── Exceptions/                               # Excepciones personalizadas
├── routes/
│   ├── web.php                                   # Rutas web
│   ├── api.php                                   # Rutas API REST
│   └── channels.php                              # Broadcasting channels
├── database/
│   ├── migrations/                               # Migraciones de base de datos
│   ├── seeders/                                  # Seeders de datos iniciales
│   └── factories/                                # Factories para testing
├── resources/
│   ├── views/
│   │   ├── layouts/                              # Layouts base
│   │   ├── dashboard.blade.php                   # Vista del dashboard
│   │   ├── publico/                              # Vistas públicas
│   │   ├── indicadores/                          # Vistas de indicadores
│   │   ├── usuarios/                             # Vistas de gestión de usuarios
│   │   └── ... otras vistas
│   ├── js/                                       # JavaScript
│   ├── sass/                                     # Estilos SASS
│   └── lang/                                     # Traducciones multiidioma
├── config/
│   ├── app.php                                   # Configuración app
│   ├── database.php                              # Configuración BD
│   ├── auth.php                                  # Configuración autenticación
│   ├── permission.php                            # Configuración de permisos
│   └── ... otras configuraciones
├── public/
│   ├── index.php                                 # Punto de entrada
│   ├── css/                                      # CSS compilado
│   ├── js/                                       # JavaScript compilado
│   ├── images/                                   # Imágenes
│   └── assets-administrador/                     # Assets administrativos
├── storage/                                      # Almacenamiento (logs, archivos)
├── bootstrap/                                    # Bootstrapping de Laravel
├── tests/                                        # Tests unitarios e integración
├── documentation/                                # Documentación del proyecto
├── .env.example                                  # Ejemplo de variables de entorno
├── composer.json                                 # Dependencias PHP
├── package.json                                  # Dependencias Node.js
├── webpack.mix.js                                # Configuración Laravel Mix
├── vite.config.js                                # Configuración Vite
└── artisan                                       # CLI de Laravel
```

## 🔑 Características Principales

### 1. **Gestión de Indicadores**
- CRUD completo con interfaz avanzada
- Vinculación con ODS
- Asociación con programas derivados
- Gestión de tendencias (Mayor es mejor / Menor es mejor)
- Cálculo automático de semaforización
- Histórico de datos anuales validados
- Sistema de evidencia por año

### 2. **Dashboard Interactivo**
- **KPIs**: Indicadores validados, incompletos, actividad reciente
- **Gráficos Interactivos** (ApexCharts):
  - Semaforización (distribución por estado)
  - Avance por enlace/responsable
  - Indicadores por año
  - Periodicidad de medición
  - Tendencias temporales
- Datos en tiempo real
- Drill-down interactivo (click en gráficos para filtrar)

### 3. **Sistema de Validación**
- Flujo de trabajo: Captura → Modificación → Validación
- Historial de cambios automático
- Estados de validación
- Control de acceso por rol
- Notificaciones de validación

### 4. **Gestión de Usuarios Flexible**
- Usuario de Institución (asociado a una institución)
- Usuario de Municipio (asociado a un municipio)
- Usuario Enlace (múltiples instituciones)
- Creación dinámico de instituciones
- Asignación granular de permisos

### 5. **API REST Pública**
- Endpoints para consulta de indicadores
- Filtros avanzados (institución, ODS, programa, búsqueda)
- Paginación configurable
- Respuestas JSON estructuradas
- Semaforización en tiempo real

**Endpoints disponibles:**
```
GET  /api/indicadores
GET  /api/indicadores/{id_or_slug}

Parámetros de filtro:
- institucion_id: Filtrar por institución
- ods_id: Filtrar por ODS
- programa_derivado: Filtrar por programa
- buscar: Búsqueda en nombre/descripción
- per_page: Indicadores por página (1-100, default 15)
```

### 6. **Gestión de Municipios y Convenios**
- Catálogo de municipios
- Convenios municipales
- Indicadores por municipio
- Seguimiento territorial

### 7. **Informes y Exportación**
- Exportación a Excel
- Generación de reportes
- Subida masiva de datos desde Excel
- Validación de integridad de datos

### 8. **Sistema de Logs de Cambios**
- Auditoría completa de modificaciones
- Seguimiento de cierre de sesión
- Intentos de login fallidos
- Historial de acciones por usuario

## 🗄️ Modelos Principales

### Indicador
```php
- id: int
- nombre: string
- slug: string
- descripcion: text
- programa_derivado: string
- programa: string
- tematica: string
- linea_base: float
- meta_2024: float
- unidad_medida: string
- periodicidad: enum
- tendencia: enum (mayor/menor)
- indicador_validado: boolean
- id_institucion: int (FK)
- fecha_actualizacion: timestamp

Relaciones:
- hasMany('DatoAnualIndicador')
- belongsTo('Institucion')
- belongsToMany('Ods')
- belongsTo('User') - responsable

Métodos:
- calcularSemaforizacion(validado)
- obtenerAvance()
```

### DatoAnualIndicador
```php
- id: int
- indicador_id: int (FK)
- anio: int
- dato: float
- validado: boolean
- modificado: boolean
- archivo_evidencia: string

Relaciones:
- belongsTo('Indicador')
```

### User (Extendido)
```php
- id: int
- name: string
- email: string
- password: string
- tipo_usuario: enum (Institución/Municipio)
- id_institucion: int (FK) - nullable
- id_municipio: int (FK) - nullable

Relaciones:
- belongsTo('Institucion') - nullable
- belongsTo('CatMunicipio') - nullable
- belongsToMany('Institucion') - para Enlace
- hasMany('Indicador') - como responsable
- hasMany('DatoAnualIndicador')
```

### Institucion
```php
- id: int
- nombre: string
- titular: string
- contacto: string - nullable

Relaciones:
- hasMany('Indicador')
- hasMany('User')
- belongsToMany('User') - Enlace
```

## 🔐 Sistema de Permisos y Roles

El proyecto utiliza **spatie/laravel-permission** para RBAC granular.

### Roles Disponibles
- **Administrador**: Acceso completo al sistema
- **Enlace**: Responsable de validación de indicadores de múltiples instituciones
- **Institución**: Usuario de una institución específica
- **Municipio**: Usuario de un municipio específico

### Permisos Principales
- `ver-indicador` / `crear-indicador` / `editar-indicador` / `borrar-indicador`
- `validar-indicador` / `editar-indicador-anual`
- `subida-masiva-indicador`
- `ver-usuario` / `crear-usuario` / `editar-usuario` / `borrar-usuario` / `des-activar-usuario`
- `ver-dashboard`
- Y más...

## 🌐 Rutas Principales

### Web
```
GET    /                                    # Página de inicio
GET    /informacion-general                 # Información general
GET    /normatividad                        # Marco normativo
GET    /datos-abiertos-ped                  # Portal de datos abiertos
GET    /dashboard                           # Dashboard principal (autenticado)

Panel Administrativo:
GET    /admin/indicadores                   # Listado de indicadores
GET    /admin/indicadores/crear             # Crear indicador
GET    /admin/indicadores/{id}/editar       # Editar indicador
GET    /admin/usuarios                      # Gestión de usuarios
GET    /admin/instituciones                 # Gestión de instituciones
GET    /admin/municipios                    # Gestión de municipios
GET    /admin/convenios                     # Gestión de convenios
```

### API REST
```
GET    /api/indicadores                     # Listar (con filtros)
GET    /api/indicadores/{id_or_slug}        # Detalle
```

## 💾 Comandos Artisan Útiles

```bash
# Migrar la base de datos
php artisan migrate

# Ejecutar seeders
php artisan db:seed

# Crear caché de configuración
php artisan config:cache

# Limpiar caché
php artisan cache:clear

# Generar documentación
php artisan ide-helper:generate

# Ejecutar tests
php artisan test

# Tinker (REPL interactivo)
php artisan tinker
```

## 📦 Scripts npm

```bash
# Desarrollo (watch mode)
npm run watch

# Build para producción
npm run production

# Build con optimización
npm run build

# Desarrollo con hot reload
npm run hot
```

## 🧪 Testing

El proyecto incluye soporte para testing con PHPUnit:

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests específicos
php artisan test tests/Feature/IndicadorTest.php
```

## 📚 Documentación Adicional

En la carpeta `documentation/` se encuentra documentación detallada de cada módulo:

- `dashboard.md` - Guía del Dashboard y sus gráficos
- `indicadores.md` - Gestión completa de indicadores
- `usuarios.md` - Gestión de usuarios y roles
- `roles.md` - Sistema de permisos
- `indicadormunicipal.md` - Indicadores por municipio
- `municipioconvenio.md` - Gestión de convenios
- `logscambios.md` - Sistema de auditoría
- `carruselindicadores.md` - Carrusel de indicadores
- `sliderinicio.md` - Configuración del slider

## 🤝 Contribución

Para contribuir al proyecto:

1. Crear una rama con tu feature: `git checkout -b feature/mi-feature`
2. Commitear los cambios: `git commit -am 'Añadir mi feature'`
3. Hacer push a la rama: `git push origin feature/mi-feature`
4. Abrir un Pull Request

## 📄 Licencia

MIT License - Ver LICENSE para más detalles.

## 👥 Autores

- **Nery Pozos** - Desarrollo Principal
- Equipo de Desarrollo

## 📞 Soporte

Para reportar bugs o solicitar features, crear un issue en el repositorio de GitHub: https://github.com/ItsNery/sped-2026/issues

## 🔗 Enlaces Útiles

- [Documentación de Laravel](https://laravel.com/docs)
- [Spatie Laravel-Permission](https://spatie.be/docs/laravel-permission/v6/introduction)
- [Livewire](https://livewire.laravel.com/)
- [ApexCharts](https://apexcharts.com/)
- [Tailwind CSS](https://tailwindcss.com/)

---

**Última actualización**: 19 de Mayo de 2026

**Rama**: main (feature/api-indicadores integrada)

**Status**: ✅ Producción - API pública disponible
