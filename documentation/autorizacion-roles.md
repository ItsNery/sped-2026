# Autorización basada en roles

## SuperAdministrador

Se agregó el rol `SuperAdministrador` como una capa adicional a los roles existentes.

- Tiene acceso a los permisos web existentes y a los permisos de sistema.
- Sustituye el bypass global basado en el correo `estadistica@puebla.gob.mx`.
- La cuenta protegida se identifica mediante `users.is_system_account`, no mediante un ID fijo.
- La migración inicial selecciona la cuenta configurada en `SPED_SUPERADMIN_EMAIL`; si no existe, usa la cuenta Administrador más antigua.

La migración aplicada es:

- `database/migrations/2026_08_05_100000_add_system_account_and_super_administrator.php`

## Permisos de sistema

- `administrar-sistema`
- `proteger-cuenta-sistema`

Las cuentas que no son `SuperAdministrador` no pueden asignar estos permisos ni modificar el rol `SuperAdministrador`.

## Protección de la cuenta del sistema

La protección se aplica en servidor, no solo en las vistas:

- No se puede eliminar la cuenta del sistema.
- No se puede desactivarla.
- No se puede degradar su rol desde la gestión normal de usuarios.
- El rol `SuperAdministrador` no puede eliminarse.

## Asignación de permisos a roles

Los formularios envían nombres de permisos, por ejemplo `ver-indicador`, que es el formato esperado por Spatie Permission. Antes se enviaban IDs numéricos, lo que provocaba errores como:

```text
There is no permission named `1` for guard `web`.
```

## Compatibilidad

- Se conservaron los roles existentes.
- Se conservaron los alcances por institución y municipio.
- No se ejecutaron migraciones destructivas ni reinicios de datos.

## Auditoría de validaciones

Las acciones de validar o invalidar una ficha y sus datos anuales se registran en `logs_cambios` con:

- Usuario autenticado.
- Tabla y registro afectado.
- Columna modificada.
- Estado anterior y nuevo (`0` o `1`).
- Acción (`validado` o `invalidado`).
- Motivo y contexto de la petición.

Estas acciones actualizan el estado mediante transacción y también invalidan la versión de métricas del PED para evitar resultados cacheados.
