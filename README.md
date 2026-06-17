# TravelWithNaomi — Vortex365 Referral Landing Page

A premium travel-brand landing page and lead-capture system for **Naomi**, an independent Vortex365 (Surge365) travel-savings ambassador. Visitors browse destinations, open a modal lead form (pre-filled by the card they clicked), and are forwarded to Naomi's Vortex365 referral link while their details are saved for follow-up. Includes an admin dashboard with lead-source and UTM tracking.

No build step. No npm, no Composer. Upload and run on any PHP 8+ / MySQL host (cPanel shared hosting or Railway).

---

## Tech stack

- **Frontend:** HTML5, Tailwind CSS (CDN), Alpine.js (CDN), Google Fonts (Playfair Display + Jost), vanilla-JS motion (IntersectionObserver, no animation libraries)
- **Backend:** PHP 8+ (PDO)
- **API:** JSON REST layer under `/api/v1` (no framework — see [API.md](API.md))
- **Database:** MySQL, with forward-only SQL migrations (`php migrate.php`)
- **Email:** PHP `mail()` by default, optional SMTP via bundled PHPMailer 6.9.3

---

## Project structure

```
.
├── index.php              # Landing page (hero, destinations, popular trips,
│                          # booking categories, comparison, testimonials,
│                          # how it works, about, modal lead form)
├── submit.php             # HTML form handler: delegates to the shared Leads
│                          # service, then redirects to the referral link
├── api/                   # JSON REST API (see API.md)
│   ├── index.php          # Front controller / router for /api/v1/*
│   ├── .htaccess          # Routes /api/* here, blocks api/lib/
│   └── lib/               # Response, Validator, Leads, Auth, RateLimiter
├── migrations/            # Timestamped, idempotent, forward-only SQL
├── migrate.php            # CLI migration runner (tracks schema_migrations)
├── API.md                 # Full API reference with curl examples
├── privacy.php            # Privacy policy (linked from the footer)
├── 404.php                # Branded error page
├── .htaccess              # Security, caching, error doc (cPanel/Apache)
├── robots.txt
├── sitemap.xml
├── config/
│   ├── db.php             # DB + SMTP config (GIT-IGNORED — create from .example)
│   └── db.php.example     # Tracked template
├── admin/
│   ├── login.php          # Session login + brute-force lockout
│   └── dashboard.php      # Stats, lead-source breakdown, searchable table, CSV export
├── assets/
│   ├── naomi.jpg          # Naomi's About photo
│   ├── favicon.svg
│   └── style.css          # Brand tokens, motion, components
├── libs/phpmailer/        # PHPMailer core files (Exception, PHPMailer, SMTP)
└── setup/
    └── install.sql        # Fresh-install schema (drops + recreates tables)
```

---

## Placeholders to replace before going live

Search the project for each and fill in your real value:

| Placeholder | File(s) | What it is |
|---|---|---|
| `[MY_REFERRAL_LINK]` | `config/db.php` (`APP_REFERRAL_LINK`) | Your Vortex365 referral URL. Set it once here — the landing page, `submit.php`, and the API all read it. |
| `[MY_WHATSAPP_LINK]` | `index.php` | e.g. `https://wa.me/447000000000` |
| `[MY_EMAIL]` | `config/db.php` (`APP_NOTIFY_EMAIL`) | Where lead notifications are sent |
| `[MY_SOCIAL_LINKS]` | `index.php` (footer ×3) | Instagram / Facebook / TikTok URLs |
| `[ADMIN_USERNAME]` / `[ADMIN_PASSWORD]` | `config/db.php` (`APP_ADMIN_*`) | Admin login — used by both the admin dashboard and the API. |
| `[DB_HOST]` `[DB_NAME]` `[DB_USER]` `[DB_PASS]` | `config/db.php` | Database credentials |
| `[SMTP_*]` | `config/db.php` | Optional SMTP (leave `SMTP_ENABLED` false to use `mail()`) |
| Add `assets/naomi.jpg` | `assets/` | Naomi's photo (already included) |

> `config/db.php` is git-ignored so credentials never reach the repo. Copy the template: `cp config/db.php.example config/db.php`, then fill it in.

---

## Database & migrations

The schema is now managed with small, timestamped, forward-only SQL files in
`migrations/`. Each file is idempotent (`CREATE TABLE IF NOT EXISTS` and guarded
`ALTER TABLE`), and applied files are tracked in a `schema_migrations` table.

After configuring `config/db.php`, run the migration runner from the project
root. It applies any pending files in order and is safe to re-run:

```bash
php migrate.php
```

This is the **canonical** way to create/upgrade the database. `setup/install.sql`
is kept for quick fresh installs via phpMyAdmin (note: it drops and recreates
tables, so never run it against a database with live leads).

## API

A JSON REST API lives under `/api/v1` (front controller: `api/index.php`,
routed by `api/.htaccess`). It shares the exact validation + insert + email path
as the HTML form via `api/lib/Leads.php`, so both stay in sync.

Quick check:

```bash
curl -s https://yoursite.com/api/v1/health
```

Public endpoints: `POST /api/v1/leads`, `POST /api/v1/referral-clicks`,
`GET /api/v1/health`. Admin endpoints (session auth) cover login/logout, a
paginated/searchable leads list, single lead, stats, and CSV export. See
[API.md](API.md) for the full reference with request/response shapes, status
codes, and a `curl` example for every endpoint.

## Local preview

```bash
php -S 127.0.0.1:8000
```
Then open http://127.0.0.1:8000. The DB will fail silently without credentials — the page still renders; only form storage needs the database.

---

## Deploy — cPanel shared hosting

1. Download the repo as a ZIP (or `git pull` on the server).
2. **File Manager** → upload into `public_html` (or a subfolder) and extract, keeping the folder structure.
3. **MySQL Databases** → create a database + user, add the user to the database with all privileges (note the host, usually `localhost`).
4. `cp config/db.php.example config/db.php` and fill in `DB_HOST` / `DB_NAME` / `DB_USER` / `DB_PASS`.
5. Create the tables. If you have shell/SSH access, run `php migrate.php` (preferred). Otherwise, in **phpMyAdmin** → select the database → **Import** → `setup/install.sql`.
6. Replace the link/email/social placeholders in `index.php` and `submit.php`, and the admin credentials in `admin/login.php`.
7. Confirm `assets/naomi.jpg` is present.
8. Visit the site, submit a test lead, confirm the redirect to your referral link.
9. Visit `/admin/login.php`, sign in, confirm the lead appears and CSV export works.
10. (Recommended) enable SSL in cPanel, then uncomment the HTTPS redirect block in `.htaccess`.

---

## Deploy — Railway

1. New project → add the **MySQL** plugin.
2. Deploy this repo as a PHP service; start command:
   `php -S 0.0.0.0:$PORT -t .`
3. Set environment variables (the app reads these first, falling back to `config/db.php`):

   | Variable | Source |
   |---|---|
   | `MYSQLHOST` `MYSQLDATABASE` `MYSQLUSER` `MYSQLPASSWORD` `MYSQLPORT` | MySQL plugin → Connect tab |
   | `SMTP_ENABLED` `SMTP_HOST` `SMTP_PORT` `SMTP_USERNAME` `SMTP_PASSWORD` `SMTP_FROM_EMAIL` `SMTP_FROM_NAME` | your mail provider |

4. Create the tables: run `php migrate.php` (e.g. via a one-off command or the
   service shell), or paste `setup/install.sql` into the MySQL plugin's query console.
5. Fill the link/email/admin placeholders and commit (Railway redeploys on push).
6. Note: Railway has no local mail server — leads still save and redirect, but use **SMTP** for email notifications.

---

## Email notifications (optional SMTP)

By default `submit.php` uses PHP `mail()` (works on most cPanel hosts). For reliable delivery (and on Railway), set `SMTP_ENABLED = true` and fill the `SMTP_*` values in `config/db.php` (or env vars). When enabled and complete, notifications send via PHPMailer over TLS; otherwise it falls back to `mail()`. Email failures never block the visitor's redirect.

Gmail needs an **App Password** (2FA on). SendGrid/Mailgun/Postmark use an API key as the password.

---

## Admin area

- `/admin/login.php` — session login, locks for 5 minutes after 5 failed attempts.
- `/admin/dashboard.php` — total leads, referral clicks, last-7-days; a **Lead sources** breakdown; a searchable leads table; and **Export CSV** (includes lead source + all UTM fields).

---

## Tracking

Every lead records:
- **`lead_source`** — which card produced it (e.g. `caribbean-cruise-card`) or `direct-form`.
- **UTM** — `utm_source/medium/campaign/content/term`, captured from the landing URL (persisted in `sessionStorage`).

Campaign links work two ways and pre-fill the form silently:
`?trip=caribbean-cruise` (best for ads/analytics) or `#caribbean-cruise`.
Example: `https://yoursite.com/?utm_source=facebook&utm_campaign=cruise_offer&trip=caribbean-cruise`

### Updating an existing database (migration)

If you imported an earlier version before lead-source/UTM tracking existed, run these once (don't re-import `install.sql` — it drops tables):

```sql
ALTER TABLE leads ADD COLUMN lead_source  VARCHAR(100) DEFAULT 'direct-form';
ALTER TABLE leads ADD COLUMN utm_source   VARCHAR(100) DEFAULT NULL;
ALTER TABLE leads ADD COLUMN utm_medium   VARCHAR(100) DEFAULT NULL;
ALTER TABLE leads ADD COLUMN utm_campaign VARCHAR(100) DEFAULT NULL;
ALTER TABLE leads ADD COLUMN utm_content  VARCHAR(100) DEFAULT NULL;
ALTER TABLE leads ADD COLUMN utm_term     VARCHAR(100) DEFAULT NULL;
```

---

## Notes

- A **Content-Security-Policy** (in `.htaccess`) restricts scripts/styles/images to the CDNs the site actually uses. If you add a new third-party script, font, or image host, add it to the policy or the browser will block it.
- Sessions (admin + API) use hardened cookies (`HttpOnly`, `SameSite=Lax`, and `Secure` once you're on HTTPS).
- Update the domain in `robots.txt` and `sitemap.xml` if your live site differs from `travelwithnaomi.com`.
- `assets/style.css` and `index.php` hold all visual/motion code; the design respects `prefers-reduced-motion`.
- This site is operated by an independent Vortex365 member and is not affiliated with or endorsed by Surge365 corporate.
