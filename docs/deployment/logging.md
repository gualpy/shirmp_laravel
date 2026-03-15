# Logging

## Objetivo
Tener logs utiles para troubleshooting sin exponer stack traces al usuario final.

## Recomendaciones
- `APP_DEBUG=false` en staging/production.
- `LOG_CHANNEL=stack`.
- `LOG_LEVEL=info` en staging y `warning` en production si el volumen es alto.
- Mantener respuestas limpias para web y API; el detalle tecnico debe quedar en logs.

## Practicas recomendadas
- Registrar errores de infraestructura, integraciones y jobs fallidos.
- Mantener `audit_logs` para acciones criticas de negocio y soporte.
- Separar logs de app y del proceso web/queue si la plataforma lo permite.

## Troubleshooting base
- Revisar `storage/logs/laravel.log`.
- Revisar estado de `/healthz` y `/readyz`.
- Revisar workers de cola y scheduler si faltan eventos esperados.

## Produccion
- No mostrar excepciones completas al navegador.
- Centralizar monitoreo de errores si luego integras una herramienta externa.
