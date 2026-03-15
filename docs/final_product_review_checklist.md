# Final Product Review Checklist

## 1. Modulos y estado

### Multitenancy
- Estado: `complete`
- Que funciona hoy:
  - resolucion por subdominio, header `X-Tenant` y sesion web para backoffice
  - `TenantContext`, `TenantScope`, `HasTenant`
  - aislamiento tenant probado por tests
- Que falta:
  - endurecimiento adicional en infraestructura real de dominios/subdominios

### Auth
- Estado: `complete`
- Que funciona hoy:
  - auth API con Sanctum
  - login/logout web en backoffice
  - `me`, register, login, logout
  - aislamiento tenant en login
- Que falta:
  - endurecer politicas de password/reset si se va a abrir autoservicio comercial

### Roles & Permissions
- Estado: `good enough for beta`
- Que funciona hoy:
  - roles `Owner`, `Admin`, `Production`, `Inventory`, `Finance`, `ReadOnly`, `SuperAdmin`
  - bloqueo real por rutas y acciones sensibles
  - menu adaptado por rol
- Que falta:
  - matriz mas fina por accion puntual si entran clientes enterprise

### Production
- Estado: `good enough for beta`
- Que funciona hoy:
  - farms, ponds, cycles, stocking, sampling, harvest
  - reglas de ciclo activo, cierre por cosecha final y validaciones de fechas
- Que falta:
  - formularios operativos web mas completos para todas las acciones del ciclo

### Feeding
- Estado: `good enough for beta`
- Que funciona hoy:
  - feed types, feed entries, reglas basicas y filtros
  - integracion con costos y metricas
- Que falta:
  - UX operativa mas rapida si se busca uso intenso en campo

### Water Quality
- Estado: `good enough for beta`
- Que funciona hoy:
  - registros de calidad de agua
  - filtros web
  - resaltado fuera de rango
  - integracion con alertas
- Que falta:
  - historicos mas profundos y analitica adicional

### Daily Mortality
- Estado: `good enough for beta`
- Que funciona hoy:
  - registro diario por ciclo/piscina
  - integracion con metricas y proyeccion
  - alerta `high_daily_mortality`
- Que falta:
  - parametrizacion mas fina de umbrales por operacion

### Alerts
- Estado: `good enough for beta`
- Que funciona hoy:
  - alert engine
  - acknowledge/resolve
  - vista web con filtros
  - exporte `xlsx`
- Que falta:
  - notificaciones externas reales

### Costs
- Estado: `good enough for beta`
- Que funciona hoy:
  - feed cost
  - costos operativos
  - total, cost/lb, cost/ha
  - vista web y exporte `xlsx`
- Que falta:
  - costos indirectos, presupuestos y cierres financieros mas maduros

### Harvest Projection
- Estado: `good enough for beta`
- Que funciona hoy:
  - proyeccion basada en muestreos, supervivencia, metricas y costos
  - bloque web y endpoint dedicado
- Que falta:
  - escenarios multiples y sensibilidad comercial

### Dashboard
- Estado: `good enough for beta`
- Que funciona hoy:
  - dashboard tenant/farm
  - KPIs ejecutivos
  - lectura rapida para demo y seguimiento
- Que falta:
  - drilldowns y reporteria gerencial adicional

### Backoffice
- Estado: `good enough for beta`
- Que funciona hoy:
  - login web
  - top nav
  - dashboard
  - ciclos, detalle, costos, mortalidad, agua, alertas, inventory, billing
  - animaciones sutiles y UX mejorada
- Que falta:
  - refinamiento final de algunas vistas admin y flujos densos

### Reports / Exports
- Estado: `good enough for beta`
- Que funciona hoy:
  - reporte ejecutivo imprimible
  - branding empresarial
  - CSV y `xlsx` para datasets principales
  - exportes extendidos para costos, alerts, billing, inventory
- Que falta:
  - hojas multiples mas ejecutivas si se quiere oferta enterprise

### Inventory / Warehouses
- Estado: `good enough for beta`
- Que funciona hoy:
  - warehouses
  - inventory items
  - movements
  - low stock
  - exportes `xlsx`
- Que falta:
  - integracion mas profunda con consumo automatico por operaciones

### SaaS Plans / Limits
- Estado: `complete`
- Que funciona hoy:
  - planes, features, limits, enforcement
- Que falta:
  - refinamiento comercial de pricing y empaquetado

### Licensing / Offline Grace
- Estado: `good enough for beta`
- Que funciona hoy:
  - on-prem activation
  - verify-now
  - offline grace
  - read-only mode
- Que falta:
  - operacion enterprise mas madura de licencias

### Billing
- Estado: `partial`
- Que funciona hoy:
  - invoices y payments
  - billing tenant y superadmin
  - exporte `xlsx`
  - audit
- Que falta:
  - pasarela real de pago
  - automatizacion de cobranza
  - suspension comercial automatizada si aplica

### SuperAdmin
- Estado: `good enough for beta`
- Que funciona hoy:
  - dashboard SaaS
  - tenants, plans, billing, audit, ops, onboarding
- Que falta:
  - operaciones mas avanzadas y tooling de soporte enterprise

### Audit Log
- Estado: `good enough for beta`
- Que funciona hoy:
  - trazabilidad de eventos criticos
  - vista tenant y vista global
- Que falta:
  - mas cobertura de eventos y posiblemente exporte/admin avanzado

### Backup / Restore
- Estado: `good enough for beta`
- Que funciona hoy:
  - backup DB
  - backup files
  - run, list, prune
  - politica simple de retencion
  - restore documentado
- Que falta:
  - restore automatizado seguro
  - backup remoto/object storage

### Production Readiness
- Estado: `good enough for beta`
- Que funciona hoy:
  - `/healthz`
  - `/readyz`
  - docs de deploy, logs, optimize, filesystem, backups
  - scheduler baseline
- Que falta:
  - infraestructura real de staging/production y observabilidad avanzada

## 2. Listo para demo

- login web multi-tenant
- dashboard ejecutivo
- lista de ciclos
- detalle de ciclo con charts
- proyeccion de cosecha
- alertas operativas
- costos del ciclo
- calidad de agua
- mortalidad diaria
- reporte ejecutivo imprimible con branding
- exportes `xlsx`
- inventory y warehouses
- panel superadmin
- billing basico

## 3. Listo para cliente beta

### Usable ahora
- multitenancy
- auth web + API
- roles y permisos
- backoffice principal
- dashboard, cycles, water, alerts, mortality, costs, inventory
- reportes/exportes
- audit log
- superadmin MVP

### Usable con supervision
- billing
- licensing/on-prem
- backup/restore baseline
- scheduler/ops
- onboarding de tenants

### No recomendable todavia
- billing como sistema comercial final
- restore automatizado de emergencia
- go-to-market autoservicio para clientes externos

## 4. Falta antes de produccion real

- fijar `PostgreSQL` como DB real de staging/production
- desplegar infraestructura real con dominio y HTTPS
- configurar mail real
- configurar `queue worker` real
- configurar cron/scheduler real
- validar backups reales en servidor
- validar restore en staging
- definir storage real para branding/archivos
- asegurar `ext-zip` y extensiones requeridas para `xlsx`
- centralizar logs/errores
- endurecer seguridad de servidor, headers y secretos
- pipeline de despliegue controlado

## 5. Falta antes de go-to-market

- nombre final del producto
- landing page comercial
- branding comercial definitivo
- pricing final
- copy comercial
- flujo de alta de cliente final
- pasarela de pago real
- materiales de demo/ventas
- politica de soporte y onboarding comercial

## 6. Riesgos abiertos

- local sigue usando SQLite mientras production deberia ir con PostgreSQL
- restore sigue siendo manual
- billing no tiene Stripe ni gateway real
- observabilidad sigue basica
- superadmin aun es MVP
- algunas vistas aun pueden mejorar en UX de alta frecuencia operativa
- la programacion de scheduler esta baseline, no operada todavia en servidor real
- permisos/ownership de runtime pueden romper exportes si el deploy no se hace bien

## 7. Checklist final accionable

### A) Antes de demo comercial
- cargar dataset demo convincente
- revisar branding visible del tenant demo
- validar login, dashboard, cycle detail, reportes y exportes
- preparar guion de demo comercial

### B) Antes de staging
- mover a PostgreSQL
- configurar `.env` de staging
- habilitar HTTPS
- configurar queue worker y scheduler
- probar backups y restore en staging

### C) Antes de produccion
- endurecer seguridad y secretos
- validar performance base
- configurar monitoreo/log centralizado
- definir soporte operativo
- probar restore real y rollback de deploy

### D) Despues del lanzamiento
- integrar pasarela de pago
- mejorar onboarding self-service
- ampliar analytics y reporteria
- iterar UX sobre feedback de clientes piloto
