# Connect MyUni — JSON Data Inventory

**Stage 02 Documentation**  
**Date:** 2026-11-08  
**Status:** COMPLETE

---

## Purpose

This document inventories all JSON data sources in the Connect MyUni application, their current consumers, proposed database tables, and migration status.

**Important:** JSON files are NOT deleted during migration. They remain as backups until migration is verified complete.

---

## 1. events.json

### Current Location
- **Write path:** `data/events.json` (admin/post-event.php)
- **Read path:** `includes/data/events.json` (CacheManager.php) ⚠️ **BUG: Path mismatch**

### Current Purpose
Stores all event data created via the admin panel.

### Current PHP Consumers

| File | Usage |
|------|-------|
| `admin/post-event.php` | Writes new events |
| `admin/index.php` | Reads event stats |
| `includes/CacheManager.php` | Reads events, caches to `includes/data/.cache/events.cache.php` |
| `updates.php` | Displays events via CacheManager |
| `event-detail.php` | Loads single event via CacheManager |
| `registration.php` | Loads event title via CacheManager |

### Data Structure
```json
[
  {
    "eventId": "event-title-2024-01-01-123456",
    "title": "Event Title",
    "category": "webinar",
    "date": "2024-01-01",
    "description": "...",
    "image": "asset/image1.png"
  }
]
```

### Issues
- ⚠️ **BUG #1:** Admin writes to `data/events.json`, CacheManager reads from `includes/data/events.json`
- ⚠️ **BUG #2:** Key is `eventId` but CacheManager expects `id`
- ⚠️ **BUG #3:** No cache invalidation after admin writes
- ⚠️ **BUG #4:** events.php uses hardcoded JS array instead of this data

### Proposed Database Table
`events` (created in migration 001)

### Migration Status
🟡 **Pending** — Will be migrated once database is created and bugs are fixed

### Migration Priority
🔴 **P0 — Critical** — Event system is broken

---

## 2. registrations.json

### Current Location
- **Path:** `data/registrations.json` (registration.php)

### Current Purpose
Stores event registration submissions from the public.

### Current PHP Consumers

| File | Usage |
|------|-------|
| `registration.php` | Writes new registrations |

### Data Structure
```json
[
  {
    "id": "reg_abc123",
    "fullName": "John Doe",
    "email": "john@example.com",
    "phone": "+234801234567",
    "fieldOfStudy": "Computer Science",
    "eventLocation": "lagos",
    "eventId": "event-slug",
    "eventTitle": "Event Title",
    "registrationDate": "2024-01-01 12:00:00"
  }
]
```

### Issues
- No read/export functionality — registrations are write-only
- No admin interface to view registrations
- `eventId` references events that may not exist

### Proposed Database Table
`event_registrations` (created in migration 001)

### Migration Status
🟡 **Pending** — Lower priority than events

### Migration Priority
🟡 **P1** — Important but not breaking functionality

---

## 3. events.cache.php

### Current Location
- **Path:** `includes/data/.cache/events.cache.php`

### Current Purpose
Serialized PHP cache of events data to avoid repeated JSON reads.

### Issues
- Cache is never invalidated after admin writes
- Cache stores data with `eventId` key, but consumers expect `id`
- Cache file may grow indefinitely

### Proposed Solution
- **Replace** with database queries after DB migration

### Migration Status
🔴 **Deprecated** — Will be replaced by database queries

---

## 4. Hardcoded Data (Not JSON, But Equivalent)

These are not JSON files but contain hardcoded data that should eventually move to the database.

### 4.1 Gallery Items
- **File:** `gallery.php` (lines ~50-100)
- **Type:** PHP array
- **Items:** 8 gallery items
- **Proposed Table:** `gallery_items`
- **Priority:** 🟡 P2

### 4.2 Partner Universities
- **File:** `partner-universities.php`
- **Type:** PHP array
- **Items:** 6 countries, multiple universities
- **Proposed Tables:** `countries`, `universities`
- **Priority:** 🟡 P1

### 4.3 Services
- **File:** `services.php`, `index.php`
- **Type:** Hardcoded HTML/PHP arrays
- **Items:** 10 services
- **Proposed Table:** `services`
- **Priority:** 🟡 P1

### 4.4 Contact Information
- **Files:** `index.php`, `about.php`, `header.php`
- **Type:** Hardcoded in multiple locations
- **Issues:** Inconsistent (2 emails, 2 phones, PH WhatsApp)
- **Proposed Table:** `settings` (key-value store)
- **Priority:** 🟡 P1

---

## Migration Strategy

### Phase 1: Fix Bugs (Stage 02)
1. Fix CacheManager path mismatch
2. Fix eventId/id key mismatch
3. Add cache invalidation
4. Connect events.php to data source
5. Create missing contact.php

### Phase 2: Database Creation (Stage 02)
1. Execute migration 001
2. Verify tables created
3. Test connection

### Phase 3: Data Migration (Stage 03+)
1. Migrate events from JSON to MySQL
2. Migrate registrations from JSON to MySQL
3. Migrate hardcoded data to MySQL
4. Update all PHP consumers to use database
5. Deprecate CacheManager

### Phase 4: Cleanup (Stage 04+)
1. Archive JSON files to `backup/`
2. Remove CacheManager
3. Remove JSON write logic
4. Update .gitignore

---

## Rollback Plan

If migration fails:
1. JSON files are NOT deleted — rollback is immediate
2. Database tables can be dropped: `DROP DATABASE connect_myuni;`
3. Restore from `backup_stage02/` if needed
4. Existing public website remains functional throughout

