# Documentación: Módulo de Gestión de Roles y Permisos

## 📝 Resumen

Este módulo es el **panel de control central para la seguridad y el control de acceso** de toda la aplicación. Su única función es permitir a los administradores crear, editar y eliminar **Roles**, y asignarles un conjunto específico de **Permisos**. Está construido sobre el popular paquete `spatie/laravel-permission`.

## 🔑 Conceptos Clave

* **Permiso (`Permission`)**: Es la acción más básica que un usuario puede realizar. En esta aplicación, los permisos tienen nombres descriptivos como `crear-indicador`, `editar-rol` o `validar-indicador-municipal`. Son la unidad fundamental de la autorización.
* **Rol (`Role`)**: Es una etiqueta o un grupo que aglutina múltiples permisos. En lugar de asignar 50 permisos individuales a un usuario, se le asigna un único rol (ej. "Enlace", "Administrador Municipal") que ya contiene todos esos permisos.

## 🔒 Seguridad del Módulo

Este módulo está "meta-asegurado". Esto significa que para poder **gestionar los roles y permisos**, el propio usuario debe tener permisos específicos para hacerlo (`ver-rol`, `crear-rol`, etc.). Un usuario sin estos permisos ni siquiera podrá ver el listado de roles.

---

## ⚙️ Flujo de Funcionalidad

### 1. Listado de Roles (`index`)

* La página principal muestra una tabla interactiva (`DataTables.js`) con todos los roles que han sido creados en el sistema.
* Para cada rol, se muestran botones de "Editar" y "Eliminar", pero solo si el usuario actual tiene los permisos `editar-rol` y `borrar-rol`, respectivamente.

### 2. Creación de un Rol (`create` y `store`)

1.  **Formulario**: Un administrador con el permiso `crear-rol` accede al formulario de creación.
2.  **Nombre**: Proporciona un nombre único para el nuevo rol (ej. "Supervisor de Contenido").
3.  **Asignación de Permisos**: Se muestra una cuadrícula con casillas de verificación (checkboxes) para **todos los permisos disponibles** en la aplicación. El administrador marca las casillas correspondientes a las acciones que este nuevo rol podrá realizar.
4.  **Guardado**: Al enviar el formulario, el método `store` crea el nuevo rol en la base de datos. Luego, utiliza el método `syncPermissions()` del paquete Spatie para vincular todos los permisos seleccionados a este nuevo rol.

### 3. Edición de un Rol (`edit` y `update`)

1.  **Formulario**: Un administrador con permiso de `editar-rol` hace clic en "Editar". Se le presenta el mismo formulario que en la creación.
2.  **Datos Precargados**: El campo del nombre del rol ya está relleno. Más importante aún, las casillas de los permisos que este rol ya posee **aparecen pre-marcadas**. Esto es posible porque el método `edit` del controlador consulta la base de datos para obtener los IDs de los permisos ya asociados.
3.  **Modificación**: El administrador puede cambiar el nombre del rol y/o cambiar la selección de permisos (marcando nuevas casillas o desmarcando existentes).
4.  **Actualización**: Al guardar, el método `update` actualiza el nombre del rol. Luego, vuelve a usar `syncPermissions()`, que inteligentemente **sincroniza** los permisos: añade los nuevos, elimina los que se desmarcaron y mantiene los que no cambiaron.

### 4. Eliminación de un Rol (`destroy`)

1.  **Activación**: Un administrador con permiso de `borrar-rol` hace clic en "Eliminar" (🗑️).
2.  **Confirmación**: Se muestra un diálogo de `SweetAlert2` para prevenir la eliminación accidental.
3.  **Ejecución**: Si se confirma, el rol se elimina de la base de datos. Las asignaciones de este rol a los usuarios y a los permisos se eliminan en cascada.