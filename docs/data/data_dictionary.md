Lee y sigue docs/AGENTS.md y docs/data/data_dictionary.md.

Crea MetricsService en app/Modules/Production/Application/Services/MetricsService.php con:
- densidad_pl_m2(cycle)
- estimated_alive_count(cycle, survival_estimate?)
- biomass_kg(cycle, pp_grams opcional: usa último muestreo si existe)
- fcr(cycle): alimento_total_kg / cosecha_total_kg (si aún no hay alimento, manejar 0)
- lbs_per_ha(cycle)
- growth_g_per_week(cycle) basado en muestreos

Entrega endpoint:
- GET /api/v1/cycles/{cycle}/metrics

Incluye tests unitarios del service + feature del endpoint.

Proceso:
- Plan checklist
- Implementación
- Tests al final