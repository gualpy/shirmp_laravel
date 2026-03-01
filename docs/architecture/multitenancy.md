# Multi-Tenancy

## Objetivo
Aislamiento fuerte por `tenant_id` para datos multi-tenant en la API.

## Decisión de ID para tenants
Se usa `BIGINT` autoincremental (`id`) en la tabla `tenants`.

Razón:
- Integración simple con `foreignId` de Laravel.
- Menor complejidad inicial para MVP.

## Resolución de tenant por request
Orden de resolución:
1. Subdominio local: `{tenant}.localhost`
2. Fallback por header: `X-Tenant: <tenant_slug>`

Si no se resuelve tenant o está inactivo (`is_active = false`), la API responde `400` con JSON claro.

## Componentes implementados
- `TenantResolver`: resuelve tenant desde host/header.
- `TenantContext`: almacena el tenant actual en el container (`scoped` por request).
- `ResolveTenant` middleware: setea `TenantContext` antes de ejecutar controllers.
- `TenantScope` + trait `HasTenant`: aplica filtro automático `tenant_id` y autocompleta `tenant_id` en `creating`.

## Rutas exentas de resolución tenant
- `GET /api/v1/health` está exenta de `ResolveTenant` para checks de infraestructura.
- El resto de rutas `/api/v1/*` requieren tenant válido.

## Bypass explícito (solo CLI/seeders)
Para procesos de consola (seeders/scripts), existe bypass explícito:

```php
app(\App\Multitenancy\TenantScopeBypass::class)->run(function (): void {
    // operaciones sin tenant scope
});
```

Regla:
- En requests HTTP, el bypass no está permitido y lanza excepción.

## Cómo probar subdominio local
Ejemplo con cURL:

```bash
curl -s -H 'Host: tenant-a.localhost' http://127.0.0.1:8000/api/v1/tenant/current
```

## Cómo probar con header X-Tenant

```bash
curl -s -H 'X-Tenant: tenant-a' http://127.0.0.1:8000/api/v1/tenant/current
```

## Tests
Ejecutar:

```bash
php artisan test
```

Casos clave cubiertos:
- Resolución por header `X-Tenant`.
- Aislamiento automático por `tenant_id` en modelos con `HasTenant`.
- Respuesta `400` cuando no hay tenant en request.
