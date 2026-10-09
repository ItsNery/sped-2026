# Túnel SSH para probar la Release Candidate

Procedimiento para probar `/var/www/html/sped-candidate` sin crear subdominio ni modificar producción.

## Datos

```text
Servidor: 10.X.X.XXX
Usuario: user_planeacion
Puerto SSH: 50610
Puerto Laravel remoto: 8081
Puerto local recomendado: 18081
```

Sustituye `10.X.X.XXX` por la IP real del servidor.

## Levantar la candidata

En una sesión SSH:

```bash
ssh -p 50610 user_planeacion@10.X.X.XXX
cd /var/www/html/sped-candidate
grep -nE '^(APP_URL|APP_ENV|APP_DEBUG)=' .env
```

La configuración debe incluir:

```dotenv
APP_URL=http://127.0.0.1:8081
APP_ENV=staging
APP_DEBUG=false
```

Si modificaste `.env`:

```bash
rm -f bootstrap/cache/config.php
php artisan config:clear
php artisan config:cache
```

Levanta Laravel y deja esa sesión abierta:

```bash
php artisan serve --host=127.0.0.1 --port=8081
```

En otra sesión SSH puedes confirmar que responde:

```bash
curl -I http://127.0.0.1:8081
ss -ltnp | grep 8081
```

## Crear el túnel desde Windows

En PowerShell local:

```powershell
ssh -N -p 50610 -L 18081:127.0.0.1:8081 user_planeacion@10.X.X.XXX
```

Mantén esa ventana abierta y abre:

```text
http://127.0.0.1:18081
```

Si el puerto local `18081` está ocupado:

```powershell
ssh -N -p 50610 -L 28081:127.0.0.1:8081 user_planeacion@10.X.X.XXX
```

Abre `http://127.0.0.1:28081`.

## Pruebas

Validar:

- Inicio de sesión y botón de mostrar contraseña.
- Buscador de indicadores.
- Fichas técnicas y gráficas históricas.
- Indicadores con muchos años.
- Línea base y meta en el mismo año.
- Descarga PDF, gráfica SVG, pleca y contador.
- Assets, fuentes, imágenes y API.

La candidata debe usar la base de beta o una base de pruebas. No ejecutar acciones destructivas contra producción.

## Diagnóstico del PDF

```bash
cd /var/www/html/sped-candidate
```

## Diagnóstico de timeout

Desde PowerShell:

```powershell
Test-NetConnection 10.X.X.XXX -Port 50610
ssh -vvv -N -p 50610 -L 18081:127.0.0.1:8081 user_planeacion@10.X.X.XXX
```

Si Laravel responde con `curl` en el servidor pero el túnel falla, el administrador debe revisar `AllowTcpForwarding` en SSH.

## Detener la prueba

- Presiona `Ctrl+C` en la sesión de `php artisan serve`.
- Presiona `Ctrl+C` en la sesión del túnel.
- Confirma que el puerto terminó:

```bash
ss -ltnp | grep 8081 || true
```

No modificar `/var/www/html/sped` ni reiniciar producción.
