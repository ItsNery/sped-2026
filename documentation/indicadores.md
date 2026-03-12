# Documentación: Módulo de Gestión de Indicadores

## 📝 Resumen

Este es un sistema de información integral y multifacético para la **gestión, seguimiento, validación y reporte de indicadores de rendimiento**. Dada su complejidad, la funcionalidad se divide en varias áreas clave: un panel de administración con un CRUD avanzado, un flujo de trabajo de validación, un sistema de subida masiva de datos desde Excel, y una API de datos abiertos para exportación.

---

## 🔐 Arquitectura y Características Centrales

* **Control de Acceso Completo**: El sistema utiliza `spatie/laravel-permission` para asignar permisos granulares a cada acción (`ver`, `crear`, `editar`, `borrar`, `validar`, `editar-indicador-anual`, `subida-masiva-indicador`). Las vistas se adaptan dinámicamente según el rol del usuario (`Administrador`, `Enlace`, etc.).
* **Modelo de Datos Relacional**: El corazón del sistema es el modelo `Indicador`, que se relaciona con `DatoAnual` (para el histórico), `User` (responsable), `Institucion`, y `Odses`.
* **Semaforización Automática**: El modelo `Indicador` contiene la lógica de negocio para calcular automáticamente el **avance** de un indicador hacia su meta, considerando su **tendencia** ("Mayor es mejor" o "Menor es mejor"). Basado en este avance, asigna un estado de **semaforización** ("Excedido", "Aceptable", "Moderado", "Insuficiente"). Estos cálculos se exponen como atributos del modelo (`$indicador->avance`), manteniendo la lógica encapsulada y reutilizable.

---

## 🛠️ Gestión de Indicadores (CRUD)

### Creación y Edición con Datos Anuales Dinámicos

* Los formularios para crear (`crear.blade.php`) y editar (`editar.blade.php`) indicadores son muy detallados.
* Permiten gestionar no solo los datos principales del indicador, sino también un **histórico de datos anuales** en la misma pantalla.
* **Interfaz Dinámica**: Un script de JavaScript permite al usuario **añadir o eliminar bloques de formulario** para cada año del histórico, haciendo la captura de datos flexible y escalable.
* **Lógica de Actualización Compleja**: El método `update()` del controlador es capaz de procesar este formulario anidado: actualiza los datos del indicador principal, y luego itera sobre los datos anuales para crearlos, actualizarlos o eliminarlos según corresponda, incluyendo la gestión de archivos de evidencia para cada año.

### Flujo de Validación

1.  **Captura**: Un usuario de tipo "Institución" o "Enlace" captura y actualiza los datos de sus indicadores y los datos anuales correspondientes.
2.  **Modificación**: Si un dato anual es modificado, una bandera `modificado` se activa en la base de datos para ese registro. El `Indicador` principal se marca automáticamente como "no validado".
3.  **Validación**: Un usuario con el permiso `validar-indicador` (probablemente un "Administrador") revisa los cambios. Si son correctos, hace clic en el botón "Validar".
4.  **Bloqueo**: Al validar, el campo `indicador_validado` se pone en `true`, lo que puede restringir la edición para roles inferiores. Un evento en el modelo `Indicador` resetea la bandera `modificado` de todos sus datos anuales a `false`.
5.  **Finalización**: Una vez que un usuario de tipo "Institución" tiene todos sus indicadores validados, se le presenta un botón para "Finalizar Captura", bloqueando futuras ediciones y habilitando la generación de reportes.

---

## 📊 Visualización y Reportes

* **Ficha de Detalle (`mostrar.blade.php`)**: Ofrece una vista completa de toda la información del indicador. Utiliza un sistema de **pestañas (tabs)** para mostrar los datos de cada año del histórico de forma organizada. Cada pestaña incluye un botón "Editar" que abre un **modal** para actualizar los datos de ese año específico de forma granular.
* **Reporte Imprimible (`generar-documento.blade.php`)**: Genera una ficha técnica limpia y profesional, lista para imprimir, que resume todos los indicadores de un usuario/institución, incluyendo una sección para firma del titular.
* **Exportación desde Tablas**: Las vistas de listado (`index.blade.php`, `indicadores_semaforizacion.blade.php`) integran `DataTables.js` con botones para exportar los datos visibles a **Excel, CSV, PDF o copiar al portapapeles**.

---

## 🚀 Funcionalidad de Subida Masiva (Excel)

Este sistema permite la creación y actualización de múltiples indicadores a partir de un archivo Excel, siguiendo un proceso seguro de dos pasos:

1.  **Paso 1: Validación (`validateFile`)**:
    * El usuario sube un archivo Excel a través de la vista `prueba.blade.php`.
    * Se envía una petición AJAX al backend. El controlador valida el formato del archivo y su estructura básica (que no esté vacío, que tenga las columnas necesarias) **sin escribir nada en la base de datos**.
    * Si el archivo es estructuralmente correcto, se guarda en la sesión del usuario.
2.  **Paso 2: Confirmación (`confirmImport`)**:
    * Se muestra un mensaje de éxito y un diálogo de confirmación (`SweetAlert2`) al usuario.
    * Si el usuario confirma, se envía una segunda petición AJAX. El controlador recupera los datos de la sesión y procesa el archivo fila por fila, dentro de una **transacción de base de datos**.
    * Para cada fila, utiliza `updateOrCreate` para actualizar indicadores existentes o crear nuevos, incluyendo sus `DatoAnual` asociados. Si una fila falla, la transacción para esa fila se revierte y se registra el error, pero el proceso continúa con las siguientes.
    * Al final, se informa al usuario cuántas filas se procesaron con éxito y cuáles fallaron.

---

## 🌐 API de Datos Abiertos

* El controlador incluye tres métodos (`datosAbiertosPed`, `datosAbiertosPedCsv`, `datosAbiertosPedJson`) que funcionan como una API pública.
* Estos endpoints permiten a usuarios externos o a otras partes del sistema descargar conjuntos de datos de indicadores en formatos estándar (**XLSX, CSV, JSON**).
* Aceptan parámetros para filtrar los datos (por ejemplo, por programa o eje), promoviendo la transparencia y la iniciativa de "Datos Abiertos".