Lee y sigue docs/AGENTS.md.

Implementa la arquitectura base del backend Laravel para un SaaS multi-tenant con módulos usando:
- Modules
- DTOs
- Actions
- Services
- Controllers delgados + FormRequests + Resources

Entregables:
1) Crear estructura de carpetas:
   app/Modules/Production/{Application/DTO,Application/Actions,Application/Services,Presentation/Controllers,Presentation/Requests,Presentation/Resources}
   (y estructura similar para otros módulos vacíos si lo consideras útil)
2) Crear un archivo de guía: docs/architecture/backend_structure.md que defina:
   - qué va en cada carpeta
   - naming conventions
   - ejemplo de flujo Request -> DTO -> Action -> Model -> Resource
3) Crear “stubs” (plantillas) mínimas:
   - BaseDTO.php (si aplica)
   - BaseAction.php (si aplica)
   - BaseService.php (si aplica)
4) Crea un ejemplo funcional mínimo (sin multi-tenant todavía):
   - Endpoint POST /api/v1/health que retorne {status:"ok"} usando Controller + Resource (o JSON)
   - Test feature que valide 200 OK

Proceso:
- Primero: plan checklist (máx 10 items)
- Luego: cambios por archivos con rutas exactas
- Al final: ejecuta tests y muestra salida