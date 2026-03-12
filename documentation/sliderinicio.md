# Documentación: Módulo del Slider de Inicio

## 📝 Resumen

Este módulo implementa una interfaz de administración para gestionar las diapositivas del carrusel (slider) de la página de inicio. Utiliza una arquitectura de **vista unificada**, donde todas las operaciones CRUD (Crear, Leer, Actualizar, Eliminar) se realizan a través de una única **ventana modal**, proporcionando una experiencia de usuario ágil y centralizada.

## ⭐ Arquitectura y Características Clave

### 1. Interfaz Unificada (Modal)

* Toda la interacción del usuario (agregar, editar) ocurre dentro de un único modal de Bootstrap (`#modalSliderInicio`), evitando recargas de página.
* Un script de JavaScript gestiona el estado del modal, cambiando dinámicamente el título, la URL de envío del formulario (`action`) y el método HTTP (`POST` para crear, `PUT` para actualizar) según la acción requerida.

### 2. Control de Acceso (Permisos)

* El módulo utiliza el sistema de permisos de Spatie (`spatie/laravel-permission`) para un control de acceso granular.
* En el constructor del `SliderInicioController`, se aplica un `middleware` que restringe las acciones (`ver`, `crear`, `editar`, `borrar`) según los permisos del usuario que ha iniciado sesión.
* Los botones en la vista también están protegidos con directivas `@can` para que solo sean visibles para usuarios autorizados.

### 3. Gestión del Orden de Slides

* Una de las lógicas clave es la gestión del orden para evitar duplicados.
* **Backend**: El controlador `index` obtiene un array con todos los números de `orden` que ya están en uso.
* **Frontend (Vista)**:
    * El menú desplegable "Orden" en el formulario utiliza este array para **deshabilitar las opciones ya ocupadas**.
    * **JavaScript Inteligente**: Al editar una diapositiva, el script se asegura de que el **número de orden actual de esa diapositiva esté habilitado**, permitiendo guardarla con su mismo orden, mientras que los demás números ocupados permanecen deshabilitados.

---

## ⚙️ Flujo de Funcionalidad

### 1. Listado (`index`)

* La página principal muestra una tabla interactiva (`DataTables.js`) con las diapositivas existentes.
* Se muestra el título, descripción, una vista previa de la imagen, el enlace y su número de orden.
* Los botones de acción solo son visibles si el usuario tiene los permisos requeridos.

### 2. Creación (`store`)

1.  **Activación**: Un usuario con permiso hace clic en el botón "Agregar".
2.  **Modal**: Se abre la ventana modal en modo "creación". El menú desplegable de "Orden" solo muestra las posiciones disponibles.
3.  **Selección**: El usuario rellena los campos, sube una imagen y selecciona un número de orden.
4.  **Guardado**: Al enviar, el método `store` valida los datos, guarda el archivo de imagen en el servidor (`public/img/sliders/`) y crea el nuevo registro en la base de datos.

### 3. Edición (`update`)

1.  **Activación**: Un usuario con permiso hace clic en el botón "Editar" (✏️) de un ítem existente.
2.  **Modal**: Se abre la misma ventana modal, pero en modo "edición". JavaScript rellena los campos con los datos actuales de la diapositiva.
3.  **Modificación**: El usuario puede cambiar cualquier dato. Si sube una nueva imagen, esta reemplazará a la anterior.
4.  **Guardado**: Al enviar, el método `update` valida los datos y guarda los cambios. Si se subió una nueva imagen, **elimina el archivo antiguo** antes de guardar la referencia al nuevo.

### 4. Eliminación (`destroy`)

1.  **Activación**: Un usuario con permiso hace clic en el botón "Eliminar" (🗑️).
2.  **Confirmación**: Se muestra un diálogo de `SweetAlert2` para prevenir eliminaciones accidentales.
3.  **Ejecución**: Si se confirma, el método `destroy` **elimina el archivo de imagen del servidor** y luego elimina el registro de la base de datos.