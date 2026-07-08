# TUMSDA Church Website — Admin Panel, PostgreSQL & M-Pesa Integration
## Project Requirements & Implementation Plan

**Status:** Draft for review
**Last updated:** 2026-07-05
**Relates to:** `REFACTOR_PROGRESS.md` (Phases 1–3, completed/in progress), `IMAGE_OPTIMIZATION_GUIDE.md`, `style-audit.md`

This document extends the existing refactor. It does not replace `REFACTOR_PROGRESS.md` — that file tracks the public-site cleanup (image optimization, styling). This file covers the **new system**: a React-based admin panel, a PHP REST API, a PostgreSQL database, and an automated M-Pesa payment flow.

---

## 1. Overview

The current site (`tumsdachurch.org`) is a static PHP/Bootstrap site with no database and no user accounts. Content (leadership bios, calendar events, sermons, departments, ministries) is hand-edited directly in PHP files. Support/mission giving currently happens via a manually displayed M-Pesa Till number in a popup.

This project adds:
- A **PostgreSQL database** to store content and member data
- A **PHP REST API** exposing that data as JSON
- A **React admin panel** (single-page app) for managing content and members
- An **automated M-Pesa payment flow** (STK Push) to replace the manual till-number popup

The public-facing pages remain PHP and are not rewritten. Whether they eventually *read* from the new database is a separate decision (see §16).

---

## 2. Objectives

- Let non-technical church staff update leadership, events, sermons, departments, ministries, and resources without editing PHP files directly.
- Introduce member accounts (name, email, role) as a foundation for future member-facing features.
- Let members and visitors give (tithe, offering, mission support) via M-Pesa without needing to manually type a till number and message the church for confirmation.
- Do all of this without requiring a Node.js runtime in production, and without breaking the existing public site.

---

## 3. Scope

### In scope
- PostgreSQL schema covering: users, departments, ministries, leadership, sermons, events, resources, payments
- PHP REST API (auth, CRUD endpoints, M-Pesa STK Push initiation + callback handling)
- React admin panel (Vite build, static output, login-gated)
- M-Pesa Daraja API integration (sandbox first, then production)
- Session-based auth with CSRF protection
- Basic audit logging of admin actions

### Out of scope (for now — revisit later)
- Rewriting the public PHP pages to pull from the database (see §16)
- Public member self-service portal (profile pages, giving history for members)
- Recurring/scheduled M-Pesa payments (initial scope is one-off STK Push only)
- SMS/email receipt notifications (can be added after core flow works)

---

## 4. Proposed architecture

```
                         Browser
                        /        \
                       v            v
        ┌─────────────────────────────────────────────┐
        │   Production PHP host (shared hosting)        │
        │                                                │
        │  ┌────────────────────┐  ┌────────────────────┐│
        │  │ Public pages        │  │ Admin panel (new)  ││
        │  │ (unchanged, PHP)    │  │ React SPA, static   ││
        │  └────────────────────┘  └──────────┬─────────┘│
        │                                      │           │
        │                          ┌───────────v─────────┐│
        │                          │ PHP REST API (new)   ││
        │                          │ auth · content ·      ││
        │                          │ members · payments    ││
        │                          └───────────┬─────────┘│
        │                                      │           │
        │                          ┌───────────v─────────┐│
        │                          │ PostgreSQL (new)      ││
        │                          └────────────────────┘│
        └──────────────────────┬──────────────────────────┘
                                │  HTTPS (outbound + inbound callback)
                                v
                       Safaricom Daraja API
                       (M-Pesa STK Push)
```

The M-Pesa flow requires your production server to (a) make outbound HTTPS calls to Safaricom's API, and (b) receive an inbound HTTPS callback from Safaricom at a **publicly reachable** URL. This will not work against `localhost`/WAMP — it needs the real, publicly hosted domain with a valid SSL certificate. Sandbox testing can use a tunnel tool (e.g. ngrok) pointed at your local machine if you want to test before deploying.

---

## 5. Technology stack

| Layer | Choice | Notes |
|---|---|---|
| Public site | PHP (existing) | Unchanged |
| Admin frontend | React + Vite + React Router | Built locally, deployed as static files |
| Backend API | PHP (plain, no framework) | Matches existing skillset; framework can be introduced later if complexity grows |
| Database | PostgreSQL | Requires `pdo_pgsql` PHP extension; confirm host support (see §16, Risk 1) |
| Auth | PHP native sessions + httpOnly cookie | Same-origin, avoids CORS and token-in-localStorage risk |
| Payments | Safaricom Daraja API (STK Push) | Sandbox credentials first, production after Safaricom go-live approval |

---

## 6. Functional requirements

### 6.1 Public site
- No functional changes in this phase. Continues to render static/hardcoded content as it does today.

### 6.2 Admin panel — content management
Admin users can create, edit, delete, and reorder:
- **Departments** (name, description, scripture quote + reference, external link, display order)
- **Ministries** (name, description, scripture quote + reference, display order)
- **Leadership** (name, position, photo, statement, display order)
- **Sermons** (title, YouTube URL, description, featured flag)
- **Events / calendar** (title, date, facilitator, description) — replaces the hardcoded table in `about.php`
- **Resources** (title, description, icon, external link, category) — replaces the hardcoded list in `sermons.php`

### 6.3 Admin panel — member management
- Admin can view, create, edit, deactivate members
- Roles: `admin`, `member` (extendable later, e.g. `treasurer` for payment reporting access)
- Decision needed: can members self-register, or are all accounts admin-created? (see §16)

### 6.4 M-Pesa payment integration
- A "Give" flow (replacing or supplementing the current manual Till popup) where a visitor or member:
  1. Selects a purpose: tithe, offering, or mission support
  2. Enters an amount and phone number
  3. Receives an STK Push prompt on their phone
  4. Completes payment; the system records the result via Safaricom's callback
- Admin panel shows a payments/giving history list (status, amount, purpose, phone, receipt number, timestamp)
- Decision needed: must a donor have an account, or can anyone give as a guest? (see §16)

---

## 7. Database schema (PostgreSQL)

```sql
CREATE TYPE user_role AS ENUM ('admin', 'member');
CREATE TYPE payment_purpose AS ENUM ('tithe', 'offering', 'mission_support', 'other');
CREATE TYPE payment_status AS ENUM ('pending', 'completed', 'failed', 'cancelled');

CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role user_role NOT NULL DEFAULT 'member',
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE departments (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    scripture_quote TEXT,
    scripture_reference VARCHAR(100),
    external_link VARCHAR(255),
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE ministries (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    scripture_quote TEXT,
    scripture_reference VARCHAR(100),
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE leadership (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    position VARCHAR(150) NOT NULL,
    photo_path VARCHAR(255),
    statement TEXT,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE sermons (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    youtube_url VARCHAR(255) NOT NULL,
    description TEXT,
    is_featured BOOLEAN NOT NULL DEFAULT false,
    published_at DATE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE events (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    event_date DATE NOT NULL,
    facilitator VARCHAR(150),
    description TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE resources (
    id SERIAL PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    icon_path VARCHAR(255),
    link_url VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE payments (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),          -- nullable: guest giving allowed
    phone_number VARCHAR(20) NOT NULL,
    amount NUMERIC(10,2) NOT NULL,
    purpose payment_purpose NOT NULL,
    status payment_status NOT NULL DEFAULT 'pending',
    mpesa_receipt_number VARCHAR(50),
    checkout_request_id VARCHAR(100) UNIQUE,
    merchant_request_id VARCHAR(100),
    raw_callback_payload JSONB,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE audit_log (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),
    action VARCHAR(100) NOT NULL,
    entity VARCHAR(100) NOT NULL,
    entity_id BIGINT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);
```

`audit_log` is a recommended addition, not explicitly requested — worth having given payments and member data are both sensitive.

---

## 8. REST API endpoints (draft)

| Method | Endpoint | Purpose | Auth |
|---|---|---|---|
| POST | `/api/auth/login` | Log in, start session | Public |
| POST | `/api/auth/logout` | End session | Member |
| GET | `/api/auth/me` | Current user info | Member |
| GET/POST | `/api/users` | List/create members | Admin |
| PUT/DELETE | `/api/users/{id}` | Edit/deactivate member | Admin |
| GET/POST | `/api/departments` | List/create | Public GET, Admin POST |
| PUT/DELETE | `/api/departments/{id}` | Edit/delete | Admin |
| GET/POST | `/api/ministries` | Same pattern as departments | — |
| GET/POST | `/api/leadership` | Same pattern | — |
| GET/POST | `/api/sermons` | Same pattern | — |
| GET/POST | `/api/events` | Same pattern | — |
| GET/POST | `/api/resources` | Same pattern | — |
| POST | `/api/payments/initiate` | Start STK Push | Public (guest) or Member |
| POST | `/api/payments/callback` | Safaricom callback receiver | Safaricom only (validated by transaction lookup, not a user session) |
| GET | `/api/payments` | Giving history | Admin |

---

## 9. Authentication & security

- Passwords hashed with PHP's `password_hash()` (bcrypt or argon2i)
- Session-based auth via httpOnly, Secure, SameSite=Lax cookies
- CSRF token issued at login, required on all state-changing requests
- All queries via PDO prepared statements (no raw string interpolation into SQL)
- Rate-limit login attempts to reduce brute-force risk
- **M-Pesa credentials (Consumer Key/Secret, Passkey, Business Short Code) go in a `.env` file or server-level environment variables — never committed to git.** This is the same lesson from the Web3Forms access key that was committed in plaintext in `leadership.php` earlier in this project — worth fixing that at the same time, not just applying the lesson going forward.
- M-Pesa callback validation: don't trust the callback payload blindly — look up the transaction by `checkout_request_id` (which you generated) and only update status if it matches a pending record you created.
- Force HTTPS site-wide once M-Pesa is live; Safaricom requires HTTPS for callback URLs regardless.

---

## 10. Non-functional requirements

- No Node.js required at runtime — only during local build of the React app.
- Public pages must not regress in load time or availability during this work.
- Admin panel must work on both desktop and mobile browsers (church staff may manage content from a phone).
- All new PHP code should follow the existing relative-path convention (no absolute `/TUMSDA/` paths, consistent with prior refactor work).

---

## 11. Project structure

```
tumsdachurch/
├── index.php, about.php, ...        existing public pages — unchanged
├── header.php, footer.php
├── assets/
│
├── api/                             NEW — PHP REST API
│   ├── index.php
│   ├── routes.php
│   ├── config/database.php          PDO connection (pdo_pgsql)
│   ├── controllers/
│   ├── models/
│   └── middleware/RequireAuth.php
│
├── admin/                           NEW — built React output only
│   ├── index.html
│   ├── assets/
│   └── .htaccess                    SPA rewrite rule
│
├── database/
│   └── schema.sql                   full schema from §7
│
└── PROJECT_REQUIREMENTS.md          this file

admin-src/                            React SOURCE — not deployed, dev machine / repo only
├── package.json, vite.config.js
└── src/ (components, pages, services, context)
```

---

## 12. Implementation plan

| Phase | Work | Exit criteria |
|---|---|---|
| 0 | Confirm production host supports PostgreSQL (`pdo_pgsql`) and outbound/inbound HTTPS | Written confirmation from host or a support ticket answer — **do not proceed past Phase 1 without this** |
| 1 | Create PostgreSQL schema (§7) on local WAMP + production | Tables exist, verified via `psql` |
| 2 | Build API core: auth (login/logout/me), session handling, CSRF | Can log in via Postman/curl and hit a protected endpoint |
| 3 | Build content CRUD endpoints: departments, ministries, leadership, sermons, events, resources | Each endpoint tested via curl/Postman |
| 4 | Scaffold React app: login page + protected dashboard shell | Can log in through the actual UI |
| 5 | Build CRUD screens for each content type | Admin can fully manage all six content types through the UI |
| 6 | Member management screens | Admin can create/edit/deactivate members |
| 7 | M-Pesa sandbox integration: STK Push initiate + callback endpoint | Test payment completes successfully in sandbox |
| 8 | Payments UI: give flow (public-facing) + admin giving history | End-to-end sandbox payment visible in admin panel |
| 9 | Security review: CSRF, rate limiting, credential storage audit, rotate the exposed Web3Forms key | Checklist in §9 fully satisfied |
| 10 | M-Pesa production go-live with Safaricom | First real production transaction confirmed |
| 11 | (Optional, future) Public pages read from database | Decided and scheduled separately — not part of this plan's completion criteria |

---

## 13. Testing checklist

- [ ] Auth: login, logout, session expiry, CSRF rejection on missing/invalid token
- [ ] Each CRUD endpoint: create, read, update, delete, and permission checks (member cannot hit admin-only routes)
- [ ] M-Pesa sandbox: successful payment, user-cancelled payment, timeout, duplicate callback delivery
- [ ] Admin panel: all forms validate input before submission; error states are visible, not silent
- [ ] Mobile browser check for both the public site and the admin panel
- [ ] SQL injection spot-check on any endpoint accepting free-text input
- [ ] Confirm `.env`/credentials file is excluded via `.gitignore` and was never committed

---

## 14. Risks, dependencies & open decisions

**Risk 1 — PostgreSQL hosting support (verify first).** Many budget shared PHP hosts only offer MySQL. Confirm with your hosting provider that PostgreSQL databases and the `pdo_pgsql` PHP extension are available in production before this plan proceeds far past Phase 1. If unavailable, the fallback is MySQL — a schema change, not a rewrite, but better decided now than after Phase 6.

**Risk 2 — M-Pesa callback reachability.** Confirm the production domain serves valid HTTPS and is publicly reachable (not behind auth, VPN, or IP allowlisting) — Safaricom's servers must be able to reach it directly.

**Decision needed — member registration model.** Can visitors self-register as members, or are all accounts created by an admin? This affects the auth flow and whether you need email verification.

**Decision needed — guest giving.** Can someone give via M-Pesa without an account, or must they be a logged-in member? Guest giving is simpler for visitors but makes giving history harder to attribute.

**Decision needed — relationship to the existing manual Till popup.** Does the new automated flow replace the manual Till number/name display in `footer.php`'s support popup immediately, or run alongside it during a transition period?

**Decision needed — public site integration timing.** Should `about.php`'s calendar and `leadership.php`'s cards start reading from the new database once it exists, or stay hand-edited PHP for now? Building the admin panel without this creates a system where admins edit data the public site never shows — worth deciding deliberately rather than discovering the gap later.

---

## 15. Appendix — secrets checklist

Before production go-live, confirm none of these ever appear in a git commit:
- [ ] PostgreSQL connection credentials
- [ ] M-Pesa Consumer Key, Consumer Secret, Passkey, Business Short Code
- [ ] Session secret / cookie signing key (if used)
- [ ] The still-outstanding Web3Forms access key in `leadership.php` — rotate this regardless of M-Pesa work, it's been exposed since Phase 2
