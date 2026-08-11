# Connect MyUni — Supabase PostgreSQL Schema Finalization
# Stage 03B

**Date:** 2026-11-08  
**Status:** Complete — Ready for review/execution in Supabase SQL Editor

---

## 1. Purpose

This document records the finalized PostgreSQL schema that will be executed in Supabase SQL Editor. It documents the decisions made during the MySQL → Supabase PostgreSQL migration and the final schema structure.

**Canonical Schema Location:** `backend/database/schema/connectmyuni_initial_schema.sql`

---

## 2. What Was Changed

### 2.1 Generic Trigger Function
Replaced the misleading per-table function name `update_admin_users_updated_at()` with a single generic function:

```sql
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
```

All 12 tables with an `updated_at` column now reference this single generic function.

### 2.2 Modern Identity Columns
Replaced `SERIAL` with modern PostgreSQL identity columns where safe:

```sql
id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY
```

Applied to all 13 tables. This does not break `$pdo->lastInsertId()` behavior — PostgreSQL returns the sequence/identity value on INSERT.

### 2.3 MySQL → PostgreSQL Conversions
- `AUTO_INCREMENT` → `GENERATED ALWAYS AS IDENTITY`
- `TINYINT(1)` → `BOOLEAN`
- `ENUM()` → `VARCHAR` + `CHECK` constraints
- `DATETIME` → `TIMESTAMP`
- `ON UPDATE CURRENT_TIMESTAMP` → triggers
- `UNSIGNED` → removed (not supported in PostgreSQL)
- `ENGINE=InnoDB` → removed
- `DEFAULT CHARSET/COLLATE` → removed (handled at database level)
- `LONGTEXT` → `TEXT`
- Backticks → removed (PostgreSQL uses double quotes only when needed)

### 2.4 Seed Data Cleanup
- **Admin email:** `admin@connectmyuni.net` (verified, no Markdown)
- **Country flag emojis:** replaced with two-letter country codes (GB, US, CA, AU, MY, PH) to avoid multi-byte encoding issues in seed files
- **Seed data:** 1 admin, 6 countries, 8 services

---

## 3. Final 13-Table Schema

| # | Table | Purpose | Security Classification |
|---|-------|---------|------------------------|
| 1 | admin_users | Admin authentication/authorization | 🔒 SENSITIVE |
| 2 | countries | Study destination countries | 🌐 PUBLIC |
| 3 | universities | Partner universities | 🌐 PUBLIC |
| 4 | services | Services offered | 🌐 PUBLIC |
| 5 | testimonials | Student success stories | 🌐 PUBLIC |
| 6 | gallery_images | Gallery images | 🌐 PUBLIC |
| 7 | hero_slides | Homepage hero banners | 🌐 PUBLIC |
| 8 | events | Webinars/workshops/announcements/videos | 🌐 PUBLIC (published only) |
| 9 | event_registrations | Event registrations | 🔒 SENSITIVE (PII) |
| 10 | contact_messages | Contact form submissions | 🔒 SENSITIVE (PII) |
| 11 | blog_posts | Blog articles/news | 🌐 PUBLIC (published only) |
| 12 | scholarships | Scholarship opportunities | 🌐 PUBLIC |
| 13 | ai_content_requests | AI content generation tracking | 🔒 SENSITIVE |

---

## 4. Relationships

### Foreign Key Relationships
```
countries.id
    <- universities.country_id        (ON DELETE CASCADE)
    <- scholarships.country_id        (ON DELETE SET NULL)

universities.id
    <- scholarships.university_id     (ON DELETE SET NULL)

events.id
    <- event_registrations.event_id   (ON DELETE CASCADE)

admin_users.id
    <- blog_posts.author_id           (ON DELETE SET NULL)
    <- ai_content_requests.admin_user_id (ON DELETE SET NULL)
```

### Cascading Behavior Decisions
- **CASCADE:** `universities.country_id`, `event_registrations.event_id`
  - Deleting a country removes its universities
  - Deleting an event removes its registrations
- **SET NULL:** All reference/admin relationships
  - Deleting a university/country keeps scholarship records (sets reference to NULL)
  - Deleting an admin keeps blog posts / AI requests (sets author to NULL)

---

## 5. Indexes

**Total: 43 indexes** across all tables. Each index is named `idx_<table>_<column>`.

### Index Summary by Table
| Table | Indexes |
|-------|---------|
| admin_users | 3 (username, email, is_active) |
| countries | 3 (slug, is_featured, sort_order) |
| universities | 3 (country_id, slug, is_featured) |
| services | 3 (slug, is_active, sort_order) |
| testimonials | 2 (is_featured, sort_order) |
| gallery_images | 3 (category, is_active, sort_order) |
| hero_slides | 2 (is_active, sort_order) |
| events | 5 (slug, category, status, event_date, created_at) |
| event_registrations | 3 (event_id, email, created_at) |
| contact_messages | 3 (email, status, created_at) |
| blog_posts | 4 (slug, status, published_at, author_id) |
| scholarships | 5 (slug, is_featured, deadline, university_id, country_id) |
| ai_content_requests | 4 (status, content_type, created_at, admin_user_id) |

---

## 6. Triggers

**Total: 12 triggers** — one per table with an `updated_at` column.

All use the generic function `update_updated_at_column()`:

```sql
CREATE TRIGGER trigger_<table>_updated_at
    BEFORE UPDATE ON <table>
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();
```

**Tables WITHOUT a trigger:** `event_registrations` (no `updated_at` column — insert-only table).

---

## 7. Check Constraints

| Table | Column | Constraint |
|-------|--------|------------|
| admin_users | role | IN ('admin','editor','viewer') |
| testimonials | rating | 1 to 5 |
| events | category | IN ('webinar','workshop','announcement','video') |
| events | status | IN ('draft','published','cancelled','archived') |
| contact_messages | status | IN ('new','read','replied','closed') |
| blog_posts | status | IN ('draft','published','archived') |
| ai_content_requests | status | IN ('pending','generated','reviewed','approved','rejected') |

---

## 8. Seed Data

**Total: 15 seed records**

| Entity | Count | Records |
|--------|-------|---------|
| admin_users | 1 | admin / admin@connectmyuni.net |
| countries | 6 | UK, US, Canada, Australia, Malaysia, Philippines |
| services | 8 | University Placement ... Career Counseling |

### Seed Insert Strategy
Uses `ON CONFLICT DO NOTHING` for idempotent execution (safe to run multiple times).

### Security
- **Admin password** is bcrypt-hashed via PHP `password_hash()` (matching the application's existing auth system)
- No plaintext password stored in the schema
- Email stored as plain string `'admin@connectmyuni.net'` — **no Markdown formatting**

---

## 9. Security Considerations

### 9.1 Sensitive Tables (Do NOT expose publicly)
These tables contain credentials or PII and should NOT be publicly readable/writable via Supabase Data API:

- `admin_users` — contains password_hash
- `contact_messages` — contains name, email, phone, message
- `event_registrations` — contains full_name, email, phone, field_of_study
- `ai_content_requests` — contains admin prompts and internal content

**Recommendation:** Enable Row Level Security (RLS) on these tables and do NOT grant `anon` role access. Only the `service_role` (server-side PHP backend) should access them.

### 9.2 Public-Facing Tables (Restricted to published/active)
These tables serve public content. Best practice: restrict public reads to only `is_active = TRUE` / `status = 'published'` records:

- `countries` — filter `is_featured`/`is_active`-aware
- `universities` — public but could be restricted
- `services` — filter `is_active = TRUE`
- `testimonials` — filter `is_featured`
- `gallery_images` — filter `is_active = TRUE`
- `hero_slides` — filter `is_active = TRUE`
- `events` — filter `status = 'published'`
- `blog_posts` — filter `status = 'published'`
- `scholarships` — public

### 9.3 Supabase Data API / RLS Strategy
- **Do NOT blindly enable anonymous access** to all tables.
- The PHP backend uses server-side database credentials (service role) and should be the only direct writer.
- Public content can be served read-only through the Data API with RLS policies scoped to `is_active`/`status`.
- **Recommendation for now:** Keep RLS OFF for the PHP backend path; if the Data API/anonymous role is enabled later, add RLS policies:
  - `anon` role: SELECT only on public tables, scoped to active/published records.
  - No `anon` INSERT/UPDATE/DELETE on any table.
  - `service_role`: full access (used by PHP backend).

### 9.4 Credentials
- **No credentials** are stored in the SQL schema file.
- Connection credentials remain in `.env` via `SUPABASE_DB_*` variables.
- The SQL file contains no hostnames, passwords, API keys, or service-role keys.

---

## 10. Supabase Setup Instructions

### Step 1: Create Database Tables
1. Log in to Supabase Dashboard
2. Open your project
3. Go to **SQL Editor**
4. Copy the entire contents of `backend/database/schema/connectmyuni_initial_schema.sql`
5. Paste into the SQL Editor
6. Click **Run**
7. Confirm success message

### Step 2: Verify Tables
1. Go to **Table Editor**
2. Confirm all 13 tables exist
3. Verify each table's columns and constraints

### Step 3: Verify Seed Data
1. Open each table in Table Editor
2. Confirm seed records exist:
   - admin_users: 1 record
   - countries: 6 records
   - services: 8 records

### Step 4: Enable pdo_pgsql (XAMPP)
1. Edit `C:\xampp\php\php.ini`
2. Uncomment `extension=pdo_pgsql` and `extension=pgsql`
3. Restart Apache

### Step 5: Configure .env
Ensure `.env` contains:
```
SUPABASE_DB_HOST=<host from Supabase Dashboard>
SUPABASE_DB_PORT=5432
SUPABASE_DB_NAME=postgres
SUPABASE_DB_USER=postgres
SUPABASE_DB_PASSWORD=<password>
SUPABASE_DB_SSLMODE=require
```

---

## 11. Compatibility Decisions

### 11.1 Identity Columns vs SERIAL
Chose `GENERATED ALWAYS AS IDENTITY` over `SERIAL` for:
- Modern PostgreSQL standard
- Safer (cannot accidentally insert duplicate IDs)
- Compatible with PHP `$pdo->lastInsertId()`

**Note:** `GENERATED ALWAYS` means manual ID insertion is not allowed. If the application ever needs to insert explicit IDs, this would need `GENERATED BY DEFAULT`. Current repositories always let the DB generate IDs, so `ALWAYS` is safe.

### 11.2 Country Flag Codes
Replaced emoji flags with two-letter country codes (GB, US, CA, AU, MY, PH) to:
- Avoid multi-byte/encoding issues in seed SQL files
- Keep character set consistent (ASCII-safe seed file)
- Allow rendering flags at the application layer

### 11.3 No Seeds for Other Tables
Universities, testimonials, gallery, events, blog posts, scholarships, contact messages, and AI requests start empty. This matches the current application state where these are managed via the admin panel.

---

## 12. Validation Results

### Static Checks Performed
| Check | Result |
|-------|--------|
| Number of tables | ✅ 13 |
| Number of indexes | ✅ 43 |
| Number of triggers | ✅ 12 |
| Number of functions | ✅ 1 |
| Seed INSERT statements | ✅ 3 |
| No AUTO_INCREMENT | ✅ |
| No ENGINE/CHARSET clauses | ✅ |
| No UNSIGNED | ✅ |
| No TINYINT | ✅ |
| No ENUM() | ✅ |
| No backticks | ✅ |
| No ON UPDATE CURRENT_TIMESTAMP in table defs | ✅ |
| No misleading trigger function name | ✅ |
| Generic trigger function present | ✅ |
| No Markdown in seed values | ✅ |
| No credentials in SQL | ✅ |

### Remaining Warnings
- **Entity/relation diagram** should be visually confirmed in Supabase after import.
- **`GENERATED ALWAYS AS IDENTITY`** prevents manual ID inserts — verify application never does this.
- **Country flag codes** changed from emoji to 2-letter codes — verify application display handles this.

---

## 13. Files

### Created
- `backend/database/schema/connectmyuni_initial_schema.sql` — Finalized canonical PostgreSQL schema

### Modified
- (this stage) — none beyond schema

### Deleted
- (none)

### Obsolete / Cleanup
- `docs/database/connectmyuni_initial_schema.sql` — an earlier MySQL/duplicate copy under docs (docs should contain documentation only). Recommend removing or moving out of docs.
- `docs/03b-database-initialization.md` — documentation of the earlier MySQL attempt; consider archiving.

---

## 14. Validation / Next Steps

1. Project owner reviews the SQL in `backend/database/schema/connectmyuni_initial_schema.sql`
2. Execute in Supabase SQL Editor
3. Confirm tables/seed data
4. Enable pdo_pgsql in XAMPP
5. Configure .env with Supabase credentials
6. Test connection and application
7. Update/remove obsolete docs/database schema copies

---

**Prepared for:** Connect MyUni project owner (manual review & execution)  
**Do not execute internally** — schema to be run in Supabase SQL Editor by owner.
