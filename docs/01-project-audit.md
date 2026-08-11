# Connect MyUni — Stage 01: Project Audit & Architecture Review

**Document ID:** CM-AUDIT-01  
**Date:** 2026-11-08  
**Stage:** Discovery / Audit  
**Status:** COMPLETE  
**Environment:** XAMPP / Apache / PHP 8.4 (local development)

---

## 1. Project Overview

**Connect MyUni** is a study-abroad consultancy website built as a PHP application running on XAMPP/Apache. It serves as a public-facing marketing and information portal for an education consultancy business based in Nigeria, targeting Nigerian students seeking international education opportunities.

The application includes public pages: Home, About, Services, Partner Universities, Events, Updates, Gallery, Event Registration, and Event Detail. It includes a basic admin dashboard for posting events. There is **no database** — all dynamic data is stored in JSON files and cached as PHP-serialized cache files.

**Key Statistics:**

| Metric | Value |
|---|---|
| PHP files | 22 |
| CSS files | 1 main (style.css, 3,350 lines), 2 inline blocks |
| JS files | 2 local + 5 inline script blocks |
| Image assets | 14 PNG files |
| Database | None (JSON file-based storage) |
| Authentication | None |

---

## 2. Existing Architecture

### 2.1 File Structure

```
connectmyuni/
├── .htaccess                        # Apache rewrite + PHP handler
├── index.php                        # Homepage (hero, cards, about, services, events carousel, contact)
├── about.php                        # About page (profile, services, universities, why, embark, contact)
├── services.php                     # Services page (accordion)
├── events.php                       # Events page (JS-rendered, hardcoded — NOT connected to admin)
├── event-detail.php                 # Event detail page (CacheManager)
├── updates.php                      # Updates page (CacheManager → data/events.json)
├── gallery.php                      # Gallery page (PHP-rendered masonry grid + lightbox)
├── partner-universities.php         # Partner universities page (hardcoded PHP arrays)
├── registration.php                 # Registration form (writes to data/registrations.json)
├── header.php                       # Public header ($base_url = '')
├── footer.php                       # Public footer ($base_url fallback = '/myuni/')
├── style.css                        # Main stylesheet (3,350 lines)
├── test.php                         # UTF-16 encoded PHP info file
├── ADMIN_GUIDE.md                   # Admin quick-start guide
├── OPTIMIZATION_GUIDE.md            # Performance optimization (claims unimplemented features)
├── js/main.js                       # Public JS (smooth scroll, nav, form)
├── asset/js/main.js                 # Unused scroll animation handler (DEAD CODE)
├── includes/CacheManager.php        # JSON file caching class (events only)
├── admin/
│   ├── admin-header.php             # Admin layout (dark theme, inline CSS)
│   ├── index.php                    # Admin dashboard (event stats, quick actions)
│   ├── post-event.php               # Event creation form (Create only)
│   ├── README.md                    # Admin documentation
│   └── .ftpquota                    # FTP quota file
└── docs/                            # Documentation (created Stage 01)
```

### 2.2 Request Flow

Traditional PHP template pattern — no front controller, no router.

```
[Request] → [PHP File] → [header.php] → [Page Content] → [footer.php] → [Response]
[Admin Request] → [admin-header.php] → [Admin Content] → [footer.php] → [Response]
```

### 2.3 Critical Path Mismatches (BUGS CONFIRMED)

**BUG #1: CacheManager path mismatch**
- CacheManager.php reads from: `includes/data/events.json`
- admin/post-event.php writes to: `data/events.json`
- **Result**: Events posted via admin are invisible to public pages.

**BUG #2: ID key mismatch**
- Admin stores event with key `eventId` (compact('eventId', ...))
- CacheManager searches for `$event['id']`
- **Result**: Event detail pages always show "Event Not Found".

**BUG #3: events.php disconnected**
- events.php has hardcoded JavaScript events array (6 events)
- Never reads from CacheManager or data/events.json
- **Result**: Admin events never appear on events.php.

**BUG #4: contact.php missing**
- Referenced in gallery.php (line 181) and partner-universities.php (line 225)
- File does not exist — broken links.

**BUG #5: No cache invalidation**
- CacheManager::clearEventsCache() is never called after admin writes.
- **Result**: Stale cached events served after new posts.

### 2.4 Technology Stack

| Layer | Technology | Source |
|---|---|---|
| Runtime | PHP 8.4 | .htaccess EA-PHP handler |
| Web Server | Apache | XAMPP, mod_rewrite enabled |
| CSS | Bootstrap 5.3.0 | CDN (jsdelivr) |
| Icons | Font Awesome 6.4.0 (public), 6.5.0 (admin) | CDN (cdnjs) |
| Fonts | Poppins (public), DM Sans/Mono (admin) | CDN (googleapis) |
| JS | Bootstrap bundle 5.3.0 | CDN (jsdelivr, deferred) |
| Cache | PHP file cache | Custom CacheManager, 1hr TTL |
| Storage | JSON files | file_get_contents/put |
| DB | None | — |

---

## 3. Existing Features — Public Website

#### Homepage (`index.php`)
- **Hero section**: Static background image (`hero_image.png`) via CSS, dark overlay, headline text, subtitle, CTA button
- **Hero cards**: Three service cards (Student-Centered Approach, Tailored Placement Services, Compliance)
- **About section**: Two-column with text and 6-image collage
- **Services section**: Bootstrap accordion with 7 service items
- **Approach section**: Red hero background with image and CTA
- **Events carousel**: Bootstrap carousel with 3 hardcoded event cards per slide (6 total)
- **Contact section**: Google Maps embed, logo, address, phone, email, WhatsApp
- **Footer**: Copyright + credit

#### About (`about.php`)
- Page header with breadcrumb; blue gradient hero; 8-image collage
- Company profile (3 paragraphs); Services list (4 items)
- Partner Universities (3 hardcoded); Why Choose Us (6 items)
- Embark CTA; Contact info; Newsletter form

#### Services (`services.php`)
- 10-item accordion (more detailed than homepage)
- Custom JS toggleAccordion; Image on right (background1.png)

#### Events (`events.php`)
- ⚠️ **Disconnected from admin system** — hardcoded JS events array (6 events)
- Filter buttons (All, Webinars, Workshops, Videos)
- JS DOM rendering — no CacheManager usage

#### Updates (`updates.php`)
- ✅ Uses CacheManager::getEvents() from data/events.json
- Filter buttons; PHP grid with lazy-loaded images (SVG placeholders)
- Links to event-detail.php and registration.php

#### Gallery (`gallery.php`)
- 8 hardcoded PHP gallery items with captions and categories
- Masonry grid (CSS columns); Custom lightbox (prev/next/close, keyboard nav)
- Category filtering with animation; CTA strip

#### Partner Universities (`partner-universities.php`)
- Hardcoded 6 countries (USA, UK, Malaysia, Philippines, Canada, Australia)
- 3 featured partner cards; Country filter tabs (JS); Table with flag emojis
- ⚠️ **Broken link** to missing contact.php

#### Registration (`registration.php`)
- Form: Full Name, Email, Phone, Field of Study, Event Location
- Writes to data/registrations.json; Basic validation; No email sending

#### Event Detail (`event-detail.php`)
- ⚠️ **BUG**: CacheManager searches `$event['id']` but admin stores `eventId`
- Displays image, date, category, description, details, etc.
- Social sharing buttons; Related events by category

#### Contact Page
- ⚠️ **Missing** — referenced from gallery.php and partner-universities.php

### 3.2 Admin Panel

Accessible at `/admin/` with **no authentication required**.

#### Admin Dashboard (`admin/index.php`)
- Reads event stats from data/events.json (same path bug as CacheManager)
- Stat cards: Total Events, Webinars, Workshops, Videos
- Quick actions: Post New Event, View Events, Visit Live Site
- "Coming soon": Analytics, Manage Users, Settings
- Recent events list (last 5); Getting started tips

#### Post Event (`admin/post-event.php`)
- **Create only** — no Edit/Delete/Read list
- Form fields: Title, Category, Date, Description, Details, What to Expect, Image Path,
  Registration Link, Duration, Capacity, Requirements, Contact Info
- Writes to data/events.json with key `eventId` (BUG: should be `id`)
- No file upload — image path entered as text

#### Admin Layout (`admin/admin-header.php`)
- Dark theme (~700 lines inline CSS)
- Fixed sidebar (240px); Fixed topbar (60px) with live JS clock
- Mobile sidebar toggle; DM Sans + DM Mono fonts
- ⚠️ **Includes `../footer.php`** — loads Bootstrap CSS again, causing duplication

### 3.3 Admin Capabilities Matrix

| Feature | Status | Implementation |
|---|---|---|
| Post Event | ✅ Existing | admin/post-event.php → data/events.json |
| View Events (dashboard) | ✅ Existing | Read from data/events.json |
| Edit Event | ❌ Missing | — |
| Delete Event | ❌ Missing | — |
| Manage Users | ⚠️ Placeholder | Disabled, "Coming soon" |
| Site Settings | ⚠️ Placeholder | Disabled, "Coming soon" |
| Analytics | ⚠️ Placeholder | Disabled, "Coming soon" |
| Hero Slides | ❌ Missing | — |
| Gallery Management | ❌ Missing | — |
| Testimonials | ❌ Missing | — |
| University Management | ❌ Missing | — |
| Admin Auth | ❌ Missing | No login system |

---

## 4. Hard-Coded Content Inventory

| Category | File(s) | Storage | Should be DB? |
|---|---|---|---|
| Hero headline/subtitle/CTA | index.php | Hard-coded HTML | ✅ Yes |
| Hero background image | style.css | CSS background-image | ✅ Yes |
| Service cards (homepage) | index.php | Hard-coded HTML | ✅ Yes |
| Service accordion (services.php) | services.php | Hard-coded HTML | ✅ Yes |
| About text content | about.php | Hard-coded HTML | Partial |
| Collage/hero images | Multiple | Hard-coded img/background | ✅ Yes (Media library) |
| Company profile | about.php | Hard-coded HTML | Partial |
| Partner universities | partner-universities.php | Hard-coded PHP array | ✅ Yes |
| Why Choose items | about.php | Hard-coded HTML | ✅ Yes |
| Embark section text | about.php | Hard-coded HTML | Partial |
| Contact info | Multiple (inconsistent!) | Hard-coded HTML | ✅ Yes (Settings table) |
| Newsletter text | about.php | Hard-coded HTML | Partial |
| Gallery items (8) | gallery.php | Hard-coded PHP array | ✅ Yes |
| Homepage events carousel | index.php | Hard-coded HTML | ✅ Yes |
| events.php events | events.php | Hard-coded JS array | ✅ Yes (should share Events table) |
| Testimonials | (none found) | — | ✅ Yes (Testimonials table) |
| Statistics (admin) | admin/index.php | Hard-coded inline styles | ✅ Yes |
| Navigation menu | header.php | Hard-coded PHP array | ✅ Yes (Menu table) |
| Footer text | footer.php | Hard-coded with date() | ✅ Yes (Settings table) |

**⚠️ Contact Info Inconsistency**: Two different emails (.net vs .org), two phone numbers, WhatsApp is a Philippine number (+63) while phones are Nigerian (+234).

---

## 5. Hero Carousel Analysis

### 5.1 Current Hero (NOT a carousel)

Single static background image: `asset/hero_image.png` via CSS `background-image` in `.hero-section` (style.css line 115).

**Hero text** (index.php lines 17-24):
- Headline: "Unite Your Passion with Purpose at CONNECT MYUNI"
- Subtitle: "Explore world-class education..."
- CTA: "GET IN TOUCH" → href="#contact"

### 5.2 Events Carousel (Bootstrap)

- Type: Bootstrap 5.3 (`data-bs-ride="carousel"`)
- Location: index.php lines 250-378
- 2 slides × 3 event cards = 6 hardcoded events
- No custom JS initialization (auto via data attribute)
- Mobile: carousel controls hidden on <768px

### 5.3 Key Findings

| Question | Answer |
|---|---|
| Slide images defined? | Hard-coded `img src` in HTML |
| Slide text defined? | Hard-coded in HTML |
| Carousel initialized? | Bootstrap `data-bs-ride="carousel"` auto |
| Bootstrap carousel used? | Yes (events carousel only) |
| Images hard-coded? | **Yes — all images, text, links** |
| Admin can manage slides? | **No** |
| Mobile-specific images? | **No** |

### 5.4 Recommendation (DO NOT IMPLEMENT YET)

1. Create `hero_slides` table (MySQL) or JSON file (interim)
2. Admin CRUD: Create/Read/Update/Delete/Reorder
3. Schema: id, title, subtitle, cta_text, cta_url, image_path, mobile_image_path,
   sort_order, is_active, timestamps
4. Frontend: Replace static hero with dynamic PHP-rendered slides
5. Security: Validate image uploads, store outside web root
6.    Migration: Start with JSON, migrate to MySQL later.

---

## 6. Current CSS / UI Analysis

### 6.1 Design Tokens (CSS Variables)

Defined in `style.css` `:root` (lines 5-26):

| Variable | Value | Usage |
|---|---|---|
| `--primary-color` | `#1a56db` | Primary blue — links, buttons, badges |
| `--primary-dark` | `#1340a8` | Hover state |
| `--primary-light` | `#e8effd` | Light blue tint — card backgrounds |
| `--secondary-color` | `#e8380d` | Red-orange — CTA buttons, badges |
| `--secondary-dark` | `#c52d0a` | Hover state for secondary |
| `--secondary-light` | `#fde8e3` | Soft orange tint |
| `--accent-color` | `#f97316` | Warm orange — highlights |
| `--accent-dark` | `#e8600a` | Darker orange |
| `--accent-light` | `#fff3e8` | Soft orange tint |
| `--text-dark` | `#1a1a2e` | Body text on light backgrounds |
| `--text-muted` | `#6b7280` | Captions, subtitles |
| `--border-light` | `#e9ecef` | Border color |
| `--bg-light` | `#f8f9fa` | Light background sections |

Admin-only variables (in admin-header.php inline CSS): `--bg (#0f1117)`, `--surface (#181c25)`,
`--primary (#0052CC)`, `--accent (#4C8EF7)`, `--txt (#e8eaf0)`.

### 6.2 Typography

| Element | Font | Weights |
|---|---|---|
| Body | Google Fonts Poppins | 400, 500, 600, 700 |
| Admin body | DM Sans | 400, 500, 600, 700 |
| Admin code/text | DM Mono | 400, 500 |
| Headings | Poppins | 700 (clamp() for responsive sizing) |

### 6.3 Button Styles

7 distinct button styles: `.btn-hero-cta` (red rounded), `.btn-primary` (blue), `.btn-about`
(white text on blue), `.btn-cta` (white text on blue), `.btn-event-link` (blue rounded),
`.btn-whatsapp` (green), `.btn-submit-registration` (red-orange).

### 6.4 Card Styles

Multiple card styles: `.service-card-item`, `.event-card`, `.value-card`, `.why-card`,
`.university-card`, `.team-card`, `.fp-card`, `.update-card`, `.masonry-item`.

### 6.5 Navigation

Public: white bg navbar, custom hamburger toggler, active link underline.
Admin: dark fixed sidebar (240px), topbar with breadcrumb + live clock.

### 6.6 Responsive Breakpoints

| Breakpoint | Max-width | Key Changes |
|---|---|---|
| XL | 1400px | Default — 3-col carousel |
| LG | 1200px | Carousel 2 items |
| MD | 992px | Hero 75vh, about grid 1-col |
| SM | 768px | Hero 65vh, carousel 1 item, cards stack |
| XS | 576px | Hero 60vh, text resize |
| Gallery mobile | 768px/480px | Masonry 2→1 columns |

### 6.7 Animations

| Animation | Trigger | Properties |
|---|---|---|
| slideInUp | IntersectionObserver | translateY(30px)→0, opacity 0→1, 0.6s |
| slideInLeft | Manual | translateX(-30px)→0 |
| slideInRight | Manual | translateX(30px)→0 |
| flyIn | page-loaded | translateY(-100px) scale(0.95)→0 scale(1), 0.8s |
| fadeIn | img loaded | opacity 0→1, 0.3s |
| loading (skeleton) | img[data-src] | bg-position animation, 1.5s infinite |
| fadeUp | Gallery items | opacity 0→1, 0.5s |
| pulse | Admin status dot | box-shadow pulse, 2s infinite |

### 6.8 Bootstrap Dependencies in Use

Grid system (`container`/`row`/`col-*`), Navbar, Accordion, Carousel, Collapse, Forms,
Utilities (`text-center`, `mb-0`, `visually-hidden`, `g-*`), Buttons (`btn`, `btn-primary`),
Flexbox utilities, Display utilities.

### 6.9 Custom CSS Dependencies

| File | Lines | Purpose |
|---|---|---|
| `style.css` | 3,350 | All public page CSS |
| `admin-header.php` inline | ~700 | Admin dark theme CSS |
| `gallery.php` inline | ~570 | Gallery masonry, lightbox |
| `partner-universities.php` inline | ~160 | University page styles |

### 6.10 CSS Conflicts & Duplicated Styles

- Duplicate `.service-card-item` definition (lines ~543 and ~725) with different shadow values
- Duplicate `.hero-cards-section` definition (blue gradient vs white bg)
- Duplicate `@media (max-width: 768px)` blocks for different sections
- `footer.php` loads Bootstrap CSS in admin pages (causing duplication with admin's own CSS)
- `asset/js/main.js` exists but is **never referenced** — `js/main.js` is loaded instead (dead code)
- `.form-group` defined both in style.css (light theme) and admin-header.php (dark theme)
- Font Awesome loaded twice with different versions (6.4.0 public, 6.5.0 admin)
- `test.php` is dead code (UTF-16 encoded)

---

## 7. Tailwind Migration Assessment

### 7.1 Current Bootstrap Dependency

Bootstrap 5.3.0 loaded from CDN (`cdn.jsdelivr.net`) in two places:
- `header.php` line 26: CSS (`<link>`)
- `footer.php` line 11: JS bundle (`<script defer>`)

Additional CDN dependencies: Font Awesome 6.4.0 (public) / 6.5.0 (admin), Google Fonts Poppins/DM Sans.

### 7.2 Bootstrap Components in Use

| Component | Files | Notes |
|---|---|---|
| Grid (`container`/`row`/`col-*`) | All pages | Critical — heavy usage |
| Navbar + toggle | header.php | Custom toggler |
| Accordion | index.php, services.php | Both Bootstrap and custom |
| Carousel | index.php | `data-bs-ride="carousel"` |
| Collapse | index.php | For accordion |
| Forms | admin pages | `form-control`, `form-label` |
| Utilities | All pages | `text-center`, `mb-0`, `visually-hidden`, etc. |

### 7.3 JavaScript Dependencies

| Script | Source | Notes |
|---|---|---|
| Bootstrap bundle (Popper + BS) | CDN | Carousel, accordion, navbar toggle |
| js/main.js | Local, deferred | Smooth scroll, nav active, form submit |
| asset/js/main.js | Local | **DEAD CODE — never referenced** |
| Inline scripts | Various pages | 5 inline `<script>` blocks |

### 7.4 Coexistence Strategy

Yes, Tailwind can coexist temporarily. Strategy:
1. Load both Bootstrap CSS and Tailwind CSS simultaneously
2. Replace components one-by-one (buttons → cards → forms → grid → navbar → accordion → carousel)
3. Remove Bootstrap when all components migrated

### 7.5 Build Requirements (PHP + XAMPP)

**Recommended: Tailwind CLI** (no Node.js dependency):
- Download standalone `tailwindcss.exe` binary
- Run: `tailwindcss.exe -i ./src/input.css -o ./style.css --watch`
- `tailwind.config.js` with content paths for all PHP files

**Alternative: Node.js + PostCSS** if build pipeline is preferred.

**Not recommended**: CDN (`tailwindcss.com`) — runtime overhead, no purging.

### 7.6 Migration Order

1. Design tokens → `tailwind.config.js`
2. Buttons
3. Cards
4. Forms
5. Grid/Layout
6. Navbar
7. Accordion (custom)
8. Carousel → Splide.js
9. Utilities
10. Remove Bootstrap

**DO NOT install or migrate Tailwind during Stage 01.**

---

## 8. Backend / CRUD Assessment

### 8.1 Current CRUD Operations

| Operation | Module | Implementation | Storage |
|---|---|---|---|
| **Create** | Events (admin) | admin/post-event.php → JSON write | data/events.json |
| **Read** | Events (public) | CacheManager::getEvents() | includes/data/events.json (**BUG**) |
| **Update** | Events | **NOT IMPLEMENTED** | — |
| **Delete** | Events | **NOT IMPLEMENTED** | — |
| **Create** | Registrations | registration.php → JSON write | data/registrations.json |
| **Read** | Registrations | **NOT IMPLEMENTED** | — |

### 8.2 Validation & Sanitization

- Input validation: Minimal — `trim()`, `empty()` checks, `filter_var()` for email
- Output escaping: `htmlspecialchars()` used inconsistently (events.js template literals lack it)
- **No CSRF tokens** on any forms
- No input length limits; no date type validation; no slug sanitization

### 8.3 File Handling Issues

- No file locking (`file_put_contents` without `LOCK_EX`)
- No directory permission checks before `mkdir`
- No JSON write error handling beyond return value check
- Unsanitized data written to JSON (only `trim()` applied)

### 8.4 Sessions & Authentication

- **No sessions** — `session_start()` never called anywhere
- **No authentication** — admin pages fully public
- **No authorization** — no role-based access control

### 8.5 Security Concerns

| Risk | Severity | Details |
|---|---|---|
| No admin auth | Critical | All admin pages publicly accessible |
| No CSRF tokens | High | All forms unprotected |
| Path mismatch | High | CacheManager path ≠ admin write path |
| ID key mismatch | High | eventId vs id — events never found |
| XSS in events.js | Medium | Template literals without escaping |
| No rate limiting | Medium | Registration form open to spam |
| test.php info leak | Low | Exposes server info |
| No .htaccess protection | Medium | data/ and admin/ dirs unprotected |

### 8.6 CRUD Roadmap (Future Modules, Prioritized)

| Priority | Module | CRUD Scope | Storage |
|---|---|---|---|
| P0 | Authentication | Read (login/logout) | MySQL users table |
| P0 | Hero Slides | Full CRUD | MySQL |
| P0 | Events | Full CRUD (fix bugs) | MySQL |
| P1 | Universities | Full CRUD | MySQL |
| P1 | Countries | Full CRUD | MySQL |
| P1 | Testimonials | Full CRUD | MySQL |
| P1 | Services | Full CRUD | MySQL |
| P2 | Gallery | Full CRUD | MySQL |
| P2 | Blog/Articles | Full CRUD | MySQL |
| P2 | Scholarships | Full CRUD | MySQL |
| P2 | Enquiries | Read (inbox) | MySQL |
| P2 | Admin Users | Full CRUD | MySQL |
| P3 | Registrations | Full CRUD | MySQL |

---

## 9. Database Assessment

### 9.1 Current State: NO DATABASE

| Data Type | Storage | File Path |
|---|---|---|
| Events (write) | JSON | data/events.json |
| Events (cache/read) | PHP serialize | includes/data/.cache/events.cache.php |
| Registrations | JSON | data/registrations.json |

The `data/` directory does **not exist** at audit time — created dynamically by PHP.

### 9.2 Why a Database is Needed

1. Concurrency — JSON file writes have race conditions
2. Querying — Cannot filter/sort/search efficiently
3. Relationships — No way to express data entity relationships
4. ACID compliance — No transaction support
5. Scalability — JSON files don't scale

### 9.3 Proposed MySQL Architecture

Key tables: `admin_users`, `countries`, `universities`, `hero_slides`, `services`,
`events`, `testimonials`, `gallery_items`, `articles`, `scholarships`, `enquiries`,
`registrations`, `ai_content_requests`.

Full SQL schema is documented in the complete audit. All tables use proper foreign key
relationships, timestamps, and boolean status flags. Connection via PDO with prepared statements.

**DO NOT create the database during Stage 01.**

---

## 10. Security Audit

### 10.1 Critical Issues

| # | Risk | Severity | Details |
|---|---|---|---|
| 1 | No admin authentication | **Critical** | admin/index.php, admin/post-event.php accessible without login |
| 2 | No sessions | **Critical** | No `session_start()` anywhere |
| 3 | No CSRF protection | **High** | All forms lack CSRF tokens |
| 4 | CacheManager path bug | **High** | Reads from includes/data/, admin writes to data/ |
| 5 | ID key mismatch | **High** | eventId stored, id expected |
| 6 | events.php disconnected | **High** | Admin events invisible |
| 7 | No .htaccess on /admin/ | Medium | Directory listing possible, no access control |
| 8 | No .htaccess on data/ | Medium | JSON files potentially web-accessible |
| 9 | XSS in events.js | Medium | Template literals inject without escaping |
| 10 | No rate limiting | Medium | Registration form open to spam/DoS |
| 11 | test.php exposed | Low | UTF-16 PHP info file in web root |
| 12 | No input length limits | Low | Fields can be arbitrarily long |

### 10.2 Security Remediation Plan (Future Stages)

**Stage 02 (P0):**
- Implement admin authentication (login, sessions, role-based access)
- Add CSRF tokens to all forms
- Fix CacheManager path mismatch
- Fix ID key mismatch
- Connect events.php to data source
- Create missing contact.php
- Remove or protect test.php
- Add .htaccess protection for admin/ and data/ directories

**Stage 03 (P1):**
- Add security headers (.htaccess: CSP, X-Frame-Options, X-Content-Type-Options)
- Add file locking to JSON writes
- Implement input length validation

**Stage 04 (P2):**
- Add rate limiting to forms
- Implement secure file upload handling
- Add audit logging for admin actions

### 10.3 .htaccess Security Gap Analysis

Current `.htaccess` only contains:
- mod_rewrite trailing slash removal
- `Options -Indexes`
- PHP 8.4 handler

**Missing** (despite being claimed in OPTIMIZATION_GUIDE.md):
- GZIP compression
- Browser caching headers
- ETag removal
- Content-Security-Policy
- X-Frame-Options
- X-Content-Type-Options
- Referrer-Policy

**⚠️ Documentation/Code Mismatch**: OPTIMIZATION_GUIDE.md claims GZIP, browser caching, and service worker features that are NOT in the actual codebase. sw.js does not exist.

---

## 11. Performance Audit

### 11.1 Image Loading

- Native `loading="lazy"` used in event-detail.php, updates.php, gallery.php
- SVG data URI placeholders used for lazy-loaded images
- **No responsive images** — no `srcset` or `<picture>` elements
- **No WebP** — all images are PNG (larger file sizes)
- Collage images use CSS `background-image` (not `src`) — different patterns on different pages

### 11.2 JavaScript Loading

| Script | Loading | Notes |
|---|---|---|
| Bootstrap bundle | `defer` | Loaded via CDN in footer.php |
| js/main.js | `defer` | Loaded via CDN in footer.php |
| asset/js/main.js | Not loaded | Dead code |
| Inline scripts | Varies | 5 inline `<script>` blocks |
| Admin clock | Inline | In admin-header.php |

### 11.3 CSS Loading

- Bootstrap CSS via CDN in `<head>` (header.php)
- `style.css?v={filemtime}` in `<head>` for cache busting
- Admin inline `<style>` in admin-header.php (~700 lines)
- Gallery/page-specific inline `<style>` blocks
- Footer.php has JS that reloads stylesheet with `?v={timestamp}` (busts cache on every page load)

### 11.4 External CDN Dependencies

| Dependency | URL | On every page? |
|---|---|---|
| Bootstrap 5.3 CSS | cdn.jsdelivr.net | ✅ Yes |
| Bootstrap 5.3 JS | cdn.jsdelivr.net | ✅ Yes |
| Font Awesome 6.4 | cdnjs.cloudflare.com | ✅ Yes (public) |
| Font Awesome 6.5 | cdnjs.cloudflare.com | ✅ Yes (admin — version mismatch) |
| Google Fonts Poppins | fonts.googleapis.com | ✅ Yes (public) |
| Google Fonts DM Sans/Mono | fonts.googleapis.com | ✅ Yes (admin only) |
| Google Maps embed | maps.google.com | Partial (index.php only) |

### 11.5 Caching

| Layer | Implementation | Issue |
|---|---|---|
| Browser cache (meta) | `Cache-Control: no-cache` in header.php | Disables HTML caching on ALL pages |
| Cache buster | `?v={filemtime}` | Good |
| Footer JS cache bust | Reloads style.css with `?v={timestamp}` | Defeats browser caching |
| PHP CacheManager | 1hr TTL for events | Path mismatch means it's useless |
| Service worker | Referenced in docs, sw.js DOES NOT EXIST | Documentation claims not implemented |
| .htaccess cache | CLAIMED but NOT IMPLEMENTED | No GZIP, no browser caching headers |

### 11.6 PHP Performance

- `file_get_contents()` for JSON — mitigated by CacheManager for events (but broken due to path bug)
- `filemtime()` called on every request for cache busting
- No OPcache visible in configuration
- Header/footer included on every page (template pattern)
- No output buffering or response compression

### 11.7 Dead/Undesirable Code

- `asset/js/main.js` — never referenced (dead code)
- `test.php` — UTF-16 encoded PHP info file (dead code)
- Service worker cleanup JS in footer.php — unregisters SW that doesn't exist
- Footer.php JS that reloads stylesheet — defeats browser caching

### 11.8 Performance Recommendations

1. Remove dead code (asset/js/main.js, test.php, SW cleanup)
2. Implement actual .htaccess caching (GZIP, browser caching headers)
3. Convert PNG images to WebP with `<picture>` fallbacks
4. Add responsive `srcset` attributes
5. Fix CacheManager path mismatch
6. Remove footer.php stylesheet reload hack
7. Enable OPcache in php.ini
8. Consolidate inline CSS/JS into external files
9. Fix duplicate CSS definitions
10. Connect events.php to data source to eliminate redundant JS events array

---

## 12. SEO Audit Preparation

### 12.1 Current SEO Implementation

**Page Titles**: Only `gallery.php` and `partner-universities.php` set `$page_title`. All other pages use the default "Connect MyUni" — no page-specific titles.

**Meta Descriptions**: Single hard-coded description in header.php: "Connect MyUni - Leading Education Consultancy Services in Nigeria." — same for all pages.

**Heading Hierarchy**:

| Page | H1 | H2 | Issues |
|---|---|---|---|
| index.php | Hero title | None | No H2 |
| about.php | "About Us" | Multiple section H2s | Redundant header |
| services.php | None | "Our Services" | No H1 |
| events.php | None | None | All JS-rendered (SEO-unfriendly) |
| updates.php | None | None | H2 used as card title |
| gallery.php | "Gallery" | None | No semantic H2 |
| partner-universities.php | "Partner Universities" | Section H2s | — |

**Image Alt Attributes**: Generally good — uses `htmlspecialchars()` with descriptive text. Hero background is CSS image (no alt possible).

**Canonical URLs**: **Not implemented** — no `<link rel="canonical">` tags.

**Open Graph**: Only `og:image` (logo.png) in header.php. Missing `og:title`, `og:description`, `og:type`, `og:url`.

**Sitemap**: **No sitemap.xml exists.**

**Robots.txt**: **No robots.txt exists.**

**Structured Data**: **No Schema.org / JSON-LD** anywhere.

### 12.2 SEO Issues

| Issue | Severity | Details |
|---|---|---|
| Missing page titles | High | Only 2 of 8+ pages have custom titles |
| Events rendered by JS | High | Google may not index JS-rendered content |
| No canonical tags | Medium | Duplicate content risk |
| No sitemap.xml | Medium | Slow discovery by search engines |
| No robots.txt | Low | Default crawling behavior |
| No structured data | Medium | Misses rich snippets |
| Broken links to contact.php | Medium | 404 errors for visitors and crawlers |
| No meta descriptions per page | Medium | Poor click-through rates |
| Non-SEO-friendly URLs | Low | `?id=event-slug-2024-...` instead of `/events/event-slug` |
| No hreflang | Low | If targeting multiple regions |

### 12.3 SEO Roadmap

**Stage 02**: Add page-specific meta titles + descriptions, canonical tags, fix broken links, create contact.php
**Stage 04**: Create sitemap.xml, robots.txt, add Open Graph tags
**Stage 06**: Add Schema.org structured data, implement SEO-friendly URL rewrites
**Stage 06+**: SEOptimer-assisted optimization

---

## 13. AI Integration Assessment

### 13.1 Proposed Workflow

```
ADMIN CREATES REQUEST
↓
AI GENERATES DRAFT
↓
ADMIN REVIEWS
↓
ADMIN EDITS IF NECESSARY
↓
ADMIN APPROVES
↓
CONTENT IS PUBLISHED
```

### 13.2 Potential AI Content Types

| Content Type | Use Case | Complexity |
|---|---|---|
| Blog articles | SEO content | High |
| Country guides | Study destination info | High |
| University descriptions | Institution profiles | Medium |
| Event descriptions | Marketing copy | Low |
| SEO titles/meta descriptions | Meta optimization | Low |
| Social media posts | Promotional content | Low |

### 13.3 Recommended Architecture

**Secure API key storage**: Store in `.env` file OUTSIDE web root, load via `config/config.php`. Never hard-code in PHP files.

**Database table** (`ai_content_requests`):
- `id`, `admin_user_id` (FK), `content_type`, `prompt`, `generated_content`, `status`
  (pending→generated→reviewed→approved/rejected), `approved_content`, `approved_at`
- `created_at`, `updated_at`

**Integration**: Content types map to existing CRUD modules (blog posts, universities, etc.) via foreign keys.

**Provider recommendation**: OpenAI GPT-4o-mini (cost-effective), with fallback to GPT-3.5-turbo.

### 13.4 Security & Cost Considerations

| Concern | Mitigation |
|---|---|
| API cost | Cache responses, token limits, cheaper model |
| Rate limiting | Request queuing, respect API limits |
| Content quality | Human review always required |
| Data privacy | Never send PII to external APIs |
| Cost attribution | Log token usage per request |

**Implementation starts at Stage 05.**

---

## 14. Future UI/UX Direction

### 14.1 Design Principles

| Principle | Implementation Plan |
|---|---|
| Modern | Clean card-based layouts, subtle shadows, refined spacing |
| Premium | High-quality imagery, sophisticated palette, attention to typography |
| Clean | Minimize visual clutter, clear hierarchy, generous whitespace |
| Professional | Study-abroad photography, trust signals, clean typography |
| Mobile-first | Design mobile layouts first, enhance for larger screens |
| Fast | Optimize images, lazy load, minimal JS |
| Accessible | WCAG 2.1 AA compliance, proper ARIA, keyboard navigation |
| Conversion-oriented | Strategic CTAs, clear value propositions, trust signals |

### 14.2 Animation Direction

| Animation | Purpose | Technology |
|---|---|---|
| Scroll reveal | Content fading in as user scrolls | Intersection Observer + CSS |
| Subtle parallax | Depth on hero/section backgrounds | CSS transform |
| Smooth transitions | Button/hover state changes | CSS transition |
| Hover effects | Card lift, image zoom | CSS transform |
| Lightweight transitions | Page navigation | CSS |
| Animated counters | Statistics display | CSS or JS animation |

### 14.3 Avoid

| Thing to Avoid | Reason |
|---|---|
| Heavy 3D scenes (Three.js) | Performance impact, not justified |
| Excessive animation | Accessibility concerns, distraction |
| Large JS libraries without justification | Bundle bloat |
| Animations ignoring `prefers-reduced-motion` | Accessibility violation |

---

## 15. 8-Week Development Roadmap

### Week 1: Foundation
| Task | Dependencies |
|---|---|
| Create MySQL database & PDO connection class | XAMPP |
| Design database schema (all 13 tables) | — |
| Implement admin authentication (login/logout, sessions) | DB |
| Add CSRF token system | — |
| Fix CacheManager path mismatch (includes/data/ → data/) | — |
| Fix event ID key mismatch (eventId → id) | post-event.php, CacheManager |
| Create missing contact.php page | — |
| Add .htaccess security headers | — |
| Protect admin/ and data/ directories | — |

### Week 2: Build Tooling & Tailwind
| Task | Dependencies |
|---|---|
| Install Tailwind CSS (CLI, no Node.js) | — |
| Create tailwind.config.js | Design tokens |
| Create src/input.css with @tailwind directives | — |
| Create base layout component | — |
| Migrate hero section to Tailwind | Tailwind |
| Migrate navigation to Tailwind | Tailwind |
| Migrate footer to Tailwind | Tailwind |
| Convert PNG images to WebP | Image tools |
| Fix $base_url inconsistency | — |

### Week 3: Tailwind CSS Migration (Components)
| Task | Dependencies |
|---|---|
| Migrate buttons to Tailwind | Tailwind |
| Migrate cards to Tailwind | Tailwind |
| Migrate forms to Tailwind | Tailwind |
| Replace custom accordion with Tailwind | Tailwind |
| Replace Bootstrap carousel with Splide.js | JS library |
| Remove Bootstrap CSS from contact.php (pilot) | — |

### Week 4: Database Migration & CRUD
| Task | Dependencies |
|---|---|
| Migrate events from JSON to MySQL | DB, path fix |
| Implement full Events CRUD (Create/Edit/Delete/List) | DB |
| Implement Hero Slides CRUD | DB |
| Implement Countries + Universities CRUD | DB |
| Remove CacheManager, use direct DB queries | Events CRUD |
| Add pagination to event lists | — |
| Clear cache invalidation (no longer needed with DB) | — |

### Week 5: Content Management Features
| Task | Dependencies |
|---|---|
| Connect events.php to Events database table | Events CRUD |
| Implement Gallery CRUD (replace hardcoded array) | DB |
| Implement Testimonials CRUD | DB |
| Implement Services CRUD | DB |
| Implement Enquiries form → database | DB, contact.php |
| Implement Registrations CRUD | DB |
| Add search functionality | DB |

### Week 6: SEO & Performance
| Task | Dependencies |
|---|---|
| Add page-specific meta titles + descriptions | — |
| Create sitemap.xml and robots.txt | All pages |
| Implement SEO-friendly URLs (remove .php) | .htaccess |
| Add Open Graph meta tags | — |
| Add Schema.org structured data | — |
| Full image optimization (WebP + srcset) | Week 2 image conversion |
| Implement actual .htaccess caching (GZIP) | — |
| Full Lighthouse audit (target 90+) | — |

### Week 7: AI Content Studio
| Task | Dependencies |
|---|---|
| Set up secure API key storage (.env + config.php) | — |
| Create AI API client class | API key |
| Create ai_content_requests table | DB |
| Build Admin "AI Content Studio" UI | Auth, DB |
| Implement content generation workflow | AI client |
| Add content approval workflow | CRUD |
| Connect AI content to blog/university modules | Week 5 CRUD |

### Week 8: Testing & Documentation
| Task | Dependencies |
|---|---|
| Security testing (XSS, CSRF, SQLi, auth bypass) | All features |
| Cross-browser testing (Chrome, Firefox, Safari, Edge) | — |
| Mobile responsiveness testing | Design system |
| Update admin documentation | All admin features |
| Write user documentation | — |
| Performance benchmarking | Week 6 optimization |
| SEO validation (SEOptimer) | Week 6 SEO |

### High-Risk Items

| Risk | Impact | Mitigation |
|---|---|---|
| Tailwind learning curve | Medium-High | Dedicate Week 2, use framework guide |
| Database migration bugs | High | Keep JSON as backup, test data migration |
| Path/ID key fixes breaking functionality | High | Test after each fix, backward compat temporarily |
| AI API costs | Medium | Set token limits, cache responses, use gpt-4o-mini |
| Carousel replacement complexity | Medium | Use Splide.js (lightweight, documented) |
| Events.php reconnection | Medium | Replace with same data source as updates.php |
| 8-week timeline | High | Prioritize critical path, defer non-essential features |

---

## 16. Stage 01 Completion Status

### Completed ✅
1. ✅ Full project structure inspection — all files cataloged
2. ✅ Existing features identified and documented (public + admin)
3. ✅ Hard-coded content inventory created
4. ✅ Hero carousel analyzed (static hero, Bootstrap events carousel separate)
5. ✅ Current CSS/UI analyzed (design tokens, typography, buttons, cards, breakpoints)
6. ✅ CSS conflicts & duplication documented
7. ✅ Tailwind migration strategy assessed
8. ✅ Backend/CRUD assessment completed
9. ✅ Database assessment completed (no DB exists, full schema proposed)
10. ✅ Security audit completed (12 risks identified)
11. ✅ Performance audit completed
12. ✅ SEO audit preparation completed
13. ✅ AI integration architecture assessed
14. ✅ Future UI/UX direction documented
15. ✅ 8-week development roadmap created
16. ✅ This audit document created

### Critical Bugs Requiring Immediate Attention

| # | Bug | Fix Description |
|---|---|---|
| 1 | CacheManager reads from `includes/data/events.json` but admin writes to `data/events.json` | Fix path in CacheManager to use `dirname(__DIR__) . '/data/events.json'` |
| 2 | Admin stores `eventId` key but CacheManager expects `id` | Unify key name to `id` |
| 3 | events.php disconnected from admin system | Connect to CacheManager::getEvents() |
| 4 | No cache invalidation after admin post | Call clearEventsCache() after write |
| 5 | Contact info inconsistent (2 emails, 2 phones, PH WhatsApp) | Unify in settings table |
| 6 | $base_url inconsistency | Unify to single path |
| 7 | No admin authentication | Implement login system |
| 8 | No CSRF protection | Add tokens to all forms |
| 9 | OPTIMIZATION_GUIDE.md claims unimplemented features | Remove or implement claims |
| 10 | Duplicate Font Awesome versions (6.4.0 vs 6.5.0) | Standardize to one version |

### Not Yet Started (Deferred)
- Tailwind installation/migration
- Database creation
- Website redesign
- AI implementation
- CRUD implementation
- SEO implementation

### Stage 01 Approval
- [ ] Audited by: _________________ Date: _______
- [ ] Architecture reviewed by: _________________ Date: _______
- [ ] Supervisor approved for Stage 02: _________________ Date: _______

---

## Appendix A: Complete File Inventory

### PHP Files (22 total)

| # | File | Lines | Size (bytes) | Purpose |
|---|---|---|---|---|
| 1 | `.htaccess` | 18 | ~700 | Apache config, PHP 8.4 handler, trailing slash redirect |
| 2 | `index.php` | ~445 | 26,806 | Homepage (hero, cards, about, services, events carousel, contact) |
| 3 | `about.php` | ~229 | 14,103 | About page (profile, services, universities, why choose, contact) |
| 4 | `services.php` | ~136 | 7,643 | Services page (accordion, 10 items) |
| 5 | `events.php` | ~128 | 4,792 | Events page (hardcoded JS array — DISCONNECTED from admin) |
| 6 | `event-detail.php` | ~213 | 9,866 | Event detail (CacheManager, BUGGED — wrong ID key) |
| 7 | `updates.php` | ~113 | 5,380 | Updates page (CacheManager::getEvents) |
| 8 | `gallery.php` | ~773 | 24,461 | Gallery (masonry grid, lightbox, 8 items) |
| 9 | `partner-universities.php` | ~506 | 17,061 | University listing (6 countries, featured partners) |
| 10 | `registration.php` | ~264 | 11,786 | Registration form (writes to data/registrations.json) |
| 11 | `header.php` | ~78 | 3,739 | Public header ($base_url = '', Bootstrap CSS, nav) |
| 12 | `footer.php` | ~40 | 1,557 | Public footer (BS JS, main.js, $base_url fallback) |
| 13 | `test.php` | 2 | 138 | UTF-16 encoded PHP test (DEAD CODE) |
| 14 | `includes/CacheManager.php` | 90 | ~2,500 | JSON caching class (events only, BUGGED) |
| 15 | `admin/index.php` | ~204 | ~5,200 | Admin dashboard (event stats, quick actions) |
| 16 | `admin/admin-header.php` | ~820 | ~28,000 | Admin layout (dark theme, inline CSS, ~700 lines CSS) |
| 17 | `admin/post-event.php` | ~232 | ~9,500 | Event creation form (Create only, BUGGED eventId key) |
| 18 | `admin/README.md` | ~340 | ~9,000 | Admin documentation |
| 19 | `ADMIN_GUIDE.md` | ~300 | 8,209 | Admin quick-start guide |
| 20 | `OPTIMIZATION_GUIDE.md` | ~219 | 5,975 | Performance guide (claims unimplemented features) |
| 21 | `js/main.js` | ~70 | ~1,800 | Public JS (smooth scroll, nav, form) |
| 22 | `asset/js/main.js` | ~37 | ~1,100 | Dead code (IntersectionObserver — never referenced) |

### Asset Files (14 images)

| File | Purpose | Used In |
|---|---|---|
| `asset/logo.png` | Site logo, favicon | All pages (header.php) |
| `asset/hero_image.png` | Hero section background | style.css (.hero-section) |
| `asset/background1.png` | Services page image | services.php |
| `asset/background2.png` | Unused | — |
| `asset/image1.png` | Team/collage photo | index.php, about.php, events carousel |
| `asset/image2.png` | Team/collage photo | Same |
| `asset/image3.png` | Team/collage photo | Same |
| `asset/image4.png` | Unused | — |
| `asset/image5.png` | Unused | — |
| `asset/gallery1-8.png` | Gallery images | gallery.php |
| `admin/.ftpquota` | FTP quota file | Auto-generated |

### CDN & External References

| URL | Type | Used In |
|---|---|---|
| `cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css` | CSS | header.php |
| `cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js` | JS | footer.php |
| `cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0` | CSS | header.php |
| `cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0` | CSS | admin-header.php (**version mismatch**) |
| `fonts.googleapis.com/css2?family=Poppins` | CSS | header.php |
| `fonts.googleapis.com/css2?family=DM+Sans&family=DM+Mono` | CSS | admin-header.php |
| `maps.google.com/maps/embed` | iframe | index.php |

### Configuration Notes

- **PHP version**: 8.4 (`ea-php84` in `.htaccess`)
- **No database** — JSON file storage only
- **No email** — registration saves to JSON, no email sending
- **No sessions** — `session_start()` never called
- **No .htaccess caching** — OPTIMIZATION_GUIDE.md claims GZIP/caching but `.htaccess` does not implement them
- **$base_url** inconsistency: `''` in header.php, `/myuni/` in admin-header.php
- **Browser cache disabled** via meta tags in header.php (`Cache-Control: no-cache`)
- **No robots.txt** or **sitemap.xml** exists















