# Documentación: Módulo de Dashboard

## 📝 Resumen

El Dashboard es un centro de inteligencia y visualización de datos para la plataforma. Su propósito no es la gestión de contenido (CRUD), sino **agregar, procesar y presentar información clave** del estado de los indicadores, las instituciones y los usuarios. Está diseñado principalmente para el rol de **Administrador**, ofreciendo una visión panorámica a través de KPIs y gráficos interactivos.

## ⚙️ Flujo de Datos (`DashboardController@index`)

El método `index` del controlador es el motor del dashboard. Antes de renderizar la vista, realiza una serie de consultas y cálculos complejos para recopilar todas las métricas necesarias. Las principales son:
* **KPIs Generales**: Calcula el total y porcentaje de indicadores validados e incompletos.
* **Actividad Reciente**: Obtiene los últimos 10 indicadores actualizados.
* **Clasificación de Instituciones**: Identifica el "Top 5" de instituciones con más indicadores validados y aquellas que aún tienen indicadores pendientes de validar.
* **Estado de Actualización**: Analiza las fechas de actualización de los indicadores para clasificarlos como "Caducados", "A Tiempo" o "Próximos a Actualizar".
* **Agregación para Gráficos**: Prepara conjuntos de datos específicos para alimentar cada uno de los gráficos de ApexCharts, como el conteo de indicadores por semáforo, por año, por periodicidad y por usuario "Enlace".

---

## 📊 Visualización de Datos e Interactividad (`dashboard.blade.php`)

El dashboard utiliza la librería **ApexCharts.js** para transformar los datos en visualizaciones claras e interactivas.

### 1. Tarjetas de KPIs (Cards)

La parte superior del dashboard muestra tarjetas con los indicadores de rendimiento clave (KPIs) más importantes, como:
* **Total de Indicadores Validados**.
* **Total de Indicadores Incompletos**.
* **Top 5 Instituciones**.
* **Actividad Reciente**.
* Listas de indicadores según su **estado de actualización**.

### 2. Gráficos Interactivos

El núcleo del dashboard son sus gráficos, que no solo muestran información, sino que también permiten "profundizar" en los datos (drill-down).

* **Semaforización (Gráfico de Pastel)**:
    * Muestra la distribución de todos los indicadores según su estado de semaforización ("Excedido", "Aceptable", etc.).
    * **Es interactivo**: Al hacer clic en una de las porciones (ej. "Aceptable"), el administrador es redirigido a una nueva página que lista únicamente los indicadores que se encuentran en ese estado.

* **Avance por Enlace (Gráficos de Pastel Múltiples)**:
    * Se genera dinámicamente un gráfico de pastel para cada usuario con el rol "Enlace".
    * Cada gráfico muestra el porcentaje de indicadores validados vs. no validados para ese usuario específico.
    * **Es interactivo**: Al hacer clic en una porción (ej. "No Validados"), el administrador es llevado a una página que lista los indicadores no validados asignados a ese "Enlace".

* **Indicadores por Año (Gráfico de Barras)**: Muestra cuántos indicadores tienen datos registrados para cada año, permitiendo ver la cobertura de información a lo largo del tiempo.

* **Periodicidad (Gráfico de Dona)**: Muestra la distribución de los indicadores según su frecuencia de medición (Anual, Semestral, etc.).

---

### 👤 Flujo de Trabajo y Roles

* **Vista de Administrador**: La vista completa del dashboard, con todos sus KPIs y gráficos, está restringida al usuario con `id = 1` (super-administrador).
* **Otras Vistas**: El controlador detecta el rol del usuario. Si no es el administrador, puede mostrar una vista más simple o, como en el caso de los usuarios de tipo "Municipio", redirigirlos a un dashboard completamente diferente y específico para ellos.