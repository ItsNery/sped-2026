# Implementation Plan - General Progress Dashboard and Axis Connection

Develop a general progress dashboard for the State Development Plan (PED), connecting indicators to specific axes (Ejes) or derived programs, and implementing gauge charts for visual progress tracking.

## User Review Required

> [!IMPORTANT]
> The hierarchy will be updated as follows:
>
> - **Plan Estatal** has many **Ejes** and many **Programas Derivados**.
> - **Ejes** have many **Indicadores**.
> - **Programas Derivados** have many **Indicadores**.
>   This means an Indicator will point to either an Eje or a Program as its parent.

> [!NOTE]
> **Data Validation Rule**:
>
> - **Public View**: Only displays progress calculated from **validated** annual data (`datos_anuales.validado = 1`).
> - **Admin View**: Displays a toggle to switch between "Validated Only" and "All Data" (including pending validation).

## Proposed Changes

### Database & Models

#### [NEW] [CatEje.php](file:///c:/laragon/www/sped/app/Models/CatEje.php)

- Create a new model for Axes.
- Fields: `nombre`, `numero`, `color`, `plan_id`.
- Relationships:
  - `belongsTo(CatPlanEstatalDesarrollo, 'plan_id')`
  - `morphMany(Indicador, 'indicadorable')`

#### [MODIFY] [CatPlanEstatalDesarrollo.php](file:///c:/laragon/www/sped/app/Models/CatPlanEstatalDesarrollo.php)

- Add `hasMany(CatEje, 'plan_id')` relationship.

#### [MODIFY] [CatProgramaDerivadoSectorial.php](file:///c:/laragon/www/sped/app/Models/CatProgramaDerivadoSectorial.php) (and other program models)

- Ensure they all relate to [CatPlanEstatalDesarrollo](file:///c:/laragon/www/sped/app/Models/CatPlanEstatalDesarrollo.php#8-27) (already exists in Sectorial).
- Maintain `morphMany(Indicador, 'indicadorable')`.

#### [MODIFY] [Indicador.php](file:///c:/laragon/www/sped/app/Models/Indicador.php)

- No structural changes needed to the polymorphic relationship itself, but we will change how it's used (linking to `CatEje` instead of [CatPlanEstatalDesarrollo](file:///c:/laragon/www/sped/app/Models/CatPlanEstatalDesarrollo.php#8-27)).

#### [NEW] [Migrations](file:///c:/laragon/www/sped/database/migrations/)

- Create table `cat_ejes`.
- Data migration script: Create 6 default Ejes for existing Plans and reassign indicators currently linked directly to a Plan to their respective Eje based on the `programa` field.

### Logic & Controllers

#### [MODIFY] [IndicadorController.php](file:///c:/laragon/www/sped/app/Http/Controllers/IndicadorController.php)

- Update [store](file:///c:/laragon/www/sped/app/Http/Controllers/IndicadorController.php#167-407) and [update](file:///c:/laragon/www/sped/app/Http/Controllers/IndicadorController.php#510-887):
  - If it's NOT a program, set `indicadorable_type = CatEje::class` and `indicadorable_id` to the selected Eje ID.
- Update [create](file:///c:/laragon/www/sped/app/Http/Controllers/IndicadorController.php#115-166) and [edit](file:///c:/laragon/www/sped/app/Http/Controllers/IndicadorController.php#447-509) views to provide a dropdown for Ejes when a Plan is selected.

#### [NEW] [DashboardGeneralController.php](file:///c:/laragon/www/sped/app/Http/Controllers/DashboardGeneralController.php)

- Implement progress calculation logic with a `validatedOnly` parameter.
- This controller will serve both Admin and Public views.
- Progress averages (Overall, Axis, Program) will be calculated based on this parameter.

### Views & UI

### Views & UI

#### [NEW] [dashboard-general.blade.php](file:///c:/laragon/www/sped/resources/views/admin/dashboard-general.blade.php)

- Admin version with a toggle switch (Validated vs. All).
- Main dashboard with ApexCharts: 1 Large Gauge, 6 Axis Gauges, and Program section.

#### [MODIFY] [eje*-ped.blade.php](file:///c:/laragon/www/sped/resources/views/eje1-ped.blade.php) and [programa-*.blade.php](file:///c:/laragon/www/sped/resources/views/programa-sectorial.blade.php)
- Redesign the indicator card using a 3-column layout:
  - **Col 7/12**: Title and ODS icons.
  - **Col 2/12**: "Resultado: [Year]" and "[Value]" (text color matched to semaphorization status).
  - **Col 3/12**: Small ApexCharts radial gauge representing the progress percentage.

#### [MODIFY] [public/css/estilos.css](file:///c:/laragon/www/sped/public/css/estilos.css)
- Add CSS variables for semaphorization colors to ensure consistency between the colored text and the gauge charts (e.g., `--color-excedido`, `--color-aceptable`, etc.).

## Verification Plan

### Automated Tests

- Test cases for progress calculation: `calculate(validatedOnly=true)` vs `calculate(validatedOnly=false)`.
- Data integrity check for the Eje migration.

### Manual Verification

- Compare Admin "Validated Only" view with the charts in "Información General" (they should match).
- Compare Admin "All Data" view with "Validated Only" (All Data should reflect recent unvalidated entries).
