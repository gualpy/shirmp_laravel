Lee y sigue:
- docs/data/erd.mmd
- docs/data/data_dictionary.md
- docs/AGENTS.md

Implementa el MVP del módulo Production con patrón:
FormRequest -> DTO -> Action -> Model -> Resource

Entidades:
- Farm
- Pond
- Cycle
- Stocking
- Sampling
- Harvest

Requisitos:
- Todo debe ser multi-tenant (tenant_id scope).
- Cycle: solo 1 activo por Pond (validación/regla).
- Stocking: 1 por Cycle.
- Harvest: puede haber múltiples (raleos + final).
- Sampling: muchos por Cycle (pp_grams y fecha).

Entregables:
1) Migraciones + modelos + factories/seed demo
2) Endpoints CRUD mínimos (list/create/show/update/delete) para Farm y Pond
3) Endpoints:
   - POST /api/v1/ponds/{pond}/cycles
   - POST /api/v1/cycles/{cycle}/stocking
   - POST /api/v1/cycles/{cycle}/samplings
   - POST /api/v1/cycles/{cycle}/harvests
4) Tests feature:
   - crear pond+cycle y bloquear segundo ciclo activo
   - crear sampling y harvest con tenant scope
5) OpenAPI actualizado

Proceso:
- Plan checklist
- Implementa y ejecuta tests