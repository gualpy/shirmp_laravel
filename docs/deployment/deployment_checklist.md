# Deployment Checklist

1. Configurar `.env` correcto para el ambiente.
2. Instalar dependencias (`composer install --no-dev --optimize-autoloader`).
3. Ejecutar migraciones: `php artisan migrate --force`.
4. Ejecutar seed solo si aplica al ambiente.
5. Crear symlink publico si aplica: `php artisan storage:link`.
6. Limpiar caches viejos: `php artisan optimize:clear`.
7. Cachear configuracion/rutas/vistas.
8. Levantar queue worker.
9. Registrar scheduler (`php artisan schedule:run` via cron).
10. Revisar politica de backup (`BACKUP_KEEP_DB`, `BACKUP_KEEP_FILES`) si aplica.
11. Ejecutar backup manual inicial: `php artisan backup:run`.
12. Confirmar que `backup:list` muestre artefactos en `storage/app/backups`.
13. Verificar `GET /healthz`.
14. Verificar `GET /readyz`.
15. Validar login web, dashboard y una operacion critica basica.
16. Validar logs y permisos de `storage/` y `bootstrap/cache/`.
