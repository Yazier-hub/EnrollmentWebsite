# Enrollment pages

All routes enter through `index.php`. Authentication and session checks run first.

- `classes/EnrollmentPage.php` maps routes and renders the shared layout.
- `classes/*Page.php` handle requests and prepare view data using the existing models and controllers. Redirects, downloads, and POST actions run before HTML output.
- `pages/*.php` contain HTML templates, loops, conditions, and formatting. They do not query the database or handle POST requests.
- `classes/PageView.php` contains reusable formatting and form-field helpers.
- `classes/SidebarView.php` loads employee information and unread notifications.
- `css/pages/<route>.css` contains each page's styles. Shared layout overrides remain under `css/layout/`, and shared presentation utilities are in `css/components/page-utilities.css`.
- `js/pages/<route>.js` contains each page's behavior. `js/page-ui.js` binds events using named `data-action-*` attributes. Values passed from PHP use escaped JSON data attributes; no JavaScript is evaluated from HTML.
- `classes/PageAssets.php` loads shared assets and the current page's files, with modification-time versions to refresh browser caches. Chart.js loads only on the dashboard.

Keep model queries and mutations in `classes/`. Add page-specific CSS or JavaScript to the matching file; the asset loader discovers these files automatically. Render view data through the page class instead of adding inline scripts, styles, or event handlers to templates. Dynamic progress widths and colors use CSS custom properties populated by `page-ui.js`.

The logout route uses `LogoutPage` and redirects without rendering a layout. Its small `pages/logout.php` entry remains for existing direct links.

## Validation

Run `php tests/pages_smoke.php` to render all 16 content pages against the configured local database inside read-only transactions. It checks layout structure, asset paths, JSON arguments, and the absence of inline CSS and JavaScript. An editable applicant is supplied with a temporary table when existing applications have all been converted; persistent records are not changed.

Run `php tests/requirements_regression.php` for the requirements workflow checks in a disposable database. Run `node tests/page_ui_regression.cjs` to check CSS-hidden panels, modal toggles, and delegated form events. JavaScript files can be checked with `node --check <file>` and PHP files with `php -l <file>`.

These checks do not submit enrollment forms, send email, or replace interactive browser testing.
