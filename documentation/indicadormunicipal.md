# Documentación: Módulo de Indicadores Municipales

## 📝 Resumen

Este es un sistema integral diseñado para que los municipios puedan gestionar sus propios indicadores de rendimiento. El módulo permite un seguimiento detallado con datos periódicos (sub-anuales) y cuenta con un robusto **flujo de trabajo de validación** para garantizar la integridad de la información. La arquitectura utiliza vistas separadas para cada acción del CRUD.

## ⭐ Arquitectura y Características Clave

### 1. Estructura de Datos Normalizada

A diferencia de un modelo que guarda datos anuales en columnas separadas, este sistema utiliza un modelo relacionado, `ResultadoIndicadorMunicipal`, para almacenar los datos.
* **Ventajas**: Esta aproximación es más **flexible y escalable**. Permite registrar datos con diferentes periodicidades (mensual, bimestral, trimestral, etc.) para distintos años sin necesidad de alterar la estructura de la base de datos.
* **Implementación**: Un `IndicadorMunicipal` `hasMany` (tiene muchos) `ResultadoIndicadorMunicipal`. Cada `Resultado` guarda un dato para un año y un periodo específicos.

### 2. Flujo de Validación Automatizado (Model Events)

Esta es la característica más potente del módulo. El sistema garantiza que cualquier cambio en los datos requiera una nueva validación por parte de un administrador.

* **Lógica "de Abajo hacia Arriba"**:
    * En el modelo `ResultadoIndicadorMunicipal`, los eventos `saved` (al crear/actualizar) y `deleted` (al eliminar) se activan automáticamente.
    * Estos eventos **buscan al `IndicadorMunicipal` padre y cambian su estado `validado` a `false` (No Validado)**. Esto asegura que si un usuario modifica o borra un simple dato de un periodo, todo el indicador se marque como "pendiente de revisión".

* **Lógica "en el Indicador"**:
    * En el modelo `IndicadorMunicipal`, el evento `saving` (antes de guardar) se activa.
    * Este evento comprueba si se está modificando cualquier campo. Si es así, **también cambia el estado `validado` a `false`**.

* **Resultado**: Este doble mecanismo crea un sistema a prueba de errores donde **cualquier modificación, por pequeña que sea, obliga a que un administrador revise y vuelva a validar el indicador**.

### 3. Control de Acceso Basado en Permisos

* El controlador utiliza el `middleware` de `spatie/laravel-permission` para asignar permisos específicos a cada acción (ej. `crear-indicador-municipal`, `validar-indicador-municipal`).
* Esto garantiza que los usuarios solo puedan ver/editar los indicadores de su propio municipio y que solo los roles autorizados (como "Administrador Municipal") puedan realizar acciones críticas como validar o eliminar.

---

## ⚙️ Funcionalidad del Panel de Administración

* **Creación de Indicadores**: Al crear un nuevo indicador, el sistema genera automáticamente las filas de resultados vacías para el primer año, basándose en la **periodicidad** seleccionada (ej. crea 12 filas para "Mensual", 4 para "Trimestral").
* **Visualización Detallada**: La vista de `show` presenta toda la información del indicador y organiza los resultados periódicos por año, probablemente en un sistema de pestañas para una navegación clara.
* **Edición Granular**: La vista de `show` permite editar los datos de años/periodos específicos, probablemente a través de modales, utilizando endpoints dedicados en el controlador como `actualizarResultadosIndMun`. Esto evita tener que editar el indicador completo para cambiar un solo dato.
* **Generación de Reportes**: El sistema cuenta con métodos para generar una **Ficha Técnica** pública y un **Reporte** interno imprimible con todos los datos del indicador y sus resultados.