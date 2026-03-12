# Documentación: Módulo de Registro de Cambios (Logs)

## 📝 Resumen

Este módulo proporciona una interfaz de **solo lectura** para que los administradores del sistema puedan consultar un registro detallado de todas las acciones y cambios importantes que ocurren dentro de la aplicación. Su propósito principal es servir como una **pista de auditoría** para el seguimiento, la seguridad y la resolución de problemas.

## ⚙️ Funcionamiento

### 1. Captura de Datos (Implícita)

* Este módulo se centra en **mostrar** los logs. La **generación** de estos registros ocurre en otras partes del sistema, de forma automática.
* Típicamente, esto se implementa mediante **Model Observers** o **Event Listeners** en Laravel. Por ejemplo, cuando un `Indicador` es actualizado, un "observador" detecta este cambio y crea automáticamente un nuevo registro en la tabla `logs_cambios` con toda la información relevante (quién lo hizo, qué campo cambió, cuál era el valor anterior y cuál es el nuevo).

### 2. Visualización (`LogCambioController@index`)

* El único método del controlador, `index()`, tiene una sola responsabilidad: obtener todos los registros de la tabla `logs_cambios` de la base de datos.
* Los registros se ordenan por fecha de creación descendente, mostrando los cambios más recientes primero.

### 3. Interfaz de Usuario (`index.blade.php`)

* La vista presenta los logs en una tabla clara y fácil de leer.
* **Interfaz Interactiva**: La tabla está potenciada con la librería **DataTables.js**, lo que permite a los administradores:
    * **Buscar** rápidamente en todos los registros.
    * **Ordenar** los datos por cualquiera de las columnas.
    * **Paginar** a través de un gran volumen de logs de manera eficiente.
* **Columnas Mostradas**:
    * `Fecha y hora`: El momento exacto en que ocurrió el cambio.
    * `Usuario`: El nombre del usuario que realizó la acción.
    * `Tabla`: La tabla de la base de datos que fue modificada (ej. `indicadors`).
    * `Campo`: El campo o columna específico que cambió.
    * `Acción`: El tipo de operación realizada (ej. "CREADO", "ACTUALIZADO", "ELIMINADO").

---

### ⭐ Propósito y Casos de Uso

* **Auditoría**: Permite responder a las preguntas "¿Quién hizo qué y cuándo?".
* **Seguimiento**: Facilita el rastreo del historial de un registro específico para entender cómo ha cambiado a lo largo del tiempo.
* **Resolución de Problemas**: Ayuda a identificar cuándo se realizó un cambio incorrecto y quién fue el responsable, agilizando la corrección de errores.

### ⭐ Característica Notable

* **Inmutabilidad**: La característica más importante de este módulo es su naturaleza de **solo lectura**. Los administradores no pueden crear, editar o eliminar logs desde esta interfaz. Esto es fundamental para garantizar la integridad y fiabilidad de la pista de auditoría.