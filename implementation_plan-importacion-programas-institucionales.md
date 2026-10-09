# Plan de Implementación: Importación de Programas Institucionales

## Objetivo

Incorporar los nuevos programas derivados institucionales y relacionarlos con los indicadores existentes a partir del archivo:

```text
public/Relacion nuevis derivados indicadr.ods
```

La importación debe conservar los programas institucionales actuales que ya tienen indicadores y agregar únicamente los nuevos registros y relaciones.

## Estado Actual

La base restaurada contiene:

- 101 indicadores del PED.
- 3 programas derivados institucionales existentes.
- 6 relaciones institucionales existentes.
- 3 planes estatales.
- El plan 2024-2030 corresponde a `plan_estatal = 3`.

Los programas institucionales existentes y sus relaciones son:

| Programa | Indicadores relacionados |
|---|---:|
| ISSSTEP | 1 |
| Secretaría de Movilidad y Transporte | 1 |
| Secretaría de Infraestructura | 4 |

Estos registros y relaciones no deben eliminarse ni reemplazarse.

## Estructura Del Archivo

El archivo contiene tres columnas:

```text
Tipo de programa
Nombre
Nombre del indicador
```

Registros detectados:

- 640 filas institucionales.
- 57 filas sectoriales.
- 9 filas especiales.
- 79 programas institucionales únicos.
- 599 relaciones institucionales únicas.
- No se detectaron filas regionales.

Solo se procesarán las filas cuyo tipo sea `Institucional`.

Las filas `Sectorial`, `Especial` y `Regional` se ignorarán, porque esos catálogos no forman parte de esta importación.

## Alcance De La Importación

La importación podrá modificar únicamente:

```text
cat_programas_derivados_institucionales
programa_institucional_indicador
```

No deberá:

- Crear indicadores nuevos.
- Modificar el nombre de los indicadores existentes.
- Modificar relaciones sectoriales, especiales o regionales.
- Eliminar programas institucionales actuales con indicadores.
- Eliminar las 6 relaciones institucionales actuales.
- Ejecutar migraciones automáticamente.
- Ejecutar pruebas PHPUnit contra MySQL.

## Transformación De Nombres

El archivo contiene nombres de instituciones, por ejemplo:

```text
Secretaría de Infraestructura
Instituto Tecnológico Superior de Tepeaca
Universidad Tecnológica de Puebla
```

El catálogo deberá almacenar nombres de programas institucionales:

```text
Programa Institucional de la Secretaría de Infraestructura
Programa Institucional del Instituto Tecnológico Superior de Tepeaca
Programa Institucional de la Universidad Tecnológica de Puebla
```

La transformación deberá:

- Agregar el prefijo `Programa Institucional`.
- Usar `de`, `de la` o `del` de acuerdo con el nombre.
- Evitar prefijos duplicados.
- Mantener acentos y caracteres originales en el nombre final.
- Normalizar espacios duplicados.
- Usar una versión sin acentos, minúsculas y sin puntuación únicamente para comparar.

## Reconciliación De Programas

Para cada programa institucional del archivo:

1. Normalizar el nombre original.
2. Generar el nombre final con el prefijo institucional.
3. Buscar una coincidencia en `cat_programas_derivados_institucionales`.
4. Si corresponde a uno de los 3 programas actuales, reutilizar su ID.
5. Mantener intactos sus datos y relaciones existentes.
6. Si no existe, crear un nuevo programa.
7. Registrar en el reporte si la coincidencia fue exacta, normalizada o ambigua.

No se deberán crear duplicados por diferencias de mayúsculas, acentos, espacios o puntuación.

## Clasificación Mediante `grupo`

El campo correcto del catálogo es:

```text
grupo
```

Actualmente se utilizan estos valores:

```text
Secretarías
Organismos Auxiliares
```

El importador actual no asigna grupos automáticamente; únicamente utiliza el valor existente del programa. Por eso el nuevo importador deberá incorporar una clasificación inicial.

### Grupo `Secretarías`

Asignar este grupo cuando el nombre institucional empiece por:

```text
Secretaría
```

También deberán revisarse casos de administración central como:

```text
Consejería Jurídica
Coordinación General de Comunicación y Agenda Digital
```

La regla puede incluirlos explícitamente si se confirma que deben formar parte de este grupo.

### Grupo `Organismos Auxiliares`

Asignar este grupo por defecto a instituciones como:

```text
Institutos
Universidades
Comisiones
Colegios
Consejos
Fideicomisos
Sistemas
Servicios
Centros
Comités
Museos
Carreteras de Cuota
```

Toda clasificación por defecto deberá quedar registrada en el reporte para revisión.

### Regla De Seguridad

Si el nombre no coincide con una regla conocida, no se debe detener automáticamente la importación. Se asignará `Organismos Auxiliares` y se marcará como `clasificación_por_defecto` en el reporte.

## Datos Para Nuevos Programas

Para los programas nuevos se utilizarán temporalmente valores comunes:

```text
Descripción:
Programa Institucional del Plan Estatal de Desarrollo 2024-2030.

Color:
#691A32

Imagen:
img/pleca-pajaro-2.png

Documento:
https://ped2024-2030.puebla.gob.mx/

Plan estatal:
3
```

Las siglas deberán generarse a partir del nombre mediante la lógica existente del modelo o una función equivalente.

Los datos comunes deberán utilizarse únicamente para los nuevos programas. Los valores de los 3 programas actuales no deben sobrescribirse sin una instrucción explícita.

## Relaciones Con Indicadores

Cada fila institucional del archivo deberá buscar un indicador ya existente.

La comparación deberá:

- Ignorar mayúsculas y minúsculas.
- Ignorar acentos.
- Ignorar signos de puntuación básicos.
- Normalizar espacios.
- Comparar el nombre completo del indicador.

Para cada coincidencia:

- Si existe una coincidencia única, agregar la relación.
- Si el indicador ya está relacionado con ese programa, no duplicar la relación.
- Si no existe, registrar la fila como `indicador_no_encontrado`.
- Si existen varias coincidencias, registrar la fila como `indicador_ambiguo`.
- No crear indicadores nuevos.
- No modificar `indicadorable_type` ni `indicadorable_id`.

La tabla pivote utilizada será:

```text
programa_institucional_indicador
```

## Preservación De Relaciones Existentes

Las relaciones institucionales actuales se conservarán.

El importador deberá:

1. Leer las 6 relaciones existentes.
2. Mantenerlas sin modificación.
3. Procesar las nuevas filas del archivo.
4. Insertar únicamente las relaciones que no existan.
5. Ignorar relaciones duplicadas.

La estrategia será de ampliación idempotente:

```text
relaciones actuales
+ relaciones nuevas válidas
- duplicados
```

No se debe hacer `sync([])` global ni borrar la tabla pivote.

## Importador Nuevo

Crear:

```text
scratch/importar_programas_institucionales.php
```

El importador deberá recibir estos modos:

```bash
php scratch/importar_programas_institucionales.php --dry-run
php scratch/importar_programas_institucionales.php --execute
```

El modo predeterminado deberá ser simulación.

El modo `--dry-run` no podrá ejecutar `insert`, `update`, `delete`, `sync` ni modificaciones de archivos.

## Flujo Del Importador

### Fase 1: Validación De Archivo

- Verificar que el archivo exista.
- Verificar que sea legible como ODS.
- Verificar que exista una hoja válida.
- Verificar las tres columnas esperadas.
- Ignorar filas completamente vacías.
- Reportar tipos de programa desconocidos.

### Fase 2: Lectura Y Normalización

- Leer todas las filas.
- Filtrar únicamente `Institucional`.
- Normalizar nombres de programas.
- Normalizar nombres de indicadores.
- Eliminar filas institucionales duplicadas.
- Conservar el número original de fila para el reporte.

### Fase 3: Revisión De Catálogo

- Cargar los programas institucionales existentes.
- Reutilizar los 3 programas actuales.
- Detectar los programas nuevos.
- Determinar el grupo de cada programa.
- Preparar los datos comunes para los nuevos registros.

### Fase 4: Revisión De Indicadores

- Cargar los 101 indicadores.
- Construir un índice por nombre normalizado.
- Resolver coincidencias exactas normalizadas.
- Detectar faltantes.
- Detectar ambigüedades.

### Fase 5: Simulación

Mostrar:

- Programas existentes reutilizados.
- Programas nuevos.
- Grupo asignado a cada programa.
- Clasificaciones por defecto.
- Indicadores encontrados.
- Indicadores no encontrados.
- Indicadores ambiguos.
- Relaciones nuevas.
- Relaciones duplicadas ignoradas.

### Fase 6: Escritura Transaccional

Solo con `--execute`:

1. Verificar que el plan `3` exista.
2. Verificar que exista la tabla pivote.
3. Crear los programas nuevos.
4. No modificar los 3 programas actuales.
5. Crear las relaciones nuevas.
6. Evitar duplicados mediante una comprobación previa.
7. Confirmar la transacción.
8. Generar un reporte final.

Ante cualquier error, ejecutar rollback.

## Reporte De Importación

Guardar un reporte fuera de la base de datos con:

```text
storage/app/imports/programas-institucionales-YYYYMMDD-HHMMSS.json
```

El reporte deberá contener:

- Archivo procesado.
- Fecha de ejecución.
- Modo de ejecución.
- Filas leídas.
- Filas institucionales.
- Filas ignoradas por tipo.
- Programas existentes reutilizados.
- Programas nuevos creados.
- Programas duplicados.
- Grupo asignado.
- Clasificaciones por defecto.
- Indicadores encontrados.
- Indicadores no encontrados.
- Indicadores ambiguos.
- Relaciones nuevas.
- Relaciones duplicadas ignoradas.
- Errores.

## Verificaciones Posteriores

Sin ejecutar PHPUnit ni migraciones, verificar:

```sql
SELECT COUNT(*)
FROM cat_programas_derivados_institucionales
WHERE plan_estatal = 3;
```

```sql
SELECT COUNT(*)
FROM programa_institucional_indicador;
```

También verificar:

- Que los 3 programas originales sigan existiendo.
- Que sus 6 relaciones sigan presentes.
- Que no existan relaciones duplicadas.
- Que todos los indicadores relacionados existan.
- Que todos los programas nuevos pertenezcan al plan `3`.
- Que no se hayan creado indicadores nuevos.
- Que las relaciones sectoriales, especiales y regionales no hayan cambiado.
- Que el dashboard siga encontrando los 101 indicadores del PED.

## Criterios De Aceptación

La importación será correcta si:

- Se conservan los 3 programas institucionales actuales.
- Se conservan sus 6 relaciones actuales.
- Se agregan los programas institucionales faltantes.
- Todos los nombres nuevos tienen el prefijo `Programa Institucional`.
- Cada programa nuevo tiene `grupo`.
- Todos los programas nuevos pertenecen al plan `3`.
- Se agregan las relaciones válidas del archivo.
- No se crean relaciones duplicadas.
- No se crean indicadores nuevos.
- No se modifican programas sectoriales, especiales ni regionales.
- Los faltantes y ambiguos quedan documentados.
- La ejecución puede repetirse sin duplicar datos.

## Restricciones Operativas

Durante la importación no ejecutar:

```bash
php artisan test
php artisan migrate
php artisan migrate:fresh
php artisan db:wipe
```

Las pruebas PHPUnit deberán permanecer aisladas en SQLite en memoria y nunca utilizar la base MySQL del proyecto.
