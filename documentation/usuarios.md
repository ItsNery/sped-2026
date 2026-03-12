# Documentación: Módulo de Gestión de Usuarios

## 📝 Resumen

Este es el módulo central para la administración de usuarios de la plataforma. Permite a los administradores con los permisos adecuados realizar operaciones CRUD completas sobre las cuentas de usuario. Su característica más destacada es un **formulario dinámico** que se adapta para gestionar múltiples tipos de usuarios y sus complejas relaciones con **Instituciones** y **Municipios**.

## ⭐ Arquitectura y Características Clave

### 1. Gestión de Múltiples Tipos de Usuario

El sistema está diseñado para manejar diferentes arquetipos de usuarios, cada uno con una estructura de relación distinta:
* **Usuario de Institución**: Es el tipo estándar. Un usuario que pertenece y está ligado a **una sola** institución.
* **Usuario de Municipio**: Un usuario que pertenece y está ligado a **un solo** municipio.
* **Usuario "Enlace"**: Un rol especial que actúa como nexo. Este tipo de usuario puede estar asociado a **múltiples instituciones** simultáneamente a través de una tabla pivote (`institucion_user`).

### 2. Formulario Dinámico e Inteligente (`form.blade.php`)

El corazón de la experiencia de usuario de este módulo es su formulario de creación/edición, que utiliza JavaScript para reconfigurarse en tiempo real:
* **Selector de Tipo**: Un interruptor (toggle switch) permite al administrador elegir si el usuario será de tipo "Institución" o "Municipio".
* **Adaptación por Rol**: La selección en el menú desplegable "Roles" también afecta al formulario. Si se elige el rol "Enlace", el formulario oculta los selectores individuales y muestra un **selector múltiple** para asignar varias instituciones.
* **Resultado**: Esta lógica combinada asegura que siempre se muestren los campos correctos para el tipo de usuario y rol que se está creando o editando, evitando errores y haciendo el proceso más intuitivo.

### 3. Creación de Instituciones "Al Vuelo"

Para agilizar el proceso de alta, el formulario de creación de usuarios de tipo "Institución" incluye una opción en el menú desplegable para "**Añadir Nueva Institución**". Al seleccionarla, aparecen campos adicionales en el mismo formulario para registrar el nombre y el titular de una nueva institución. El controlador se encarga de crear primero la institución y luego asociarla al nuevo usuario, todo en una sola operación.

### 4. Control de Acceso Granular

El módulo está protegido por el `middleware` de `spatie/laravel-permission`. Cada acción (`ver-usuario`, `crear-usuario`, `editar-usuario`, `borrar-usuario`, `des-activar-usuario`) requiere que el administrador tenga el permiso explícito correspondiente.

---

## ⚙️ Flujo de Funcionalidad

* **Listado (`index`)**: Muestra una tabla interactiva (`DataTables.js`) con todos los usuarios. Para cada uno, se muestran sus roles como insignias y botones de acción (Editar, Activar/Desactivar, Eliminar) protegidos por permisos.
* **Activación/Desactivación**: Los administradores pueden habilitar o deshabilitar temporalmente el acceso de un usuario sin borrar su cuenta. Un diálogo de confirmación (`SweetAlert2`) previene acciones accidentales.
* **Creación y Edición (`store` / `update`)**: El administrador interactúa con el formulario dinámico para establecer los datos del usuario, su tipo, su rol y sus relaciones. La lógica del controlador valida los datos de forma condicional y se encarga de crear o actualizar las relaciones correctas en la base de datos (`id_institucion`, `id_municipio`, o la tabla pivote `institucion_user`).
* **Seguridad de Contraseña**: Al crear un usuario, la contraseña se hashea de forma segura. Al editar, la contraseña solo se actualiza si el administrador introduce un nuevo valor en el campo, de lo contrario, se mantiene la existente.