# Queues And Scheduler

## Queue worker
Recomendado para staging/production:

```bash
php artisan queue:work --queue=default --tries=3 --timeout=90
```

Usar un supervisor de procesos (`systemd`, `supervisor`, contenedor dedicado) para reinicios automaticos.

## Scheduler
Registrar cron:

```cron
* * * * * cd /var/www/app && php artisan schedule:run >> /dev/null 2>&1
```

## Tareas recomendadas

### Evaluar alertas
Comando existente:

```bash
php artisan alerts:evaluate
```

Ejemplo diario:
- madrugada o al cierre del dia operativo

### Marcar invoices vencidas
Comando agregado:

```bash
php artisan billing:mark-overdue
```

Ejemplo diario:
- una vez al dia despues de medianoche

### Backups y rotacion
Comandos agregados:

```bash
php artisan backup:run
php artisan backup:prune
```

Baseline recomendada:
- `backup:run` a las `02:00`
- `backup:prune` a las `02:20`

La baseline actual ya deja esas horas registradas en `routes/console.php`.

## Notas
- En local puedes mantener `QUEUE_CONNECTION=sync`.
- En staging/production usar `redis` para colas reales.
- Verificar jobs fallidos si una operacion automatica no se refleja en backoffice.
- Verificar espacio libre antes de asumir que la retencion sola resuelve crecimiento de disco.
