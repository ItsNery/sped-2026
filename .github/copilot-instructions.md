# Copilot instructions for SPED

## Project snapshot

This repository is a Laravel 12 application for the SPED platform: a government monitoring and evaluation system for indicators, dashboards, derived programs, municipal tracking, and public data exports. Most feature work spans public views plus authenticated admin panels, so changes often affect both route-level access patterns and the indicator data model.

## Build, test, and validation commands

### Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
```

### Frontend development and production build

```bash
npm run dev
npm run build
```

The repo defines `npm run dev`, `npm run build`, and the Laravel Mix/Webpack pipeline in `package.json`; there is no dedicated repo-level lint script configured.

### PHP tests

```bash
php artisan test
```

Run a single test or a single file quickly with:

```bash
php artisan test --filter=AuthenticationTest
# or
./vendor/bin/phpunit tests/Feature/AuthenticationTest.php
```

For feature work around dashboards or indicators, prefer a targeted test (by class or `--filter`) before widening to the full suite.

### Deployment-related commands reflected in CI

The deployment workflow in `.github/workflows/deploy.yml` performs Laravel cache refreshes and migrations on `beta`/`main` branches:

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## High-level architecture

- `routes/web.php` is the main entry point for public pages, data-open pages, indicator detail views, and protected admin routes behind `auth:sanctum` + `verified`.
- `app/Http/Controllers/` holds request handlers grouped by feature area such as dashboards, indicator management, public data exports, and catalog administration.
- `app/Models/` contains the Eloquent domain models (`Indicador`, catalog tables, institutions, municipalities, users, and audit/log models). These are the main data layer for business logic and relations.
- `app/Services/` holds reusable dashboard/metric logic (`PedMetricsService`, `PedTrendService`, `DashboardFilterService`, `ActivePlanResolver`). Prefer extending these services instead of duplicating aggregation code.
- `resources/views/` contains Blade templates for public pages, dashboard screens, admin panels, exports, and shared layouts. The front end mixes Bootstrap, Tailwind, Alpine, and Livewire components.
- `database/migrations/` and `database/seeders/` define the schema and initial catalog data.
- `public/` is the web root; `storage/` is for generated runtime assets and uploaded/downloaded files.

The application follows a classic Laravel structure, but the core domain model is the monitoring of indicators and derived programs; dashboard calculations and public/facing statistics are treated as first-class logic rather than ad hoc controller code.

## Key conventions

- Use the existing domain vocabulary in Spanish and the project naming patterns (`Indicador`, `CatEje`, `CatPlanEstatalDesarrollo`, `MunicipioConvenio`, etc.) instead of introducing generic naming.
- Protected sections are intentionally organized by feature and permission (`permission:...` middleware in `routes/web.php`). Respect the existing permission-based guardrails and do not bypass RBAC checks for admin flows.
- Public and admin flows share the same underlying models; if a feature concerns indicator data, check both the public rendering path and the admin management path.
- Dashboard and summary logic is centralized in services rather than embedded in controllers. When adding a metric or filtering behavior, follow the service pattern already used by the dashboard stack.
- Frontend changes should stay in the Laravel Mix/Webpack flow (`resources/js`, `resources/css`, or Blade templates) and be validated with `npm run dev`/`npm run build` rather than introducing a separate build path.
- Deployment is environment-specific and is handled in `.github/workflows/deploy.yml`; it includes migrations and cache warming on both `beta` and `main`.

## Important repository context

- README.md is the canonical setup guide and describes the project as a Laravel 12 + Jetstream + Livewire system with MySQL and public dashboard/data-export features.
- The app is not a generic CRUD app; its main domain is performance indicators, public transparency pages, and institutional monitoring workflows.
- Existing tests are under `tests/Feature/` and `tests/Unit/`; keep new regression coverage in the same structure when adding behavior.
