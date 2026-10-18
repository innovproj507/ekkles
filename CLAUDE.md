# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Ekkles is a custom PHP MVC church management system (sistema integral de gestión para iglesias). It is multi-tenant: each church's data is isolated by `church_id`. The framework is hand-rolled (no Laravel/Symfony). Code comments, UI text, flash messages and URL paths are in Spanish (`/miembros`, `/ofrendas`, `/eventos`, `/finanzas`, …); keep new code consistent with that.

## Setup and commands

There is no build step, test suite, or linter configured (`composer.json` declares a `Tests\` namespace but `tests/` does not exist).

- `composer install`: installs dependencies and copies `.env.example` to `.env` if missing.
- Database: for a fresh install with initial config data (default church, admin user, categories, ministries), import `database/ekkles_migration.sql`. `database/schema.sql` is structure only. Target is MySQL 8+ / MariaDB 10.6+, utf8mb4.
- Schema changes after the initial dump live in `database/migrations/*.sql` (run each once, in date order, on every environment). `ekkles_migration.sql`/`schema.sql` do not include them.
- Seeds: `php database/seed_ofrendas_categories.php` (also `seed_construccion_categories.php`, `seed_donaciones_categories.php`). These scripts read `DB_DATABASE`/`DB_USERNAME`/`DB_PASSWORD`, not the `DB_NAME`/`DB_USER`/`DB_PASS` used by the app, so they silently fall back to `ekkles`/`root`/empty unless you set those variables too.
- Serve with Laragon/Apache. The root `.htaccess` rewrites everything into `public/`, and `public/index.php` is the front controller.
- Syntax check a file: `php -l path/to/file.php`.
- Runtime requires PHP 8.0+ (`mixed`, `str_contains`, `str_starts_with` are used), even though `composer.json` says `>=7.4`.

Key `.env` variables: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`. `APP_ENV=local` rethrows uncaught exceptions with full stack traces; any other value logs them (see `public/error_log`) and renders `layouts/error.php`. `APP_URL` is used by `url()` and to build QR URLs. `DEFAULT_CHURCH_ID` is the church the login form authenticates against (`AuthController`); after login the tenant comes from the session (`Auth::churchId()`, which falls back to 1).

## Architecture

### Request lifecycle

`public/index.php` (security headers, Dotenv) → `App\Core\App` (session start, `APP_CONFIG`/`ROLES_CONFIG` constants, timezone) → `routes/web.php` → `Router::dispatch()` → middleware → controller method.

- The router uses the **first matching route** (regex from `{param}` placeholders), so specific routes such as `/eventos/tipos` must be declared before `/eventos/{id}`.
- Action strings are `'Module\ControllerName@method'`, which resolve to `App\Modules\{Module}\Controllers\{ControllerName}`. A name without a backslash falls back to the legacy namespace `App\Controllers\`.
- Controller methods receive `(Request $request, Response $response)`. Route params come from `$request->param('id')` and form fields from `$request->post(...)`.
- `Response::redirect()`, `back()`, `json()`, `abort()` and `download()` all call `exit`.

### Middleware: what actually runs

`Router::runMiddleware()` only recognizes `'auth'` (`AuthMiddleware`) and `'role:<permission>'` (`RoleMiddleware`). **Any other string is silently skipped.** This means:
- The `'audit'` entry on many POST routes does nothing. `AuditMiddleware` is never instantiated, and its `handle()` is an empty stub anyway. Audit records exist only where code calls `AuditLog::record($action, $module, $userId, ...)` explicitly (`Auth` does this for login/logout). New write actions must call it themselves. Keep adding `'audit'` to POST routes for consistency, but don't rely on it.
- `SessionTrackerMiddleware` exists but is not wired into the router.

### Modules

Business logic lives in `app/Modules/{Auth,Dashboard,Events,Finance,Members,Pastoral,Reports,System}/` with `Controllers/`, `Models/` and `Views/` folders. Shared pieces are `app/Core` (framework), `app/Models/Model.php` (base model), `app/Helpers` (`functions.php` autoloaded globally, plus `Validator`, `Upload`, `Helper`), `app/Middleware`, and `app/Views/{layouts,partials}`.

Finance's `DonationController` serves three URL families (`/ofrendas`, `/construccion`, `/donaciones`) that share the same controller methods.

### Controllers (`App\Core\Controller`)

- `$this->view('Module::folder/name', $data, $layout = 'app')`. This injects `$auth_user`, `$auth_can` (closure), `$flash_success`/`$flash_error`/`$flash_info`, `$csrf_token`, `$app_config`.
- `redirect()`, `redirectWithSuccess()`, `redirectWithError()`, `json()`.
- `requireCsrf($request)` flashes an error and redirects back (exits) on failure. Every POST form needs `<input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">` (the field is `_csrf_token`; AJAX can send the `X-CSRF-Token` header).
- `churchId()` returns the tenant ID. `requirePermission('x.y')` redirects to the first section the user can access.

### Views (`App\Core\View`)

Plain PHP files with variables passed through `extract()`. `'folder/name'` resolves to `app/Views/folder/name.php`, and `'Module::folder/name'` resolves to `app/Modules/Module/Views/folder/name.php` (dots are also converted to slashes). Layouts in `app/Views/layouts/` are `app` (sidebar), `auth` and `error`. `View::partial()` renders without a layout. `View::make()` returns a string, which is how PDF views (`*_pdf.php`, rendered by dompdf) are built. Escape output with `e()`.

### Models and data access

- `App\Models\Model` provides `find`, `findWithTrashed`, `all($churchId)`, `create`, `update`, `delete` (soft delete: sets `deleted_at`), `count($churchId, $conds)`, and `paginate($page, $perPage, $churchId, $extraWhere, $params)`.
- **`find()`, `update()` and `delete()` filter by primary key only, with no `church_id` check.** When loading or modifying a record by an ID from the URL, verify that `$record['church_id'] === $this->churchId()` (or write a scoped query) to avoid cross-tenant access.
- `App\Core\Database` is a PDO singleton: `query()` (all rows), `queryOne()` (row or null), `execute()` (affected rows), `lastInsertId()`. Always use `?` placeholders. Each connection runs `SET time_zone` to PHP's offset (`APP_TIMEZONE`), so `NOW()`/`CURRENT_TIMESTAMP` match PHP dates.
- Several tables have **no `deleted_at`** (e.g. `donations`, `finances`, `event_types`, `simple_attendances`, `finance_categories`). The base `find()`/`delete()` filter/set `deleted_at` and will throw on them; models for those tables must override `find()`/`delete()` (see `EventType`, `SimpleAttendance`).

### Auth and RBAC

- `Auth` (static, session-backed) provides `check()`, `user()`, `id()`, `churchId()`, `role()`, `can($perm)`. Login locks the account after 5 failed attempts. When `mfa_enabled` is set, the user is held in `mfa_pending_user_id` until a TOTP code is verified (`spomky-labs/otphp`).
- `config/roles.php` defines `roles` (level 1–10 plus optional `exceptions.deny`/`exceptions.allow`) and `permissions` (permission → minimum level).
- `Auth::can()` checks in this order: denied if in `deny` (unless also in `allow`) → allowed if in `allow` → allowed if `level >= permissions[perm]`. **A permission missing from `permissions` requires `PHP_INT_MAX`, so it is denied for everyone except via `allow`.** Add every new permission key to `config/roles.php`.
- Restricted roles (`tesorero`, `lider_ministerio`, `secretario`, `coordinador`, `anciano`, `diacono`) rely heavily on exception lists. Check them when adding permissions that those roles should or should not get.

### QR attendance

There are two separate flows:
1. **Church-level public form:** `/qr/asistencia/{uuid}` (no auth), where `{uuid}` is `churches.uuid`. It is handled by `AttendanceController@publicQr`/`storeQr`/`searchMembers`.
2. **Per-event QR images:** `EventController` generates them into `public/uploads/qr/event_{uuid}.png` (stored as `qr/...` in `events.qr_code`). They encode `APP_URL/asistencia/checkin/{event uuid}`, **but no such route exists in `routes/web.php`**.

`app/Modules/public/uploads/qr/` is a stray copy from an old path bug. The real location is `public/uploads/qr/`.

The public QR form blocks a second registration for the same member/name on the same day (`SimpleAttendance::findForDay`) and remembers the day's registration in the phone's `localStorage`.

### Domain notes

- **Members:** `is_converted` (convertido/bautizado) is separate from `status`. `Member::SECTIONS` defines the list sections: "Miembros" = converted and not `visitante`; "No convertidos"; "Visitas" (`status = 'visitante'`). Use `getSectionCounts()` for member totals. Couples are families with two `conyuge` relationships (`Family::SECTIONS`).
- **Events/rooms:** events can have a `room_id` (`rooms` table, managed at `/salones`) and a `food_contribution` flag (highlighted in amber). `Event::findRoomConflicts()` detects overlaps (events without end time are assumed to last `Event::DEFAULT_DURATION_HOURS`); `EventController` blocks overlapping saves unless `confirm_room_conflict` is sent.
- **Income accounts:** income lives in `donations` and the account is derived from `type` (`ofrendas` → `diezmo/ofrenda_*`, `construccion` → `construccion`, `donaciones` → `donacion/misiones/proyecto/promesa`). Expenses live in `finances` with `target_module`. The third card on each income page is the account balance (`Donation::getBalance`). Income categories are grouped by `finance_categories.group_name` (managed at `/{cuenta}/categorias`).
- **Expense receipts:** stored outside `public/` in `storage/uploads/recibos/AAAA/MM/AAAA-MM-DD_...` (`Upload::uploadReceipt`), referenced from `finances.attachments` (JSON array) and served only through `GET /egresos/{id}/recibo?n=`.

### Third-party libraries

dompdf (PDF receipts, reports, calendar), phpspreadsheet (Excel export), endroid/qr-code, spomky-labs/otphp (TOTP MFA), ramsey/uuid, vlucas/phpdotenv.
