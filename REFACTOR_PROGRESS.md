# TUMSDA Church Website Refactor — Progress & Handoff

**Status:** ✅ Phase 1 Complete | 🔄 Phase 2–4 In Progress

---

## ✅ Completed (Committed to `main`)

### 1. **Header/Footer Extraction**
- ✅ `header.php` — shared navigation, mobile side-panel, sticky navbar
- ✅ `footer.php` — unified footer, seven-section branding, all popups (support, mission chair, gallery, contact)
- ✅ Both use **relative asset paths** (`assets/...` not `/TUMSDA/...`)

### 2. **Page Conversion to Shared Template**
- ✅ `index.php` — homepage with hero carousel
- ✅ `about.php` — about, mission/vision, history, beliefs, weekly meetings, calendar
- ✅ `departments.php` — department cards with descriptions
- ✅ `evangelism.php` — mission accordion and details
- ✅ `sermons.php` — featured & playlist sermon embeds
- ✅ `ministries.php` — ministry descriptions
- ✅ `leadership.php` — leadership cards + contact form (currently integrated with Web3Forms)

**All 7 pages now:**
- Use `<?php $currentPage = basename($_SERVER['PHP_SELF']); include 'header.php'; ?>`
- Use `<?php include 'footer.php'; ?>` closing tag
- Have **relative asset paths** throughout

### 3. **Absolute Path Cleanup**
- ✅ Removed `index.html` (redundant, used old `/TUMSDA/` paths)
- ✅ Verified all `.php` files use relative paths

---

## 🔄 Work In Progress (updated)

### 2. **Image Optimization** (IN PROGRESS)

**Current Status:** Guide created and added to the repository as `IMAGE_OPTIMIZATION_GUIDE.md` (see repository root).

**What I added:**
- `IMAGE_OPTIMIZATION_GUIDE.md` — step-by-step instructions, scripts and example commands for batching image compression, generating modern fallbacks (.webp + .jpg), moving originals to `assets/img/original/`, and a suggested `<picture>` markup pattern to use in templates.

**Next steps (implementation):**
1. Run the provided batch script on a development machine (or CI runner) to generate optimized images in `assets/img/` and copy originals into `assets/img/original/`.
2. Replace inline `<img src="assets/img/*.png">` references with `<picture>` blocks where critical hero/carousel images are used (see examples in the guide).
3. Manually verify responsive sizes and visual quality on mobile and desktop.
4. Commit the optimized image files and a short PR describing size savings per-file (attach before/after sizes).

**Expected Impact:** Page load ~30–50% faster, mobile bandwidth reduced.

---

### 3. **Consistent Styling Across Pages** (TODO)

**Current State:**
- Pages share a single stylesheet (`assets/style.css`) but a light audit should be performed to ensure consistent component styles (cards, buttons, inputs, accordions).

**Checklist & small tasks:**
- [ ] Run a visual pass: open each page under `php -S localhost:8000` and check the components listed in the checklist.
- [ ] Add small helper utilities into `assets/style.css` (spacing and typography tokens) to reduce duplication.
- [ ] Standardize form inputs (focus/hover styles) used in `leadership.php` with classes from `style.css`.
- [ ] Create a `style-audit.md` note (optional) listing any inconsistencies found and the required CSS changes.

---

### 4. **Real Formspree / Forms Integration** (QUICK FIX — ACTION RECOMMENDED)

**Current:** `leadership.php` currently posts to Web3Forms using an access_key (live):
```html
<form method="post" action="https://api.web3forms.com/submit">
  <input type="hidden" name="access_key" value="f0ddf1cb-9e8c-494f-a7a1-262385c5a479">
```

**Options & recommended steps:**
- If you prefer Formspree:
  1. Create a Formspree form at https://formspree.io and copy the form ID.
  2. Replace the `action` value with `https://formspree.io/f/<FORM_ID>` and remove the Web3Forms `access_key` input.
  3. Test the form submission and confirm delivery to the configured email or webhook.
- If Web3Forms is acceptable (already live):
  - Keep as-is, but rotate the access key if it was committed accidentally in a public repo (recommended best practice).

**Action item:** Decide which provider to use and update `leadership.php` accordingly. If you want, I can: create a PR that replaces the form action with a Formspree placeholder or (if you provide a Formspree ID) commit it directly.

---

### 5. **Functional Testing** (CRITICAL BEFORE DEPLOYMENT)

**Checklist (expanded):**
- [ ] Navigation: verify active link highlights for each page.
- [ ] Hero Carousel: automatic rotation, manual prev/next, touch swipe on mobile.
- [ ] Popups: support/mission/gallery/contact open & close, accessible via keyboard where applicable.
- [ ] Mobile Menu: hamburger opens side-panel <1024px.
- [ ] Forms: contact form (leadership.php) submits, receives confirmation;
- [ ] External Links: YouTube, WhatsApp, Adventist.org open in new tab.
- [ ] Image Loads: Verify optimized images fallback to JPG/PNG if not supported.
- [ ] Console Errors: fix any JS/CSS console errors.
- [ ] Lighthouse: run Lighthouse and aim for performance/accessibility >80.

**Suggested automated checks:**
- Local manual test: `php -S localhost:8000` then browse pages.
- Optional: Add a GitHub Actions workflow that runs a headless Lighthouse check against a deployed preview or `http-server` in CI.

---

## Deployment Checklist (updated)

Before pushing to production:
- [ ] Images optimized (webp + fallback) — GUIDE created
- [ ] All 7 pages tested locally (no 404, all styles applied)
- [ ] Carousels/popups/menus work on mobile & desktop
- [ ] Contact form connected to chosen provider and tested
- [ ] No console errors (F12 → Console tab)
- [ ] Lighthouse score >80 (if checked)
- [ ] All external links open correctly
- [ ] No trailing `index.html` references in code

---

## Notes for Next Collaborator

1. **This is a feature-branch-ready refactor** — if starting changes, create a branch (`git checkout -b feature/optimization`) before making changes.
2. **Asset paths are relative** — server must be at root of `tumsdachurch/` directory.
3. **No external CDN issues expected** — Bootstrap, Font Awesome, Google Fonts all HTTPS.
4. **PHP version:** Assumes PHP 7.2+ (for `$_SERVER` globals, `include` statements)
5. **No database** — all content is static HTML/PHP, no `.env` or config files needed.
6. **Existing data intact** — all page content, calendar events, leadership bios preserved.

---

**Last Updated:** 2026-07-04 (IMAGE_OPTIMIZATION_GUIDE.md added; image optimization work started)
