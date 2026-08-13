# Sistema SPED - Sistema de Información para el Seguimiento  a la Planeación y Evaluación del Desarrollo

<div align="center">
  <img src="https://sped.puebla.gob.mx/img/logos_sped.png" alt="SEI Logo" width="200"/>
</div>

Sistema web para el seguimiento, control y evaluación de los indicadores del Plan Estatal de Desarrollo y sus Programas Derivados de la Subsecretaría de Planeación, desarrollado con Laravel 12.

## 📋 Descripción

El **Sistema SPED** es una plataforma web integral diseñada para la gestión, seguimiento y publicación de indicadores de desempeño y resultados del gobierno. Permite visualizar el avance de metas mediante tableros de control (dashboards), generar fichas técnicas detalladas y proveer datos abiertos a la ciudadanía, facilitando la toma de decisiones basada en evidencia.

## ✨ Características Principales

### Gestión de Indicadores

- **Registro y Seguimiento**: Control del ciclo de vida de los indicadores del Plan Estatal de Desarrollo (PED).
- **Programas Derivados**: Gestión de programas Sectoriales, Regionales, Institucionales y Especiales.
- **Indicadores Municipales**: Seguimiento específico para municipios con convenio.
- **Importación Masiva**: Carga y actualización de indicadores mediante archivos Excel.
- **Fichas Técnicas**: Generación detallada del estado e historial de cada indicador, sus líneas base y resultados anuales.

### Tableros de Control y Visualización

- **Dashboards Dinámicos**: Vista general del avance y cumplimiento de metas.
- **Semaforización**: Sistema visual de alertas (Azul, Verde, Amarillo, Rojo, Gris) según el grado de cumplimiento.
- **Gráficos Interactivos**: Visualización de avances con ApexCharts y medidores interactivos.

### Transparencia y Datos Abiertos

- **Portal Público**: Acceso ciudadano a la información general, normatividad y tableros resumen.
- **Descarga de Datos**: Exportación de datos abiertos en formatos JSON, CSV y XLSX.

### Gestión de Usuarios y Permisos

- **Roles Dinámicos**: Sistema de autorización basado en roles y permisos (Administrador, Enlace, etc.).
- **Gestión de Instituciones**: Organización de usuarios por dependencias o instituciones.
- **Auditoría**: Registro de logs y cambios en el sistema.

## 🛠️ Stack Tecnológico

- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Base de Datos**: MySQL
- **Autenticación y UI**: Laravel Jetstream + Livewire 3.0 + Sanctum
- **Roles y Permisos**: Spatie Laravel Permission
- **Frontend**:
    - Bootstrap 5.1
    - TailwindCSS 3.1
    - Alpine.js 3.2
- **Tablas de Datos**: Yajra DataTables
- **Excel/Exportación**: PhpSpreadsheet
- **Build Tools**: Laravel Mix / Webpack

## 📦 Instalación

### Requisitos Previos

- PHP >= 8.2
- Composer
- MySQL
- Node.js y NPM

### Pasos de Instalación

1. **Clonar el repositorio**

    ```bash
    git clone https://github.com/tu-organizacion/sped-final.git
    cd sped-final
    ```

2. **Instalar dependencias de PHP**

    ```bash
    composer install
    ```

3. **Instalar dependencias de Node.js**

    ```bash
    npm install
    ```

4. **Configurar el archivo de entorno**

    ```bash
    cp .env.example .env
    ```

    Editar `.env` con tus configuraciones de base de datos y aplicación:

    ```env
    APP_NAME="Sistema SPED"
    APP_URL=http://localhost:8000

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=db_sped
    DB_USERNAME=root
    DB_PASSWORD=
    ```

5. **Generar la clave de aplicación**

    ```bash
    php artisan key:generate
    ```

6. **Ejecutar las migraciones**

    ```bash
    php artisan migrate
    ```

7. **Ejecutar los seeders (Catálogos iniciales)**

    ```bash
    php artisan db:seed
    ```

8. **Compilar assets**

    ```bash
    npm run dev
    # o para producción: npm run build
    ```

9. **Crear el enlace simbólico de storage**

    ```bash
    php artisan storage:link
    ```

10. **Iniciar el servidor de desarrollo**
    ```bash
    php artisan serve
    ```

La aplicación estará disponible en `http://localhost:8000`.
_(Nota: Para cambios en tiempo real en frontend, también puedes usar `npm run watch`)_

## 📁 Estructura del Proyecto

```
sped-final/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── DashboardController.php      # Tableros principales
│   │       ├── IndicadorController.php      # Lógica central del PED
│   │       ├── DatosAbiertosController.php  # Exportación JSON/CSV/XLSX
│   │       └── ...
│   ├── Models/                              # Modelos Eloquent
│   ├── Providers/                           # Proveedores de servicios
│   └── Console/                             # Comandos de consola
├── database/
│   ├── migrations/                          # Estructura de BD
│   └── seeders/                             # Datos semilla (Catálogos)
├── public/
│   └── storage/                             # Archivos públicos referenciados
├── resources/
│   ├── views/
│   │   ├── auth/                            # Vistas Jetstream/Login
│   │   ├── panel-indicadores/               # Interfaces de administración
│   │   ├── livewire/                        # Componentes Livewire
│   │   └── layouts/                         # Plantillas maestras base
│   ├── css/                                 # Estilos Tailwind/Bootstrap/Sass
│   └── js/                                  # Scripts Alpine/Vanilla
└── routes/
    ├── web.php                              # Rutas web públicas y autenticadas
    └── api.php                              # Rutas de API
```

## 🔐 Usuarios y Permisos

El sistema utiliza **Spatie Laravel Permission** acoplado a la administración interna para un control de acceso granular.

- **Administrador**: Control total sobre catálogos, usuarios, y flujos de captura de indicadores.
- **Enlace Institucional**: Captura de resultados anuales y manejo de indicadores propios de su dependencia.
- **Público**: Acceso de solo lectura a los portales de Datos Abiertos, Fichas Técnicas publicadas y Avance General.

## 🌐 Módulos y Rutas Principales

### Rutas Públicas e Informativas

- `/` - Landing Page / Carrusel Informativo
- `/ped` - Avance General del Plan Estatal de Desarrollo
- `/informacion-general` - Contexto y misión
- `/normatividad` - Marco legal

### Transparencia y Datos Abiertos

- `/datos-abiertos-ped` - Portal de descarga de datos del PED
- `/datos-abiertos-mun` - Datos municipales

### Fichas Técnicas

- `/ficha-tecnica/{indicador}` - Vista pública de las fichas de indicadores.

### Panel de Administración (Protegido)

- `/dashboard` - Tablero de estado general interno.
- `/panel-indicadores` - CRUD y gestión integral de los indicadores.
- `/panel-usuarios` - Gestión de personal y accesos.
- `/panel-cat-planes` y relacionados - Mantenimiento de catálogos base (Clasificaciones, Ejes, Programas).

## 🚀 Despliegue en Producción

1. **Ajustar variables de entorno**

    ```bash
    APP_ENV=production
    APP_DEBUG=false
    ```

2. **Caché y Optimización**

    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    composer install --optimize-autoloader --no-dev
    ```

3. **Compilación de Producción**

    ```bash
    npm run prod
    ```

4. **Permisos de Archivo**
   Asegúrate de conceder permisos de escritura (`775` o `www-data`) al servidor web en las carpetas `storage/` y `bootstrap/cache/`.

## 🤝 Contribución

Este es un proyecto institucional del Gobierno del Estado de Puebla. Para contribuir o reportar incidencias, contacta al equipo de desarrollo del SEI.

## 📄 Licencia

Este proyecto es de uso interno y confidencial del Gobierno del Estado de Puebla.

## 📧 Contacto

**Sistema Estatal de Información (SEI)**  
Subsecretaría de Planeación  
Secretaría de Planeación, Finanzas y Administración  
Gobierno del Estado de Puebla

Para soporte o consultas sobre el sistema, contacta al equipo de desarrollo.

---

<div align="center">
  Desarrollado con ❤️ por el equipo de SEI
</div>
