# Incidente: saturación de Apache causada por Fichas SEI

Fecha del incidente: 21 de octubre de 2026.

## Resumen

Los sitios alojados en el servidor devolvían HTTP 503 o tardaban demasiado en cargar. Reiniciar Apache restablecía el servicio sólo de forma temporal.

La causa confirmada fue el agotamiento global de trabajadores Apache. El servidor usa `mpm_prefork` con `mod_php`, con `MaxRequestWorkers 150`; todos los sitios comparten ese mismo límite.

En el momento de la captura, `server-status` mostró:

- 150 solicitudes procesándose y 0 workers libres.
- 147 de las 150 solicitudes pertenecían a `sei.puebla.gob.mx`.
- 139 correspondían a `GET /fichas/ficha/municipio/comparar/...`.
- También se observaron solicitudes de generación de PDF bajo `/fichas/ficha/municipio/<municipio>/perfil/pdf`.
- La duración promedio de las solicitudes era de aproximadamente 44 segundos.

Las IP registradas (`10.1.4.9` y `10.1.4.15`) son privadas y parecen corresponder al proxy o balanceador interno. No identifican por sí solas al visitante original.

## Conclusión

No se encontró evidencia de que una vulnerabilidad de PHP hubiera originado la caída. El problema fue una secuencia automatizada o de alta concurrencia que recorrió rutas costosas de comparación municipal y ocupó todos los procesos Apache. Como consecuencia, aplicaciones no relacionadas también devolvieron 503.

La actualización de PHP sigue siendo necesaria por seguridad, pero es una acción independiente de esta causa de disponibilidad.

## Mitigación aplicada

Se agregó temporalmente esta regla dentro del virtual host de `sei.puebla.gob.mx`:

```apache
<LocationMatch "^/fichas/ficha/municipio/(comparar/|[^/]+/perfil/pdf)">
    Require all denied
</LocationMatch>
```

Después se validó la configuración y se reinició Apache:

```bash
sudo apachectl configtest
sudo systemctl restart apache2
```

El resultado esperado es HTTP 403 únicamente para las comparativas municipales y los PDF de perfil, liberando capacidad para el resto de SEI y los demás sitios del servidor.

## Cambios requeridos en el proyecto Fichas

Antes de retirar el bloqueo temporal, implementar y validar estas medidas.

1. Aplicar limitación de tasa a las rutas de comparación y PDF.
   - Debe limitar por IP real, usuario autenticado o ambos.
   - Debe devolver una respuesta controlada (`429 Too Many Requests`) cuando se exceda el límite.
   - El límite debe permitir el uso humano normal, pero impedir recorrer cientos de combinaciones en paralelo.

2. Reducir el costo de las comparativas.
   - Revisar las consultas y eliminar lecturas repetidas, especialmente N+1.
   - Cachear resultados por combinación de municipios y por versión de datos.
   - Evitar recalcular indicadores ya publicados en cada petición.

3. Tratar los PDF como trabajo costoso.
   - Cachear el archivo generado y servirlo mientras sus datos no cambien.
   - Generar o regenerar el PDF en cola cuando sea viable.
   - Impedir múltiples generaciones simultáneas para el mismo municipio.

4. Registrar datos útiles para investigación.
   - Registrar ruta, duración, código HTTP y usuario/IP real.
   - Propagar y confiar en `X-Forwarded-For` únicamente desde los proxies internos autorizados.
   - Crear alertas para aumento de respuestas lentas, 429 y saturación de workers.

## Medidas de infraestructura recomendadas

- Conservar `ExtendedStatus On` y consultar `http://127.0.0.1/server-status` durante incidentes.
- No aumentar `MaxRequestWorkers` sin medir memoria por worker; con `prefork` y `mod_php`, aumentar el límite puede agotar la RAM.
- Definir tiempos de espera razonables para PHP y para los proxys internos para que una dependencia lenta no retenga workers durante minutos.
- Planear migración de los sitios PHP a PHP-FPM con `mpm_event`; idealmente aislar SEI en su propio pool o servicio para que no pueda consumir la capacidad de todos los sitios.
- Configurar el proxy/balanceador para conservar la IP real del cliente y aplicar rate limiting antes de llegar a Apache.

## Validación para retirar el bloqueo

1. Hacer pruebas concurrentes sobre comparación y PDF dentro de un entorno controlado.
2. Confirmar que el tiempo de respuesta se mantiene estable y que Apache conserva workers libres.
3. Revisar con `server-status` que las rutas de Fichas no llenan el pool.
4. Retirar el `LocationMatch`, ejecutar `sudo apachectl configtest` y recargar Apache.
5. Monitorear las métricas y registros durante las primeras horas posteriores.

## Comandos de diagnóstico

```bash
sudo curl -s http://127.0.0.1/server-status
sudo curl -s http://127.0.0.1/server-status?auto
sudo ss -tnp state established | grep apache2
sudo apachectl -S
```

