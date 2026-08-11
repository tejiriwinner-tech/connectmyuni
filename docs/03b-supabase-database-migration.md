# Connect MyUni — Supabase PostgreSQL Migration
# Stage 03B: Database Platform Migration

**Date:** 2026-11-08  
**Previous Database:** MySQL / XAMPP / phpMyAdmin  
**New Database:** Supabase PostgreSQL  

---

## 1. Why Connect MyUni Moved from MySQL to Supabase

### Benefits of Supabase
- **Managed PostgreSQL:** No need to manage MySQL server locally
- **Built-in Auth:** Future-ready for Supabase Auth integration
- **Auto-scaling:** Database scales with application growth
- **Real-time capabilities:** Ready for future real-time features
- **Backup & Recovery:** Automated backups managed by Supabase
- **Connection Pooling:** Built-in PgBouncer for PHP applications
- **Secure:** SSL-only connections, Row Level Security support

### Migration Drivers
- Eliminate local MySQL dependency for database
- Modern PostgreSQL feature set
- Better alignment with modern PHP frameworks
- Simplified deployment and hosting options

---

## 2. Previous MySQL Architecture

### Database Platform
- **Server:** XAMPP MySQL/MariaDB
- **Management:** phpMyAdmin
- **Connection:** Local MySQL socket
- **File:** `backend/config/database.php` (config array)
- **Class:** `backend/config/Database.php` (PDO singleton)
- **Schema:** `backend/database/migrations/001_create_database_schema.sql`

### Configuration
- Host: 127.0.0.1
- Port: 3306
- User: root
- Password: (empty)
- Database: connect_myuni
- Charset: utf8mb4

---

## 3. New PostgreSQL Architecture

### Database Platform
- **Server:** Supabase PostgreSQL
- **Management:** Supabase Dashboard / SQL Editor
- **Connection:** PostgreSQL TCP/IP with SSL
- **File:** `backend/config/database.php` (config array)
- **Class:** `backend/config/Database.php` (PDO singleton)
- **Schema:** `backend/database/schema/connectmyuni_initial_schema.sql`

### Configuration
- Host: db.xxxxxxxxxxxxxx.supabase.co (from Supabase Dashboard)
- Port: 5432
- User: postgres
- Password: (from Supabase Dashboard)
- Database: postgres (Supabase default)
- SSL Mode: require

---

## 4. Schema Conversion: MySQL → PostgreSQL

### Major Changes

#### 4.1 AUTO_INCREMENT → SERIAL
**MySQL:**
```sql
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
```

**PostgreSQL:**
```sql
id SERIAL PRIMARY KEY
```

**Applied to all 13 tables:**
- admin_users
- countries
- universities
- services
- testimonials
- gallery_images
- hero_slides
- events
- event_registrations
- contact_messages
- blog_posts
- scholarships
- ai_content_requests

#### 4.2 BOOLEAN/TINYINT Handling
**MySQL:**
```sql
is_active TINYINT(1) DEFAULT 0
is_featured TINYINT(1) DEFAULT 0
```

**PostgreSQL:**
```sql
is_active BOOLEAN DEFAULT FALSE
is_featured BOOLEAN DEFAULT FALSE
```

**Note:** PostgreSQL has native BOOLEAN type. Application code should treat these as true/false.

#### 4.3 ENUM → CHECK Constraints
**MySQL:**
```sql
status ENUM('draft', 'published', 'archived') DEFAULT 'draft'
category ENUM('webinar', 'workshop', 'announcement', 'video')
```

**PostgreSQL:**
```sql
status VARCHAR(20) DEFAULT 'draft' CHECK (status IN ('draft', 'published', 'archived'))
category VARCHAR(20) DEFAULT 'announcement' CHECK (category IN ('webinar', 'workshop', 'announcement', 'video'))
```

**Rationale:** PostgreSQL CHECK constraints provide similar validation while maintaining flexibility.

#### 4.4 DATETIME → TIMESTAMP
**MySQL:**
```sql
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

**PostgreSQL:**
```sql
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

**Note:** `ON UPDATE CURRENT_TIMESTAMP` is replaced with triggers (see section 4.5).

#### 4.5 Automatic Timestamps → Triggers
**MySQL:**
```sql
updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

**PostgreSQL:**
```sql
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- Plus a trigger:
CREATE OR REPLACE FUNCTION update_tablename_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER trigger_tablename_updated_at
    BEFORE UPDATE ON tablename
    FOR EACH ROW
    EXECUTE FUNCTION update_tablename_updated_at();
```

**Applied to:** admin_users, countries, universities, services, testimonials, gallery_images, hero_slides, events, contact_messages, blog_posts, scholarships, ai_content_requests

#### 4.6 UNSIGNED Removed
**MySQL:**
```sql
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
```

**PostgreSQL:**
```sql
id SERIAL PRIMARY KEY
```

**Note:** PostgreSQL does not support UNSIGNED integers. SERIAL provides sufficient range for application needs.

#### 4.7 TINYINT → SMALLINT/INTEGER
**MySQL:**
```sql
rating TINYINT UNSIGNED DEFAULT 5
views_count INT UNSIGNED DEFAULT 0
```

**PostgreSQL:**
```sql
rating SMALLINT DEFAULT 5 CHECK (rating >= 1 AND rating <= 5)
views_count INTEGER DEFAULT 0
```

#### 4.8 Backticks Removed
**MySQL:**
```sql
SELECT * FROM `table_name`
```

**PostgreSQL:**
```sql
SELECT * FROM table_name
```

**Note:** PostgreSQL uses double quotes for identifiers if needed, but unquoted is standard.

#### 4.9 TEXT Types
**MySQL:**
```sql
content LONGTEXT NOT NULL
```

**PostgreSQL:**
```sql
content TEXT NOT NULL
```

**Note:** PostgreSQL TEXT type has no length limit, equivalent to MySQL LONGTEXT.

#### 4.10 Engine/Charset Removed
**MySQL:**
```sql
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**PostgreSQL:**
```sql
-- No engine or charset specification needed
```

**Note:** PostgreSQL handles storage and encoding at database level.

---

## 5. Supabase Project Configuration

### Required Supabase Settings

#### 5.1 Create Supabase Project
1. Go to https://supabase.com
2. Sign up / Log in
3. Click "New Project"
4. Enter project name: "Connect MyUni"
5. Set database password (save this securely)
6. Select region closest to users
7. Click "Create new project"

#### 5.2 Get Connection Details
1. In Supabase Dashboard, go to **Settings** → **Database**
2. Scroll to **Connection string**
3. Select **"Direct connection"** or **"Connection pooling"**
   - **Direct connection:** For development/testing
   - **Connection pooling:** For production (uses PgBouncer)
4. Copy the connection string or individual parameters:
   - Host: db.xxxxxxxxxxxxxx.supabase.co
   - Port: 5432
   - Database: postgres
   - User: postgres
   - Password: (your project password)

#### 5.3 Enable Required Extensions
Supabase typically includes these by default, but verify:
- `uuid-ossp` (if using UUIDs in future)
- `pgcrypto` (for additional encryption if needed)

---

## 6. Environment Variables

### .env File Format
```env
# Supabase PostgreSQL Database
SUPABASE_DB_HOST=db.xxxxxxxxxxxxxx.supabase.co
SUPABASE_DB_PORT=5432
SUPABASE_DB_NAME=postgres
SUPABASE_DB_USER=postgres
SUPABASE_DB_PASSWORD=your-password-here
SUPABASE_DB_SSLMODE=require

# Application
APP_ENV=local
APP_DEBUG=true
SESSION_NAME=connectmyuni_session
SESSION_LIFETIME=1440
```

### Environment Variable Reference
| Variable | Purpose | Example Value | Required |
|----------|---------|---------------|----------|
| SUPABASE_DB_HOST | Supabase database host | db.abc123.supabase.co | YES |
| SUPABASE_DB_PORT | PostgreSQL port | 5432 | YES |
| SUPABASE_DB_NAME | Database name | postgres | YES |
| SUPABASE_DB_USER | Database user | postgres | YES |
| SUPABASE_DB_PASSWORD | Database password | (from Supabase) | YES |
| SUPABASE_DB_SSLMODE | SSL connection mode | require | YES |
| APP_ENV | Application environment | local | YES |
| APP_DEBUG | Debug mode | true/false | YES |

**Security Note:** Never commit .env file to version control. It is already in .gitignore.

---

## 7. Connection Method

### PHP PDO PostgreSQL Connection

**DSN Format:**
```
pgsql:host=db.xxxxxxxxxxxxxx.supabase.co;port=5432;dbname=postgres;sslmode=require
```

**Connection Code:**
```php
$dsn = sprintf(
    'pgsql:host=%s;port=%d;dbname=%s;sslmode=%s',
    $config['host'],
    $config['port'],
    $config['dbname'],
    $config['sslmode']
);

$pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
```

### SSL Requirement
Supabase requires SSL connections. The `sslmode=require` parameter enforces:
- SSL/TLS encryption for all database connections
- Protection against man-in-the-middle attacks
- Compliance with Supabase security requirements

---

## 8. Security Considerations

### 8.1 Credentials Management
- ✅ Credentials stored in .env (not hardcoded)
- ✅ .env in .gitignore (not committed)
- ✅ SSL required for all connections
- ✅ Error messages logged, not displayed to users

### 8.2 SQL Injection Prevention
- ✅ All queries use prepared statements
- ✅ PDO::ATTR_EMULATE_PREPARES = false (native prepares)
- ✅ No string concatenation in queries

### 8.3 Row Level Security (RLS)
Supabase supports PostgreSQL RLS. **Current Status: NOT ENABLED**

**Rationale:** 
- Application uses PHP backend with service role credentials
- RLS is primarily for direct client-side access
- Current architecture authenticates at application layer
- Enabling RLS would require rewriting queries to include user context

**When to Enable RLS:**
- If adding direct client-side database access
- If implementing multi-tenant data isolation
- If exposing API via Supabase client SDKs

**Recommended Approach:**
- Keep RLS disabled for now
- Use application-level authentication and authorization
- Revisit if architecture changes to include client-side DB access

### 8.4 Password Security
- ✅ Passwords hashed with PHP password_hash() (bcrypt)
- ✅ Default admin password: admin123 (hashed, not plaintext)
- ✅ Password verification via password_verify()

---

## 9. RLS Strategy (Recommended)

### Current Strategy: Application-Level Security
- Authentication: PHP session-based
- Authorization: Admin role checks in PHP
- Data Access: Direct SQL via PDO

### Tables and Recommended RLS Status

| Table | RLS Recommended | Reason |
|-------|----------------|--------|
| admin_users | NO | Backend-only access, sensitive credentials |
| countries | NO | Public data, no sensitive info |
| universities | NO | Public data, no sensitive info |
| services | NO | Public data, no sensitive info |
| testimonials | NO | Public data, no sensitive info |
| gallery_images | NO | Public data, no sensitive info |
| hero_slides | NO | Public data, no sensitive info |
| events | NO | Public data, admin manages via backend |
| event_registrations | NO | Sensitive PII, backend-only access |
| contact_messages | NO | Sensitive PII, backend-only access |
| blog_posts | NO | Mixed public/private, backend manages |
| scholarships | NO | Public data, no sensitive info |
| ai_content_requests | NO | Admin-only data |

### Future RLS Implementation (If Needed)
If RLS is needed in the future:
1. Enable RLS on specific tables
2. Create policies using `auth.uid()` for user context
3. Use `service_role` key for admin operations
4. Create separate policies for INSERT/UPDATE/DELETE

---

## 10. Migration/Testing Procedure

### Phase 1: Preparation (Current Phase)
- ✅ Audit MySQL-specific code
- ✅ Convert schema to PostgreSQL
- ✅ Update database configuration files
- ✅ Create environment variable templates
- ✅ Document migration process

### Phase 2: Environment Setup (Pending)
1. **Enable pdo_pgsql in PHP**
   - Edit `C:\xampp\php\php.ini`
   - Uncomment: `extension=pdo_pgsql`
   - Uncomment: `extension=pgsql`
   - Restart Apache

2. **Create Supabase Project**
   - Sign up at https://supabase.com
   - Create new project
   - Save connection credentials

3. **Configure .env**
   - Copy .env.example to .env
   - Add Supabase credentials
   - Verify SUPABASE_DB_SSLMODE=require

### Phase 3: Database Creation (Pending)
1. Open Supabase Dashboard
2. Go to **SQL Editor**
3. Copy contents of `backend/database/schema/connectmyuni_initial_schema.sql`
4. Paste into SQL Editor
5. Click **"Run"**
6. Verify: Check **Table Editor** for 13 tables

### Phase 4: Application Testing (Pending)
1. Test database connection
2. Test admin login
3. Test CRUD operations
4. Test public pages
5. Verify seed data

### Phase 5: Data Migration (If Needed)
If migrating from existing MySQL database:
1. Export MySQL data as CSV
2. Transform data types if needed
3. Import via Supabase Dashboard or psql
4. Verify data integrity

---

## 11. Repository Compatibility Review

### Overview
All repositories use standard PDO prepared statements, which are compatible with PostgreSQL.

### Key Findings

#### 11.1 Query Compatibility
✅ **INSERT queries:** Use placeholders (?) — PostgreSQL compatible
✅ **SELECT queries:** Standard SQL — PostgreSQL compatible
✅ **UPDATE queries:** Standard SQL — PostgreSQL compatible
✅ **DELETE queries:** Standard SQL — PostgreSQL compatible
✅ **JOIN queries:** Standard SQL — PostgreSQL compatible

#### 11.2 lastInsertId() Usage
**Files using lastInsertId():**
- AdminUserRepository.php (line 130)
- ContactMessageRepository.php (line 65)
- CountryRepository.php (line 84)
- EventRepository.php (line 127)
- GalleryRepository.php (line 77)
- HeroSlideRepository.php (line 79)
- ServiceRepository.php (line 83)
- TestimonialRepository.php (line 79)
- UniversityRepository.php (line 106)

**PostgreSQL Compatibility:** ✅ Compatible
- `$pdo->lastInsertId()` works with PostgreSQL SERIAL types
- PostgreSQL sequences are automatically used
- No code changes required

#### 11.3 No MySQL-Specific Functions Found
✅ No use of MySQL-specific functions:
- NO `NOW()` — Uses application-level timestamps
- NO `CURDATE()` — Not used
- NO `DATE_FORMAT()` — Not used
- NO `GROUP_CONCAT()` — Not used
- NO `IFNULL()` — Not used
- NO backticks — Not used

#### 11.4 Boolean Handling
**Change Required in Application Code:**

MySQL:
```php
$isActive = 1; // or 0
```

PostgreSQL:
```php
$isActive = true; // or false
```

**Impact:** Low — Most boolean fields are used internally

---

## 12. Required PHP Configuration

### Enable pdo_pgsql Extension

**File:** `C:\xampp\php\php.ini`

**Changes:**
```ini
; Uncomment these lines:
extension=pdo_pgsql
extension=pgsql
```

**Verification:**
```bash
php -m | findstr pgsql
```

**Expected Output:**
```
pdo_pgsql
pgsql
```

**Status:** ❌ NOT YET ENABLED — Requires manual php.ini edit

---

## 13. Schema Differences Summary

| Feature | MySQL | PostgreSQL | Impact |
|---------|-------|------------|--------|
| Auto-increment | AUTO_INCREMENT | SERIAL | Schema converted |
| Booleans | TINYINT(1) | BOOLEAN | Schema converted |
| Enums | ENUM() | CHECK constraint | Schema converted |
| Timestamps | DATETIME | TIMESTAMP | Schema converted |
| Auto-update | ON UPDATE CURRENT_TIMESTAMP | Triggers | Schema converted |
| Unsigned | UNSIGNED | (not supported) | Removed (no impact) |
| Engine | ENGINE=InnoDB | (not needed) | Removed |
| Charset | DEFAULT CHARSET | (not needed) | Removed |
| Text types | LONGTEXT | TEXT | Schema converted |

---

## 14. Supabase-Specific Considerations

### 14.1 Connection Pooling
Supabase provides PgBouncer for connection pooling:
- **Transaction pooling:** Recommended for PHP apps
- **Connection string:** Use pooler hostname for production
- **Max connections:** Managed by Supabase

### 14.2 Database Size Limits
- **Free tier:** 500MB database size
- **Pro tier:** 8GB database size
- **Current schema:** Approximately 1-2MB

### 14.3 Backup Strategy
- Supabase provides automatic daily backups
- Point-in-time recovery available on Pro plans
- Manual exports via Dashboard or pg_dump

### 14.4 Performance
- Supabase uses connection pooling (PgBouncer)
- Queries should be optimized with proper indexes
- All indexes from MySQL schema have been preserved

---

## 15. Rollback Considerations

### If Migration Fails

#### Option 1: Revert to MySQL
1. Keep original `backend/config/database.php` backup
2. Restore MySQL configuration
3. Re-import MySQL schema if needed
4. Update .env with MySQL credentials

#### Option 2: Fix PostgreSQL Issues
1. Check Supabase logs in Dashboard
2. Verify PHP pdo_pgsql is enabled
3. Check SSL configuration
4. Review error logs

### Rollback Checklist
- [ ] Original MySQL schema preserved
- [ ] Database configuration backup available
- [ .env backup available
- [ ] Migration documented for reversal

---

## 16. Testing Checklist

### Database Connection
- [ ] pdo_pgsql extension enabled in PHP
- [ ] Supabase project created
- [ ] .env configured with Supabase credentials
- [ ] Database connection test passes
- [ ] SSL connection verified

### Schema
- [ ] All 13 tables created in Supabase
- [ ] All indexes created
- [ ] All triggers created
- [ ] Foreign keys verified
- [ ] Seed data inserted

### Application
- [ ] Admin login works
- [ ] Public pages load
- [ ] CRUD operations work
- [ ] No SQL errors in logs
- [ ] lastInsertId() works correctly

---

## 17. Known Limitations

### Current Limitations
1. **pdo_pgsql not enabled:** Requires manual php.ini edit
2. **No Supabase Auth integration:** Using PHP sessions (future enhancement)
3. **No Supabase Storage:** Using local storage (future enhancement)
4. **No Row Level Security:** Using application-level auth (acceptable for current architecture)

### Future Enhancements
- Supabase Auth integration
- Supabase Storage for uploads
- Row Level Security if needed
- Real-time subscriptions
- Edge Functions for serverless logic

---

## 18. Support and Resources

### Supabase Documentation
- https://supabase.com/docs
- https://supabase.com/docs/reference/javascript
- https://supabase.com/docs/guides/database

### PostgreSQL Documentation
- https://www.postgresql.org/docs/
- https://www.postgresql.org/docs/current/tutorial.html

### PHP PDO Documentation
- https://www.php.net/manual/en/ref.pdo-pgsql.php
- https://www.php.net/manual/en/pdo.connections.php

---

## 19. Migration Status

### Completed
- ✅ MySQL to PostgreSQL schema conversion
- ✅ Database configuration updated
- ✅ Environment variable templates created
- ✅ Documentation complete
- ✅ Repository audit completed

### Pending (Requires Lead Input)
- ⏳ Enable pdo_pgsql in PHP
- ⏳ Create Supabase project
- ⏳ Configure .env with Supabase credentials
- ⏳ Import schema into Supabase
- ⏳ Test database connection
- ⏳ Test application functionality

### Blocked
- ❌ Cannot test connection without Supabase credentials
- ❌ Cannot enable pdo_pgsql without manual php.ini edit

---

## 20. Contact and Next Steps

### Next Steps for Lead
1. Review this migration plan
2. Create Supabase project
3. Provide Supabase connection credentials (securely)
4. Enable pdo_pgsql in XAMPP PHP
5. Import schema into Supabase
6. Configure .env with provided credentials
7. Test database connection
8. Test application functionality

### Questions for Lead
1. Which Supabase region should be used? (Choose closest to users)
2. Should we use direct connection or connection pooling?
3. Do you have a preferred Supabase project name?
4. When should we enable pdo_pgsql in PHP?

---

**Migration prepared by:** Connect MyUni Development Team  
**Date:** 2026-11-08  
**Status:** Ready for Supabase connection configuration
