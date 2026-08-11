# Stage 02 — Core Infrastructure Remodeling

**Status:** Implemented, validated, committed
**Scope:** Structurally runnable and correctly bootstrapped application core — no UI redesign, no new features, no credential display, no Supabase connection/execution.
**Related:** Stage 01 audit (`docs/01-project-audit.md`), Stage 03B schema (`backend/database/schema/connectmyuni_initial_schema.sql` — canonical, **untouched by this stage**).

---

## 1. Goal

Stage 02 makes the ConnectMyUni codebase *bootable* — every frontend/admin/public entry point now loads one canonical backend bootstrap (configuration + autoloader), all include paths are `__DIR__`-relative, the PHP class/repository case-collision is resolved, no BOMs remain in the core files, and no credentials are stored or displayed anywhere.

## 2. Stage 01 Findings Addressed

| Finding | Resolution |
|---|---|
| **C1** `config/database.php` (lowercase) vs `config/Database.php` class file name collision + missing autoload | Created **`backend/config/db_config.php`** as the single canonical config array. `Database.php` loads it via `require_once __DIR__ . '/db_config.php'`. All legacy lowercase `config/database.php` references removed. Verified: zero `config\database.php` literal references remain in the tree. |
| **C2** No autoloader — one-off `require_once` chains | Created **`backend/bootstrap.php`**: PSR-4-style prefix map (`Config`, `Middleware`, `Repositories`, `Services`, `Helpers`) + exact class map for `ConnectMyUni\Database` and `ConnectMyUni\CacheManager` (files live outside their matching dir). |
| **C3** Broken middleware path (`../middleware/AuthMiddleware.php` from admin component) | `frontend/admin/components/admin-header.php` now bootstraps via `../../../backend/bootstrap.php` and calls `ConnectMyUni\Middleware\AuthMiddleware::requireAuth()`. No direct file requires. |
| **C6** UTF-8 BOM present in several PHP entry files | All core changed files verified BOM-free (`3c3f70` = `<?php`): `app.php`, `Security.php`, `db_config.php`, `Database.php`, `bootstrap.php`, `AuthMiddleware.php`, both migration scripts, `header.php`, `admin-header.php`, `logout.php`. |
| **C7** `$base_url` inconsistency (`''` in header vs `/myuni/` in admin-header) | Single source of truth: `CONNECTMYUNI_BASE_URL` defined once in `bootstrap.php` from `config/app.php` → `env('APP_BASE_URL', '/ConnectMyUni/')`, normalized to leading/trailing slash. `header.php`, `admin-header.php`, and `AuthMiddleware::requireAuth()` all consume the constant. |
## 3. Implementation

### 3.1 Configuration — `backend/config/db_config.php` (new)

Canonical DB config array with layered resolution:

```
SUPABASE_DB_*  →  DB_* (legacy)  →  defaults
```

Resolved through `ConnectMyUni\Config\env()` (project `.env` loader) with a `getenv()` fallback closure for CLI scripts that do not load `app.php`. **No credentials stored in code.**

Keys: `host`, `port`, `dbname`, `username`, `password`, `sslmode` (default `require` for Supabase), `charset`, shared PDO options.

### 3.2 `env()` helper improved — `backend/config/app.php`

The helper previously read **only** the `.env` file. It now resolves in this order:

1. Real process environment (`getenv()`) — enables CLI/cron/container-injected values and `putenv()` in tests;
2. Parsed `.env` file values (cached in-process);
3. The supplied default.

`APP_BASE_URL=/ConnectMyUni/` added as `base_url`.

### 3.3 `Database.php` rewritten — `backend/config/Database.php`

- Loads `db_config.php` (no lowercase collision).
- Builds PostgreSQL DSN: `pgsql:host=…;port=…;dbname=…[;sslmode=…]`.
- **Neutral error contract:** on `PDOException`, the technical detail is written to `error_log()` server-side and only the generic message `Database connection failed. Check server logs for details.` is thrown. DSN/host/credentials are never exposed to users (validated in the smoke test below).

### 3.4 Bootstrap — `backend/bootstrap.php` (new)

- Idempotent guard (`CONNECTMYUNI_BOOTSTRAPPED`); safe to `require` from many pages in one request.
- Loads `config/app.php`, defines `CONNECTMYUNI_BASE_URL`, registers the autoloader.
- Returns the app config array to callers.

### 3.5 Entry-point wiring

Batch 1 (already in work tree):
- `frontend/components/header.php` — bootstraps backend, `$base_url = CONNECTMYUNI_BASE_URL`, CSS path → `frontend/assets/css/style.css?v=…`
- `frontend/admin/components/admin-header.php` — bootstrap + `AuthMiddleware::requireAuth()` + constant-based `$base_url`/`$admin_url`
- `backend/middleware/AuthMiddleware.php` — redirect URL uses `CONNECTMYUNI_BASE_URL . 'frontend/admin/login.php'`
- `frontend/admin/logout.php` — bootstrap instead of direct middleware require

Batch 2 (this session):
- `frontend/admin/index.php` — removed 6 direct repository `require_once`s; now bootstraps (classes autoload).
- All **23 stub admin entry points** (`login.php`, `change-password.php`, `contact-messages.php`, and the CRUD `index`/`create`/`edit`/`delete` pages for countries, events, hero-slides, services, universities) now `require_once __DIR__ . '/../../backend/bootstrap.php'` as the first statement.
- `frontend/public/contact.php` — bootstrapped.

### 3.6 Public-page include normalization

`index`, `about`, `services`, `events`, `event-detail`, `registration`, `updates`, `gallery`, `universities` — all `include 'components/header.php'` / `'components/footer.php'` replaced with **`__DIR__`-relative** `'../components/header.php'` / `'../components/footer.php'`, so pages render regardless of the current working directory.

The direct `require_once …/helpers/CacheManager.php` lines in `events.php`, `event-detail.php`, `registration.php`, `updates.php` were **left in place deliberately**: they are harmless (`require_once` dedupes against the autoloader class map) and preserve the working JSON-data fallback path exactly as-is (degradation-only migration policy).

### 3.7 `.env.example` completed

Canonical keys documented with placeholders only:

```
APP_BASE_URL=/ConnectMyUni/
SUPABASE_DB_HOST=db.<your-project-ref>.supabase.co
SUPABASE_DB_PORT=5432
SUPABASE_DB_DATABASE=postgres
SUPABASE_DB_USERNAME=postgres
SUPABASE_DB_PASSWORD=your-password-here
SUPABASE_DB_SSLMODE=require
```

Local `.env` remains untracked (gitignored). No real credentials exist anywhere.

## 4. Validation (all executed)

| Check | Result |
|---|---|
| `php -l` on all modified backend + migration files | 8/8 pass |
| `php -l` on all 38 frontend PHP files | 38/38 pass |
| Runtime suite (bootstrap idempotency, BASE_URL, 18 class resolutions, db_config env resolution incl. `putenv()`, AuthMiddleware URL, credential scan) | 33/33 pass |
| Final battery (no `config/database.php` refs, no literal DSN/credentials, canonical schema unmodified, 9 public pages `__DIR__`-based, 24 admin entry points bootstrapped, admin/index.php no direct repo requires) | 16/16 pass |
| **Smoke:** `header.php` renders static navbar without fatal | pass |
| **Smoke:** `admin-header.php` passes `requireAuth` with test session; DB attempt (no `pdo_pgsql` here) surfaces only the neutral error message — no DSN/credentials leaked | pass |
| **Smoke:** JSON `CacheManager` path degrades to `[]` without fatal when `data/` is absent | pass |
| BOM scan of all core changed files | none present |
| `.env` gitignored; `.env.example` tracked | confirmed |

## 5. Pre-existing Caveats (documented, NOT fixed per scope)

- Many admin pages and `frontend/public/contact.php` are **empty stubs** (login, change-password, contact-messages, and all CRUD create/edit/delete forms). Feature implementation is deferred to a later stage.
- `pdo_pgsql` is **not enabled** in the local PHP (`php -m` shows only `mysql`, `sqlite`). This blocks actual PDO connection testing; the hardened neutral-error path is what a user would see until the driver is enabled.
- Composer is not initialized (`composer.json` absent); the interim decision stands — `spl_autoload_register` is the autoloader.
- `frontend/components/footer.php` is a pure HTML fragment (no opening `<?php`) — pre-existing, harmless.

## 6. Manual Next Actions (operator)

1. Enable `pdo_pgsql` in `php.ini` (`extension=pdo_pgsql`, `extension=pgsql`) and restart the webserver.
2. Edit the local `.env` from `.env.example`: set real `SUPABASE_DB_*` values from Supabase → Settings → Database → Connection string.
3. Run the canonical schema `backend/database/schema/connectmyuni_initial_schema.sql` in the Supabase SQL Editor (Stage 03B).
4. Load `/ConnectMyUni/frontend/public/index.php` and `/ConnectMyUni/frontend/admin/login.php`; confirm the admin flow redirects to login when unauthenticated.
Local `.env` remains untracked (gitignored). No real credentials exist anywhere.