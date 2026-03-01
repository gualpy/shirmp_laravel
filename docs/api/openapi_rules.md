Lee y sigue docs/AGENTS.md y docs/api/openapi_rules.md.

Implementa autenticación JWT para un SaaS multi-tenant:
- Registro: crea usuario dentro del tenant actual
- Login: retorna token
- Middleware auth para /api/v1
- Roles básicos: Owner/Admin/Production/Inventory/Finance/ReadOnly (puede ser enum o tabla roles)

Entregables:
1) Endpoints:
   - POST /api/v1/auth/register
   - POST /api/v1/auth/login
   - GET /api/v1/auth/me
2) Policies/guards para asegurar aislamiento por tenant
3) Tests feature para register/login/me
4) OpenAPI actualizado

Nota:
- Si eliges un package JWT, configúralo correctamente y documenta pasos.
- Si implementas JWT manual, incluye firma/expiración segura y refresh si lo crees necesario.

Proceso:
- Plan checklist
- Implementación
- Ejecutar tests

Implementation details:
- OpenAPI spec actualizado en `docs/api/openapi.yaml`.
- Endpoints auth documentados:
  - `POST /api/v1/auth/register`
  - `POST /api/v1/auth/login`
  - `GET /api/v1/auth/me`
  - `POST /api/v1/auth/logout`
- Header `X-Tenant` requerido para endpoints tenant-scoped.
- Endpoints protegidos requieren `Authorization: Bearer {token}`.
