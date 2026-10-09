# Auditoria de campos: PED 2

Este reporte identifica campos vacios, `NULL`, `N/D` u otro marcador equivalente en el archivo fuente.
No implica que deban rellenarse automaticamente; los valores deben confirmarse contra la fuente oficial.

## Normalizaciones aplicadas

- La columna `Unidad de Medida` se importa desde su encabezado normalizado correcto.
- Los valores fuente `NULL` se conservan como `N/D` y no como texto literal.
- El valor fuente `Ascendente` se normaliza como `Mayor es Mejor`.

## Resumen

| Campo | Casos en fuente |
| --- | ---: |
| Unidad de medida | 0 |
| Cobertura | 25 |
| Tendencia | 31 |

## Valores sospechosos para revisar

| Fila Excel | Indicador | Tipo | Programa o region | Periodicidad | Cobertura |
| ---: | --- | --- | --- | --- | --- |
| 354 | Ingresos por bienes y servicios de alojamiento y de preparación de alimentos y bebidas, Región Xicotepec | Programa Regional | Xicotepec | Quinquenal | Quinquenal |
| 355 | Ingresos por bienes y servicios de alojamiento y de preparación de alimentos y bebidas, Región Huauchinango | Programa Regional | Huauchinango | Quinquenal | Quinquenal |

## Unidad de medida

| Fila Excel | Indicador | Tipo | Programa o region | Valor fuente |
| ---: | --- | --- | --- | --- |

## Cobertura

| Fila Excel | Indicador | Tipo | Programa o region | Valor fuente |
| ---: | --- | --- | --- | --- |
| 60 | Promedio de cobertura de educación pública en los niveles de educación básica, media superior y superior | Programa Sectorial | Sostenibilidad Territorial y Desarrollo Integral | Vacio |
| 62 | Porcentaje de ciudadanos que se trasladan a su lugar de trabajo en modos de movilidad no motorizados | Programa Sectorial | Sostenibilidad Territorial y Desarrollo Integral | Vacio |
| 63 | Porcentaje de personas que se trasladan a su lugar de trabajo en los servicios de transporte público y mercantil | Programa Sectorial | Sostenibilidad Territorial y Desarrollo Integral | Vacio |
| 64 | Porcentaje de satisfacción con el servicio de transporte público | Programa Sectorial | Sostenibilidad Territorial y Desarrollo Integral | Vacio |
| 65 | Satisfacción de las personas usuarias del servicio de los sistemas de Infraestructura Carretera y Red Urbana de Transporte Articulado | Programa Sectorial | Sostenibilidad Territorial y Desarrollo Integral | Vacio |
| 66 | Satisfacción de las usuarias el servicio de autobús de tránsito rápido | Programa Sectorial | Sostenibilidad Territorial y Desarrollo Integral | Vacio |
| 123 | Calificación promedio de los Índices de Calidad y Satisfacción Ciudadana de los trámites y servicios estatales evaluados | Programa Sectorial | Transparencia, Participación Ciudadana y Combate a la Corrupción | Vacio |
| 124 | Promedio de habitantes del estado de Puebla participantes por cada mecanismo de participación y contraloría social implementado | Programa Sectorial | Transparencia, Participación Ciudadana y Combate a la Corrupción | Vacio |
| 125 | Tasa de prevalencia de la corrupción por cada 100 mil habitantes en trámites | Programa Sectorial | Transparencia, Participación Ciudadana y Combate a la Corrupción | Vacio |
| 152 | Porcentaje de mujeres integrantes de órganos de representación de núcleos agrarios | Programa Especial | Igualdad Sustantiva | NULL |
| 159 | Unidades de Igualdad Sustantiva creadas en la Administración Pública Estatal | Programa Especial | Igualdad Sustantiva | NULL |
| 160 | Número de instituciones de la Administración Pública Estatal certificadas en la Norma Mexicana NMX-R025-SCFI-2015 en Igualdad Laboral y No Discriminación | Programa Especial | Igualdad Sustantiva | NULL |
| 183 | Porcentaje de apoyos asistenciales y alimentarios otorgados a niñas, mujeres adolescentes y mujeres adultas de atención prioritaria con al menos una carencia social | Programa Institucional | Sistema para el Desarrollo Integral de la Familia del Estado de Puebla | Vacio |
| 184 | Porcentaje de apoyos asistenciales y alimentarios otorgados a adultos mayores de atención prioritaria con al menos una carencia social | Programa Institucional | Sistema para el Desarrollo Integral de la Familia del Estado de Puebla | Vacio |
| 185 | Porcentaje de apoyos asistenciales y alimentarios otorgados a personas con discapacidad con al menos una carencia social | Programa Institucional | Sistema para el Desarrollo Integral de la Familia del Estado de Puebla | Vacio |
| 186 | Porcentaje de apoyos asistenciales y alimentarios otorgados a niñas, niños y adolescentes de con al menos una carencia social | Programa Institucional | Sistema para el Desarrollo Integral de la Familia del Estado de Puebla | Vacio |
| 187 | Porcentaje de apoyos asistenciales y alimentarios otorgados a población abierta con al menos una carencia social | Programa Institucional | Sistema para el Desarrollo Integral de la Familia del Estado de Puebla | Vacio |
| 188 | Porcentaje de documentación otorgada a ciudadanos poblanos radicados en Estados Unidos de América y a sus familias | Programa Institucional | Instituto Poblano de Asistencia al Migrante | Vacio |
| 189 | Porcentaje de poblanos reunificados con el Programa Reencuentro Familiar, Adultos Mayores | Programa Institucional | Instituto Poblano de Asistencia al Migrante | Vacio |
| 190 | Porcentaje de traslados de restos mortales repatriados | Programa Institucional | Instituto Poblano de Asistencia al Migrante | Vacio |
| 191 | Porcentaje de apoyos otorgados a migrantes en retorno con el Programa Migrante Emprende | Programa Institucional | Instituto Poblano de Asistencia al Migrante | Vacio |
| 192 | Porcentaje de migrantes poblanos capacitados o certificados | Programa Institucional | Instituto Poblano de Asistencia al Migrante | Vacio |
| 193 | Número formaciones en sistema braille otorgados a instituciones públicas o privadas | Programa Institucional | Instituto de la Discapacidad del Estado de Puebla | Vacio |
| 194 | Número de personas con discapacidad incluidas en el mercado laboral formal y autoempleo | Programa Institucional | Instituto de la Discapacidad del Estado de Puebla | Vacio |
| 353 | Tasa de desempleo, Región Tepeaca | Programa Regional | Tepeaca | Vacio |

## Tendencia

| Fila Excel | Indicador | Tipo | Programa o region | Valor fuente |
| ---: | --- | --- | --- | --- |
| 2 | Razón de policías por cada mil habitantes | Plan Estatal de Desarrollo | Justicia Social y Fortalecimiento del Estado de Derecho | Vacio |
| 87 | Estadía promedio | Programa Sectorial | Fortalecimiento del Campo e Impulso a la Economía Justa y Social | Vacio |
| 110 | Porcentaje de eficiencia terminal media superior | Programa Sectorial | Desarrollo Integral, Educación y Diversidad Cultural | Vacio |
| 161 | Tasa de mortalidad infantil | Programa Especial | Niñas, Niños y Adolescentes | Vacio |
| 185 | Porcentaje de apoyos asistenciales y alimentarios otorgados a personas con discapacidad con al menos una carencia social | Programa Institucional | Sistema para el Desarrollo Integral de la Familia del Estado de Puebla | Vacio |
| 266 | Valor de la producción agrícola, Región Xicotepec | Programa Regional | Xicotepec | Vacio |
| 267 | Valor de la producción agrícola, Región Huauchinango | Programa Regional | Huauchinango | Vacio |
| 268 | Valor de la producción agrícola, Región Zacatlán | Programa Regional | Zacatlán | Vacio |
| 269 | Valor de la producción agrícola, Región Huehuetla | Programa Regional | Huehuetla | Vacio |
| 270 | Valor de la producción agrícola, Región Zacapoaxtla | Programa Regional | Zacapoaxtla | Vacio |
| 271 | Valor de la producción agrícola, Región Teziutlán | Programa Regional | Teziutlán | Vacio |
| 272 | Valor de la producción agrícola, Región Chignahuapan | Programa Regional | Chignahuapan | Vacio |
| 273 | Valor de la producción agrícola, Región Libres | Programa Regional | Libres | Vacio |
| 274 | Valor de la producción agrícola, Región Quimixtlán | Programa Regional | Quimixtlán | Vacio |
| 275 | Valor de la producción agrícola, Región Acatzingo | Programa Regional | Acatzingo | Vacio |
| 276 | Valor de la producción agrícola, Región Ciudad Serdán | Programa Regional | Ciudad Serdán | Vacio |
| 277 | Valor de la producción agrícola, Región Tecamachalco | Programa Regional | Tecamachalco | Vacio |
| 278 | Valor de la producción agrícola, Región Tehuacán | Programa Regional | Tehuacán | Vacio |
| 279 | Valor de la producción agrícola, Región Sierra Negra | Programa Regional | Sierra Negra | Vacio |
| 280 | Valor de la producción agrícola, Región Izúcar de Matamoros | Programa Regional | Izúcar de Matamoros | Vacio |
| 281 | Valor de la producción agrícola, Región Chiautla | Programa Regional | Chiautla | Vacio |
| 282 | Valor de la producción agrícola, Región Acatlán | Programa Regional | Acatlán | Vacio |
| 283 | Valor de la producción agrícola, Región Tepexi de Rodríguez | Programa Regional | Tepexi de Rodríguez | Vacio |
| 284 | Valor de la producción agrícola, Región Atlixco | Programa Regional | Atlixco | Vacio |
| 285 | Valor de la producción agrícola, Región San Martín Texmelucan | Programa Regional | San Martín Texmelucan | Vacio |
| 286 | Valor de la producción agrícola, Región Área Metropolitana de la Ciudad de Puebla | Programa Regional | Área Metropolitana de la Ciudad de Puebla | Vacio |
| 287 | Valor de la producción agrícola, Región Tepeaca | Programa Regional | Tepeaca | Vacio |
| 291 | Valor de la producción pecuaria, Región Huehuetla | Programa Regional | Huehuetla | Vacio |
| 298 | Valor de la producción pecuaria, Región Ciudad Serdán | Programa Regional | Ciudad Serdán | Vacio |
| 308 | Valor de la producción pecuaria, Región Área Metropolitana de la Ciudad de Puebla | Programa Regional | Área Metropolitana de la Ciudad de Puebla | Vacio |
| 353 | Tasa de desempleo, Región Tepeaca | Programa Regional | Tepeaca | Vacio |
