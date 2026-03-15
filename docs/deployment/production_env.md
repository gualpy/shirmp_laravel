# Production Environment

## Objetivo
Definir una base minima y segura para `local`, `staging` y `production` sin mezclar secretos con defaults inseguros.

## Variables clave

### Aplicacion
- `APP_NAME=Shrimp SaaS`
- `APP_ENV=local|staging|production`
- `APP_KEY=base64:...`
- `APP_DEBUG=false` en `staging` y `production`
- `APP_URL=https://app.example.com`
- `APP_TIMEZONE=America/Guayaquil`

### Base de datos
- `DB_CONNECTION=pgsql` recomendado en staging/production
- `DB_HOST=...`
- `DB_PORT=5432`
- `DB_DATABASE=...`
- `DB_USERNAME=...`
- `DB_PASSWORD=...`

### Sesiones, cache y colas
- `SESSION_DRIVER=database` o `redis`
- `CACHE_STORE=redis` recomendado
- `QUEUE_CONNECTION=redis` recomendado

### Correo
- `MAIL_MAILER=smtp`
- `MAIL_HOST=...`
- `MAIL_PORT=587`
- `MAIL_USERNAME=...`
- `MAIL_PASSWORD=...`
- `MAIL_ENCRYPTION=tls`
- `MAIL_FROM_ADDRESS=no-reply@example.com`
- `MAIL_FROM_NAME="${APP_NAME}"`

### Logs
- `LOG_CHANNEL=stack`
- `LOG_STACK=single`
- `LOG_LEVEL=info` o `warning`

## Recomendacion por ambiente

### Local
- `APP_ENV=local`
- `APP_DEBUG=true`
- `SESSION_DRIVER=file`
- `CACHE_STORE=file`
- `QUEUE_CONNECTION=sync`

### Staging
- `APP_ENV=staging`
- `APP_DEBUG=false`
- `SESSION_DRIVER=database`
- `CACHE_STORE=redis`
- `QUEUE_CONNECTION=redis`
- `LOG_LEVEL=info`

### Production
- `APP_ENV=production`
- `APP_DEBUG=false`
- `SESSION_DRIVER=redis` o `database`
- `CACHE_STORE=redis`
- `QUEUE_CONNECTION=redis`
- `LOG_LEVEL=warning`

## Notas operativas
- No publicar `.env`.
- No usar `APP_DEBUG=true` fuera de local.
- Validar `APP_URL` final antes de cachear configuracion.
- Si usas branding por tenant con archivos, asegurar `FILESYSTEM_DISK` y `storage:link`.
- Para exportes `xlsx` con `maatwebsite/excel`, asegurar `ext-zip` en el runtime web/worker.
- Si usas `phpoffice/phpspreadsheet`, validar tambien extensiones PHP comunes de XML habilitadas en staging/production.
