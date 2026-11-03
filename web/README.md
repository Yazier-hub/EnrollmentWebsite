# Bestlink public website

The existing XAMPP/PHP application now separates page markup, browser behavior, styling, and persistence. Public page URLs remain under `pages/`; the root home page and legacy aliases point there.

## Where to edit

| Location | Responsibility |
| --- | --- |
| `pages/*.html` | Public informational pages |
| `pages/*.php` | Small request entry points |
| `views/*.php` | PHP page templates; no database queries or executable inline scripts |
| `classes/*Controller.php` | Request handling, validation orchestration, and view data |
| `classes/*Repository.php` | Database reads/writes; enrollment and document writes use a transaction |
| `classes/EnrollmentForm.php`, `ContactMessage.php` | Form models and server validation |
| `classes/PageView.php` | Template rendering, HTML escaping, and safe JSON serialization |
| `bootstrap.php` | Session startup and class autoloading |
| `assets/css/` | Page styles and shared responsive rules in `site.css` |
| `assets/js/` | Page behavior and shared `SiteNavigation` class in `site.js` |
| `assets/vendor/` | Locally served Vue, GSAP, Leaflet, and Font Awesome distributions |

For the enrollment page, edit `views/enrollment-form.php`, `assets/css/enrollment-form.css`, and `assets/js/enrollment-form.js`. Its public entry point is still `pages/enrollment-form.php`.

## Running locally

Use the existing Apache/XAMPP configuration or `php -S 127.0.0.1:8095 -t .` for local development. Open `/pages/index.html`. Apache applies `.htaccess`, including legacy URL redirects and access restrictions for implementation/test directories; PHP's development server does not apply these rules.

The existing MySQL schema is required (`payr_bcp` by default). No database migration was introduced. Database settings come from `../db_config.php`, shared with the employee login and enrollment module. Keep that file one level above the `web` folder when deploying. Optional overrides are `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, and `MYSQLPASSWORD`, or `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`. For local XAMPP, set those variables to your local database settings. PHP needs PDO MySQL, mbstring, and fileinfo. Uploads retain the existing 5 MB per-document limit; PHP's upload and request-size settings must allow the combined request.

The frontend libraries and compiled CSS are committed local assets. Node is only needed when rebuilding CSS:

```sh
npm ci
npm run build:css
```

Run the build after changing Tailwind utility classes in templates or scripts. Edit `tailwind.input.css` and page styles, not the generated `utilities.css`.

Map tiles, address lookup, and the embedded contact map still use external services. Enrollment now allows manually entering the street, barangay, city, and province if the map service is unavailable. Existing event demo fallback behavior remains unchanged. The old browser-only `pages/enroll.html` demonstration now redirects to the database-backed application flow, which starts at `pages/online-admission.html` and proceeds through course selection to `pages/enrollment-form.php`.

## Verification

```sh
php tests/backend.php
node tests/browser-check.cjs
```

The backend tests use a PDO test double and never insert real applicants. They cover validation, existing address restoration, safe JSON data, insert/update transactions, rollback, and the success view. Browser checks expect a local server at port 8095 (override with `TEST_BASE_URL`) and an isolated Chromium/Edge DevTools endpoint at port 9223. They check all 14 pages at 320, 375, 768, 1024, and 1440 pixels, mobile menus, local image loading, and enrollment validation. Browser checks set a selected course in their own session but do not submit an application or contact message. Screenshots are written to ignored `tests/artifacts/`.

Live database inserts and external geocoding were not exercised during this refactor. Existing applicant lookup/authorization and database schema conventions are retained; this cleanup does not replace those application policies.
