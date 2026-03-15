# Optimize Commands

## Para staging/production

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Cuando usar
- `optimize:clear`: antes de un redeploy o cuando sospeches cache vieja.
- `config:cache`: despues de validar `.env`.
- `route:cache`: cuando las rutas ya estan estables.
- `view:cache`: para reducir compilacion en runtime.
- `optimize`: paso final rapido de optimizacion.

## Precauciones
- No cachear config si el `.env` aun no es definitivo.
- Despues de cambios en rutas/configuracion, limpiar y regenerar cache.

## Para desarrollo
Normalmente basta con:

```bash
php artisan optimize:clear
```
