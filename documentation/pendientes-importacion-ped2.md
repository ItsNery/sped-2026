# Pendientes: Importacion del PED 2

## Estado Actual

- El PED 1 ya fue importado correctamente.
- El PED 2 todavia no se ha importado.
- No se hicieron cambios en la base de datos durante la ultima revision.
- El archivo fuente es:

```text
public/docs/datos-abiertos/2019-2024/mod-ped/datos-generales/BaseCompletaModPED.xlsx
```

- El archivo contiene la hoja principal `Base`.
- La base contiene `419` filas de indicadores y `36` columnas.
- Los datos anuales abarcan de `2010` a `2024`.

## Dry-Run Actual

Comando utilizado:

```bash
php artisan sped:import-historical --plan=2 --file="C:\laragon\www\sped\public\docs\datos-abiertos\2019-2024\mod-ped\datos-generales\BaseCompletaModPED.xlsx"
```

Resultado con el importador actual:

- Filas fuente: `419`.
- Filas validas: `187`.
- Filas invalidas: `232`.
- Valores anuales detectados en filas validas: `1,345`.

No ejecutar `--execute` hasta resolver los pendientes de esta nota.

## Ajustes Pendientes

### 1. Normalizar El Plan

El archivo contiene valores como:

```text
Plan Estatal de Desarrollo 2
```

Debe reconocerse como:

```text
Plan Estatal de Desarrollo
```

El sufijo `2` solo evita homonimias del sistema anterior y no debe almacenarse como parte del tipo.

### 2. Resolver Ejes Del PED 2

El PED 2 tiene cinco ejes:

- Justicia Social y Fortalecimiento del Estado de Derecho.
- Sostenibilidad Territorial y Desarrollo Integral.
- Fortalecimiento del Campo e Impulso a la Economía Justa y Social.
- Desarrollo Integral, Educación y Diversidad Cultural.
- Transparencia, Participación Ciudadana y Combate a la Corrupción.

Crear los registros en `cat_ejes` asociados al plan `id = 2`.

Usar como apoyo:

```text
public/docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje1P2.xlsx
public/docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje2P2.xlsx
public/docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje3P2.xlsx
public/docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje4P2.xlsx
public/docs/datos-abiertos/2019-2024/mod-ped/ped/BD_Eje5P2.xlsx
```

`BD_Eje4P2.xlsx` esta vacio, pero el eje puede obtenerse del archivo principal.

### 3. Resolver Programas Regionales

El archivo tiene `220` filas regionales.

En muchas filas `Temática` esta vacia. La region aparece dentro del nombre del indicador, por ejemplo:

```text
Porcentaje de hogares víctimas del delito, Región Xicotepec
```

Reglas pendientes:

- Usar `Temática` cuando contenga una región válida.
- Si `Temática` esta vacia, extraer el texto posterior a `Región` en el nombre.
- Si `Temática` contiene `Regional` o `Desarrollo Regional`, extraer también la región desde el nombre.
- Normalizar `Región Atlixco` y `Atlixco` como una sola región.
- Crear o reutilizar los programas regionales del plan `id = 2`.

Se esperan aproximadamente `23` programas regionales.

### 4. Incluir La Temática En La Identidad

La clave interna para reconciliar indicadores debe considerar:

```text
tipo de relación + programa/eje + temática + nombre del indicador
```

Esto evita fusionar indicadores con el mismo nombre y eje, pero distinta temática.

Hay dos pares de filas duplicadas exactas en el archivo. Deben reportarse y no generar registros duplicados.

### 5. Mapear Instituciones

La columna `Institución Responsable` contiene `35` nombres distintos.

Usar la primera coincidencia encontrada mediante normalización de:

- Mayúsculas y minúsculas.
- Acentos.
- Espacios duplicados.
- Puntuación.
- Prefijos comunes.

Si no existe coincidencia segura:

- No crear una institución nueva automáticamente.
- Dejar `id_institucion` en `null`.
- Registrar el nombre en el reporte de importación.

Se debe conservar la primera coincidencia, como se acordó, aunque existan variaciones ortográficas históricas.

### 6. Semaforización

La columna `Semaforo` del Excel no debe importarse directamente.

El sistema debe recalcular la semaforización a partir de:

- Meta.
- Tendencia.
- Dato anual más reciente.
- Validación del dato.

El Excel contiene muchos registros como `No clasificado`; esto no debe convertirse en un valor persistido.

### 7. Tendencias

El archivo contiene:

- `Mayor es Mejor`.
- `Mayor es mejor`.
- `Menor es Mejor`.
- `Menor es mejor`.
- `Constante`.
- `Ascendente`.
- Valores vacios.

Normalizar a:

```text
Mayor es Mejor
Menor es Mejor
Constante
No definida
```

`Ascendente` y valores vacios deben quedar como `No definida`.

## Valores Esperados

Distribución aproximada del archivo:

- PED directo: `37` indicadores.
- Programas sectoriales: `94` indicadores.
- Programas especiales: `48` indicadores.
- Programas institucionales: `20` indicadores.
- Programas regionales: `220` filas.

Catálogos esperados:

- `5` ejes.
- `5` programas sectoriales.
- `6` programas especiales.
- `6` programas institucionales.
- `23` programas regionales.

## Comandos Para Retomar

Primero ejecutar el dry-run:

```bash
php artisan sped:import-historical --plan=2 --file="C:\laragon\www\sped\public\docs\datos-abiertos\2019-2024\mod-ped\datos-generales\BaseCompletaModPED.xlsx"
```

Cuando el dry-run no tenga errores:

```bash
php artisan sped:import-historical --plan=2 --file="C:\laragon\www\sped\public\docs\datos-abiertos\2019-2024\mod-ped\datos-generales\BaseCompletaModPED.xlsx" --execute
```

El comando genera un reporte en:

```text
storage/app/imports/
```

## Validaciones Posteriores

Verificar que el PED 2 tenga indicadores:

```php
App\Models\Indicador::forPlan(2)->count();
```

Verificar que el PED 1 y PED 3 no cambien:

```php
App\Models\Indicador::forPlan(1)->count();
App\Models\Indicador::forPlan(3)->count();
```

Verificar catálogos del PED 2:

```php
App\Models\CatEje::where('plan_id', 2)->count();
App\Models\CatProgramaDerivadoSectorial::where('plan_estatal', 2)->count();
App\Models\CatProgramaDerivadoEspecial::where('plan_estatal', 2)->count();
App\Models\CatProgramaDerivadoRegional::where('plan_estatal', 2)->count();
App\Models\CatProgramaDerivadoInstitucional::where('plan_estatal', 2)->count();
```

Cambiar temporalmente `SPED_ACTIVE_PLAN_ID` a `2` y revisar el dashboard general.

Confirmar que:

- El dashboard muestre los indicadores del PED 2.
- Los ejes y programas correspondan al PED 2.
- Los datos anuales aparezcan como validados.
- No se mezclen indicadores del PED 1 o PED 3.
- El selector de plan siga permitiendo cambiar entre planes.

## Respaldo Y Commit

Antes de ejecutar la importación real:

1. Crear un dump completo actualizado de `bd_sped`.
2. Ejecutar el dry-run sin errores.
3. Ejecutar la importación dentro de la transacción.
4. Revisar los conteos en Tinker.
5. Revisar `git diff --check`.
6. Commitear solamente los cambios del importador.
7. Hacer push a la rama `beta`.
