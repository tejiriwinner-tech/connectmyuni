# Connect MyUni — Stage 03A: File Migration Map

**Document ID:** CM-MIGRATION-03A  
**Date:** 2026-11-08  
**Stage:** Project Structure Refactoring  
**Status:** PLANNING  

---

## Purpose

This document maps every file in the current Connect MyUni project to its new location in the restructured frontend/backend architecture. No files will be moved until this map is complete and approved.

---

## Migration Summary

| Category | Count | Notes |
|----------|-------|-------|
| Frontend Public Pages | 13 | Move to frontend/public/ |
| Frontend Admin Pages | 20 | Move to frontend/admin/ |
| Frontend Components | 3 | Move to frontend/components/ |
| Frontend Assets | 18 | Move to frontend/assets/ |
| Backend Config | 4 | Move to backend/config/ |
| Backend Database | 3 | Move to backend/database/ |
| Backend Middleware | 1 | Move to backend/middleware/ |
| Backend Repositories | 9 | Move to backend/repositories/ |
| Backend Services | 5 | Move to backend/services/ |
| Backend Helpers | 1 | Move to backend/helpers/ |
| Storage | 0 | Create storage/ structure |
| Documentation | 5 | Already in docs/, reorganize |
| Root Config | 4 | Keep at root (.env, .gitignore, .htaccess, README) |
| **TOTAL** | **~82** | Including docs and config |

---

## Detailed Migration Map

### ROOT LEVEL FILES

| Old Location | New Location | Category | Reason | Dependencies | Risk |
|--------------|--------------|----------|--------|--------------|------|
| index.php | frontend/public/index.php | Frontend | Homepage - renders HTML | header.php, footer.php | LOW |
| about.php | frontend/public/about.php | Frontend | About page - renders HTML | header.php, footer.php | LOW |
| services.php | frontend/public/services.php | Frontend | Services page - renders HTML | header.php, footer.php | LOW |
| events.php | frontend/public/events.php | Frontend | Events page - renders HTML | EventService (backend) | MEDIUM |
| event-detail.php | frontend/public/event-detail.php | Frontend | Event detail - renders HTML | EventService (backend) | MEDIUM |
| updates.php | frontend/public/updates.php | Frontend | Updates page - renders HTML | EventService (backend) | MEDIUM |
| gallery.php | frontend/public/gallery.php | Frontend | Gallery page - renders HTML | GalleryRepository (backend) | MEDIUM |
| partner-universities.php | frontend/public/universities.php | Frontend | Universities page - renders HTML | header.php, footer.php | LOW |
| registration.php | frontend/public/registration.php | Frontend | Registration form - renders HTML | EventService (backend) | MEDIUM |
| contact.php | frontend/public/contact.php | Frontend | Contact form - renders HTML | ContactMessageRepository | LOW |
| header.php | frontend/components/header.php | Frontend | Shared UI component | base_url paths | LOW |
| footer.php | frontend/components/footer.php | Frontend | Shared UI component | base_url paths | LOW |
| style.css | frontend/assets/css/style.css | Frontend | Main stylesheet | None | LOW |
| test.php | **REMOVE** | N/A | Test file, not needed | None | NONE |
| ADMIN_GUIDE.md | docs/ADMIN_GUIDE.md | Documentation | Admin documentation | None | LOW |
| OPTIMIZATION_GUIDE.md | docs/OPTIMIZATION_GUIDE.md | Documentation | Optimization guide | None | LOW |

[Migration map continues in next section...]

### CONFIG FILES

| Old Location | New Location | Category | Reason | Dependencies | Risk |
|--------------|--------------|----------|--------|--------------|------|
| config/app.php | backend/config/app.php | Backend | Application configuration | All backend files | HIGH |
| config/Database.php | backend/config/Database.php | Backend | PDO connection class | All repositories | HIGH |
| config/database.php | backend/config/database.php | Backend | Database config array | Database.php | HIGH |
| config/Security.php | backend/config/Security.php | Backend | Security utilities | All forms, auth | HIGH |

### DATABASE FILES

| Old Location | New Location | Category | Reason | Dependencies | Risk |
|--------------|--------------|----------|--------|--------------|------|
| database/migrations/001_create_database_schema.sql | backend/database/migrations/001_create_database_schema.sql | Backend | Database schema | Migration tools | MEDIUM |
| database/migration/migrate_json_to_mysql.php | backend/database/migration/migrate_json_to_mysql.php | Backend | Migration tool | database.php config | MEDIUM |
| database/migration/simple_migrate.php | backend/database/migration/simple_migrate.php | Backend | Simple migration tool | database.php config | MEDIUM |

### MIDDLEWARE

| Old Location | New Location | Category | Reason | Dependencies | Risk |
|--------------|--------------|----------|--------|--------------|------|
| middleware/AuthMiddleware.php | backend/middleware/AuthMiddleware.php | Backend | Authentication middleware | Admin pages | HIGH |

### REPOSITORIES

| Old Location | New Location | Category | Reason | Dependencies | Risk |
|--------------|--------------|----------|--------|--------------|------|
| repositories/AdminUserRepository.php | backend/repositories/AdminUserRepository.php | Backend | Data access layer | Database.php | HIGH |
| repositories/ContactMessageRepository.php | backend/repositories/ContactMessageRepository.php | Backend | Data access layer | Database.php | HIGH |
| repositories/CountryRepository.php | backend/repositories/CountryRepository.php | Backend | Data access layer | Database.php | HIGH |
| repositories/EventRepository.php | backend/repositories/EventRepository.php | Backend | Data access layer | Database.php | HIGH |
| repositories/GalleryRepository.php | backend/repositories/GalleryRepository.php | Backend | Data access layer | Database.php | HIGH |
| repositories/HeroSlideRepository.php | backend/repositories/HeroSlideRepository.php | Backend | Data access layer | Database.php | HIGH |
| repositories/ServiceRepository.php | backend/repositories/ServiceRepository.php | Backend | Data access layer | Database.php | HIGH |
| repositories/TestimonialRepository.php | backend/repositories/TestimonialRepository.php | Backend | Data access layer | Database.php | HIGH |
| repositories/UniversityRepository.php | backend/repositories/UniversityRepository.php | Backend | Data access layer | Database.php | HIGH |

### SERVICES

| Old Location | New Location | Category | Reason | Dependencies | Risk |
|--------------|--------------|----------|--------|--------------|------|
| services/CountryService.php | backend/services/CountryService.php | Backend | Business logic | CountryRepository | HIGH |
| services/EventService.php | backend/services/EventService.php | Backend | Business logic | EventRepository | HIGH |
| services/HeroSlideService.php | backend/services/HeroSlideService.php | Backend | Business logic | HeroSlideRepository | HIGH |
| services/ServiceService.php | backend/services/ServiceService.php | Backend | Business logic | ServiceRepository | HIGH |
| services/UniversityService.php | backend/services/UniversityService.php | Backend | Business logic | UniversityRepository | HIGH |

### HELPERS

| Old Location | New Location | Category | Reason | Dependencies | Risk |
|--------------|--------------|----------|--------|--------------|------|
| includes/CacheManager.php | backend/helpers/CacheManager.php | Backend | Caching utility (deprecated) | data/events.json | MEDIUM |

### ADMIN PAGES

| Old Location | New Location | Category | Reason | Dependencies | Risk |
|--------------|--------------|----------|--------|--------------|------|
| admin/admin-header.php | frontend/admin/components/admin-header.php | Frontend | Admin layout component | base_url, CSS paths | HIGH |
| admin/index.php | frontend/admin/index.php | Frontend | Admin dashboard | EventService, CountryService, etc | HIGH |
| admin/login.php | frontend/admin/login.php | Frontend | Admin login form | AuthMiddleware, Security | HIGH |
| admin/logout.php | frontend/admin/logout.php | Frontend | Admin logout handler | AuthMiddleware | MEDIUM |
| admin/change-password.php | frontend/admin/change-password.php | Frontend | Change password form | AdminUserRepository, Security | HIGH |
| admin/contact-messages.php | frontend/admin/contact-messages.php | Frontend | Contact messages list | ContactMessageRepository | MEDIUM |
| admin/post-event.php | frontend/admin/events/create.php | Frontend | Event creation (legacy) | EventService | MEDIUM |
| admin/events/index.php | frontend/admin/events/index.php | Frontend | Events list | EventService | MEDIUM |
| admin/events/create.php | frontend/admin/events/create.php | Frontend | Create event form | EventService | MEDIUM |
| admin/events/edit.php | frontend/admin/events/edit.php | Frontend | Edit event form | EventService | MEDIUM |
| admin/events/delete.php | frontend/admin/events/delete.php | Frontend | Delete event handler | EventService | MEDIUM |
| admin/countries/index.php | frontend/admin/countries/index.php | Frontend | Countries list | CountryService | MEDIUM |
| admin/countries/create.php | frontend/admin/countries/create.php | Frontend | Create country form | CountryService | MEDIUM |
| admin/countries/edit.php | frontend/admin/countries/edit.php | Frontend | Edit country form | CountryService | MEDIUM |
| admin/countries/delete.php | frontend/admin/countries/delete.php | Frontend | Delete country handler | CountryService | MEDIUM |
| admin/universities/index.php | frontend/admin/universities/index.php | Frontend | Universities list | UniversityService | MEDIUM |
| admin/universities/create.php | frontend/admin/universities/create.php | Frontend | Create university form | UniversityService | MEDIUM |
| admin/universities/edit.php | frontend/admin/universities/edit.php | Frontend | Edit university form | UniversityService | MEDIUM |
| admin/universities/delete.php | frontend/admin/universities/delete.php | Frontend | Delete university handler | UniversityService | MEDIUM |
| admin/services/index.php | frontend/admin/services/index.php | Frontend | Services list | ServiceService | MEDIUM |
| admin/services/create.php | frontend/admin/services/create.php | Frontend | Create service form | ServiceService | MEDIUM |
| admin/services/edit.php | frontend/admin/services/edit.php | Frontend | Edit service form | ServiceService | MEDIUM |
| admin/services/delete.php | frontend/admin/services/delete.php | Frontend | Delete service handler | ServiceService | MEDIUM |
| admin/hero-slides/index.php | frontend/admin/hero-slides/index.php | Frontend | Hero slides list | HeroSlideService | MEDIUM |
| admin/hero-slides/create.php | frontend/admin/hero-slides/create.php | Frontend | Create hero slide form | HeroSlideService | MEDIUM |
| admin/hero-slides/edit.php | frontend/admin/hero-slides/edit.php | Frontend | Edit hero slide form | HeroSlideService | MEDIUM |
| admin/hero-slides/delete.php | frontend/admin/hero-slides/delete.php | Frontend | Delete hero slide handler | HeroSlideService | MEDIUM |

### ASSETS

| Old Location | New Location | Category | Reason | Dependencies | Risk |
|--------------|--------------|----------|--------|--------------|------|
| asset/gallery1.png - gallery8.png | frontend/assets/images/gallery/ | Frontend | Gallery images | gallery.php | LOW |
| asset/hero_image.png | frontend/assets/images/hero_image.png | Frontend | Hero image | index.php | LOW |
| asset/image1.png - image5.png | frontend/assets/images/about/ | Frontend | About page images | about.php | LOW |
| asset/logo.png | frontend/assets/images/logo.png | Frontend | Site logo | header.php, footer.php | LOW |
| asset/js/main.js | frontend/assets/js/main.js | Frontend | Unused JS (dead code) | None | LOW |
| js/main.js | frontend/assets/js/main.js | Frontend | Main JavaScript | Public pages | MEDIUM |

### DOCUMENTATION

| Old Location | New Location | Category | Reason | Dependencies | Risk |
|--------------|--------------|----------|--------|--------------|------|
| docs/01-project-audit.md | docs/01-project-audit.md | Documentation | Stage 01 audit | None | NONE |
| docs/02-backend-foundation.md | docs/02-backend-foundation.md | Documentation | Stage 02 backend | None | NONE |
| docs/json-data-inventory.md | docs/json-data-inventory.md | Documentation | JSON data inventory | None | NONE |
| docs/03a-file-migration-map.md | docs/03a-file-migration-map.md | Documentation | This document | None | NONE |
| **NEW** | docs/03a-project-structure-refactor.md | Documentation | Stage 03A report | This migration | NEW |

### BACKUP FILES

| Old Location | New Location | Category | Reason | Dependencies | Risk |
|--------------|--------------|----------|--------|--------------|------|
| backup_stage02/*.bak | **REMOVE** | N/A | Stage 02 backups, no longer needed | None | NONE |

### FILES TO CREATE

| New Location | Purpose | Source | Risk |
|--------------|---------|--------|------|
| frontend/public/.htaccess | Public frontend routing | Update existing .htaccess | HIGH |
| frontend/admin/.htaccess | Admin area protection | Create new | MEDIUM |
| storage/.gitkeep | Mark storage directory | Create empty | NONE |
| storage/uploads/.gitkeep | Mark uploads directory | Create empty | NONE |
| storage/uploads/hero/.gitkeep | Hero uploads | Create empty | NONE |
| storage/uploads/universities/.gitkeep | University uploads | Create empty | NONE |
| storage/uploads/countries/.gitkeep | Country uploads | Create empty | NONE |
| storage/uploads/events/.gitkeep | Event uploads | Create empty | NONE |
| storage/uploads/gallery/.gitkeep | Gallery uploads | Create empty | NONE |
| storage/cache/.gitkeep | Cache directory | Create empty | NONE |
| storage/logs/.gitkeep | Logs directory | Create empty | NONE |

### FILES TO REMOVE

| Old Location | Reason | Risk |
|--------------|--------|------|
| test.php | Test file, not part of application | NONE |
| backup_stage02/ directory | Stage 02 backups, no longer needed | NONE |
| admin/.ftpquota | FTP quota file, not needed | NONE |
| admin/README.md | Moved to docs/ | NONE |
| asset/js/main.js | Dead code, replaced by js/main.js | LOW |

## Path Update Requirements

### PHP Include/Require Paths

All files containing `require`, `require_once`, `include`, or `include_once` must be updated:

**Pattern to search for:**
```php
require
require_once
include
include_once
```

**Common old paths:**
- `config/`
- `includes/`
- `admin/`
- `repositories/`
- `services/`
- `middleware/`

**New path strategy:**
- Use `__DIR__` for relative paths within same module
- Use absolute paths from project root for cross-module references
- Example: `require_once __DIR__ . '/../../backend/config/Database.php';`

### Asset Paths

All references to CSS, JS, images must be updated:

**Pattern to search for:**
```html
<link rel="stylesheet" href="
<script src="
<img src="
background-image: url(
```

**Common old paths:**
- `style.css`
- `asset/
- `js/main.js`

**New paths:**
- `frontend/assets/css/style.css`
- `frontend/assets/js/main.js`
- `frontend/assets/images/`

### Upload Paths

All upload destinations must be updated:

**Old paths:**
- `data/uploads/` (if exists)

**New paths:**
- `storage/uploads/hero/`
- `storage/uploads/universities/`
- `storage/uploads/countries/`
- `storage/uploads/events/`
- `storage/uploads/gallery/`

### Database/Migration Paths

All references to database directories must be updated:

**Old paths:**
- `database/migrations/`
- `database/migration/`

**New paths:**
- `backend/database/migrations/`
- `backend/database/migration/`

---

## .htaccess Changes

### Current .htaccess (Root)

The existing `.htaccess` file handles:
- PHP 8.4 handler
- Trailing slash redirects
- Security headers

### Required Changes

1. **Keep root .htaccess** for security headers and PHP settings
2. **Add frontend routing** to route all requests through frontend/public/
3. **Protect backend/** from direct web access
4. **Protect storage/** from direct web access (except uploads if needed)

### New Routing Strategy

```apache
# Route all requests to frontend/public/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ frontend/public/$1 [L]

# Protect backend directory
<Directory "backend">
    Require all denied
</Directory>

# Protect storage directory (except uploads)
<Directory "storage">
    Require all denied
</Directory>

<Directory "storage/uploads">
    Require all granted
</Directory>
```

---

## Testing Strategy

### Pre-Migration
- [ ] Create Git commit: "refactor: separate frontend and backend architecture"
- [ ] Document current working state
- [ ] Test all public pages
- [ ] Test all admin pages
- [ ] Test database connection
- [ ] Test file uploads

### During Migration
- [ ] Move files in batches by category
- [ ] Update paths immediately after moving each batch
- [ ] Test after each batch

### Post-Migration
- [ ] Test all public pages (homepage, about, events, etc.)
- [ ] Test all admin pages (login, dashboard, CRUD)
- [ ] Test database connection
- [ ] Test file uploads
- [ ] Test mobile responsiveness (320px, 375px, 768px, 1024px, 1440px)
- [ ] Verify no broken links
- [ ] Verify no broken images
- [ ] Verify no broken CSS/JS
- [ ] Check Apache error logs
- [ ] Check PHP error logs

---

## Risk Mitigation

### High Risk Items
1. **Config files** - Will break everything if paths are wrong
   - Mitigation: Update all require paths systematically, test immediately
   
2. **Admin pages** - Many dependencies on backend services
   - Mitigation: Move admin pages last, test each page individually
   
3. **Asset paths** - Can break UI without PHP errors
   - Mitigation: Use browser dev tools to check for 404s

### Medium Risk Items
1. **Database migration paths** - Migration tools need correct paths
   - Mitigation: Test migration tools before and after move

2. **Upload paths** - File uploads may fail silently
   - Mitigation: Test file upload functionality explicitly

### Low Risk Items
1. **Documentation** - No functional impact
   - Mitigation: Simple file moves

---

## Rollback Plan

If critical issues are encountered:

1. **Git rollback:** `git reset --hard HEAD`
2. **Manual rollback:** Restore from backup_stage02/ if needed
3. **Incremental approach:** Move one category at a time, verify, then continue

---

## Next Steps

1. ✅ **COMPLETE** - Read existing documentation
2. ✅ **COMPLETE** - Create file inventory
3. ✅ **COMPLETE** - Create migration map (this document)
4. **PENDING** - Create backup/commit
5. **PENDING** - Create new directory structure
6. **PENDING** - Move files by category
7. **PENDING** - Update all paths
8. **PENDING** - Test thoroughly
9. **PENDING** - Create final documentation
10. **PENDING** - Generate final report

---

**Document End**

**Next Step:** Create Git backup, then begin file migration
