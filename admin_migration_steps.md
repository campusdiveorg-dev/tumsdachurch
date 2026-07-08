# TUMSDA Church Website — Admin Panel Refactoring & Styling Steps

This document outlines the refactoring, styling, and debugging steps completed on the TUMSDA Church Admin Panel and Backend REST API. Anyone looking to run, extend, or deploy this project can use this as a direct handoff guide.

---

## 1. Database Schema Optimization (Consolidated Tables)
To remove structural redundancy, the separate `departments` and `ministries` tables have been consolidated into a single table.

- **Changes in `database/schema_mysql.sql`:**
  - Removed tables `departments` and `ministries`.
  - Created a unified `departments_ministries` table:
    ```sql
    CREATE TABLE IF NOT EXISTS departments_ministries (
        id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT PRIMARY KEY,
        type                ENUM('department', 'ministry') NOT NULL,
        name                VARCHAR(150)    NOT NULL,
        description         TEXT,
        scripture_quote     TEXT,
        scripture_reference VARCHAR(100),
        external_link       VARCHAR(255),
        sort_order          INT             NOT NULL DEFAULT 0,
        created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ```
  - Combined the initial seeds for both departments and ministries into a single `INSERT INTO departments_ministries` block, utilizing the `'department'` and `'ministry'` Enum values respectively.

- **Developer Action Required:**
  If setting up or updating a local or staging database, drop the old tables and re-run the updated `database/schema_mysql.sql` or run:
  ```sql
  DROP TABLE IF EXISTS departments;
  DROP TABLE IF EXISTS ministries;
  -- Then run the departments_ministries table creation and insert the seed data.
  ```

---

## 2. Backend REST API Debugging & Refactoring

### A. Dynamic API Root Router
- **File:** [api/index.php](file:///c:/wamp64/www/tumsda.org/api/index.php)
- **Problem:** The routing script used a hardcoded string `/tumsda.org/api/` to split path segments. If the site was deployed under the root directory on a production domain, all routes failed with a `404 Endpoint not found` error.
- **Fix:** Changed the parser to locate the positioning of `/api/` dynamically. This makes the URL path parsing completely robust on localhost, WAMP, custom domains, or subdirectories alike.

### B. Transparent Table Remapping
- **File:** [api/controllers/ContentController.php](file:///c:/wamp64/www/tumsda.org/api/controllers/ContentController.php)
- **Problem:** The merged database schema could break the frontend client endpoints (`/api/departments` and `/api/ministries`).
- **Fix:** Added routing interceptors to `ContentController.php` so that calls to `departments` and `ministries` dynamically remap query/insert/update/delete requests to the `departments_ministries` table with the respective `type = 'department'` or `type = 'ministry'` values. The React frontend continues to use the exact same REST API endpoints without change.

---

## 3. Frontend Client-Side & Dev Config Debugging

### A. Dynamic API base
- **File:** [admin-src/src/services/api.js](file:///c:/wamp64/www/tumsda.org/admin-src/src/services/api.js)
- **Fix:** Changed hardcoded `/tumsda.org/api` URL base to dynamically detect and strip `/admin` from the current path. The admin frontend now resolves the correct API paths automatically in any hosting directory environment.

### B. Auto-Login on Registration
- **File:** [admin-src/src/context/AuthContext.jsx](file:///c:/wamp64/www/tumsda.org/admin-src/src/context/AuthContext.jsx)
- **Problem:** The registration endpoint creates the database row but does not open a session cookie. The React client-side previously updated state directly, making the user appear logged in, but causing subsequent API calls to fail with `401 Unauthorized`.
- **Fix:** Modified `register` to internally call `login` upon successful registration. This ensures session cookies are initialized properly on the backend.

### C. Development Environment Port Setup
- **Files:** `Login.jsx`, `Register.jsx`, `Layout.jsx`
- **Fix:** Updated dev environment port checks to verify both `5173` (Vite's default port) and `5174` (fallback port) to ensure logo images and public assets resolve properly during testing.

### D. Empty Table Form Render Bug
- **File:** [admin-src/src/pages/ContentPage.jsx](file:///c:/wamp64/www/tumsda.org/admin-src/src/pages/ContentPage.jsx)
- **Problem:** The input form previously relied on scanning properties of existing table items. If the collection was empty, no input fields rendered at all, making it impossible to add new entries.
- **Fix:** Integrated a static `fieldsConfig` dictionary map for each collection type. Fields now render correctly even on a fresh database configuration with zero rows.

### E. Missing Form Fields
- **File:** [admin-src/src/pages/ContentPage.jsx](file:///c:/wamp64/www/tumsda.org/admin-src/src/pages/ContentPage.jsx)
- **Fix:** Added full form input support for previously omitted columns:
  - Dates (`published_at`, `event_date`, `start_date`, `end_date`) render as native date pickers.
  - Boolean flags (`is_featured`, `is_upcoming`) render as toggle checkboxes.
  - Weekly meeting days (`day_of_week`) render as a dropdown list of valid Enums.

---

## 4. Design & Aesthetics Refinement (Tailwind CSS)

To inherit the premium feel of the main site (`tumsdachurch.org`), the admin UI was enhanced using Tailwind CSS classes paired with design tokens matching `assets/style.css`.

- **Typography & Font Tokens:**
  - Header titles: `'League Spartan', sans-serif`
  - Body text & labels: `'Inter', sans-serif`
- **Branding Color Palette:**
  - Primary Brand Accent: `bg-brand` / `text-brand` (`#0b5ed7`)
  - Hover Dark State: `hover:bg-brand-dark` (`#0a53be`)
  - Dark Elements & Sidebar: `bg-slate-900`
- **Layout & Structure:**
  - **Sidebar:** Styled with a clean vertical list structure, active state indicators (rounded shapes with brand glow shadows), user details widget, and quick icons.
  - **Top Bar:** Modern profile tags showing the current user's system role.
  - **Forms:** Input controls styled with soft background shades (`bg-slate-50`), rounded corners (`rounded-xl`), and focus rings (`focus:ring-brand/20`).
  - **Tables:** Styled header cards (`bg-slate-900`), border dividers (`divide-slate-100`), clean action buttons, status labels (amber badges for Featured, green for Active/Upcoming).
