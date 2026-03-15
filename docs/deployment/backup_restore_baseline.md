# Backup Restore Baseline

## Baseline implementada
- Comando DB: `php artisan backup:db`
- Comando files: `php artisan backup:files`
- Comando combinado: `php artisan backup:run`
- Comando listado: `php artisan backup:list`
- Comando de rotacion: `php artisan backup:prune`

## Que respaldar
- Base de datos completa
- `storage/app/public`
- logos, branding y archivos operativos subidos por tenants
- `.env` y secretos en un sistema seguro aparte

## Donde se guarda
- Base de datos: `storage/app/backups/database/`
- Archivos: `storage/app/backups/files/`

Estas rutas quedan fuera de `public/` y no deben exponerse por web.

## Politica de retencion
- DB: conservar ultimos `14` backups por defecto
- files: conservar ultimos `14` backups por defecto

Configurable via:
- `BACKUP_KEEP_DB`
- `BACKUP_KEEP_FILES`

Tambien disponible en `config/backup.php`.

## Frecuencia sugerida
- Base de datos: diaria como minimo
- Backup adicional antes de cambios estructurales o migraciones delicadas
- Archivos publicos: diaria o segun volumen de cambios

## Como correr comandos

### Solo base de datos
```bash
php artisan backup:db
```

### Solo archivos
```bash
php artisan backup:files
```

### Ambos
```bash
php artisan backup:run
```

### Rotacion manual
```bash
php artisan backup:prune
```

### Ver backups disponibles
```bash
php artisan backup:list
```

## Scheduler recomendado
- `backup:run` diario a las `02:00`
- `backup:prune` diario a las `02:20`

La baseline actual ya deja esta recomendacion registrada en `routes/console.php`.

## Restore
- Restaurar primero base de datos
- Restaurar luego `storage/app/public`
- Validar `storage:link`
- Verificar `/readyz` y flujos criticos despues del restore

## Restore de DB

### SQLite
- detener trafico o poner mantenimiento
- reemplazar archivo `.sqlite` por el backup correspondiente
- volver a levantar app

### MySQL
Ejemplo:
```bash
mysql -h HOST -P 3306 -u USER -p DATABASE < backup.sql
```

### PostgreSQL
Ejemplo:
```bash
psql -h HOST -p 5432 -U USER -d DATABASE < backup.sql
```

## Restore de archivos
- restaurar contenido del backup hacia `storage/app/public`
- validar branding, logos y archivos generados relevantes
- revalidar symlink con `php artisan storage:link` si aplica

## Permisos recomendados
- backups con escritura solo para usuario/grupo del proceso operativo
- no usar `777`
- validar que `storage/app/backups` no quede servido por nginx/apache
- validar espacio en disco y crecimiento de backups comprimidos

## Consideraciones multi-tenant
- Un error de restore afecta multiples tenants si la BD es compartida
- Probar restore en staging antes de asumir un procedimiento valido
- Mantener timestamps y consistencia entre base de datos y archivos

## Que probar despues de un restore
- `GET /healthz`
- `GET /readyz`
- login web
- dashboard tenant
- un export `xlsx`
- branding/logo si aplica
- una lectura de billing o inventory
