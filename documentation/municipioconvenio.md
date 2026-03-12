# Documentación: Módulo de Municipios con Convenio

## 📝 Resumen

Este módulo gestiona una lista curada de municipios que han firmado un convenio. No administra el catálogo completo de municipios, sino la información específica del acuerdo para cada uno. Tiene un doble propósito:
1.  **Panel de Administración**: Una interfaz CRUD para que los administradores puedan añadir, editar y eliminar municipios de esta lista, junto con sus archivos asociados.
2.  **Sitio Público**: Endpoints para mostrar al público la lista de municipios con convenio y una página de detalle para cada uno con sus indicadores públicos.

## ⭐ Arquitectura y Características Clave

### 1. Interfaz Unificada (Modal)

* Todas las operaciones del panel de administración (crear y editar) se realizan a través de una **única ventana modal de Bootstrap**, lo que agiliza el flujo de trabajo del administrador.
* JavaScript se encarga de configurar dinámicamente el modal para la acción de "crear" o "editar", cambiando el título y la URL de envío del formulario.

### 2. Gestión de Múltiples Archivos

* Cada registro de "Municipio con Convenio" gestiona tres archivos distintos:
    1.  `convenio`: Un documento PDF.
    2.  `icono`: Una imagen representativa.
    3.  `banner`: Una imagen de mayor tamaño para encabezados.
* El controlador maneja de forma segura el ciclo de vida de estos tres archivos: subida en la creación, reemplazo opcional en la actualización (eliminando el archivo antiguo) y eliminación completa al borrar el registro.

### 3. Visualización de Datos Relacionados en Tabla

* Una característica destacada de la interfaz de listado (`index.blade.php`) es el uso de la etiqueta HTML `<details>`.
* Dentro de cada fila de la tabla principal, se muestra una lista desplegable con los **indicadores públicos** asociados a ese municipio. Esto permite a los administradores obtener una vista rápida de los indicadores de cada municipio sin tener que navegar a otra página.

---

## ⚙️ Flujo de Funcionalidad (Panel de Administración)

* **Listado (`index`)**: Muestra una tabla interactiva (`DataTables.js`) con los municipios que tienen un convenio. Incluye el nombre, objetivo, un enlace al PDF del convenio, el ícono y la lista desplegable de indicadores.
* **Creación y Edición (`store` / `update`)**: El administrador utiliza el modal para crear o editar un registro. Puede seleccionar un municipio de un catálogo (usando `Choices.js` para un buscador amigable), definir el objetivo y subir los tres archivos requeridos.
* **Eliminación (`destroy`)**: Tras una confirmación con `SweetAlert2`, el controlador elimina el registro de la base de datos y **borra los tres archivos asociados (convenio, ícono y banner)** del servidor.

## 🌐 Funcionalidad Pública

* **Listado Público (`mostrarMunicipiosConvenio`)**: Este método alimenta una página (`planes-mun.blade.php`) que muestra a los visitantes del sitio una galería o lista de todos los municipios con los que se tiene un convenio.
* **Detalle Público (`show`)**: Proporciona una página de detalle para cada municipio. Muestra la información del convenio (objetivo, ícono, banner) y, lo más importante, consulta y presenta una lista de todos los **indicadores públicos** de ese municipio, calculando y mostrando su dato más reciente.