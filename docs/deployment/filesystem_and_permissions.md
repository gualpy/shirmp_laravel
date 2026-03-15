# Filesystem And Permissions

## Directorios criticos
- `storage/`
- `bootstrap/cache/`

El usuario del proceso web/worker debe poder escribir en ambos.

## Permisos base
- propietario consistente entre deploy, web y queue worker
- permisos de escritura solo donde hace falta

Ejemplo tipico:

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache
```

## Storage publico
Si usas archivos publicos o branding subido por tenants:

```bash
php artisan storage:link
```

## Notas
- No dar permisos amplios a todo el proyecto.
- Validar restore de `storage/app/public` si hay logos o adjuntos operativos.
