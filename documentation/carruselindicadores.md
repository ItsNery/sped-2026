# Documentación: Módulo del Carrusel de Indicadores

## 📝 Resumen

Este módulo implementa una interfaz de administración para gestionar un carrusel de "indicadores destacados". Utiliza una arquitectura de **vista unificada**, donde todas las operaciones CRUD (Crear, Leer, Actualizar, Eliminar) se realizan a través de una única **ventana modal**, proporcionando una experiencia de usuario ágil y centralizada.

## ⭐ Arquitectura y Características Clave

### 1. Interfaz Unificada (Modal)

* Toda la interacción del usuario (agregar, editar) ocurre dentro de un único modal de Bootstrap, evitando recargas de página.
* Un script de JavaScript gestiona el estado del modal, cambiando dinámicamente el título, la URL de envío del formulario (`action`) y el método HTTP (`POST` para crear, `PUT` para actualizar).

### 2. Control de Acceso (Permisos)

* El módulo utiliza el sistema de permisos de Spatie (`spatie/laravel-permission`) para un control de acceso granular.
* En el constructor del `CarruselIndicadorController`, se aplica un `middleware` que restringe las acciones (`ver`, `crear`, `editar`, `borrar`) según los permisos del usuario que ha iniciado sesión.
* La vista (`index.blade.php`) utiliza directivas de Blade (`@can`) para mostrar u ocultar los botones de acción correspondientes.

### 3. Prevención de Duplicados

* Una de las lógicas más importantes del controlador `index` es que, al preparar la lista de indicadores para el menú desplegable del modal, **excluye automáticamente los indicadores que ya forman parte del carrusel**.
* Esto se logra con una consulta `whereNotIn()`, que previene que un usuario pueda agregar el mismo indicador dos veces, garantizando la integridad de los datos.

### 4. Selector de Iconos Predefinidos

* En lugar de un campo de subida de archivos, el formulario ofrece una **cuadrícula de iconos predefinidos**.
* El controlador escanea un directorio (`public/img/iconos_indicadores`) y pasa la lista de nombres de archivo a la vista. La vista renderiza estos iconos como botones de radio, permitiendo al usuario seleccionar visualmente el ícono para el indicador.

---

## ⚙️ Flujo de Funcionalidad

### 1. Listado (`index`)

* La página principal muestra una tabla interactiva (`DataTables.js`) con los indicadores que actualmente se encuentran en el carrusel.
* Se muestra el nombre del indicador, el ícono seleccionado y el último dato anual disponible, formateado directamente en el controlador.
* Los botones de acción ("Agregar", "Editar", "Eliminar") solo son visibles si el usuario tiene los permisos requeridos.

### 2. Creación (`store`)

1.  **Activación**: Un usuario con permiso hace clic en el botón "Agregar".
2.  **Modal**: Se abre la ventana modal en modo "creación". El menú desplegable (`Choices.js`) solo muestra los indicadores que aún no están en el carrusel.
3.  **Selección**: El usuario selecciona un indicador de la lista y un ícono de la cuadrícula.
4.  **Guardado**: Al enviar, el método `store` valida los datos (asegurando que el indicador seleccionado no se haya agregado mientras tanto) y crea el nuevo registro.

### 3. Edición (`update`)

1.  **Activación**: Un usuario con permiso hace clic en el botón "Editar" (✏️) de un ítem existente.
2.  **Modal**: Se abre la misma ventana modal, pero en modo "edición". JavaScript rellena los campos con los datos actuales del ítem (el indicador y el ícono seleccionados).
3.  **Modificación**: El usuario puede cambiar el indicador o seleccionar un ícono diferente.
4.  **Guardado**: Al enviar, el método `update` valida y guarda los cambios en el registro existente.

### 4. Eliminación (`destroy`)

1.  **Activación**: Un usuario con permiso hace clic en el botón "Eliminar" (🗑️).
2.  **Confirmación**: Se muestra un diálogo de `SweetAlert2` para prevenir eliminaciones accidentales.
3.  **Ejecución**: Si se confirma, el método `destroy` elimina el registro de la base de datos.