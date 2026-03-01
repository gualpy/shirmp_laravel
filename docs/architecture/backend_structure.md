# Backend Structure (Modular Laravel)

## Objetivo
Definir una arquitectura modular y consistente para backend Laravel en `/app/Modules`, con controllers delgados y casos de uso explícitos.

## Estructura base

```text
app/Modules/
  Shared/
    Application/
      DTO/
      Actions/
      Services/

  Production/
    Application/
      DTO/
      Actions/
      Services/
    Presentation/
      Controllers/
      Requests/
      Resources/
```

## Responsabilidad por carpeta

- `Application/DTO`: Objetos de transferencia de datos de entrada/salida para casos de uso.
- `Application/Actions`: Casos de uso; coordinan validaciones de negocio y orquestan servicios/modelos.
- `Application/Services`: Servicios de aplicación/dominio reutilizables.
- `Presentation/Controllers`: Adaptadores HTTP; reciben request y delegan al `Action`.
- `Presentation/Requests`: `FormRequest` para validación/autorización de entrada.
- `Presentation/Resources`: Transformación de salida JSON de la API.
- `Shared/Application/*`: Clases base compartidas (`BaseDTO`, `BaseAction`, `BaseService`).

## Flujo estándar

`Request -> FormRequest -> DTO -> Action -> Model -> Resource`

1. Llega request HTTP al controller.
2. `FormRequest` valida/autorización.
3. Controller crea DTO desde datos validados.
4. Controller invoca Action.
5. Action ejecuta lógica de negocio y usa modelos/servicios.
6. Controller retorna `Resource` para salida estable.

## Regla de controllers delgados

- Controller solo debe:
  - Recibir request
  - Transformar a DTO
  - Invocar Action
  - Retornar Resource/Response
- Controller no debe:
  - Contener reglas de negocio
  - Ejecutar queries complejas
  - Aplicar cálculos de dominio

## Regla obligatoria

La lógica de negocio **NO** va en controllers; va en `Application/Actions` y `Application/Services`.
